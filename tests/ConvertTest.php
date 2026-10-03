<?php

declare(strict_types=1);

namespace KMark\Tests;

use KMark\Convert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConvertTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function syntaxProvider(): iterable
    {
        yield 'heading' => ['## Sub title', '<h2>Sub title</h2>'];
        yield 'paragraphs' => ["Para 1\n\nPara 2", "<p>Para 1</p>\n\n<p>Para 2</p>"];
        yield 'horizontal rule' => ['------', '<hr>'];
        yield 'unordered list' => [
            "+\tElem 1\n+\tElem 2\n+\tElem 3",
            '<ul><li>Elem 1</li><li>Elem 2</li><li>Elem 3</li></ul>',
        ];
        yield 'ordered list' => [
            "1.\tElem 1\n2.\tElem 2\n3.\tElem 3",
            '<ol><li>Elem 1</li><li>Elem 2</li><li>Elem 3</li></ol>',
        ];
        yield 'quote' => [">\tLine 1\n>\tLine 2", "<blockquote>Line 1\nLine 2\n</blockquote>"];
        yield 'table' => [
            "| A | B\n| --------- | ---------\n| 1 | 2",
            '<table><tr><td>A</td><td>B</td></tr><tr><td>1</td><td>2</td></tr></table>',
        ];
        yield 'image' => [
            '![alt](http://x.fr/a.png {#i .c})',
            '<p><img src="http://x.fr/a.png" alt="alt" id="i" class="c"></p>',
        ];
        yield 'image without attributes' => [
            '![alt](http://x.fr/a.png)',
            '<p><img src="http://x.fr/a.png" alt="alt"></p>',
        ];
        yield 'link' => [
            '[Example]:(https://example.com)',
            '<p><a href="https://example.com" >Example</a></p>',
        ];
        yield 'link with title' => [
            '[Example]:(https://example.com "Go")',
            '<p><a href="https://example.com"  title="Go">Example</a></p>',
        ];
        yield 'document' => [
            "# Title\n\nSome text with a [link]:(https://example.com).\n\n+\tOne\n+\tTwo",
            "<h1>Title</h1>\n\n<p>Some text with a <a href=\"https://example.com\" >link</a>.</p>\n\n<ul><li>One</li><li>Two</li></ul>",
        ];
    }

    #[DataProvider('syntaxProvider')]
    public function testConvertsTheSyntax(string $input, string $expected): void
    {
        self::assertSame($expected, (new Convert())->setText($input)->convert()->getText());
    }

    public function testSetTextTrimsTheText(): void
    {
        self::assertSame('Hello', (new Convert())->setText("  Hello\n")->getText());
    }
}
