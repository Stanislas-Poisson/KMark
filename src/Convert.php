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
final class Convert
{
    private string $text = '';

    public function __construct(private readonly Options $options = new Options()) {}

    /**
     * Converts a text in one call.
     */
    public static function toHtml(string $text, ?Options $options = null): string
    {
        return (new self($options ?? new Options()))->setText($text)->convert()->getText();
    }

    public function convert(): self
    {
        $renderer = new BlockRenderer($inline = new InlineRenderer($this->options), new ListRenderer($inline));
        $blocks   = [];

        foreach ((new BlockSplitter())->split($this->clean($this->text)) as $block) {
            $blocks[] = $renderer->render($block);
        }

        $this->text = implode("\n\n", $blocks);

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text = ''): self
    {
        $this->text = trim($text);

        return $this;
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
