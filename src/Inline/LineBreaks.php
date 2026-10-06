<?php

declare(strict_types=1);

namespace KMark\Inline;

use KMark\Stash;

/**
 * A hard line break: two spaces or more, or a backslash, at the end of a line.
 *
 * @internal
 */
final class LineBreaks
{
    public function protect(string $text, Stash $stash): string
    {
        return preg_replace_callback(
            '/(?: {2,}|(?<!\\\\)\\\\)\n/',
            static fn (): string => $stash->put('<br>') . "\n",
            $text,
        ) ?? $text;
    }
}
