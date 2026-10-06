<?php

declare(strict_types=1);

namespace KMark\Inline;

/**
 * Tells whether a URL may be a link: a relative URL, or one whose scheme is allowed.
 *
 * @internal
 */
final readonly class SafeUrls
{
    /**
     * @param list<string> $allowedSchemes
     */
    public function __construct(private array $allowedSchemes) {}

    public function isSafe(string $url): bool
    {
        $compact = preg_replace('/[\x00-\x20]+/', '', $url) ?? '';

        if (1 !== preg_match('/^([a-z][a-z0-9+.\-]*):/i', $compact, $scheme)) {
            return true;
        }

        return in_array(strtolower($scheme[1]), $this->allowedSchemes, true);
    }
}
