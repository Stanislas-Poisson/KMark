<?php

declare(strict_types=1);

namespace KMark;

/**
 * Splits a text into blocks: on the blank lines, except inside a code block, and after a heading.
 *
 * @internal
 */
final class BlockSplitter
{
    /**
     * @return list<string>
     */
    public function split(string $text): array
    {
        $buffer = new BlockBuffer();
        $inCode = false;

        foreach (explode("\n", $text) as $line) {
            $inCode = $this->read($buffer, $line, $inCode);
        }

        return $buffer->finish();
    }

    private function fence(BlockBuffer $buffer, string $line, bool $inCode): bool
    {
        if (! $inCode) {
            $buffer->flush();
        }

        $buffer->add($line);

        if ($inCode) {
            $buffer->flush();
        }

        return ! $inCode;
    }

    /**
     * @return bool whether the next line is in a code block
     */
    private function read(BlockBuffer $buffer, string $line, bool $inCode): bool
    {
        if (1 === preg_match('/^~{2,}\s*$/', $line)) {
            return $this->fence($buffer, $line, $inCode);
        }

        if (! $inCode) {
            $this->readOutsideCode($buffer, $line);
        }
        else {
            $buffer->add($line);
        }

        return $inCode;
    }

    private function readOutsideCode(BlockBuffer $buffer, string $line): void
    {
        if ('' === trim($line)) {
            $buffer->flush();

            return;
        }

        $startsBlock = $buffer->isEmpty();
        $buffer->add($line);

        // A heading is a block of its own, even when a text follows it right away.
        if ($startsBlock && 1 === preg_match('/^#{1,6} /', $line)) {
            $buffer->flush();
        }
    }
}
