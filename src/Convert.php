<?php

declare(strict_types=1);

namespace KMark;

use KMark\Block\BlockParser;
use KMark\Inline\InlineParser;

/**
 * KMark converts Markdown to HTML, as the original Markdown does, and lets you set an id and CSS classes on
 * the elements with "{#id .class}".
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
        $references      = new References();
        $lines           = $references->extract(Lines::split($this->text));
        $blockParser     = new BlockParser(new InlineParser($this->options, $references), $this->options);

        return new self($this->options, $blockParser->parse($lines));
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
        // Only the blank lines around the text are dropped: the first line can be indented, as code is.
        return new self($this->options, trim($text, "\r\n"));
    }
}
