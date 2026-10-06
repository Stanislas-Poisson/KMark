# KMark

A custom Markdown parser written in PHP. It extends the usual syntax so that an id and CSS classes can be attached directly to an element, for example `# Title {#myId .class1 .class2}`. It is made for the web: the output is HTML.

> **Status: stable, `1.0.0`.** KMark was written in 2013 and moved to PHP 7 in 2017. The converter was cleaned and tested ([#2](https://github.com/Stanislas-Poisson/KMark/issues/2)) and the text styles were added ([#3](https://github.com/Stanislas-Poisson/KMark/issues/3)), and it is published as a Composer package. See [Known limits](#known-limits) and [Roadmap](#roadmap).

## Documentation

The documentation site is <https://stanislas-poisson.github.io/KMark/>: this guide and the reference of every class, read from the source, for each released version (selector at the top right, `next` is `main`). Build it with `cd docs && npm ci && npm run dev`.

## Requirements

PHP 8.3 or higher. There is no dependency.

## Usage

Install it with Composer:

```bash
composer require stanislas-poisson/kmark
```

```php
<?php

use KMark\Convert;

require 'vendor/autoload.php';

echo Convert::toHtml("# Title\n\nSome text with a [link]:(https://example.com).\n\n+\tOne\n+\tTwo");
```

The same with an instance:

```php
$html = (new Convert())
    ->withText("# Title")
    ->convert()
    ->getText();
```

A `Convert` object never changes: `withText()` and `convert()` return a new one.

Output:

```html
<h1>Title</h1>

<p>Some text with a <a href="https://example.com">link</a>.</p>

<ul><li>One</li><li>Two</li></ul>
```

The blocks of the output are separated by a blank line.

## Syntax

The columns show the input and the output. Indentation inside lists and quotes uses a real tab character.

| Element | Input | Output |
| :--- | :--- | :--- |
| Heading (1 to 6 `#`) | `## Sub title` | `<h2>Sub title</h2>` |
| Paragraph | text, separated by a blank line | `<p>text</p>` |
| Unordered list | `+<tab>One`, one more tab per level | `<ul><li>One</li>…</ul>` |
| Ordered list | `1.<tab>One`, one more tab per level | `<ol><li>One</li>…</ol>` |
| Quote | `><tab>Line` | `<blockquote>Line…</blockquote>` |
| Code | lines between two lines of `~~` | `<code>…</code>`, escaped, tabs as spaces |
| Table | `\| A \| B`, a line of dashes, then rows | `<table><thead><tr><th>…</th></tr></thead><tbody><tr><td>…</td></tr>…</tbody></table>` |
| Horizontal rule | six dashes or more, alone | `<hr>` |
| Bold | `*foo*` | `<span class="b">foo</span>` |
| Italic | `-foo-` | `<span class="i">foo</span>` |
| Underline | `_foo_` | `<span class="u">foo</span>` |
| Strikethrough | `~foo~` | `<span class="d">foo</span>` |
| Line break | two spaces at the end of a line | `<br>` |
| Link | `[Example]:(https://example.com "Title")` | `<a href="https://example.com" title="Title">Example</a>` |
| Bare URL | `https://example.com` | `<a href="https://example.com">https://example.com</a>` |
| Image | `![alt](https://example.com/a.png)` | `<img src="https://example.com/a.png" alt="alt">` |

Lists can be nested and mixed: one more tab opens a list inside the current item, and the marker (`+` or `1.`) of each item gives the type of its list.

### Styles

A marker is written right before the first character and right after the last one of the styled text, with no space inside: `*foo bar*`, not `* foo bar *`. It never starts or ends inside a word, so `snake_case_name`, `well-known-fact` and `2013-08-12` stay as they are. A dash between two spaces is a dash: `a - b - c` stays as it is, and `a - -b- - c` gives `a - <span class="i">b</span> - c`.

The markers can be combined by closing them in the reverse order: `_-*foo*-_` gives `<span class="u i b">foo</span>`. Markers that are not closed in the reverse order stay in the text.

A style works in a paragraph, a heading, a list item, a quote and a table cell, and around a bare URL. It does not work inside a code block or in the text of a link.

### Bare URLs

The `http://` and `https://` URLs written in a text become links. A final `.`, `,`, `;`, `:`, `!`, `?`, `*`, `_`, `~`, `-` or a closing parenthesis that the URL did not open is left outside the link, and the style markers inside a URL are not read. To keep the URLs as text, see the option `autoLinks` below.

### Escape character

A backslash before a marker writes the marker as it is, so that it is not read as a style, a link, a block or an id: `\*not bold\*` gives `*not bold*`.

| Written | Gives | Used for |
| :--- | :--- | :--- |
| `\*` `\-` `\_` `\~` | `*` `-` `_` `~` | the style markers |
| `\[` `\]` `\!` `\(` `\)` | `[` `]` `!` `(` `)` | a link or an image, or a parenthesis in a URL |
| `\{` `\}` | `{` `}` | the id and the classes: `# Title \{#id}` keeps its braces |
| `\#` `\+` `\>` | `#` `+` `>` | the start of a heading, a list or a quote |
| `\\` | `\` | a backslash before one of these characters |

In a table, `\|` writes a bar inside a cell.

A backslash before any other character stays a backslash (`C:\Users` is written as it is), and a code block is never read.

### Tables

The lines before the line of dashes are the header cells (`<th>` in a `<thead>`), and the lines after it are the rows (`<td>` in a `<tbody>`). The colons of the line of dashes align the columns, with a `style="text-align: …"`:

```text
| Name | Size | Note
|:-----|-----:|:----:
| a    | 1    | x
```

`:---` is left, `---:` is right and `:---:` is the centre. A table without a line of dashes has only `<td>` cells.

### Id and classes

Put `{#id .class1 .class2}` at the end of an element: a heading, a paragraph, a list item, a quote line, or inside the parentheses of a link or an image.

```text
# Title {#myId .class1 .class2}
[Example]:(https://example.com "Title" {#myId .class})
![alt](https://example.com/a.png {#myId .class})
```

Only the first id is kept, and only letters, digits, `_` and `-` are accepted in a name. If the braces hold anything else, they stay in the text.

### Safety

By default the input is escaped: HTML written in the text is shown as text, so `<script>` is never executed. Only the links and images with a relative URL or an allowed scheme (`http`, `https`, `mailto`, `tel` and `ftp` by default) are kept. For any other scheme (`javascript:`, `data:`…), the text of the link is written without the link.

## Options

The settings are given with an immutable `Options` object, to `Convert::toHtml()` or to the constructor of `Convert`. Use the named arguments to change one:

```php
use KMark\Convert;
use KMark\Options;

$options = new Options(
    autoLinks: false,
    styleClasses: ['*' => 'strong', '-' => 'em'],
    allowedSchemes: ['https', 'mailto'],
);

echo Convert::toHtml('*hello* https://example.com', $options);
```

| Option | Default | Description |
| :--- | :--- | :--- |
| `autoLinks` | `true` | Turn the bare `http://` and `https://` URLs into links. |
| `styleClasses` | `['*' => 'b', '-' => 'i', '_' => 'u', '~' => 'd']` | The CSS class of each style marker. A marker that is left out is not a style: `[]` switches all the styles off. |
| `allowedSchemes` | `['http', 'https', 'mailto', 'tel', 'ftp']` | The schemes that a link or an image can have, in lowercase. A relative URL is always allowed. |
| `unsafeAllowRawHtml` | `false` | Keep the HTML written in the text instead of escaping it. **Never use it on a text you do not trust**: it allows scripts. Code blocks stay escaped. |

An invalid marker, class or scheme throws an `InvalidArgumentException`.

### Malformed input

KMark never throws on a text. Whatever it does not understand stays in the text, escaped: a code block that is not closed is a paragraph, a "{…}" block that is not an id and classes stays as it is, a style marker that is not closed stays as it is, and a link with an unsafe URL is written as its text.

## Known limits

- **A link URL holds one level of parentheses**: `[x]:(https://e.com/a_(b))` works, `a_((b))` does not. Write `\)` for any other closing parenthesis.

## Development

```sh
make install
make hooks     # the Git hooks
make quality   # Pint, PHPStan, Rector, PHP Insights and PHPUnit
```

`make help` lists every command. See [CONTRIBUTING.md](CONTRIBUTING.md) for the details.

## Roadmap

1. Fix the known bugs and clean the application ([#2](https://github.com/Stanislas-Poisson/KMark/issues/2)): done.
2. Add the missing features ([#3](https://github.com/Stanislas-Poisson/KMark/issues/3)): done.
3. Turn KMark into a Composer package with a configuration ([#4](https://github.com/Stanislas-Poisson/KMark/issues/4)): done, released as `1.0.0`.

## License

[MIT](LICENSE). Copyright (c) 2013 Stanislas Poisson.
