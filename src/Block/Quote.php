<?php

declare(strict_types=1);

namespace KMark\Block;

use KMark\Lines;

/**
 * Lines that start with ">". What is inside is read as blocks, so a quote can hold headings, lists, code and
 * quotes. A line of text without ">" right after a line of text goes on with it.
 *
 * @internal
 */
final class Quote implements BlockRule
{
    public function match(Lines $lines, BlockContext $blockContext): ?string
    {
        if (1 !== preg_match('/^ {0,3}>/', $lines->line())) {
            return null;
        }

        $inside = $blockContext->blocks->parse($this->collect($lines));

        return '' === $inside ? "<blockquote>\n</blockquote>" : "<blockquote>\n" . $inside . "\n</blockquote>";
    }

    /**
     * @return list<string> the lines of the quote, without their ">"
     */
    private function collect(Lines $lines): array
    {
        $inside = [];

        while (! $lines->eof()) {
            $line = $lines->line();

            if (1 === preg_match('/^ {0,3}> ?(.*)$/', $line, $match)) {
                $inside[] = $match[1];
            }
            elseif ($this->continues($line, $inside)) {
                $inside[] = ltrim($line);
            }
            else {
                break;
            }

            $lines->skip();
        }

        return $inside;
    }

    /**
     * @param list<string> $inside
     */
    private function continues(string $line, array $inside): bool
    {
        if ('' === trim($line) || [] === $inside || Interrupts::line($line)) {
            return false;
        }

        return '' !== trim($inside[array_key_last($inside)]);
    }
}
