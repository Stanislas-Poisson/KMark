<?php

declare(strict_types=1);

namespace KMark\Block;

use KMark\Lines;

/**
 * A table written with bars, as on GitHub: a line of headers, a line of dashes whose colons align the columns,
 * then the rows.
 *
 * @internal
 */
final class Table implements BlockRule
{
    private const string DELIMITER = '/^ {0,3}\|?[ ]*:?-+:?[ ]*(?:\|[ ]*:?-+:?[ ]*)*\|?[ ]*$/';

    public function match(Lines $lines, BlockContext $blockContext): ?string
    {
        $header    = $lines->peek()  ?? '';
        $delimiter = $lines->peek(1) ?? '';

        if (! str_contains($header, '|') || 1 !== preg_match(self::DELIMITER, $delimiter)) {
            return null;
        }

        $headers = $this->cells($header);
        $aligns  = $this->cells($delimiter);

        if (count($headers) !== count($aligns)) {
            return null;
        }

        $lines->skip(2);

        $rows = $this->rows($lines, count($headers));

        return $this->render($headers, array_map($this->align(...), $aligns), $rows, $blockContext);
    }

    private function align(string $cell): string
    {
        $left  = str_starts_with($cell, ':');
        $right = str_ends_with($cell, ':');

        return match (true) {
            $left && $right => ' style="text-align: center"',
            $left           => ' style="text-align: left"',
            $right          => ' style="text-align: right"',
            default         => '',
        };
    }

    /**
     * @return list<string>
     */
    private function cells(string $line): array
    {
        $line = trim($line);
        $line = preg_replace('/^\||(?<!\\\\)\|$/', '', $line) ?? $line;

        $parts = preg_split('/(?<!\\\\)\|/', $line);

        return array_map(trim(...), false === $parts ? [$line] : $parts);
    }

    /**
     * @param list<string>       $headers
     * @param list<string>       $aligns
     * @param list<list<string>> $rows
     */
    private function render(array $headers, array $aligns, array $rows, BlockContext $blockContext): string
    {
        $html = "<table>\n<thead>\n" . $this->row('th', $headers, $aligns, $blockContext) . "\n</thead>";

        if ([] !== $rows) {
            $body = array_map(fn (array $row): string => $this->row('td', $row, $aligns, $blockContext), $rows);
            $html .= "\n<tbody>\n" . implode("\n", $body) . "\n</tbody>";
        }

        return $html . "\n</table>";
    }

    /**
     * @param list<string> $cells
     * @param list<string> $aligns
     */
    private function row(string $tag, array $cells, array $aligns, BlockContext $blockContext): string
    {
        $html = [];

        foreach ($aligns as $index => $align) {
            $html[] = '<' . $tag . $align . '>' . $blockContext->inline->render($cells[$index]) . '</' . $tag . '>';
        }

        return "<tr>\n" . implode("\n", $html) . "\n</tr>";
    }

    /**
     * @return list<list<string>>
     */
    private function rows(Lines $lines, int $columns): array
    {
        $rows = [];

        while (! $lines->eof() && '' !== trim($lines->line()) && ! Interrupts::line($lines->line())) {
            $rows[] = array_pad(array_slice($this->cells($lines->next()), 0, $columns), $columns, '');
        }

        return $rows;
    }
}
