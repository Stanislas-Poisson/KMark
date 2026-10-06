<?php

declare(strict_types=1);

namespace KMark\Block;

use KMark\Lines;

/**
 * Reads the lines of a list item: the ones that are indented as far as its text, the blank lines between them,
 * and the lines of text that go on with a paragraph without being indented.
 *
 * @internal
 */
final class ItemReader
{
    /**
     * The number of blank lines from here.
     */
    public function blanks(Lines $lines): int
    {
        $count = 0;

        while ('' === trim($lines->peek($count) ?? 'x')) {
            $count++;
        }

        return $count;
    }

    public function read(Lines $lines, ListMarker $listMarker): ListItem
    {
        $lines->skip();

        $itemBuilder = new ItemBuilder($listMarker->content);

        while (! $lines->eof() && $this->advance($lines, $itemBuilder, $listMarker->contentIndent)) {
        }

        return $itemBuilder->build();
    }

    /**
     * Reads what follows in the item, or tells that the item is over.
     */
    private function advance(Lines $lines, ItemBuilder $itemBuilder, int $indent): bool
    {
        $blanks = $this->blanks($lines);
        $line   = $lines->peek($blanks);

        if (null === $line || $this->ended($line, $indent, $blanks)) {
            return false;
        }

        if ($this->indentOf($line) >= $indent) {
            $itemBuilder->indented($blanks, substr($line, $indent));
            $lines->skip($blanks + 1);

            return true;
        }

        if (! $this->goesOn($line, $itemBuilder->last())) {
            return false;
        }

        $itemBuilder->lazy(ltrim($line));
        $lines->skip();

        return true;
    }

    /**
     * Blank lines, then a line that is not indented as far as the text of the item.
     */
    private function ended(string $line, int $indent, int $blanks): bool
    {
        return 0 < $blanks && $this->indentOf($line) < $indent;
    }

    private function goesOn(string $line, string $last): bool
    {
        return '' !== trim($last)
            && ! Interrupts::line($line)
            && ! ListMarker::read($line) instanceof ListMarker;
    }

    private function indentOf(string $line): int
    {
        return strlen($line) - strlen(ltrim($line, ' '));
    }
}
