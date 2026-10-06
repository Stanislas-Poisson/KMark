<?php

declare(strict_types=1);

namespace KMark\Block;

/**
 * The lines of a list item that is being read.
 *
 * @internal
 */
final class ItemBuilder
{
    private string $last;

    /**
     * @var list<string>
     */
    private array $lines;

    private bool $spaced = false;

    public function __construct(string $first)
    {
        $this->lines = [$first];
        $this->last  = $first;
    }

    public function build(): ListItem
    {
        return new ListItem($this->lines, $this->spaced);
    }

    /**
     * Blank lines, then a line of the item without its indentation.
     */
    public function indented(int $blanks, string $line): void
    {
        $this->spaced = $this->spaced || (0 < $blanks && ! $this->nested());
        $this->lines  = [...$this->lines, ...array_fill(0, $blanks, ''), $line];
        $this->last   = $line;
    }

    public function last(): string
    {
        return $this->last;
    }

    /**
     * A line that goes on with a paragraph without being indented.
     */
    public function lazy(string $line): void
    {
        $this->lines[] = $line;
        $this->last    = $line;
    }

    /**
     * Whether a nested list has started in the item: the blank lines after it belong to that list.
     */
    private function nested(): bool
    {
        foreach (array_slice($this->lines, 1) as $line) {
            if (ListMarker::read($line) instanceof ListMarker) {
                return true;
            }
        }

        return false;
    }
}
