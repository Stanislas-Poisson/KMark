<?php

declare(strict_types=1);

namespace KMark\Inline;

use KMark\Attributes;
use KMark\Html;
use KMark\References;
use KMark\Stash;

/**
 * Links and images, written inline (`[text](url "title")`) or with a reference (`[text][label]`, `[label]`).
 *
 * A link, once made, is put aside in the stash with its text already read, so that nothing reads it again.
 *
 * @internal
 */
final readonly class Links
{
    private const string ANGLE = '&lt;(?:(?!&gt;).)*&gt;';

    private const string ATTRIBUTES = '(?:\{([#.][^}]*)\})?';

    private const string PLAIN = '(?:[^()\s]|\([^()\s]*\))*';

    private const string TARGET = '\(\s*(' . self::ANGLE . '|' . self::PLAIN . ')(?:\s+(' . self::TITLE . '))?\s*\)';

    private const string TEXT = '((?:[^\[\]]|\[[^\[\]]*\])*)';

    private const string TITLE = '"[^"]*"|\'[^\']*\'';

    public function __construct(
        private SafeUrls $safeUrls,
        private References $references,
        private Emphasis $emphasis,
    ) {}

    public function render(string $text, Stash $stash): string
    {
        $text = $this->inline($text, $stash, true);
        $text = $this->reference($text, $stash, true);
        $text = $this->inline($text, $stash, false);
        $text = $this->reference($text, $stash, false);

        return $this->shortcut($text, $stash);
    }

    private function build(Link $link, bool $image, Stash $stash): string
    {
        $url = 1 === preg_match('/^&lt;(.*)&gt;$/', $link->url, $inner) ? $inner[1] : $link->url;

        if (! $this->safeUrls->isSafe($url)) {
            return $link->text;
        }

        $title      = '' === $link->title ? '' : ' title="' . Html::attribute(trim($link->title, '"\'')) . '"';
        $attributes = $link->attributes?->render() ?? '';
        $href       = Html::attribute($url);

        if ($image) {
            $alt = Html::attribute(strip_tags($stash->restore($link->text)));

            return $stash->put('<img src="' . $href . '" alt="' . $alt . '"' . $title . $attributes . '>');
        }

        $text = $this->emphasis->render($link->text);

        return $stash->put('<a href="' . $href . '"' . $title . $attributes . '>' . $text . '</a>');
    }

    /**
     * @param array<int, string> $match
     */
    private function fromInline(array $match, bool $image, Stash $stash): string
    {
        $attributes = Attributes::fromList($match[4] ?? '');
        $tail       = isset($match[4]) && ! $attributes instanceof Attributes ? '{' . $match[4] . '}' : '';

        return $this->build(new Link($match[1], $match[2], $match[3] ?? '', $attributes), $image, $stash) . $tail;
    }

    private function inline(string $text, Stash $stash, bool $image): string
    {
        return preg_replace_callback(
            '/' . ($image ? '!' : '') . '\[' . self::TEXT . '\]' . self::TARGET . self::ATTRIBUTES . '/',
            fn (array $match): string => $this->fromInline($match, $image, $stash),
            $text,
        ) ?? $text;
    }

    /**
     * The label of a reference link: the one between the second brackets, or the text when it is empty.
     *
     * @param array<int, string> $match
     */
    private function label(array $match): string
    {
        return '' === $match[2] ? $match[1] : $match[2];
    }

    private function reference(string $text, Stash $stash, bool $image): string
    {
        return preg_replace_callback(
            '/' . ($image ? '!' : '') . '\[' . self::TEXT . '\]\s?\[([^\]]*)\]/',
            fn (array $match): string => $this->referenced($match, $this->label($match), $image, $stash),
            $text,
        ) ?? $text;
    }

    /**
     * @param array<int, string> $match
     */
    private function referenced(array $match, string $label, bool $image, Stash $stash): string
    {
        $definition = $this->references->find($label);

        if (null === $definition) {
            return $match[0];
        }

        return $this->build(new Link($match[1], $definition[0], $definition[1]), $image, $stash);
    }

    private function shortcut(string $text, Stash $stash): string
    {
        return preg_replace_callback(
            '/\[([^\[\]]+)\](?![\[(])/',
            fn (array $match): string => $this->referenced($match, $match[1], false, $stash),
            $text,
        ) ?? $text;
    }
}
