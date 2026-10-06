<?php

declare(strict_types=1);

namespace KMark\Inline;

use KMark\Html;
use KMark\Stash;

/**
 * A URL or an email address between < and >.
 *
 * @internal
 */
final readonly class AutoLinks
{
    public function __construct(private SafeUrls $safeUrls) {}

    public function protect(string $text, Stash $stash): string
    {
        $text = preg_replace_callback(
            '/<([a-z][a-z0-9+.\-]*:[^\s<>]*)>/i',
            fn (array $match): string => $this->link($match[1], $match[0], $stash),
            $text,
        ) ?? $text;

        return preg_replace_callback(
            '/<([^\s@<>]+@[^\s@<>]+\.[^\s@<>]+)>/',
            static function (array $match) use ($stash): string {
                $address = Html::escape($match[1]);

                return $stash->put('<a href="mailto:' . Html::attribute($address) . '">' . $address . '</a>');
            },
            $text,
        ) ?? $text;
    }

    private function link(string $url, string $written, Stash $stash): string
    {
        if (! $this->safeUrls->isSafe($url)) {
            return $written;
        }

        $escaped = Html::escape($url);

        return $stash->put('<a href="' . Html::attribute($escaped) . '">' . $escaped . '</a>');
    }
}
