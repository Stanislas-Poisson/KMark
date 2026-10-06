<?php

declare(strict_types=1);

namespace KMark\Block;

use KMark\Attributes;
use KMark\Html;
use KMark\Lines;

/**
 * Code between two lines of three backticks or more (or three tildes or more). The word after the opening
 * fence is the language, written as a class "language-php", and "{#id .class}" after it are the attributes.
 *
 * @internal
 */
final class FencedCode implements BlockRule
{
    private const string OPEN = '/^( {0,3})(`{3,}|~{3,})[ ]*([^\s`{]*)[ ]*(?:\{([#.][^}]*)\})?[ ]*$/';

    public function match(Lines $lines, BlockContext $blockContext): ?string
    {
        if (1 !== preg_match(self::OPEN, $lines->line(), $open)) {
            return null;
        }

        $lines->skip();

        return $this->render($open, $this->read($lines, $open[2], strlen($open[1])));
    }

    /**
     * The lines up to the closing fence, each one without the indentation of the opening fence.
     */
    private function read(Lines $lines, string $fence, int $indent): string
    {
        $code = '';

        while (! $lines->eof()) {
            $line = $lines->next();

            if (1 === preg_match('/^ {0,3}' . preg_quote($fence[0], '/') . '{' . strlen($fence) . ',}[ ]*$/', $line)) {
                break;
            }

            $code .= (preg_replace('/^ {0,' . $indent . '}/', '', $line) ?? $line) . "\n";
        }

        return $code;
    }

    /**
     * @param array<int, string> $open
     */
    private function render(array $open, string $code): string
    {
        $class      = '' === $open[3] ? '' : ' class="language-' . Html::attribute($open[3]) . '"';
        $attributes = Attributes::fromList($open[4] ?? '') ?? new Attributes();

        return '<pre' . $attributes->render() . '><code' . $class . '>' . Html::escapeCode($code) . '</code></pre>';
    }
}
