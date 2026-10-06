<?php

declare(strict_types=1);

namespace KMark\Inline;

use KMark\Html;
use KMark\Stash;

/**
 * A backslash before a punctuation character writes the character as it is.
 *
 * @internal
 */
final class Escapes
{
    public function protect(string $text, Stash $stash): string
    {
        return preg_replace_callback(
            '/\\\\([!-\/:-@\[-`{-~])/',
            static fn (array $match): string => $stash->put(Html::escapeCode($match[1])),
            $text,
        ) ?? $text;
    }
}
