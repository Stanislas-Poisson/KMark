<?php

declare(strict_types=1);

namespace KMark;

/**
 * KMark is an adaptation of the Markdown syntax, dedicated to the web,
 * that allows to set an id and CSS classes on every element.
 *
 * Copyright (c) 2013 Stanislas Poisson - MIT license
 * https://stanislas-poisson.fr
 */
final readonly class Convert
{
    public function __construct(
        private Options $options = new Options(),
        private string $text = '',
    ) {}

    /**
     * Converts a text in one call.
     */
    public static function toHtml(string $text, ?Options $options = null): string
    {
        return (new self($options ?? new Options()))->withText($text)->convert()->getText();
    }

    /**
     * A converter that holds the text of this one, converted to HTML.
     */
    public function convert(): self
    {
        $blockRenderer = new BlockRenderer(new InlineRenderer($this->options));
        $blocks        = [];

        foreach ((new BlockSplitter())->split($this->clean($this->text)) as $block) {
            $blocks[] = $blockRenderer->render($block);
        }

        return new self($this->options, implode("\n\n", $blocks));
    }

    public function getText(): string
    {
        return $this->text;
    }

    /**
     * A converter that has the same options and holds the given text.
     */
    public function withText(string $text): self
    {
        return new self($this->options, trim($text));
    }

    /**
     * Unifies the line breaks and keeps at most one blank line between two blocks.
     */
    private function clean(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/\n *\n/', "\n\n", $text) ?? $text;

        return preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;
    }
}
