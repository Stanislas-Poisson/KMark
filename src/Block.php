<?php

declare(strict_types=1);

namespace KMark;

/**
 * A kind of block of text: a heading, a list, a quote, a code block, a table, a rule or a paragraph.
 *
 * @internal
 */
interface Block
{
    /**
     * @param list<string> $lines the lines of the block
     */
    public function matches(array $lines): bool;

    /**
     * @param list<string> $lines the lines of the block
     */
    public function render(array $lines): string;
}
