<?php

declare(strict_types=1);

namespace KMark;

/**
 * Small helpers to write HTML.
 *
 * @internal
 */
final class Html
{
    /**
     * The value of an attribute.
     */
    public static function attribute(string $value): string
    {
        return str_replace('"', '&quot;', $value);
    }

    /**
     * The text with its special characters written as entities, except the entities that are already there.
     */
    public static function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
    }

    /**
     * The text of a code span or of a code block: every special character is written as an entity.
     */
    public static function escapeCode(string $text): string
    {
        return htmlspecialchars($text, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Adds attributes to the first tag of an HTML block, with the ones it already has: the id of the first
     * wins, the classes are added to each other.
     */
    public static function withAttributes(string $html, Attributes $attributes): string
    {
        $pattern = '/^<([a-z0-9]+)((?:\s+[a-z-]+="[^"]*")*)>/';

        if ('' === $attributes->render() || 1 !== preg_match($pattern, $html, $tag)) {
            return $html;
        }

        $id      = '';
        $classes = [];
        $rest    = preg_replace_callback(
            '/\s+(id|class)="([^"]*)"/',
            static function (array $match) use (&$id, &$classes): string {
                if ('id' === $match[1]) {
                    $id = $match[2];

                    return '';
                }

                $parts   = preg_split('/\s+/', $match[2]);
                $classes = false === $parts ? [] : $parts;

                return '';
            },
            $tag[2],
        ) ?? $tag[2];

        $merged = (new Attributes($id, $classes))->merge($attributes);

        return '<' . $tag[1] . $merged->render() . $rest . '>' . substr($html, strlen($tag[0]));
    }
}
