<?php

declare(strict_types=1);

namespace KMark\Inline;

use KMark\Attributes;
use KMark\Html;
use KMark\Stash;

/**
 * Code between backticks: the same number of backticks opens and closes it, so that a code can hold backticks.
 *
 * @internal
 */
final class CodeSpans
{
    public function protect(string $text, Stash $stash): string
    {
        return preg_replace_callback(
            '/(?<!`)(`+)(?!`)(.+?)(?<!`)\1(?!`)(?:\{([#.][^}]*)\})?/s',
            fn (array $match): string => $this->render($match, $stash),
            $text,
        ) ?? $text;
    }

    /**
     * A line break counts as a space, and one space is dropped on each side when there is one on both.
     */
    private function content(string $code): string
    {
        $code = str_replace("\n", ' ', $code);

        return 1 === preg_match('/^ (.*\S.*) $/', $code, $inner) ? $inner[1] : $code;
    }

    /**
     * @param array<int, string> $match
     */
    private function render(array $match, Stash $stash): string
    {
        $attributes = Attributes::fromList($match[3] ?? '');
        $tail       = ! $attributes instanceof Attributes && isset($match[3]) ? '{' . $match[3] . '}' : '';

        $code       = Html::escapeCode($this->content($match[2]));

        return $stash->put('<code' . ($attributes?->render() ?? '') . '>' . $code . '</code>') . $tail;
    }
}
