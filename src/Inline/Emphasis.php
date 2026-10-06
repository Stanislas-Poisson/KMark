<?php

declare(strict_types=1);

namespace KMark\Inline;

use KMark\Options;

/**
 * Emphasis, as in the original Markdown: "*em*" and "_em_" give <em>, "**strong**" and "__strong__" give
 * <strong>. And, as in GitHub Markdown, "~~strikethrough~~" gives <del>.
 *
 * An underscore inside a word is not a marker, so snake_case_name stays as it is.
 *
 * @internal
 */
final readonly class Emphasis
{
    public function __construct(private Options $options = new Options()) {}

    public function render(string $text): string
    {
        $del    = $this->options->tag('del')->wrap();
        $strong = $this->options->tag('strong')->wrap();
        $em     = $this->options->tag('em')->wrap();
        $rules  = [
            '/(?<![~\w])~~(?=\S)(.+?)(?<=\S)~~(?!~)/s'              => $del,
            '/(?<![*\w])\*\*(?=\S)(.+?[*]*)(?<=\S)\*\*(?!\*)/s'     => $strong,
            '/(?<![_\w])__(?=\S)(.+?[_]*)(?<=\S)__(?![_\w])/s'      => $strong,
            '/(?<![*\w])\*(?=[^\s*])(.+?)(?<=[^\s*])\*(?!\*)/s'     => $em,
            '/(?<![_\w])_(?=[^\s_])(.+?)(?<=[^\s_])_(?![_\w])/s'    => $em,
        ];

        foreach ($rules as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text) ?? $text;
        }

        return $text;
    }
}
