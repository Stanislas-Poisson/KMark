<?php

declare(strict_types=1);

namespace KMark\Block;

use KMark\Lines;

/**
 * A block of HTML written in the text, kept as it is up to the next blank line. It is only read when the
 * option asks for it.
 *
 * @internal
 */
final class HtmlBlock implements BlockRule
{
    public function match(Lines $lines, BlockContext $blockContext): ?string
    {
        if (1 !== preg_match('/^ {0,3}<(?:\/?[a-zA-Z][\w-]*[\s>\/]|!--)/', $lines->line())) {
            return null;
        }

        $html = [];

        while (! $lines->eof() && '' !== trim($lines->line())) {
            $html[] = $lines->next();
        }

        return implode("\n", $html);
    }
}
