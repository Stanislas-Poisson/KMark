<?php

declare(strict_types=1);

namespace KMark\Block;

use KMark\Attributes;
use KMark\Html;
use KMark\Inline\InlineParser;
use KMark\Lines;
use KMark\Options;

/**
 * Reads the lines of a text block by block, with the first kind of block that matches, and gives the HTML.
 *
 * @internal
 */
final readonly class BlockParser
{
    private Paragraph $paragraph;

    /**
     * @var list<BlockRule> the kinds of block that are tried in turn, before the paragraph
     */
    private array $rules;

    public function __construct(private InlineParser $inlineParser, Options $options = new Options())
    {
        $this->rules = [
            new FencedCode(),
            new AtxHeading(),
            new ThematicBreak(),
            new Quote(),
            new Lists(),
            new Table(),
            new IndentedCode(),
            ...($options->unsafeAllowRawHtml ? [new HtmlBlock()] : []),
        ];
        $this->paragraph = new Paragraph();
    }

    /**
     * The HTML of each block.
     *
     * @param list<string> $lines
     *
     * @return list<string>
     */
    public function blocks(array $lines, bool $tight = false): array
    {
        $stream       = new Lines($lines);
        $blockContext = new BlockContext($this, $this->inlineParser, $tight);
        $blocks       = [];

        while (! $stream->eof()) {
            if ('' !== trim($stream->peek() ?? '')) {
                $blocks[] = $this->block($stream, $blockContext);

                continue;
            }

            $stream->skip();
        }

        return $blocks;
    }

    /**
     * @param list<string> $lines
     */
    public function parse(array $lines, bool $tight = false): string
    {
        return implode("\n", $this->blocks($lines, $tight));
    }

    /**
     * Lines of their own, "{#id .class}", give their attributes to the block just before them.
     */
    private function attributes(Lines $lines): Attributes
    {
        $attributes = new Attributes();

        while (1 === preg_match('/^\s*\{([#.][^}]*)\}\s*$/', $lines->line(), $match)) {
            $found = Attributes::fromList($match[1]);

            if (! $found instanceof Attributes) {
                break;
            }

            $attributes = $attributes->merge($found);
            $lines->skip();
        }

        return $attributes;
    }

    private function block(Lines $lines, BlockContext $blockContext): string
    {
        foreach ($this->rules as $rule) {
            $html = $rule->match($lines, $blockContext);

            if (null !== $html) {
                return Html::withAttributes($html, $this->attributes($lines));
            }
        }

        return Html::withAttributes($this->paragraph->match($lines, $blockContext), $this->attributes($lines));
    }
}
