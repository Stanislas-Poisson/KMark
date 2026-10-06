<?php

declare(strict_types=1);

namespace KMark;

/**
 * A code block: lines between two lines of "~~". Its content is always escaped.
 *
 * @internal
 */
final readonly class CodeBlock implements Block
{
    private const string FENCE = '/^~{2,}\s*$/';

    /**
     * @param list<string> $lines
     */
    public function matches(array $lines): bool
    {
        return count($lines) >= 2
            && 1 === preg_match(self::FENCE, $lines[0])
            && 1 === preg_match(self::FENCE, $lines[count($lines) - 1]);
    }

    /**
     * @param list<string> $lines
     */
    public function render(array $lines): string
    {
        $content = [];

        foreach (array_slice($lines, 1, -1) as $line) {
            $content[] = str_replace(
                "\t",
                '&nbsp;&nbsp;&nbsp;&nbsp;',
                htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE),
            );
        }

        return '<code>' . implode("<br />\n", $content) . '</code>';
    }
}
