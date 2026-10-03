<?php

declare(strict_types=1);

namespace KMark;

/**
 * KMark is an adaptation of the Markdown syntax, dedicated to the web,
 * that allows to set an id and CSS classes on every element.
 *
 * Copyright (c) 2013 Stanislas Poisson - MIT license
 * https://stanislas-poisson.fr
 */
final class Convert
{
    private const ALLOWED_SCHEMES = ['http', 'https', 'mailto', 'tel', 'ftp'];

    private string $text = '';

    public function setText(string $text = ''): self
    {
        $this->text = trim($text);

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function convert(): self
    {
        $blocks = [];
        foreach ($this->splitBlocks($this->clean($this->text)) as $block) {
            $blocks[] = $this->renderBlock($block);
        }

        $this->text = implode("\n\n", $blocks);

        return $this;
    }

    /**
     * Unifies the line breaks and keeps at most one blank line between two blocks.
     */
    private function clean(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = $this->replace('/\n *\n/', "\n\n", $text);

        return $this->replace('/\n{3,}/', "\n\n", $text);
    }

    /**
     * Splits the text on the blank lines, except inside a fenced code block.
     *
     * @return list<string>
     */
    private function splitBlocks(string $text): array
    {
        $blocks = [];
        $current = [];
        $inCode = false;

        foreach (explode("\n", $text) as $line) {
            if (1 === preg_match('/^~{2,}\s*$/', $line)) {
                if (!$inCode && [] !== $current) {
                    $blocks[] = implode("\n", $current);
                    $current = [];
                }

                $inCode = !$inCode;
                $current[] = $line;

                if (!$inCode) {
                    $blocks[] = implode("\n", $current);
                    $current = [];
                }

                continue;
            }

            if (!$inCode && '' === trim($line)) {
                if ([] !== $current) {
                    $blocks[] = implode("\n", $current);
                    $current = [];
                }

                continue;
            }

            $current[] = $line;
        }

        if ([] !== $current) {
            $blocks[] = implode("\n", $current);
        }

        return $blocks;
    }

    private function renderBlock(string $block): string
    {
        $lines = explode("\n", $block);

        if (1 === preg_match('/^(#{1,6}) (.*)$/', $lines[0], $heading)) {
            $html = $this->renderHeading(strlen($heading[1]), $heading[2]);
            $rest = array_slice($lines, 1);

            return [] === $rest ? $html : $html . "\n" . $this->renderBlock(implode("\n", $rest));
        }

        if (1 === preg_match('/^(\+|\d+\.)\t/', $lines[0])) {
            return $this->renderList($lines);
        }

        if (1 === preg_match('/^>\t/', $lines[0])) {
            return $this->renderQuote($lines);
        }

        if (count($lines) >= 2 && 1 === preg_match('/^~{2,}\s*$/', $lines[0]) && 1 === preg_match('/^~{2,}\s*$/', $lines[count($lines) - 1])) {
            return $this->renderCode(array_slice($lines, 1, -1));
        }

        if (str_starts_with($lines[0], '|')) {
            return $this->renderTable($lines);
        }

        if (1 === preg_match('/^-{6,}$/', trim($block))) {
            return '<hr>';
        }

        [$text, $attributes] = $this->extractAttributes($block);

        return '<p' . $attributes . '>' . $this->inline($text) . '</p>';
    }

    private function renderHeading(int $level, string $text): string
    {
        [$text, $attributes] = $this->extractAttributes($text);

        return '<h' . $level . $attributes . '>' . $this->inline(trim($text)) . '</h' . $level . '>';
    }

    /**
     * @param list<string> $lines
     */
    private function renderList(array $lines): string
    {
        /** @var list<array{int, string, string}> $items */
        $items = [];

        foreach ($lines as $line) {
            if (1 === preg_match('/^(\t*)(\+|\d+\.)\t(.*)$/', $line, $item)) {
                $items[] = [strlen($item[1]), '+' === $item[2] ? 'ul' : 'ol', $item[3]];

                continue;
            }

            if ([] !== $items) {
                $last = count($items) - 1;
                $items[$last] = [$items[$last][0], $items[$last][1], $items[$last][2] . "\n" . $line];
            }
        }

        $html = '';
        /** @var list<string> $open the type of each opened list, from the outermost */
        $open = [];

        foreach ($items as [$level, $type, $text]) {
            $level = min($level, count($open));

            while (count($open) > $level + 1) {
                $html .= '</li></' . array_pop($open) . '>';
            }

            if (count($open) === $level + 1) {
                $html .= '</li>';

                if ($open[$level] !== $type) {
                    $html .= '</' . array_pop($open) . '><' . $type . '>';
                    $open[] = $type;
                }
            } else {
                $html .= '<' . $type . '>';
                $open[] = $type;
            }

            [$text, $attributes] = $this->extractAttributes($text);
            $html .= '<li' . $attributes . '>' . $this->inline($text);
        }

        while ([] !== $open) {
            $html .= '</li></' . array_pop($open) . '>';
        }

        return $html;
    }

    /**
     * @param list<string> $lines
     */
    private function renderQuote(array $lines): string
    {
        $id = '';
        $classes = [];
        $content = [];

        foreach ($lines as $line) {
            $line = 1 === preg_match('/^>\t(.*)$/', $line, $quote) ? $quote[1] : $line;
            [$line, $lineId, $lineClasses] = $this->parseAttributes($line);

            if ('' === $id) {
                $id = $lineId;
            }

            $classes = [...$classes, ...$lineClasses];
            $content[] = $this->inline($line);
        }

        return '<blockquote' . $this->renderAttributes($id, $classes) . '>' . implode("\n", $content) . '</blockquote>';
    }

    /**
     * @param list<string> $lines
     */
    private function renderCode(array $lines): string
    {
        $content = [];

        foreach ($lines as $line) {
            $content[] = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE));
        }

        return '<code>' . implode("<br />\n", $content) . '</code>';
    }

    /**
     * @param list<string> $lines
     */
    private function renderTable(array $lines): string
    {
        $html = '<table>';

        foreach ($lines as $line) {
            if (!str_starts_with($line, '|') || 1 === preg_match('/^[| -]+$/', $line)) {
                continue;
            }

            preg_match_all('/\| ([^|]+)/', $line, $cells);
            $html .= '<tr>';

            foreach ($cells[1] as $cell) {
                $html .= '<td>' . $this->inline(trim($cell)) . '</td>';
            }

            $html .= '</tr>';
        }

        return $html . '</table>';
    }

    /**
     * Escapes the text, then converts the links and the images.
     */
    private function inline(string $text): string
    {
        $text = htmlspecialchars($text, ENT_NOQUOTES | ENT_SUBSTITUTE);
        $text = preg_replace_callback('/\[([^\]]*)\]:\(([^)]*)\)/', $this->renderLink(...), $text) ?? $text;

        return preg_replace_callback('/!\[([^\]]*)\]\(([^)]*)\)/', $this->renderImage(...), $text) ?? $text;
    }

    /**
     * @param array<int|string, string> $match
     */
    private function renderLink(array $match): string
    {
        [$target, $attributes] = $this->extractAttributes($match[2]);
        $title = '';

        if (1 === preg_match('/^(.*?)\s*"(.*)"$/s', $target, $parts)) {
            $target = $parts[1];
            $title = trim($parts[2]);
        }

        $url = trim($target);

        if (!$this->isSafeUrl($url)) {
            return $match[1];
        }

        $html = '<a href="' . $this->quote($url) . '"';

        if ('' !== $title) {
            $html .= ' title="' . $this->quote($title) . '"';
        }

        return $html . $attributes . '>' . $match[1] . '</a>';
    }

    /**
     * @param array<int|string, string> $match
     */
    private function renderImage(array $match): string
    {
        [$target, $attributes] = $this->extractAttributes($match[2]);
        $url = trim($target);

        if (!$this->isSafeUrl($url)) {
            return $match[1];
        }

        return '<img src="' . $this->quote($url) . '" alt="' . $this->quote($match[1]) . '"' . $attributes . '>';
    }

    /**
     * Accepts the relative URLs and the ones with a known scheme, so that
     * "javascript:" and "data:" never reach an attribute.
     */
    private function isSafeUrl(string $url): bool
    {
        $compact = $this->replace('/[\x00-\x20]+/', '', $url);

        if (1 !== preg_match('/^([a-z][a-z0-9+.\-]*):/i', $compact, $scheme)) {
            return true;
        }

        return in_array(strtolower($scheme[1]), self::ALLOWED_SCHEMES, true);
    }

    /**
     * The text is already escaped, only the double quote can end an attribute.
     */
    private function quote(string $value): string
    {
        return str_replace('"', '&quot;', $value);
    }

    /**
     * Takes the "{#id .class}" block off the end of a text.
     *
     * @return array{string, string} the text, and the attributes ready to be written in a tag
     */
    private function extractAttributes(string $text): array
    {
        [$text, $id, $classes] = $this->parseAttributes($text);

        return [$text, $this->renderAttributes($id, $classes)];
    }

    /**
     * @return array{string, string, list<string>} the text, the id and the classes
     */
    private function parseAttributes(string $text): array
    {
        if (1 !== preg_match('/^(.*?)\s*\{([#.\w\s-]*)\}\s*$/s', $text, $match)) {
            return [$text, '', []];
        }

        $id = '';
        $classes = [];
        $tokens = preg_split('/\s+/', trim($match[2]), -1, PREG_SPLIT_NO_EMPTY);

        if (false === $tokens || [] === $tokens) {
            return [$text, '', []];
        }

        foreach ($tokens as $token) {
            $name = substr($token, 1);

            if (1 !== preg_match('/^[\w-]+$/', $name) || !in_array($token[0], ['#', '.'], true)) {
                return [$text, '', []];
            }

            if ('#' === $token[0]) {
                $id = '' === $id ? $name : $id;
            } else {
                $classes[] = $name;
            }
        }

        return [$match[1], $id, $classes];
    }

    /**
     * @param list<string> $classes
     */
    private function renderAttributes(string $id, array $classes): string
    {
        $html = '' === $id ? '' : ' id="' . $id . '"';

        return [] === $classes ? $html : $html . ' class="' . implode(' ', $classes) . '"';
    }

    private function replace(string $pattern, string $replacement, string $subject): string
    {
        return preg_replace($pattern, $replacement, $subject) ?? $subject;
    }
}
