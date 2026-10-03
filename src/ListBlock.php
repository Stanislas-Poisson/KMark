<?php

declare(strict_types=1);

namespace KMark;

/**
 * A list: "+" for an unordered item and "1." for an ordered one, followed by a tab, and one more tab for each level.
 *
 * @internal
 */
final readonly class ListBlock implements Block
{
    public function __construct(private InlineRenderer $inlineRenderer) {}

    /**
     * @param list<string> $lines
     */
    public function matches(array $lines): bool
    {
        return 1 === preg_match('/^(\+|\d+\.)\t/', $lines[0]);
    }

    /**
     * @param list<string> $lines
     */
    public function render(array $lines): string
    {
        $html  = '';
        $lists = new OpenLists();

        foreach ($this->items($lines) as [$level, $type, $text]) {
            [$text, $attributes] = Attributes::extract($text);
            $html .= $lists->enter($level, $type) . '<li' . $attributes->render() . '>'
                . $this->inlineRenderer->render($text);
        }

        return $html . $lists->closeAll();
    }

    /**
     * @param list<string> $lines
     *
     * @return list<array{int, string, string}> the level, the type ("ul" or "ol") and the text of each item
     */
    private function items(array $lines): array
    {
        $items   = [];
        $pending = null;

        foreach ($lines as $line) {
            if (1 === preg_match('/^(\t*)(\+|\d+\.)\t(.*)$/', $line, $item)) {
                $items[] = $pending;
                $pending = [strlen($item[1]), '+' === $item[2] ? 'ul' : 'ol', $item[3]];
            }
            elseif (null !== $pending) {
                $pending[2] .= "\n" . $line;
            }
        }

        $items[] = $pending;

        return array_values(array_filter($items));
    }
}
