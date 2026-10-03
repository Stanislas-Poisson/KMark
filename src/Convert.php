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

    private bool $autoLinks = true;

    public function setText(string $text = ''): self
    {
        $this->text = trim($text);

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Turns the bare "http://" and "https://" URLs into links. It is on by default.
     */
    public function setAutoLinks(bool $autoLinks): self
    {
        $this->autoLinks = $autoLinks;

        return $this;
    }

    public function convert(): self
    {
        $renderer = new BlockRenderer($inline = new InlineRenderer($this->autoLinks), new ListRenderer($inline));
        $blocks = [];

        foreach ((new BlockSplitter())->split($this->clean($this->text)) as $block) {
            $blocks[] = $renderer->render($block);
        }

        $this->text = implode("\n\n", $blocks);

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
