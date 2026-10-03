<?php

declare(strict_types=1);

namespace KMark;

/**
 * Renders a block of text with the first kind of block that matches it.
 *
 * @internal
 */
final readonly class BlockRenderer
{
    /**
     * @var list<Block> the kinds of block that are tried in turn
     */
    private array $blocks;

    private ParagraphBlock $paragraphBlock;

    public function __construct(InlineRenderer $inlineRenderer)
    {
        $this->paragraphBlock = new ParagraphBlock($inlineRenderer);
        $this->blocks         = [
            new HeadingBlock($inlineRenderer),
            new ListBlock($inlineRenderer),
            new QuoteBlock($inlineRenderer),
            new CodeBlock(),
            new TableBlock($inlineRenderer),
            new RuleBlock(),
        ];
    }

    public function render(string $block): string
    {
        $lines = explode("\n", $block);

        foreach ($this->blocks as $kind) {
            if ($kind->matches($lines)) {
                return $kind->render($lines);
            }
        }

        return $this->paragraphBlock->render($lines);
    }
}
