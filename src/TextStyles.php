<?php

declare(strict_types=1);

namespace KMark;

/**
 * Converts the styles of a text that is already escaped: "* bold *", "- italic -",
 * "_ underline _" and "/ strikethrough /", and the line break (two spaces at the end of a line).
 *
 * The markers are put around the words with a space inside, and they can be combined
 * by closing them in the reverse order: "_-* text *-_" gives a span with the classes "u i b".
 *
 * @internal
 */
final class TextStyles
{
    private const CLASSES = ['*' => 'b', '_' => 'u', '-' => 'i', '/' => 'd'];

    public function render(string $text): string
    {
        $text = preg_replace_callback('/(?<!\w)([*_\-\/]+) (\S(?:[^\n]*?\S)?) ([*_\-\/]+)(?!\w)/', $this->style(...), $text) ?? $text;

        return preg_replace('/ {2,}\n/', "<br>\n", $text) ?? $text;
    }

    /**
     * @param array<int|string, string> $match
     */
    private function style(array $match): string
    {
        if (strrev($match[1]) !== $match[3]) {
            return $match[0];
        }

        $classes = [];

        foreach (str_split($match[1]) as $marker) {
            $classes[self::CLASSES[$marker]] = true;
        }

        return '<span class="' . implode(' ', array_keys($classes)) . '">' . $match[2] . '</span>';
    }
}
