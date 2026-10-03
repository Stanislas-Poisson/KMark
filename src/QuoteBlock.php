<?php

declare(strict_types=1);

namespace KMark;

/**
 * A quote: lines that start with ">" and a tab.
 *
 * @internal
 */
final readonly class QuoteBlock implements Block
{
    public function __construct(private InlineRenderer $inlineRenderer) {}

    /**
     * @param list<string> $lines
     */
    public function matches(array $lines): bool
    {
        return 1 === preg_match('/^>\t/', $lines[0]);
    }

    /**
     * @param list<string> $lines
     */
    public function render(array $lines): string
    {
        $attributes = new Attributes();
        $content    = [];

        foreach ($lines as $line) {
            [$text, $lineAttributes] = Attributes::extract($this->withoutMarker($line));
            $attributes              = $attributes->merge($lineAttributes);
            $content[]               = $this->inlineRenderer->render($text);
        }

        return '<blockquote' . $attributes->render() . '>' . implode("\n", $content) . '</blockquote>';
    }

    private function withoutMarker(string $line): string
    {
        return 1 === preg_match('/^>\t(.*)$/', $line, $quote) ? $quote[1] : $line;
    }
}
