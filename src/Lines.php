<?php

declare(strict_types=1);

namespace KMark;

/**
 * The lines of a text and the place that is being read.
 *
 * @internal
 */
final class Lines
{
    private int $position = 0;

    /**
     * @param list<string> $lines
     */
    public function __construct(private readonly array $lines) {}

    /**
     * Splits a text into lines: one kind of line break, and the tabs written as the spaces they stand for.
     *
     * @return list<string>
     */
    public static function split(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        return array_map(self::expandTabs(...), explode("\n", $text));
    }

    public function eof(): bool
    {
        return count($this->lines) <= $this->position;
    }

    /**
     * The line at a place from here, or an empty string when there is none.
     */
    public function line(int $ahead = 0): string
    {
        return $this->lines[$this->position + $ahead] ?? '';
    }

    public function next(): string
    {
        $line = $this->line();
        $this->position++;

        return $line;
    }

    public function peek(int $ahead = 0): ?string
    {
        return $this->lines[$this->position + $ahead] ?? null;
    }

    public function skip(int $count = 1): void
    {
        $this->position += $count;
    }

    private static function expandTabs(string $line): string
    {
        while (false !== ($tab = strpos($line, "\t"))) {
            $line = substr($line, 0, $tab) . str_repeat(' ', 4 - ($tab % 4)) . substr($line, $tab + 1);
        }

        return $line;
    }
}
