<?php

declare(strict_types=1);

namespace KMark;

/**
 * A horizontal rule: six dashes or more, alone.
 *
 * @internal
 */
final readonly class RuleBlock implements Block
{
    /**
     * @param list<string> $lines
     */
    public function matches(array $lines): bool
    {
        return 1 === preg_match('/^-{6,}$/', trim(implode("\n", $lines)));
    }

    /**
     * @param list<string> $lines
     */
    public function render(array $lines): string
    {
        return '<hr>';
    }
}
