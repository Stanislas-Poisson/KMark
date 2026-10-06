<?php

declare(strict_types=1);

namespace KMark\Inline;

use KMark\Html;
use KMark\Options;
use KMark\References;
use KMark\Stash;

/**
 * Turns the text of a block into HTML: code spans, links, images, emphasis, line breaks, bare URLs.
 *
 * The pieces that must not be read again are put aside in a stash as soon as they are made, and written back
 * at the end.
 *
 * @internal
 */
final readonly class InlineParser
{
    private AutoLinks $autoLinks;

    private BareUrls $bareUrls;

    private CodeSpans $codeSpans;

    private Emphasis $emphasis;

    private Escapes $escapes;

    private LineBreaks $lineBreaks;

    private Links $links;

    private RawHtml $rawHtml;

    public function __construct(private Options $options = new Options(), References $references = new References())
    {
        $safeUrls         = new SafeUrls($options->allowedSchemes);
        $this->emphasis   = new Emphasis($options);
        $this->autoLinks  = new AutoLinks($safeUrls);
        $this->bareUrls   = new BareUrls($options->autoLinks);
        $this->codeSpans  = new CodeSpans();
        $this->escapes    = new Escapes();
        $this->lineBreaks = new LineBreaks();
        $this->links      = new Links($safeUrls, $references, $this->emphasis);
        $this->rawHtml    = new RawHtml();
    }

    public function render(string $text): string
    {
        $stash = new Stash();
        $text  = $this->protect(Stash::clean($text), $stash);
        $text  = $this->options->unsafeAllowRawHtml ? $text : Html::escape($text);
        $text  = $this->links->render($text, $stash);
        $text  = $this->bareUrls->render($text, $stash);

        return $stash->restore($this->emphasis->render($text));
    }

    /**
     * Puts aside what is read before the text is escaped: line breaks, escapes, code, automatic links.
     */
    private function protect(string $text, Stash $stash): string
    {
        $text = $this->lineBreaks->protect($text, $stash);
        $text = $this->escapes->protect($text, $stash);
        $text = $this->codeSpans->protect($text, $stash);
        $text = $this->autoLinks->protect($text, $stash);

        return $this->options->unsafeAllowRawHtml ? $this->rawHtml->protect($text, $stash) : $text;
    }
}
