<?php

declare(strict_types=1);

namespace KMark;

/**
 * The definitions of the reference links, written as "[label]: url "title"" on a line of their own.
 *
 * @internal
 */
final class References
{
    private const string DEFINITION = '/^ {0,3}\[([^\]]+)\]:\s*<?(\S+?)>?'
        . '(?:\s+(?:"([^"]*)"|\'([^\']*)\'|\(([^)]*)\)))?\s*$/';

    private const string FENCE = '/^ {0,3}(`{3,}|~{3,})/';

    /**
     * @var array<string, array{string, string}>
     */
    private array $definitions = [];

    /**
     * Reads the definitions and gives the lines without them. A line in a fenced code block is not a definition.
     *
     * @param list<string> $lines
     *
     * @return list<string>
     */
    public function extract(array $lines): array
    {
        $kept  = [];
        $fence = '';

        foreach ($lines as $line) {
            $fence = $this->fence($fence, $line);

            if ('' === $fence && $this->define($line)) {
                continue;
            }

            $kept[] = $line;
        }

        return $kept;
    }

    /**
     * @return array{string, string}|null the URL and the title
     */
    public function find(string $label): ?array
    {
        return $this->definitions[strtolower(trim($label))] ?? null;
    }

    private function define(string $line): bool
    {
        if (1 !== preg_match(self::DEFINITION, $line, $match)) {
            return false;
        }

        $title = $match[3] ?? '';
        $title = '' !== $title ? $title : ($match[4] ?? '');
        $title = '' !== $title ? $title : ($match[5] ?? '');

        $this->definitions[strtolower(trim($match[1]))] ??= [$match[2], $title];

        return true;
    }

    /**
     * The fence that is open after this line, or an empty string.
     */
    private function fence(string $open, string $line): string
    {
        if (1 !== preg_match(self::FENCE, $line, $match)) {
            return $open;
        }

        if ('' === $open) {
            return $match[1];
        }

        return $match[1][0] === $open[0] && strlen($match[1]) >= strlen($open) ? '' : $open;
    }
}
