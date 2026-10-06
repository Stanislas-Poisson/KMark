<?php

declare(strict_types=1);

namespace KMark\Block;

use KMark\Lines;

/**
 * A kind of block: a heading, a code block, a quote, a list, a table, a rule, a paragraph.
 *
 * @internal
 */
interface BlockRule
{
    /**
     * Reads the block that starts at the current line and gives its HTML, or null when the line starts no such
     * block, in which case nothing has been read.
     */
    public function match(Lines $lines, BlockContext $blockContext): ?string;
}
