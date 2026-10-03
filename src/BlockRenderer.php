<?php

declare(strict_types=1);

namespace KMark;

/**
 * Renders a block of text: a heading, a list, a quote, a code block, a table,
 * a rule or a paragraph.
 *
 * @internal
 */
final readonly class BlockRenderer
{
    private const FENCE = '/^~{2,}\s*$/';

    public function __construct(
        private InlineRenderer $inline,
        private ListRenderer $lists,
    ) {
    }

    public function render(string $block): string
    {
        $lines = explode("\n", $block);

        if (1 === preg_match('/^(#{1,6}) (.*)$/', $lines[0], $heading)) {
            $html = $this->heading(strlen($heading[1]), $heading[2]);
            $rest = array_slice($lines, 1);

            return [] === $rest ? $html : $html . "\n" . $this->render(implode("\n", $rest));
        }

        if (1 === preg_match('/^(\+|\d+\.)\t/', $lines[0])) {
            return $this->lists->render($lines);
        }

        if (1 === preg_match('/^>\t/', $lines[0])) {
            return $this->quote($lines);
        }

        if (count($lines) >= 2 && 1 === preg_match(self::FENCE, $lines[0]) && 1 === preg_match(self::FENCE, $lines[count($lines) - 1])) {
            return $this->code(array_slice($lines, 1, -1));
        }

        if (str_starts_with($lines[0], '|')) {
            return $this->table($lines);
        }

        if (1 === preg_match('/^-{6,}$/', trim($block))) {
            return '<hr>';
        }

        [$text, $attributes] = Attributes::extract($block);

        return '<p' . $attributes->render() . '>' . $this->inline->render($text) . '</p>';
    }

    private function heading(int $level, string $text): string
    {
        [$text, $attributes] = Attributes::extract($text);

        return '<h' . $level . $attributes->render() . '>' . $this->inline->render(trim($text)) . '</h' . $level . '>';
    }

    /**
     * @param list<string> $lines
     */
    private function quote(array $lines): string
    {
        $attributes = new Attributes();
        $content = [];

        foreach ($lines as $line) {
            $line = 1 === preg_match('/^>\t(.*)$/', $line, $quote) ? $quote[1] : $line;
            [$line, $lineAttributes] = Attributes::extract($line);
            $attributes = $attributes->merge($lineAttributes);
            $content[] = $this->inline->render($line);
        }

        return '<blockquote' . $attributes->render() . '>' . implode("\n", $content) . '</blockquote>';
    }

    /**
     * @param list<string> $lines
     */
    private function code(array $lines): string
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
    private function table(array $lines): string
    {
        $html = '<table>';

        foreach ($lines as $line) {
            if (!str_starts_with($line, '|') || 1 === preg_match('/^[| -]+$/', $line)) {
                continue;
            }

            preg_match_all('/\| ([^|]+)/', $line, $cells);
            $html .= '<tr>';

            foreach ($cells[1] as $cell) {
                $html .= '<td>' . $this->inline->render(trim($cell)) . '</td>';
            }

            $html .= '</tr>';
        }

        return $html . '</table>';
    }
}
