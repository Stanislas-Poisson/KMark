<?php

declare(strict_types=1);

namespace KMark\Tests;

use KMark\Convert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The corners of the syntax: what starts a block inside another, what only looks like a block, and attributes.
 */
final class EdgeCasesTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideEdgeCases(): iterable
    {
        yield 'heading as the first line of an item' => [
            '- # Title
- text',
            '<ul>
<li>
<h1>Title</h1>
</li>
<li>text</li>
</ul>',
        ];

        yield 'fence as the first line of an item' => [
            '- ```
  code
  ```
- text',
            '<ul>
<li>
<pre><code>code
</code></pre>
</li>
<li>text</li>
</ul>',
        ];

        yield 'indented code followed by blank lines and text' => [
            '    a



text',
            '<pre><code>a
</code></pre>
<p>text</p>',
        ];

        yield 'item with two blocks and no blank line' => [
            '- a
  > q
  text',
            '<ul>
<li>a
<blockquote>
<p>q
text</p>
</blockquote>
</li>
</ul>',
        ];

        yield 'nested list in a tight item with text after' => [
            '- a
  - b

  c
- d',
            '<ul>
<li>a
<ul>
<li>b</li>
</ul>
c
</li>
<li>d</li>
</ul>',
        ];

        yield 'ordered list after a paragraph with 1.' => [
            'text
1. one
2. two',
            '<p>text</p>
<ol>
<li>one</li>
<li>two</li>
</ol>',
        ];

        yield 'numbers of two digits' => [
            '10. ten
11. eleven',
            '<ol start="10">
<li>ten</li>
<li>eleven</li>
</ol>',
        ];

        yield 'bullet without a space is text' => [
            '-no
*no
+no',
            '<p>-no
*no
+no</p>',
        ];

        yield 'long marker gap is code' => [
            '-      code',
            '<ul>
<li>
<pre><code> code
</code></pre>
</li>
</ul>',
        ];

        yield 'mixed content' => [
            '# T

text *a*

- x

> q

```
c
```

---',
            '<h1>T</h1>
<p>text <i>a</i></p>
<ul>
<li>x</li>
</ul>
<blockquote>
<p>q</p>
</blockquote>
<pre><code>c
</code></pre>
<hr>',
        ];

        yield 'link text with brackets' => [
            '[a [b] c](/u)',
            '<p><a href="/u">a [b] c</a></p>',
        ];

        yield 'image alone in an item' => [
            '- ![i](/i.png)',
            '<ul>
<li><img src="/i.png" alt="i"></li>
</ul>',
        ];

        yield 'several attribute lines' => [
            '# T
{.a}
{.b}',
            '<h1 class="a b">T</h1>',
        ];

        yield 'attribute line at the very start' => [
            '{.a}

text',
            '<p>{.a}</p>
<p>text</p>',
        ];

        yield 'table followed by text' => [
            '| A |
|---|
| 1 |
text',
            '<table>
<thead>
<tr>
<th>A</th>
</tr>
</thead>
<tbody>
<tr>
<td>1</td>
</tr>
<tr>
<td>text</td>
</tr>
</tbody>
</table>',
        ];

        yield 'table with escaped pipe at the end' => [
            '| A | B \\|
|---|---|
| 1 | 2 |',
            '<table>
<thead>
<tr>
<th>A</th>
<th>B |</th>
</tr>
</thead>
<tbody>
<tr>
<td>1</td>
<td>2</td>
</tr>
</tbody>
</table>',
        ];

        yield 'heading right after text' => [
            'text
# H
more',
            '<p>text</p>
<h1>H</h1>
<p>more</p>',
        ];

        yield 'quote after text' => [
            'text
> q',
            '<p>text</p>
<blockquote>
<p>q</p>
</blockquote>',
        ];

        yield 'fence after text' => [
            'text
```
c
```',
            '<p>text</p>
<pre><code>c
</code></pre>',
        ];

        yield 'tab after list marker' => [
            '-	text',
            '<ul>
<li>text</li>
</ul>',
        ];

        yield 'tab indented code' => [
            '	code',
            '<pre><code>code
</code></pre>',
        ];

        yield 'two code spans' => [
            '`a` `b`',
            '<p><code>a</code> <code>b</code></p>',
        ];

        yield 'unclosed backtick' => [
            'a ` b',
            '<p>a ` b</p>',
        ];

        yield 'star alone' => [
            'a * b * c',
            '<p>a * b * c</p>',
        ];

        yield 'underscores around words' => [
            '_a_b and a_b_ and __a__b',
            '<p><i>a_b and a_b</i> and __a__b</p>',
        ];

        yield 'heading with both' => [
            '# T {#a .x}
{#b .y}',
            '<h1 id="a" class="x y">T</h1>',
        ];

        yield 'list with both' => [
            '1. a
3. b
{.l}',
            '<ol class="l">
<li>a</li>
<li>b</li>
</ol>',
        ];

        yield 'an attribute line that is not valid' => [
            '# T
{#a b}',
            '<h1>T</h1>
<p>{#a b}</p>',
        ];
    }

    #[DataProvider('provideEdgeCases')]
    public function test_edge_cases(string $markdown, string $html): void
    {
        self::assertSame($html, Convert::toHtml($markdown));
    }
}
