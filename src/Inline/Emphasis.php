<?php

declare(strict_types=1);

namespace KMark\Inline;

use KMark\Options;

/**
 * The text styles. As in the original Markdown, `*x*` and `_x_` are italic and `**x**` and `__x__` are bold. `--x--` and, as on
 * GitHub, `~~x~~` are strikethrough, written as deleted text. `++x++` is underline, which Markdown does not have.
 *
 * An underscore inside a word is not a marker, so snake_case_name stays as it is.
 *
 * Each style is written with the element of the option "tags". When two styles use the same element with classes,
 * they are merged into one: bold and italic as "span" give `<span class="b i">`.
 *
 * @internal
 */
final readonly class Emphasis
{
    private const string INNER = '((?:(?!<\1[ >]|<\/\1>).)*)';

    private const string MERGEABLE = '/<(\w+) class="([^"]*)"><\1 class="([^"]*)">' . self::INNER . '<\/\1><\/\1>/s';

    public function __construct(private Options $options = new Options()) {}

    public function render(string $text): string
    {
        $underline = $this->options->tag('underline')->wrap();
        $strike    = $this->options->tag('strike')->wrap();
        $bold      = $this->options->tag('bold')->wrap();
        $italic    = $this->options->tag('italic')->wrap();
        $rules     = [
            '/(?<![+\w])\+\+(?=\S)(.+?)(?<=\S)\+\+(?!\+)/s'      => $underline,
            '/(?<![-\w])--(?=\S)(.+?)(?<=\S)--(?!-)/s'           => $strike,
            '/(?<![~\w])~~(?=\S)(.+?)(?<=\S)~~(?!~)/s'           => $strike,
            '/(?<![*\w])\*\*(?=\S)(.+?[*]*)(?<=\S)\*\*(?!\*)/s'  => $bold,
            '/(?<![_\w])__(?=\S)(.+?[_]*)(?<=\S)__(?![_\w])/s'   => $bold,
            '/(?<![*\w])\*(?=[^\s*])(.+?)(?<=[^\s*])\*(?!\*)/s'  => $italic,
            '/(?<![_\w])_(?=[^\s_])(.+?)(?<=[^\s_])_(?![_\w])/s' => $italic,
        ];

        foreach ($rules as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text) ?? $text;
        }

        return $this->merge($text);
    }

    /**
     * An element with classes that holds nothing but an element of the same name with classes is one element.
     */
    private function merge(string $text): string
    {
        do {
            $before = $text;
            $text   = preg_replace_callback(
                self::MERGEABLE,
                static fn (array $match): string => sprintf(
                    '<%1$s class="%2$s">%3$s</%1$s>',
                    $match[1],
                    implode(' ', array_unique([...explode(' ', $match[2]), ...explode(' ', $match[3])])),
                    $match[4],
                ),
                $text,
            ) ?? $text;
        }
        while ($before !== $text);

        return $text;
    }
}
