<?php

declare(strict_types=1);

namespace KMark;

/**
 * A table: lines that start with "|", the line of dashes between the head and the rows being skipped.
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
        $html = '<table>';

        foreach ($lines as $line) {
            $html .= $this->isRow($line) ? $this->row($line) : '';
        }

        return $html . '</table>';
    }

    private function isRow(string $line): bool
    {
        return str_starts_with($line, '|') && 1 !== preg_match('/^[| -]+$/', $line);
    }

    private function row(string $line): string
    {
        preg_match_all('/\| ([^|]+)/', $line, $cells);
        $html = '<tr>';

        foreach ($cells[1] as $cell) {
            $html .= '<td>' . $this->inlineRenderer->render(trim($cell)) . '</td>';
        }

        return $html . '</tr>';
    }
}
