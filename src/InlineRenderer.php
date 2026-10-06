<?php

declare(strict_types=1);

namespace KMark;

/**
 * Escapes a text, then converts its links, its images, its styles and, if asked, its bare URLs.
 *
 * @internal
 */
final readonly class InlineRenderer
{
    /**
     * A backslash and a character that can be escaped: a marker, a bracket, a brace, "|", "!", "#", "+" or ">"
     * (already written "&gt;" at this point).
     */
    private const string ESCAPE = '/\\\\(&gt;|[\\\\*\-_~\[\](){}|!#+>])/';

    private const string ESCAPE_END = "\x04";

    private const string ESCAPE_START = "\x03";

    public function __construct(private Options $options = new Options()) {}

    public function render(string $text): string
    {
        $text = str_replace([self::ESCAPE_START, self::ESCAPE_END], '', $text);

        if (! $this->options->unsafeAllowRawHtml) {
            $text = htmlspecialchars($text, ENT_NOQUOTES | ENT_SUBSTITUTE);
        }

        // A backslash before a marker writes the marker as it is. The characters are put aside, so that
        // nothing reads them, and written back at the end.
        $text = preg_replace_callback(self::ESCAPE, $this->protectEscape(...), $text) ?? $text;

        // The URL of a link or of an image can hold one level of parentheses.
        $text = preg_replace_callback('/\[([^\]]*)\]:\(((?:[^()]|\([^()]*\))*)\)/', $this->link(...), $text)  ?? $text;
        $text = preg_replace_callback('/!\[([^\]]*)\]\(((?:[^()]|\([^()]*\))*)\)/', $this->image(...), $text) ?? $text;

        // The links and the tags are left as they are: the text of a link is not styled, and
        // the HTML that is allowed to stay in the text is not read.
        $segments = preg_split('/(<a\b.*?<\/a>|<[^>]*>)/s', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $segments = false === $segments ? [$text] : $segments;

        $textStyles   = new TextStyles($this->options->styleClasses);
        $urls         = new Urls();

        foreach ($segments as $index => $segment) {
            if (0 === $index % 2) {
                [$segment, $found] = $urls->protect($segment);
                $segments[$index]  = $urls->restore($textStyles->render($segment), $found, $this->options->autoLinks);
            }
        }

        return $this->restoreEscapes(implode('', $segments));
    }

    /**
     * @param array<int|string, string> $match
     */
    private function image(array $match): string
    {
        [$target, $attributes] = Attributes::extract($match[2]);
        $url                   = trim($target);

        if (! $this->isSafeUrl($url)) {
            return $match[1];
        }

        return '<img src="' . $this->quote($url) . '" alt="' . $this->quote($match[1]) . '"'
            . $attributes->render() . '>';
    }

    /**
     * Accepts the relative URLs and the ones with a known scheme, so that
     * "javascript:" and "data:" never reach an attribute.
     */
    private function isSafeUrl(string $url): bool
    {
        $compact = preg_replace('/[\x00-\x20]+/', '', $url) ?? '';

        if (1 !== preg_match('/^([a-z][a-z0-9+.\-]*):/i', $compact, $scheme)) {
            return true;
        }

        return in_array(strtolower($scheme[1]), $this->options->allowedSchemes, true);
    }

    /**
     * @param array<int|string, string> $match
     */
    private function link(array $match): string
    {
        [$target, $attributes] = Attributes::extract($match[2]);
        $title                 = '';

        if (1 === preg_match('/^(.*?)\s*"(.*)"$/s', $target, $parts)) {
            $target = $parts[1];
            $title  = trim($parts[2]);
        }

        $url = trim($target);

        if (! $this->isSafeUrl($url)) {
            return $match[1];
        }

        $html = '<a href="' . $this->quote($url) . '"';

        if ('' !== $title) {
            $html .= ' title="' . $this->quote($title) . '"';
        }

        return $html . $attributes->render() . '>' . $match[1] . '</a>';
    }

    /**
     * @param array<int|string, string> $match
     */
    private function protectEscape(array $match): string
    {
        return self::ESCAPE_START . ord('&gt;' === $match[1] ? '>' : $match[1]) . self::ESCAPE_END;
    }

    /**
     * The text is already escaped, only the double quote can end an attribute.
     */
    private function quote(string $value): string
    {
        return str_replace('"', '&quot;', $value);
    }

    private function restoreEscapes(string $text): string
    {
        return preg_replace_callback(
            '/' . self::ESCAPE_START . '(\d+)' . self::ESCAPE_END . '/',
            static fn (array $match): string => htmlspecialchars(chr((int) $match[1]), ENT_NOQUOTES),
            $text,
        ) ?? $text;
    }
}
