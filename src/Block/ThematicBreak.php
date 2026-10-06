<?php

declare(strict_types=1);

namespace KMark\Block;

use KMark\Lines;

/**
 * Three characters or more, "-", "*" or "_", alone on a line.
 *
 * @internal
 */
final class ThematicBreak implements BlockRule
{
    public function match(Lines $lines, BlockContext $blockContext): ?string
    {
        if (1 !== preg_match('/^ {0,3}([-*_])(?:[ ]*\1){2,}[ ]*$/', $lines->line())) {
            return null;
        }

        $lines->skip();

        return '<hr>';
    }
}
