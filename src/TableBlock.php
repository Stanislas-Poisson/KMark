<?php

declare(strict_types=1);

namespace KMark;

/**
 * A table: lines that start with "|". A line of dashes ends the head: the lines before it are the
 * header cells, the ones after it are the rows, and the colons of the line of dashes align the
 * columns (":---" left, "---:" right, ":---:" centre). Without a line of dashes, every cell is a "td".
 *
 * @internal
 */
final readonly class TableBlock implements Block
{
    public function __construct(private InlineRenderer $inlineRenderer) {}

    /**
     * @param list<string> $lines
     */
    public function matches(array $lines): bool
    {
        return str_starts_with($lines[0], '|');
    }

    /**
     * @param list<string> $lines
     */
    public function render(array $lines): string
    {
        $separator = $this->firstSeparator($lines);
        $alignment = null === $separator ? [] : $this->alignment($lines[$separator]);

        if (null === $separator || 0 === $separator) {
            return '<table>' . $this->rows($lines, 'td', $alignment) . '</table>';
        }

        return '<table><thead>' . $this->rows(array_slice($lines, 0, $separator), 'th', $alignment) . '</thead>'
            . '<tbody>' . $this->rows(array_slice($lines, $separator + 1), 'td', $alignment) . '</tbody></table>';
    }

    /**
     * @return list<string> the alignment of each column: "left", "right", "center", or "" for none
     */
    private function alignment(string $separator): array
    {
        $alignment = [];

        foreach ($this->separatorCells($separator) as $cell) {
            $alignment[] = match (true) {
                str_starts_with($cell, ':') && str_ends_with($cell, ':') => 'center',
                str_starts_with($cell, ':')                              => 'left',
                str_ends_with($cell, ':')                                => 'right',
                default                                                  => '',
            };
        }

        return $alignment;
    }

    /**
     * @param list<string> $lines
     */
    private function firstSeparator(array $lines): ?int
    {
        foreach ($lines as $index => $line) {
            if ($this->isSeparator($line) && str_contains($line, '-')) {
                return $index;
            }
        }

        return null;
    }

    private function isSeparator(string $line): bool
    {
        return 1 === preg_match('/^[| :-]+$/', $line);
    }

    /**
     * @param list<string> $alignment
     */
    private function row(string $line, string $tag, array $alignment): string
    {
        preg_match_all('/\| ((?:\\\\.|[^|])+)/', $line, $cells);
        $html = '<tr>';

        foreach ($cells[1] as $index => $cell) {
            $style = '' === ($alignment[$index] ?? '') ? '' : ' style="text-align: ' . $alignment[$index] . '"';
            $html .= '<' . $tag . $style . '>' . $this->inlineRenderer->render(trim($cell)) . '</' . $tag . '>';
        }

        return $html . '</tr>';
    }

    /**
     * @param list<string> $lines
     * @param list<string> $alignment
     */
    private function rows(array $lines, string $tag, array $alignment): string
    {
        $html = '';

        foreach ($lines as $line) {
            $html .= $this->isSeparator($line) ? '' : $this->row($line, $tag, $alignment);
        }

        return $html;
    }

    /**
     * @return list<string> the cells of a line of dashes, trimmed
     */
    private function separatorCells(string $separator): array
    {
        $cells = array_map(trim(...), explode('|', substr($separator, 1)));

        return array_values(array_filter($cells, static fn (string $cell): bool => '' !== $cell));
    }
}
