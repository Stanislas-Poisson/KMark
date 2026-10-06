<?php

declare(strict_types=1);

namespace KMark\Block;

use KMark\Html;
use KMark\Lines;

/**
 * Code indented by four spaces or one tab.
 *
 * @internal
 */
final class IndentedCode implements BlockRule
{
    public function match(Lines $lines, BlockContext $blockContext): ?string
    {
        if (1 !== preg_match('/^ {4,}\S/', $lines->line())) {
            return null;
        }

        $code = [];

        while (! $lines->eof() && $this->continues($lines->line())) {
            $code[] = preg_replace('/^ {1,4}/', '', $lines->next()) ?? '';
        }

        return '<pre><code>' . Html::escapeCode(implode("\n", $this->trim($code)) . "\n") . '</code></pre>';
    }

    private function continues(string $line): bool
    {
        return '' === trim($line) || 1 === preg_match('/^ {4,}/', $line);
    }

    /**
     * @param list<string> $code
     *
     * @return list<string> the lines without the blank ones at the end
     */
    private function trim(array $code): array
    {
        while ([] !== $code && '' === trim($code[array_key_last($code)])) {
            array_pop($code);
        }

        return $code;
    }
}
