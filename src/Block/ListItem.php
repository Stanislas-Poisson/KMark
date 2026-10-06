<?php

declare(strict_types=1);

namespace KMark\Block;

/**
 * The lines of one list item, without their indentation, and whether a blank line separates its blocks.
 *
 * @internal
 */
final readonly class ListItem
{
    /**
     * @param list<string> $lines
     */
    public function __construct(public array $lines, public bool $spaced) {}
}
