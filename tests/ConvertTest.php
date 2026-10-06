<?php

declare(strict_types=1);

namespace KMark\Tests;

use KMark\Convert;
use KMark\Options;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConvertTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideAttributes(): iterable
    {
        yield 'attr line' => [
            '# T
{.a}

- x
- y
{#l .m}

> q
{.q}

```
c
```
{.c}

| A |
|---|
| 1 |
{#t}',
            '<h1 class="a">T</h1>
<ul id="l" class="m">
<li>x</li>
<li>y</li>
</ul>
<blockquote class="q">
<p>q</p>
</blockquote>
<pre class="c"><code>c
</code></pre>
<table id="t">
<thead>
<tr>
<th>A</th>
</tr>
</thead>
<tbody>
<tr>
<td>1</td>
</tr>
</tbody>
</table>',
        ];

        yield 'attr line bad' => [
            'text

{nothing}',
            '<p>text</p>
<p>{nothing}</p>',
        ];

        yield 'attr line para' => [
            'text
{.p}',
            '<p class="p">text</p>',
        ];
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideCodeBlocks(): iterable
    {
        yield 'fence' => [
            '```
plain
```',
            '<pre><code>plain
</code></pre>',
        ];

        yield 'fence lang' => [
            '```php
<?php
echo 1;
```',
            '<pre><code class="language-php">&lt;?php
echo 1;
</code></pre>',
        ];

        yield 'fence tilde' => [
            '~~~js
let a;
~~~',
            '<pre><code class="language-js">let a;
</code></pre>',
        ];

        yield 'fence attrs' => [
            '```php {#a .b}
echo 1;
```',
            '<pre id="a" class="b"><code class="language-php">echo 1;
</code></pre>',
        ];

        yield 'fence unclosed' => [
            '```
open',
            '<pre><code>open
</code></pre>',
        ];

        yield 'fence blank lines' => [
            '```
a


b
```',
            '<pre><code>a


b
</code></pre>',
        ];

        yield 'fence longer' => [
            '````
```
inner
```
````',
            '<pre><code>```
inner
```
</code></pre>',
        ];

        yield 'indented code' => [
            '    code
    more

        deeper',
            '<pre><code>code
more

    deeper
</code></pre>',
        ];

        yield 'indented not para' => [
            'text
    still text',
            '<p>text
still text</p>',
        ];
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideHeadings(): iterable
    {
        yield 'atx' => [
            '# One
## Two
###### Six
####### Seven',
            '<h1>One</h1>
<h2>Two</h2>
<h6>Six</h6>
<p>####### Seven</p>',
        ];

        yield 'atx closing' => [
            '## Title ##
### Other ###   ',
            '<h2>Title</h2>
<h3>Other</h3>',
        ];

        yield 'atx no space' => [
            '#hashtag',
            '<p>#hashtag</p>',
        ];

        yield 'atx empty' => [
            '#
##',
            '<h1></h1>
<h2></h2>',
        ];

        yield 'atx attrs' => [
            '## Title {#id .a .b}',
            '<h2 id="id" class="a b">Title</h2>',
        ];

        yield 'setext' => [
            'Title
=====

Sub
---',
            '<h1>Title</h1>
<h2>Sub</h2>',
        ];

        yield 'setext multi' => [
            'Line one
line two
===',
            '<h1>Line one
line two</h1>',
        ];

        yield 'setext attrs' => [
            'Title {.x}
===',
            '<h1 class="x">Title</h1>',
        ];
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideLinksAndImages(): iterable
    {
        yield 'link' => [
            '[t](http://x.org "Ti") [u](/p \'T2\') [e]()',
            '<p><a href="http://x.org" title="Ti">t</a> <a href="/p" title="T2">u</a> <a href="">e</a></p>',
        ];

        yield 'link angle' => [
            '[t](<http://x.org/a b>)',
            '<p><a href="http://x.org/a b">t</a></p>',
        ];

        yield 'link parens' => [
            '[t](http://x.org/a_(b))',
            '<p><a href="http://x.org/a_(b)">t</a></p>',
        ];

        yield 'link attrs' => [
            '[t](/u){#i .c}',
            '<p><a href="/u" id="i" class="c">t</a></p>',
        ];

        yield 'link unsafe' => [
            '[t](javascript:alert(1)) ![i](data:text/html;x)',
            '<p>t i</p>',
        ];

        yield 'ref link' => [
            '[t][a] and [b][] and [c]

[a]: http://a.org "A"
[b]: /b
[c]: <http://c.org> \'C\'',
            '<p><a href="http://a.org" title="A">t</a> and <a href="/b">b</a> and <a href="http://c.org" title="C">c</a></p>',
        ];

        yield 'ref missing' => [
            '[t][nope] and [x]',
            '<p>[t][nope] and [x]</p>',
        ];

        yield 'ref in fence' => [
            '```
[a]: /x
```

[a]',
            '<pre><code>[a]: /x
</code></pre>
<p>[a]</p>',
        ];

        yield 'image' => [
            '![alt *x*](/i.png "T"){.img} ![r][im]

[im]: /r.png',
            '<p><img src="/i.png" alt="alt *x*" title="T" class="img"> <img src="/r.png" alt="r"></p>',
        ];

        yield 'autolink' => [
            '<http://x.org/a?b=1&c=2> <me@x.org> <javascript:alert(1)>',
            '<p><a href="http://x.org/a?b=1&amp;c=2">http://x.org/a?b=1&amp;c=2</a> <a href="mailto:me@x.org">me@x.org</a> &lt;javascript:alert(1)&gt;</p>',
        ];

        yield 'bare url' => [
            'see https://x.org/a_b_c. and (https://x.org/p_(q)) *https://x.org*',
            '<p>see <a href="https://x.org/a_b_c">https://x.org/a_b_c</a>. and (<a href="https://x.org/p_(q)">https://x.org/p_(q)</a>) <em><a href="https://x.org">https://x.org</a></em></p>',
        ];
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideLists(): iterable
    {
        yield 'ul' => [
            '- a
- b
- c',
            '<ul>
<li>a</li>
<li>b</li>
<li>c</li>
</ul>',
        ];

        yield 'ul star plus' => [
            '* a
+ b
- c',
            '<ul>
<li>a</li>
</ul>
<ul>
<li>b</li>
</ul>
<ul>
<li>c</li>
</ul>',
        ];

        yield 'ol' => [
            '1. a
2. b
3. c',
            '<ol>
<li>a</li>
<li>b</li>
<li>c</li>
</ol>',
        ];

        yield 'ol start' => [
            '3. a
4. b',
            '<ol start="3">
<li>a</li>
<li>b</li>
</ol>',
        ];

        yield 'ol paren' => [
            '1) a
2) b',
            '<ol>
<li>a</li>
<li>b</li>
</ol>',
        ];

        yield 'nested' => [
            '- a
  - b
    - c
  - d
- e',
            '<ul>
<li>a
<ul>
<li>b
<ul>
<li>c</li>
</ul>
</li>
<li>d</li>
</ul>
</li>
<li>e</li>
</ul>',
        ];

        yield 'nested ordered' => [
            '1. a
   1. b
   2. c
2. d',
            '<ol>
<li>a
<ol>
<li>b</li>
<li>c</li>
</ol>
</li>
<li>d</li>
</ol>',
        ];

        yield 'loose' => [
            '- a

- b

- c',
            '<ul>
<li>
<p>a</p>
</li>
<li>
<p>b</p>
</li>
<li>
<p>c</p>
</li>
</ul>',
        ];

        yield 'item paragraphs' => [
            '- a

  b
- c',
            '<ul>
<li>
<p>a</p>
<p>b</p>
</li>
<li>
<p>c</p>
</li>
</ul>',
        ];

        yield 'item code' => [
            '- a

      code
- b',
            '<ul>
<li>
<p>a</p>
<pre><code>code
</code></pre>
</li>
<li>
<p>b</p>
</li>
</ul>',
        ];

        yield 'item quote' => [
            '- a
  > q',
            '<ul>
<li>a
<blockquote>
<p>q</p>
</blockquote>
</li>
</ul>',
        ];

        yield 'lazy item' => [
            '- a
b
- c',
            '<ul>
<li>a
b</li>
<li>c</li>
</ul>',
        ];

        yield 'empty item' => [
            '-
- b',
            '<ul>
<li></li>
<li>b</li>
</ul>',
        ];

        yield 'item attrs' => [
            '- a {.x}
- b',
            '<ul>
<li class="x">a</li>
<li>b</li>
</ul>',
        ];

        yield 'list then para' => [
            '- a
- b

text',
            '<ul>
<li>a</li>
<li>b</li>
</ul>
<p>text</p>',
        ];

        yield 'list tab nested' => [
            '- a
	- b',
            '<ul>
<li>a
<ul>
<li>b</li>
</ul>
</li>
</ul>',
        ];

        yield 'tight blank nested' => [
            '- a
  - b

  - c
- d',
            '<ul>
<li>a
<ul>
<li>
<p>b</p>
</li>
<li>
<p>c</p>
</li>
</ul>
</li>
<li>d</li>
</ul>',
        ];

        yield 'ordered interrupt' => [
            'text
2. no

text
1. yes',
            '<p>text
2. no</p>
<p>text</p>
<ol>
<li>yes</li>
</ol>',
        ];

        yield 'bullet interrupt' => [
            'text
- list',
            '<p>text</p>
<ul>
<li>list</li>
</ul>',
        ];

        yield 'list in quote in list' => [
            '- a
  > b
  > - c',
            '<ul>
<li>a
<blockquote>
<p>b</p>
<ul>
<li>c</li>
</ul>
</blockquote>
</li>
</ul>',
        ];
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideParagraphsAndInline(): iterable
    {
        yield 'para' => [
            'One
two

Three',
            '<p>One
two</p>
<p>Three</p>',
        ];

        yield 'break spaces' => [
            'one  
two',
            '<p>one<br>
two</p>',
        ];

        yield 'break backslash' => [
            'one\\
two',
            '<p>one<br>
two</p>',
        ];

        yield 'para attrs' => [
            'text {.lead}',
            '<p class="lead">text</p>',
        ];

        yield 'em strong' => [
            '*a* _b_ **c** __d__ ***e***',
            '<p><em>a</em> <em>b</em> <strong>c</strong> <strong>d</strong> <strong><em>e</em></strong></p>',
        ];

        yield 'snake' => [
            'snake_case_word and 2*3*4',
            '<p>snake_case_word and 2*3*4</p>',
        ];

        yield 'del' => [
            '~~gone~~ and ~one~',
            '<p><del>gone</del> and ~one~</p>',
        ];

        yield 'emph in link' => [
            '[*a* b](/u)',
            '<p><a href="/u"><em>a</em> b</a></p>',
        ];

        yield 'code span' => [
            'use `code` here and ``a ` b`` and ` x `',
            '<p>use <code>code</code> here and <code>a ` b</code> and <code>x</code></p>',
        ];

        yield 'code span attrs' => [
            '`x`{.k} and `y`{bad}',
            '<p><code class="k">x</code> and <code>y</code>{bad}</p>',
        ];

        yield 'code escapes html' => [
            '`<b>&</b>`',
            '<p><code>&lt;b&gt;&amp;&lt;/b&gt;</code></p>',
        ];

        yield 'escapes' => [
            '\\*not\\* \\_x\\_ \\[a\\](b) \\# \\\\ \\<b\\> \\&amp;',
            '<p>*not* _x_ [a](b) # \\ &lt;b&gt; &amp;amp;</p>',
        ];

        yield 'backslash other' => [
            'C:\\Users and \\a',
            '<p>C:\\Users and \\a</p>',
        ];

        yield 'entities' => [
            'a & b &amp; c &copy; < > "q"',
            '<p>a &amp; b &amp; c &copy; &lt; &gt; "q"</p>',
        ];

        yield 'html escaped' => [
            '<b>x</b> <script>alert(1)</script>',
            '<p>&lt;b&gt;x&lt;/b&gt; &lt;script&gt;alert(1)&lt;/script&gt;</p>',
        ];

        yield 'unicode' => [
            'é *à* `ü`',
            '<p>é <em>à</em> <code>ü</code></p>',
        ];

        yield 'crlf' => [
            'a
b

c',
            '<p>a
b</p>
<p>c</p>',
        ];

        yield 'only blank' => [
            '

   
',
            '',
        ];

        yield 'empty' => [
            '',
            '',
        ];
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideQuotes(): iterable
    {
        yield 'quote' => [
            '> a
> b

> c',
            '<blockquote>
<p>a
b</p>
</blockquote>
<blockquote>
<p>c</p>
</blockquote>',
        ];

        yield 'quote lazy' => [
            '> a
lazy',
            '<blockquote>
<p>a
lazy</p>
</blockquote>',
        ];

        yield 'quote nested' => [
            '> a
>
> > b',
            '<blockquote>
<p>a</p>
<blockquote>
<p>b</p>
</blockquote>
</blockquote>',
        ];

        yield 'quote with blocks' => [
            '> # H
> - x
> - y
>
> ```
> c
> ```',
            '<blockquote>
<h1>H</h1>
<ul>
<li>x</li>
<li>y</li>
</ul>
<pre><code>c
</code></pre>
</blockquote>',
        ];

        yield 'quote empty' => [
            '>',
            '<blockquote>
</blockquote>',
        ];
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideRulesAndTables(): iterable
    {
        yield 'hr' => [
            '---

***

___

- - -',
            '<hr>
<hr>
<hr>
<hr>',
        ];

        yield 'hr after text' => [
            'text

---',
            '<p>text</p>
<hr>',
        ];

        yield 'table' => [
            '| A | B |
|---|---|
| 1 | 2 |',
            '<table>
<thead>
<tr>
<th>A</th>
<th>B</th>
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

        yield 'table align' => [
            'A | B | C
:-- | :-: | --:
1 | 2 | 3',
            '<table>
<thead>
<tr>
<th style="text-align: left">A</th>
<th style="text-align: center">B</th>
<th style="text-align: right">C</th>
</tr>
</thead>
<tbody>
<tr>
<td style="text-align: left">1</td>
<td style="text-align: center">2</td>
<td style="text-align: right">3</td>
</tr>
</tbody>
</table>',
        ];

        yield 'table short row' => [
            '| A | B |
|---|---|
| 1 |',
            '<table>
<thead>
<tr>
<th>A</th>
<th>B</th>
</tr>
</thead>
<tbody>
<tr>
<td>1</td>
<td></td>
</tr>
</tbody>
</table>',
        ];

        yield 'table pipe escape' => [
            '| A |
|---|
| a \\| b |',
            '<table>
<thead>
<tr>
<th>A</th>
</tr>
</thead>
<tbody>
<tr>
<td>a | b</td>
</tr>
</tbody>
</table>',
        ];

        yield 'table inline' => [
            '| A |
|---|
| *x* `y` |',
            '<table>
<thead>
<tr>
<th>A</th>
</tr>
</thead>
<tbody>
<tr>
<td><em>x</em> <code>y</code></td>
</tr>
</tbody>
</table>',
        ];

        yield 'table header only' => [
            '| A | B |
|---|---|',
            '<table>
<thead>
<tr>
<th>A</th>
<th>B</th>
</tr>
</thead>
</table>',
        ];

        yield 'not table' => [
            'a | b
---',
            '<h2>a | b</h2>',
        ];
    }

    public function test_a_converter_keeps_its_options_and_holds_the_converted_text(): void
    {
        $converter = (new Convert(new Options(autoLinks: false)))->withText("\n\n*a*\n")->convert();

        self::assertSame('<p><em>a</em></p>', $converter->getText());
    }

    public function test_a_text_that_forges_a_token_is_written_as_text(): void
    {
        self::assertSame('<p>a 0 b</p>', Convert::toHtml("a \x1a0\x1b b"));
    }

    #[DataProvider('provideAttributes')]
    public function test_attributes(string $markdown, string $html): void
    {
        self::assertSame($html, Convert::toHtml($markdown));
    }

    public function test_bare_urls_stay_as_text_when_the_option_says_so(): void
    {
        self::assertSame('<p>https://x.org/_a_</p>', Convert::toHtml('https://x.org/_a_', new Options(autoLinks: false)));
    }

    #[DataProvider('provideCodeBlocks')]
    public function test_code_blocks(string $markdown, string $html): void
    {
        self::assertSame($html, Convert::toHtml($markdown));
    }

    #[DataProvider('provideHeadings')]
    public function test_headings(string $markdown, string $html): void
    {
        self::assertSame($html, Convert::toHtml($markdown));
    }

    #[DataProvider('provideLinksAndImages')]
    public function test_links_and_images(string $markdown, string $html): void
    {
        self::assertSame($html, Convert::toHtml($markdown));
    }

    #[DataProvider('provideLists')]
    public function test_lists(string $markdown, string $html): void
    {
        self::assertSame($html, Convert::toHtml($markdown));
    }

    #[DataProvider('provideParagraphsAndInline')]
    public function test_paragraphs_and_inline(string $markdown, string $html): void
    {
        self::assertSame($html, Convert::toHtml($markdown));
    }

    #[DataProvider('provideQuotes')]
    public function test_quotes(string $markdown, string $html): void
    {
        self::assertSame($html, Convert::toHtml($markdown));
    }

    public function test_raw_html_is_kept_when_the_option_says_so(): void
    {
        $html = Convert::toHtml("<div class=\"x\">\nraw *no*\n</div>\n\ntext <b>bold</b> <!-- c -->\n\n*em*", new Options(unsafeAllowRawHtml: true));

        self::assertSame("<div class=\"x\">\nraw *no*\n</div>\n<p>text <b>bold</b> <!-- c --></p>\n<p><em>em</em></p>", $html);
    }

    #[DataProvider('provideRulesAndTables')]
    public function test_rules_and_tables(string $markdown, string $html): void
    {
        self::assertSame($html, Convert::toHtml($markdown));
    }
}
