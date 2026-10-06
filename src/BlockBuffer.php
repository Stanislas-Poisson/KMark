<?php

declare(strict_types=1);

namespace KMark;

/**
 * The lines of the block that is being read, and the blocks that are done.
 *
 * @internal
 */
final class BlockBuffer
{
    /**
     * @var list<string>
     */
    private array $blocks = [];

    /**
     * @var list<string>
     */
    private array $lines = [];

    public function add(string $line): void
    {
        $this->lines[] = $line;
    }

    /**
     * @return list<string>
     */
    public function finish(): array
    {
        $this->flush();

        return $this->blocks;
    }

    /**
     * Ends the block that is being read, if there is one.
     */
    public function flush(): void
    {
        if ([] !== $this->lines) {
            $this->blocks[] = implode("\n", $this->lines);
            $this->lines    = [];
        }
    }

    public function isEmpty(): bool
    {
        return [] === $this->lines;
    }
}
