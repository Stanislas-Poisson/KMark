<?php

declare(strict_types=1);

namespace KMark;

/**
 * Splits a text on its blank lines, except inside a fenced code block.
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
        $blocks = [];
        $current = [];
        $inCode = false;

        foreach (explode("\n", $text) as $line) {
            if (1 === preg_match('/^~{2,}\s*$/', $line)) {
                if (!$inCode && [] !== $current) {
                    $blocks[] = implode("\n", $current);
                    $current = [];
                }

                $inCode = !$inCode;
                $current[] = $line;

                if (!$inCode) {
                    $blocks[] = implode("\n", $current);
                    $current = [];
                }

                continue;
            }

            if (!$inCode && '' === trim($line)) {
                if ([] !== $current) {
                    $blocks[] = implode("\n", $current);
                    $current = [];
                }

                continue;
            }

            $current[] = $line;
        }

        if ([] !== $current) {
            $blocks[] = implode("\n", $current);
        }

        return $blocks;
    }
}
