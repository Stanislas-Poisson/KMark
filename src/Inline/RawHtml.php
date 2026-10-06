<?php

declare(strict_types=1);

namespace KMark\Inline;

use KMark\Stash;

/**
 * The tags that are kept as they are, when the option asks for it: they are put aside so that nothing reads them.
 *
 * @internal
 */
final class RawHtml
{
    public function protect(string $text, Stash $stash): string
    {
        return preg_replace_callback(
            '~</?[a-zA-Z][^<>]*>|<!--.*?-->~s',
            static fn (array $match): string => $stash->put($match[0]),
            $text,
        ) ?? $text;
    }
}
