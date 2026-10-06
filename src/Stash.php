<?php

declare(strict_types=1);

namespace KMark;

/**
 * HTML that is put aside while a text is read, so that nothing reads it again: each piece is replaced by
 * a token, and the tokens are replaced by the pieces at the end.
 *
 * @internal
 */
final class Stash
{
    private const string END = "\x1b";

    private const string START = "\x1a";

    /**
     * @var list<string>
     */
    private array $pieces = [];

    /**
     * The text without the characters that make the tokens, so that a text cannot forge one.
     */
    public static function clean(string $text): string
    {
        return str_replace([self::START, self::END], '', $text);
    }

    /**
     * Puts a piece of HTML aside and gives the token that stands for it.
     */
    public function put(string $html): string
    {
        $this->pieces[] = $html;

        return self::START . (count($this->pieces) - 1) . self::END;
    }

    /**
     * Writes the pieces back, the ones that hold tokens too.
     */
    public function restore(string $text): string
    {
        $pattern = '/' . self::START . '(\d+)' . self::END . '/';

        while (1 === preg_match($pattern, $text)) {
            $text = preg_replace_callback(
                $pattern,
                fn (array $match): string => $this->pieces[(int) $match[1]] ?? '',
                $text,
            ) ?? '';
        }

        return $text;
    }
}
