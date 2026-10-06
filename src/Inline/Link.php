<?php

declare(strict_types=1);

namespace KMark\Inline;

use KMark\Attributes;

/**
 * The parts of a link or of an image: its text, its URL, its title and its attributes.
 *
 * @internal
 */
final readonly class Link
{
    public function __construct(
        public string $text,
        public string $url,
        public string $title = '',
        public ?Attributes $attributes = null,
    ) {}
}
