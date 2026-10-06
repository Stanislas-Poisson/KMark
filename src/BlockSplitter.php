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
        $blockBuffer = new BlockBuffer();
        $inCode      = false;

        foreach (explode("\n", $text) as $line) {
            $inCode = $this->read($blockBuffer, $line, $inCode);
        }

        return $blockBuffer->finish();
    }

    private function fence(BlockBuffer $blockBuffer, string $line, bool $inCode): bool
    {
        if (! $inCode) {
            $blockBuffer->flush();
        }

        $blockBuffer->add($line);

        if ($inCode) {
            $blockBuffer->flush();
        }

        return ! $inCode;
    }

    /**
     * @return bool whether the next line is in a code block
     */
    private function read(BlockBuffer $blockBuffer, string $line, bool $inCode): bool
    {
        if (1 === preg_match('/^~{2,}\s*$/', $line)) {
            return $this->fence($blockBuffer, $line, $inCode);
        }

        if (! $inCode) {
            $this->readOutsideCode($blockBuffer, $line);
        }
        else {
            $blockBuffer->add($line);
        }

        return $inCode;
    }

    private function readOutsideCode(BlockBuffer $blockBuffer, string $line): void
    {
        if ('' === trim($line)) {
            $blockBuffer->flush();

            return;
        }

        $startsBlock = $blockBuffer->isEmpty();
        $blockBuffer->add($line);

        // A heading is a block of its own, even when a text follows it right away.
        if ($startsBlock && 1 === preg_match('/^#{1,6} /', $line)) {
            $blockBuffer->flush();
        }
    }
}
