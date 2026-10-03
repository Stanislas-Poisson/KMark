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
        yield 'quote' => [">\tLine 1\n>\tLine 2", "<blockquote>Line 1\nLine 2</blockquote>"];
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
            '<p><a href="https://example.com">Example</a></p>',
        ];
        yield 'link with title' => [
            '[Example]:(https://example.com "Go")',
            '<p><a href="https://example.com" title="Go">Example</a></p>',
        ];
        yield 'document' => [
            "# Title\n\nSome text with a [link]:(https://example.com).\n\n+\tOne\n+\tTwo",
            "<h1>Title</h1>\n\n<p>Some text with a <a href=\"https://example.com\">link</a>.</p>\n\n<ul><li>One</li><li>Two</li></ul>",
        ];
        yield 'heading with id and classes' => ['# Mon titre {#monId .a .b}', '<h1 id="monId" class="a b">Mon titre</h1>'];
        yield 'heading followed by a text' => ["# T\ntext", "<h1>T</h1>\n<p>text</p>"];
        yield 'paragraph with id and classes' => ['Hello {#intro .lead}', '<p id="intro" class="lead">Hello</p>'];
        yield 'braces that are not attributes' => ['Use {x} here', '<p>Use {x} here</p>'];
        yield 'braces at the end that are not attributes' => ['Hello {x}', '<p>Hello {x}</p>'];
        yield 'empty braces' => ['Hello {}', '<p>Hello {}</p>'];
        yield 'two ids keep the first one' => ['Hello {#a #b .c}', '<p id="a" class="c">Hello</p>'];
        yield 'link with title, id and classes' => [
            '[Site]:(http://x.fr "Go" {#i .c})',
            '<p><a href="http://x.fr" title="Go" id="i" class="c">Site</a></p>',
        ];
        yield 'link and image in the same text' => [
            '![alt](a.png) and [a]:(b)',
            '<p><img src="a.png" alt="alt"> and <a href="b">a</a></p>',
        ];
        yield 'ampersand in an URL' => [
            '[a]:(https://x.fr/?a=1&b=2)',
            '<p><a href="https://x.fr/?a=1&amp;b=2">a</a></p>',
        ];
        yield 'quote with id and classes' => [">\tA {#q .c}\n>\tB {.d}", '<blockquote id="q" class="c d">A' . "\n" . 'B</blockquote>'];
        yield 'nested unordered list' => [
            "+\tA\n\t+\tB\n+\tC",
            '<ul><li>A<ul><li>B</li></ul></li><li>C</li></ul>',
        ];
        yield 'mixed list' => [
            "1.\tA\n\t+\tB\n2.\tC",
            '<ol><li>A<ul><li>B</li></ul></li><li>C</li></ol>',
        ];
        yield 'list closed on several levels' => [
            "+\tA\n\t+\tB\n\t\t+\tC\n+\tD",
            '<ul><li>A<ul><li>B<ul><li>C</li></ul></li></ul></li><li>D</li></ul>',
        ];
        yield 'list that ends deep' => [
            "+\tA\n\t+\tB",
            '<ul><li>A<ul><li>B</li></ul></li></ul>',
        ];
        yield 'list item on several lines' => ["+\tA\ncontinued\n+\tB", '<ul><li>A' . "\n" . 'continued</li><li>B</li></ul>'];
        yield 'list item with id and classes' => ["+\tA {#a .b}", '<ul><li id="a" class="b">A</li></ul>'];
        yield 'list that changes type on the same level' => [
            "+\tA\n1.\tB",
            '<ul><li>A</li></ul><ol><li>B</li></ol>',
        ];
        yield 'code' => ["~~\n<div>x</div>\n~~", '<code>&lt;div&gt;x&lt;/div&gt;</code>'];
        yield 'code with a tab and a blank line' => [
            "~~\n\ta\n\nb\n~~",
            "<code>&nbsp;&nbsp;&nbsp;&nbsp;a<br />\n<br />\nb</code>",
        ];
        yield 'code right after a text' => ["Text\n~~\nx\n~~", "<p>Text</p>\n\n<code>x</code>"];
        yield 'unclosed code' => ["~~\ncode", "<p>~~\ncode</p>"];
        yield 'dashes inside a text' => ['a ------ b', '<p>a ------ b</p>'];
        yield 'bold' => ['* foo *', '<p><span class="b">foo</span></p>'];
        yield 'italic' => ['- foo -', '<p><span class="i">foo</span></p>'];
        yield 'underline' => ['_ foo _', '<p><span class="u">foo</span></p>'];
        yield 'strikethrough' => ['/ foo /', '<p><span class="d">foo</span></p>'];
        yield 'combined styles' => ['_-* foo *-_', '<p><span class="u i b">foo</span></p>'];
        yield 'repeated marker' => ['** foo **', '<p><span class="b">foo</span></p>'];
        yield 'styles inside a text' => [
            'a * b c * d and / e / f',
            '<p>a <span class="b">b c</span> d and <span class="d">e</span> f</p>',
        ];
        yield 'styles inside a heading, a list, a table and a quote' => [
            "# * T *\n\n+\t* L *\n\n| * C *\n\n>\t* Q *",
            '<h1><span class="b">T</span></h1>' . "\n\n" . '<ul><li><span class="b">L</span></li></ul>' . "\n\n"
                . '<table><tr><td><span class="b">C</span></td></tr></table>' . "\n\n"
                . '<blockquote><span class="b">Q</span></blockquote>',
        ];
        yield 'markers without a space inside' => ['*foo* and _bar_', '<p>*foo* and _bar_</p>'];
        yield 'closing markers not in the reverse order' => ['_* foo _*', '<p>_* foo _*</p>'];
        yield 'a marker alone' => ['a - b', '<p>a - b</p>'];
        yield 'two dashes around words are an italic' => ['a - b - c', '<p>a <span class="i">b</span> c</p>'];
        yield 'styles are not applied in a code block' => ["~~\n* foo *\n~~", '<code>* foo *</code>'];
        yield 'styles are not applied in a link text' => ['[* a *]:(b)', '<p><a href="b">* a *</a></p>'];
        yield 'line break' => ["L1  \nL2", "<p>L1<br>\nL2</p>"];
        yield 'one trailing space is not a line break' => ["L1 \nL2", "<p>L1 \nL2</p>"];
        yield 'bare URL' => [
            'See https://example.com/a?b=1&c=2.',
            '<p>See <a href="https://example.com/a?b=1&amp;c=2">https://example.com/a?b=1&amp;c=2</a>.</p>',
        ];
        yield 'bare URL between parentheses' => [
            '(https://example.com)',
            '<p>(<a href="https://example.com">https://example.com</a>)</p>',
        ];
        yield 'bare URL that holds parentheses' => [
            'https://en.wikipedia.org/wiki/PHP_(language), ok',
            '<p><a href="https://en.wikipedia.org/wiki/PHP_(language)">https://en.wikipedia.org/wiki/PHP_(language)</a>, ok</p>',
        ];
        yield 'bare URL between angle brackets' => [
            '<https://example.com>',
            '<p>&lt;<a href="https://example.com">https://example.com</a>&gt;</p>',
        ];
        yield 'bare URL in a style' => [
            '* https://example.com *',
            '<p><span class="b"><a href="https://example.com">https://example.com</a></span></p>',
        ];
        yield 'bare URL as the text and the target of a link' => [
            '[https://example.com]:(https://example.com)',
            '<p><a href="https://example.com">https://example.com</a></p>',
        ];
        yield 'bare URL in the alt text of an image' => [
            '![https://example.com](https://example.com/a.png)',
            '<p><img src="https://example.com/a.png" alt="https://example.com"></p>',
        ];
        yield 'URL with another scheme' => ['ftp://example.com', '<p>ftp://example.com</p>'];
        yield 'windows line breaks' => ["A\r\n\r\nB", "<p>A</p>\n\n<p>B</p>"];
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function escapingProvider(): iterable
    {
        yield 'script in a paragraph' => ['<script>alert(1)</script>', '<p>&lt;script&gt;alert(1)&lt;/script&gt;</p>'];
        yield 'html in a heading' => ['# <b>x</b>', '<h1>&lt;b&gt;x&lt;/b&gt;</h1>'];
        yield 'html in a list' => ["+\t<i>x</i>", '<ul><li>&lt;i&gt;x&lt;/i&gt;</li></ul>'];
        yield 'html in a table cell' => ["| <u>x</u>\n| 1", '<table><tr><td>&lt;u&gt;x&lt;/u&gt;</td></tr><tr><td>1</td></tr></table>'];
        yield 'html in a link text' => ['[<b>x</b>]:(a)', '<p><a href="a">&lt;b&gt;x&lt;/b&gt;</a></p>'];
        yield 'double quote in an alt text' => ['![a"b](x.png)', '<p><img src="x.png" alt="a&quot;b"></p>'];
        yield 'double quote that ends an attribute' => ['[x]:(a"onclick="y)', '<p><a href="a&quot;onclick=&quot;y">x</a></p>'];
        yield 'javascript link' => ['[x]:(javascript:alert)', '<p>x</p>'];
        yield 'javascript link with a tab and capitals' => ["[x]:(JaVa\tScRiPt:alert)", '<p>x</p>'];
        yield 'data image' => ['![x](data:image/png;base64,AAAA)', '<p>x</p>'];
        yield 'mailto link' => ['[x]:(mailto:a@b.fr)', '<p><a href="mailto:a@b.fr">x</a></p>'];
        yield 'relative link' => ['[x]:(/page#top)', '<p><a href="/page#top">x</a></p>'];
    }

    #[DataProvider('syntaxProvider')]
    public function testConvertsTheSyntax(string $input, string $expected): void
    {
        self::assertSame($expected, (new Convert())->setText($input)->convert()->getText());
    }

    #[DataProvider('escapingProvider')]
    public function testEscapesTheOutput(string $input, string $expected): void
    {
        self::assertSame($expected, (new Convert())->setText($input)->convert()->getText());
    }

    public function testBareUrlsCanBeLeftAlone(): void
    {
        $html = (new Convert())->setAutoLinks(false)->setText('See https://example.com')->convert()->getText();

        self::assertSame('<p>See https://example.com</p>', $html);
    }

    public function testBareUrlsAreLinksByDefault(): void
    {
        self::assertSame(
            '<p><a href="https://example.com">https://example.com</a></p>',
            (new Convert())->setText('https://example.com')->convert()->getText(),
        );
    }

    public function testSetTextTrimsTheText(): void
    {
        self::assertSame('Hello', (new Convert())->setText("  Hello\n")->getText());
    }
}
