<?php

declare(strict_types=1);

namespace KMark\Block;

use KMark\Attributes;
use KMark\Lines;

/**
 * Lines of text up to a blank line or to a block that interrupts them. A line of "=" or of "-" under the text
 * makes it a heading (the underlined style of the original Markdown), and "{#id .class}" at the end of the text
 * gives its attributes.
 *
 * @internal
 */
final class Paragraph implements BlockRule
{
    /**
     * In a tight list the text is written without <p>: this mark in front of it tells so to the list.
     */
    public const string TIGHT = "\x1f";

    public function match(Lines $lines, BlockContext $blockContext): string
    {
        $text = [ltrim($lines->next())];

        while (! $lines->eof()) {
            $line = $lines->line();

            if (1 === preg_match('/^ {0,3}(=+|-+)[ ]*$/', $line, $underline)) {
                $lines->skip();

                return $this->heading($text, '=' === $underline[1][0] ? 1 : 2, $blockContext);
            }

            if ($this->ends($line)) {
                break;
            }

            $text[] = ltrim($lines->next());
        }

        return $this->paragraph($text, $blockContext);
    }

    private function ends(string $line): bool
    {
        return '' === trim($line) || Interrupts::line($line);
    }

    /**
     * @param list<string> $text
     */
    private function heading(array $text, int $level, BlockContext $blockContext): string
    {
        [$content, $attributes] = Attributes::extract(rtrim(implode("\n", $text)));

        $html = $blockContext->inline->render($content);

        return '<h' . $level . $attributes->render() . '>' . $html . '</h' . $level . '>';
    }

    /**
     * @param list<string> $text
     */
    private function paragraph(array $text, BlockContext $blockContext): string
    {
        $written                = rtrim(implode("\n", $text));
        [$content, $attributes] = Attributes::extract($written);

        // Attributes alone, with no text before them, are not attributes: they are the text.
        if ('' === $content) {
            [$content, $attributes] = [$written, new Attributes()];
        }

        $html = $blockContext->inline->render($content);

        return $blockContext->tight ? self::TIGHT . $html : '<p' . $attributes->render() . '>' . $html . '</p>';
    }
}
