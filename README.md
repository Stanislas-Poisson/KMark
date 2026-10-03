# KMark

A custom Markdown parser written in PHP. It extends the usual syntax so that an id and CSS classes can be attached directly to an element, for example `# Title {#myId .class1 .class2}`. It is made for the web: the output is HTML.

> **Status: proof of concept.** KMark was written in 2013 and moved to PHP 7 in 2017. It works for the basic blocks below, but part of the syntax is broken or disabled, the output is not escaped, and there is no package, no test and no configuration yet. Do not use it on text you do not trust. See [Known issues](#known-issues) and [Roadmap](#roadmap).

## Requirements

PHP 7.0 or higher. The code has been run on PHP 8.4. There is no dependency.

## Usage

There is no Composer package yet. Include the file, then convert a text:

```php
<?php

require 'KMark.php';

$converter = new KMark\Convert();

echo $converter
    ->setText("# Title\n\nSome text with a [link]:(https://example.com).\n\n+\tOne\n+\tTwo")
    ->convert()
    ->getText();
```

Output:

```html
<h1>Title</h1>

<p>Some text with a <a href="https://example.com" >link</a>.</p>

<ul><li>One</li><li>Two</li></ul>
```

## Syntax

The columns show the input and the output of the current code. Indentation inside lists, quotes and tables uses a real tab character.

| Element | Input | Output |
| :--- | :--- | :--- |
| Heading (1 to 6 `#`) | `## Sub title` | `<h2>Sub title</h2>` |
| Paragraph | text, separated by a blank line | `<p>text</p>` |
| Unordered list | `+<tab>One`, one more tab per level | `<ul><li>One</li>…</ul>` |
| Ordered list | `1.<tab>One` | `<ol><li>One</li>…</ol>` |
| Quote | `><tab>Line` | `<blockquote>Line…</blockquote>` |
| Code | text between two lines of `~~` | `<code>…</code>`, escaped, tabs as spaces |
| Table | `\| A \| B`, a separator line, then rows | `<table><tr><td>…</td></tr>…</table>` |
| Horizontal rule | six dashes or more | `<hr>` |
| Link | `[Example]:(https://example.com "Title")` | `<a href="https://example.com"  title="Title">Example</a>` |
| Image | `![alt](https://example.com/a.png)` | `<img src="https://example.com/a.png" alt="alt">` |

Id and classes go at the end of the element, in braces: `# Title {#myId .class1 .class2}` and `![alt](a.png {#myId .class})`.

## Known issues

These behaviours were checked by running the code on PHP 8.4.

- **Id and classes** work on images, but not on links and paragraphs (`Hello {#intro .lead}` stays as text, and a link gets the braces inside its `href`). On headings they work but the attributes are glued together: `<h1 id="monId"class="a b">`.
- **Text styles are disabled**: bold, italic, underline and strikethrough (`* foo *`, `- foo -`, `_ foo _`, `/ foo /`) and the line break (two trailing spaces) are in the code but commented out, so they are left as plain text.
- **Nested lists** are not closed correctly: the outer `</li></ul>` can be missing, and a mixed list can produce `<li>B</ul>`.
- **Code blocks** start with a stray `<br />`.
- **The input is not escaped**: raw HTML, including `<script>`, goes through to the output. Only the content of code blocks is escaped.
- The code has no test, duplicates the id and class parsing and does not follow a coding standard.

## Roadmap

1. Fix the known bugs and clean the application ([#2](https://github.com/Stanislas-Poisson/KMark/issues/2)).
2. Add the missing features ([#3](https://github.com/Stanislas-Poisson/KMark/issues/3)).
3. Turn KMark into a Composer package with a configuration, for example to switch the automatic links on or off ([#4](https://github.com/Stanislas-Poisson/KMark/issues/4)).

## License

[MIT](LICENSE). Copyright (c) 2013 Stanislas Poisson.
