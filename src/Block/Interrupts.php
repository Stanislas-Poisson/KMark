<?php

declare(strict_types=1);

namespace KMark\Block;

/**
 * Tells whether a line starts a block that can interrupt a paragraph, a quote or a list item.
 *
 * @internal
 */
final class Interrupts
{
    private const array STARTS = [
        '/^ {0,3}#{1,6}(?:[ ]|$)/',
        '/^ {0,3}(?:`{3,}|~{3,})/',
        '/^ {0,3}([-*_])(?:[ ]*\1){2,}[ ]*$/',
        '/^ {0,3}>/',
        '/^ {0,3}[-*+](?:[ ]|$)/',
        '/^ {0,3}1[.)](?:[ ]|$)/',
        // A line of its own, "{#id .class}", ends the block before it and gives it its attributes.
        '/^\s*\{[#.][^}]*\}\s*$/',
    ];

    public static function line(string $line): bool
    {
        foreach (self::STARTS as $pattern) {
            if (1 === preg_match($pattern, $line)) {
                return true;
            }
        }

        return false;
    }
}
