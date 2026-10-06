<?php

declare(strict_types=1);

namespace KMark\Block;

/**
 * The start of a list item: "-", "*" or "+" for a bullet, "1." or "1)" for a number. What follows the marker
 * is the first line of the item, and the lines that go on with it are indented as far as this text.
 *
 * @internal
 */
final readonly class ListMarker
{
    private const string BULLET = '/^( {0,3})([-*+])( *)(.*)$/';

    private const string NUMBER = '/^( {0,3})(\d{1,9})([.)])( *)(.*)$/';

    private function __construct(
        public bool $ordered,
        public string $symbol,
        public int $start,
        public int $contentIndent,
        public string $content,
    ) {}

    public static function read(?string $line): ?self
    {
        if (null === $line) {
            return null;
        }

        return self::bullet($line) ?? self::number($line);
    }

    /**
     * The same kind of list: the same bullet, or the same kind of number.
     */
    public function sameList(self $other): bool
    {
        return $this->ordered === $other->ordered && $this->symbol === $other->symbol;
    }

    private static function bullet(string $line): ?self
    {
        if (1 !== preg_match(self::BULLET, $line, $match) || ! self::fits($match[3], $match[4])) {
            return null;
        }

        return self::make(false, $match[2], 0, strlen($match[1]) + 1, [$match[3], $match[4]]);
    }

    /**
     * A marker is followed by a space or by the end of the line.
     */
    private static function fits(string $spaces, string $content): bool
    {
        return '' !== $spaces || '' === $content;
    }

    /**
     * The text of the item starts after one to four spaces; with more, the text is code, indented from the first.
     *
     * @param array{string, string} $after the spaces after the marker, and the text
     */
    private static function make(bool $ordered, string $symbol, int $start, int $width, array $after): self
    {
        [$spaces, $content] = $after;
        $gap                = strlen($spaces);

        if ('' === trim($content)) {
            return new self($ordered, $symbol, $start, $width + 1, '');
        }

        if (4 < $gap) {
            return new self($ordered, $symbol, $start, $width + 1, str_repeat(' ', $gap - 1) . $content);
        }

        return new self($ordered, $symbol, $start, $width + $gap, $content);
    }

    private static function number(string $line): ?self
    {
        if (1 !== preg_match(self::NUMBER, $line, $match) || ! self::fits($match[4], $match[5])) {
            return null;
        }

        $width = strlen($match[1]) + strlen($match[2]) + 1;

        return self::make(true, $match[3], (int) $match[2], $width, [$match[4], $match[5]]);
    }
}
