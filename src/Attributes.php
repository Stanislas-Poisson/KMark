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
    ) {}

    /**
     * Takes the "{#id .class}" block off the end of a text. If the braces hold
     * anything else than names with a "#" or a ".", they stay in the text.
     *
     * @return array{string, self} the text and the attributes
     */
    public static function extract(string $text): array
    {
        if (1 !== preg_match('/^(.*?)\s*(?<!\\\\)\{([#.\w\s-]*)\}\s*$/s', $text, $match)) {
            return [$text, new self()];
        }

        $attributes = self::parse($match[2]);

        return $attributes instanceof Attributes ? [$match[1], $attributes] : [$text, new self()];
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

    /**
     * @param list<array<int, string>> $tokens
     *
     * @return list<string> the names that start with a marker
     */
    private static function names(array $tokens, string $marker): array
    {
        $tokens = array_filter($tokens, static fn (array $token): bool => $marker === $token[1]);

        return array_values(array_map(static fn (array $token): string => $token[2], $tokens));
    }

    /**
     * @return self|null the attributes, or null if the names are not valid
     */
    private static function parse(string $names): ?self
    {
        if (1 !== preg_match('/^[#.][\w-]+(?:\s+[#.][\w-]+)*$/', trim($names))) {
            return null;
        }

        preg_match_all('/([#.])([\w-]+)/', $names, $tokens, PREG_SET_ORDER);

        return new self(self::names($tokens, '#')[0] ?? '', self::names($tokens, '.'));
    }
}
