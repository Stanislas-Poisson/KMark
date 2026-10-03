<?php

declare(strict_types=1);

namespace KMark;

/**
 * The id and the CSS classes that a "{#id .class}" block sets on an element.
 *
 * @internal
 */
final readonly class Attributes
{
    /**
     * @param list<string> $classes
     */
    public function __construct(
        public string $id = '',
        public array $classes = [],
    ) {
    }

    /**
     * Takes the "{#id .class}" block off the end of a text. If the braces hold
     * anything else than names with a "#" or a ".", they stay in the text.
     *
     * @return array{string, self} the text and the attributes
     */
    public static function extract(string $text): array
    {
        if (1 !== preg_match('/^(.*?)\s*\{([#.\w\s-]*)\}\s*$/s', $text, $match)) {
            return [$text, new self()];
        }

        $tokens = preg_split('/\s+/', trim($match[2]), -1, PREG_SPLIT_NO_EMPTY);

        if (false === $tokens || [] === $tokens) {
            return [$text, new self()];
        }

        $id = '';
        $classes = [];

        foreach ($tokens as $token) {
            $name = substr($token, 1);

            if (1 !== preg_match('/^[\w-]+$/', $name) || !in_array($token[0], ['#', '.'], true)) {
                return [$text, new self()];
            }

            if ('#' === $token[0]) {
                $id = '' === $id ? $name : $id;
            } else {
                $classes[] = $name;
            }
        }

        return [$match[1], new self($id, $classes)];
    }

    /**
     * Keeps the first id and gathers the classes.
     */
    public function merge(self $other): self
    {
        return new self('' === $this->id ? $other->id : $this->id, [...$this->classes, ...$other->classes]);
    }

    /**
     * The attributes, ready to be written in a tag.
     */
    public function render(): string
    {
        $html = '' === $this->id ? '' : ' id="' . $this->id . '"';

        return [] === $this->classes ? $html : $html . ' class="' . implode(' ', $this->classes) . '"';
    }
}
