<?php

declare(strict_types=1);

namespace KMark\Block;

use KMark\Attributes;
use KMark\Lines;

/**
 * Bulleted and numbered lists, nested by indentation. A list is loose when a blank line separates its items or
 * the blocks of an item: its text is then in paragraphs; otherwise the text is written without <p>.
 *
 * @internal
 */
final readonly class Lists implements BlockRule
{
    public function __construct(private ItemReader $itemReader = new ItemReader()) {}

    public function match(Lines $lines, BlockContext $blockContext): ?string
    {
        $first = ListMarker::read($lines->peek());

        if (! $first instanceof ListMarker) {
            return null;
        }

        [$items, $loose] = $this->items($lines, $first);

        $tag   = $first->ordered ? 'ol' : 'ul';
        $start = $first->ordered && 1 !== $first->start ? ' start="' . $first->start . '"' : '';
        $html  = array_map(fn (ListItem $listItem): string => $this->item($listItem, $loose, $blockContext), $items);

        return '<' . $tag . $start . ">\n" . implode("\n", $html) . "\n</" . $tag . '>';
    }

    /**
     * The first line of an item can end with "{#id .class}", the attributes of the item.
     *
     * @return array{string, Attributes}
     */
    private function attributes(string $first): array
    {
        if (1 === preg_match('/^(?:#{1,6}[ ]|`{3}|~{3})/', $first)) {
            return [$first, new Attributes()];
        }

        return Attributes::extract($first);
    }

    private function continues(?string $line, ListMarker $listMarker): bool
    {
        $marker = ListMarker::read($line);

        return $marker instanceof ListMarker && $marker->sameList($listMarker);
    }

    private function item(ListItem $listItem, bool $loose, BlockContext $blockContext): string
    {
        [$first, $attributes] = $this->attributes($listItem->lines[0]);
        $blocks               = $blockContext->blocks->blocks([$first, ...array_slice($listItem->lines, 1)], ! $loose);
        $open                 = '<li' . $attributes->render() . '>';

        if ([] === $blocks) {
            return $open . '</li>';
        }

        $plain = str_starts_with($blocks[0], Paragraph::TIGHT);
        $html  = str_replace(Paragraph::TIGHT, '', implode("\n", $blocks));

        if ($loose || ! $plain) {
            return $open . "\n" . $html . "\n</li>";
        }

        return 1 === count($blocks) ? $open . $html . '</li>' : $open . $html . "\n</li>";
    }

    /**
     * @return array{list<ListItem>, bool} the items, and whether the list is loose
     */
    private function items(Lines $lines, ListMarker $listMarker): array
    {
        $items = [];
        $loose = false;

        while ($this->continues($lines->peek(), $listMarker)) {
            $item    = $this->itemReader->read($lines, ListMarker::read($lines->peek()) ?? $listMarker);
            $items[] = $item;
            $blanks  = $this->itemReader->blanks($lines);
            $loose   = $loose || $item->spaced;

            if (! $this->continues($lines->peek($blanks), $listMarker)) {
                break;
            }

            $loose = $loose || 0 < $blanks;
            $lines->skip($blanks);
        }

        return [$items, $loose];
    }
}
