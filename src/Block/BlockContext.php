<?php

declare(strict_types=1);

namespace KMark\Block;

use KMark\Inline\InlineParser;

/**
 * What a rule needs to read a block: the parsers of the blocks and of the text, and whether the paragraphs are
 * written without <p> (in a tight list).
 *
 * @internal
 */
final readonly class BlockContext
{
    public function __construct(
        public BlockParser $blocks,
        public InlineParser $inline,
        public bool $tight = false,
    ) {}
}
