<?php

declare(strict_types=1);

namespace KMark;

/**
 * A paragraph: any text that is not another kind of block. It is not one of the kinds that are tried in turn,
 * because it is what is left when none of them matches.
 *
 * @internal
 */
final readonly class ParagraphBlock
{
    public function __construct(private InlineRenderer $inlineRenderer) {}

    /**
     * @param list<string> $lines
     */
    public function render(array $lines): string
    {
        [$text, $attributes] = Attributes::extract(implode("\n", $lines));

        return '<p' . $attributes->render() . '>' . $this->inlineRenderer->render($text) . '</p>';
    }
}
