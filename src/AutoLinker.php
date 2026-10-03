<?php

declare(strict_types=1);

namespace KMark;

/**
 * Turns the bare "http://" and "https://" URLs of a text that is already escaped into links.
 *
 * @internal
 */
final class AutoLinker
{
    public function render(string $text): string
    {
        return preg_replace_callback('~\bhttps?://(?:(?!&lt;|&gt;)[^\s<>"])+~i', $this->link(...), $text) ?? $text;
    }

    /**
     * @param array<int|string, string> $match
     */
    private function link(array $match): string
    {
        $url = $match[0];
        $trailing = '';

        while (1 === preg_match('/[.,;:!?)]$/', $url) && !$this->isPartOfUrl($url)) {
            $trailing = substr($url, -1) . $trailing;
            $url = substr($url, 0, -1);
        }

        return '<a href="' . $url . '">' . $url . '</a>' . $trailing;
    }

    /**
     * A closing parenthesis belongs to the URL when it closes one that the URL opened,
     * and a semicolon belongs to it when it ends an escaped character such as "&amp;".
     */
    private function isPartOfUrl(string $url): bool
    {
        if (str_ends_with($url, ')')) {
            return substr_count($url, '(') >= substr_count($url, ')');
        }

        return str_ends_with($url, ';') && 1 === preg_match('/&(?:amp|lt|gt);$/', $url);
    }
}
