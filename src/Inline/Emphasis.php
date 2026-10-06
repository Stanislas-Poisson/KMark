<?php

declare(strict_types=1);

namespace KMark\Inline;

/**
 * Emphasis, as in the original Markdown: "*em*" and "_em_" give <em>, "**strong**" and "__strong__" give
 * <strong>. And, as in GitHub Markdown, "~~strikethrough~~" gives <del>.
 *
 * An underscore inside a word is not a marker, so snake_case_name stays as it is.
 *
 * @internal
 */
final class Emphasis
{
    private const array RULES = [
        '/(?<![~\w])~~(?=\S)(.+?)(?<=\S)~~(?!~)/s'                  => '<del>$1</del>',
        '/(?<![*\w])\*\*(?=\S)(.+?[*]*)(?<=\S)\*\*(?!\*)/s'         => '<strong>$1</strong>',
        '/(?<![_\w])__(?=\S)(.+?[_]*)(?<=\S)__(?![_\w])/s'          => '<strong>$1</strong>',
        '/(?<![*\w])\*(?=[^\s*])(.+?)(?<=[^\s*])\*(?!\*)/s'         => '<em>$1</em>',
        '/(?<![_\w])_(?=[^\s_])(.+?)(?<=[^\s_])_(?![_\w])/s'        => '<em>$1</em>',
    ];

    public function render(string $text): string
    {
        foreach (self::RULES as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text) ?? $text;
        }

        return $text;
    }
}
