<?php

declare(strict_types=1);

namespace KMark;

/**
 * A heading: one to six "#", a space and the text.
 *
 * @internal
 */
final readonly class HeadingBlock implements Block
{
    public function __construct(private InlineRenderer $inlineRenderer) {}

    /**
     * @param list<string> $lines
     */
    public function matches(array $lines): bool
    {
        return null !== $this->parse($lines);
    }

    /**
     * @param list<string> $lines
     */
    public function render(array $lines): string
    {
        [$level, $text]      = $this->parse($lines) ?? [1, ''];
        [$text, $attributes] = Attributes::extract($text);

        return '<h' . $level . $attributes->render() . '>'
            . $this->inlineRenderer->render(trim($text))
            . '</h' . $level . '>';
    }

    /**
     * @param list<string> $lines
     *
     * @return array{int, string}|null the level and the text of the heading, or null
     */
    private function parse(array $lines): ?array
    {
        return 1 === preg_match('/^(#{1,6}) (.*)$/', $lines[0], $heading) ? [strlen($heading[1]), $heading[2]] : null;
    }
}
