<?php

declare(strict_types=1);

namespace KMark;

/**
 * Renders the lines of a list: "+" for an unordered item, "1." for an ordered
 * one, one more tab for each level.
 *
 * @internal
 */
final readonly class ListRenderer
{
    public function __construct(private InlineRenderer $inline) {}

    /**
     * @param list<string> $lines
     */
    public function render(array $lines): string
    {
        $html = '';

        /** @var list<string> $open the type of each opened list, from the outermost */
        $open = [];

        foreach ($this->items($lines) as [$level, $type, $text]) {
            $level = min($level, count($open));

            while (count($open) > $level + 1) {
                $html .= '</li></' . array_pop($open) . '>';
            }

            if (count($open) === $level + 1) {
                $html .= '</li>';

                if ($open[$level] !== $type) {
                    $html .= '</' . array_pop($open) . '><' . $type . '>';
                    $open[] = $type;
                }
            }
            else {
                $html .= '<' . $type . '>';
                $open[] = $type;
            }

            [$text, $attributes] = Attributes::extract($text);
            $html .= '<li' . $attributes->render() . '>' . $this->inline->render($text);
        }

        while ([] !== $open) {
            $html .= '</li></' . array_pop($open) . '>';
        }

        return $html;
    }

    /**
     * @param list<string> $lines
     *
     * @return list<array{int, string, string}> the level, the type ("ul" or "ol") and the text of each item
     */
    private function items(array $lines): array
    {
        $items = [];

        foreach ($lines as $line) {
            if (1 === preg_match('/^(\t*)(\+|\d+\.)\t(.*)$/', $line, $item)) {
                $items[] = [strlen($item[1]), '+' === $item[2] ? 'ul' : 'ol', $item[3]];

                continue;
            }

            if ([] !== $items) {
                $last         = count($items) - 1;
                $items[$last] = [$items[$last][0], $items[$last][1], $items[$last][2] . "\n" . $line];
            }
        }

        return $items;
    }
}
