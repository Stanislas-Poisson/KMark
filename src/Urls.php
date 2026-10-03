<?php

declare(strict_types=1);

namespace KMark;

/**
 * Finds the bare "http://" and "https://" URLs of a text that is already escaped, so that the
 * styles never change them, then writes them back as they are or as links.
 *
 * @internal
 */
final class Urls
{
    private const START = "\x01";

    private const END = "\x02";

    /**
     * Replaces each URL with a token, without the punctuation and the style markers
     * that follow it.
     *
     * @return array{string, list<string>} the text and the URLs
     */
    public function protect(string $text): array
    {
        $urls = [];
        $text = str_replace([self::START, self::END], '', $text);
        $text = preg_replace_callback('~\bhttps?://(?:(?!&lt;|&gt;)[^\s<>"])+~i', function (array $match) use (&$urls): string {
            [$url, $trailing] = $this->split($match[0]);
            $urls[] = $url;

            return self::START . (count($urls) - 1) . self::END . $trailing;
        }, $text) ?? $text;

        return [$text, $urls];
    }

    /**
     * @param list<string> $urls
     */
    public function restore(string $text, array $urls, bool $asLinks): string
    {
        return preg_replace_callback('/' . self::START . '(\d+)' . self::END . '/', static function (array $match) use ($urls, $asLinks): string {
            $url = $urls[(int) $match[1]] ?? '';

            return $asLinks ? '<a href="' . $url . '">' . $url . '</a>' : $url;
        }, $text) ?? $text;
    }

    /**
     * @return array{string, string} the URL and what follows it
     */
    private function split(string $url): array
    {
        $trailing = '';

        while (1 === preg_match('/[.,;:!?)*_~-]$/', $url) && !$this->endsTheUrl($url)) {
            $trailing = substr($url, -1) . $trailing;
            $url = substr($url, 0, -1);
        }

        return [$url, $trailing];
    }

    /**
     * A closing parenthesis ends the URL when it closes one that the URL opened,
     * and a semicolon when it ends an escaped character such as "&amp;".
     */
    private function endsTheUrl(string $url): bool
    {
        if (str_ends_with($url, ')')) {
            return substr_count($url, '(') >= substr_count($url, ')');
        }

        return str_ends_with($url, ';') && 1 === preg_match('/&(?:amp|lt|gt);$/', $url);
    }
}
