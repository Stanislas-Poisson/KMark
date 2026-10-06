<?php

declare(strict_types=1);

namespace KMark\Inline;

use KMark\Html;
use KMark\Stash;

/**
 * The "http://" and "https://" URLs written in a text. They become links, or stay as text when the option
 * says so; either way nothing reads them again, so the markers in a URL are not styles.
 *
 * @internal
 */
final readonly class BareUrls
{
    public function __construct(private bool $asLinks) {}

    public function render(string $text, Stash $stash): string
    {
        return preg_replace_callback(
            '~\bhttps?://(?:(?!&lt;|&gt;)[^\s<>"\x1a\x1b])+~i',
            function (array $match) use ($stash): string {
                [$url, $trailing] = $this->split($match[0]);
                $html             = $this->asLinks ? '<a href="' . Html::attribute($url) . '">' . $url . '</a>' : $url;

                return $stash->put($html) . $trailing;
            },
            $text,
        ) ?? $text;
    }

    /**
     * A closing parenthesis or an entity that the URL did not open ends it.
     */
    private function endsTheUrl(string $url): bool
    {
        if (str_ends_with($url, ')')) {
            return substr_count($url, '(') >= substr_count($url, ')');
        }

        return str_ends_with($url, ';') && 1 === preg_match('/&(?:amp|lt|gt);$/', $url);
    }

    /**
     * The URL, and the punctuation at its end that belongs to the sentence.
     *
     * @return array{string, string}
     */
    private function split(string $url): array
    {
        $trailing = '';

        while (1 === preg_match('/[.,;:!?)*_~-]$/', $url) && ! $this->endsTheUrl($url)) {
            $trailing = substr($url, -1) . $trailing;
            $url      = substr($url, 0, -1);
        }

        return [$url, $trailing];
    }
}
