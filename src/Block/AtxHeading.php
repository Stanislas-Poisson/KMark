<?php

declare(strict_types=1);

namespace KMark\Block;

use KMark\Attributes;
use KMark\Lines;

/**
 * A heading written with one to six "#": "## Title", with "#" at the end if you like.
 *
 * @internal
 */
final class AtxHeading implements BlockRule
{
    public function match(Lines $lines, BlockContext $blockContext): ?string
    {
        if (1 !== preg_match('/^ {0,3}(#{1,6})(?:[ ]+(.*?))?[ ]*$/', $lines->line(), $match)) {
            return null;
        }

        $lines->skip();
        [$text, $attributes] = Attributes::extract(preg_replace('/[ ]+#+$/', '', $match[2] ?? '') ?? '');
        $level               = strlen($match[1]);

        $html = $blockContext->inline->render($text);

        return '<h' . $level . $attributes->render() . '>' . $html . '</h' . $level . '>';
    }
}
