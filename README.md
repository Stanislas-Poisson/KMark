# KMark

A custom Markdown parser written in PHP. It extends the usual syntax so that an id and CSS classes can be attached directly to an element, for example `# Title {#myId .class1 .class2}`. It is made for the web: the output is HTML.

> **Status: proof of concept.** KMark was written in 2013 and moved to PHP 7 in 2017. The converter was cleaned and tested ([#2](https://github.com/Stanislas-Poisson/KMark/issues/2)), but the text styles are still missing and there is no package and no configuration yet. See [Known limits](#known-limits) and [Roadmap](#roadmap).

## Requirements

PHP 8.3 or higher. There is no dependency.

## Usage

There is no published Composer package yet. Include the file, then convert a text:

```php
<?php

require 'src/Convert.php';

$converter = new KMark\Convert();

echo $converter
    ->setText("# Title\n\nSome text with a [link]:(https://example.com).\n\n+\tOne\n+\tTwo")
    ->convert()
    ->getText();
```

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
| Table | `\| A \| B`, a separator line, then rows | `<table><tr><td>…</td></tr>…</table>` |
| Horizontal rule | six dashes or more, alone | `<hr>` |
| Link | `[Example]:(https://example.com "Title")` | `<a href="https://example.com" title="Title">Example</a>` |
| Image | `![alt](https://example.com/a.png)` | `<img src="https://example.com/a.png" alt="alt">` |

Lists can be nested and mixed: one more tab opens a list inside the current item, and the marker (`+` or `1.`) of each item gives the type of its list.

### Id and classes

Put `{#id .class1 .class2}` at the end of an element: a heading, a paragraph, a list item, a quote line, or inside the parentheses of a link or an image.

```text
# Title {#myId .class1 .class2}
[Example]:(https://example.com "Title" {#myId .class})
![alt](https://example.com/a.png {#myId .class})
```

Only the first id is kept, and only letters, digits, `_` and `-` are accepted in a name. If the braces hold anything else, they stay in the text.

### Safety

The input is escaped: HTML written in the text is shown as text, so `<script>` is never executed. Only the links and images with a relative URL or one of the schemes `http`, `https`, `mailto`, `tel` and `ftp` are kept. For any other scheme (`javascript:`, `data:`…), the text of the link is written without the link.

## Known limits

- **Text styles are not available yet**: bold, italic, underline and strikethrough, and the line break (two trailing spaces), are left as plain text ([#3](https://github.com/Stanislas-Poisson/KMark/issues/3)).
- **An URL cannot contain a closing parenthesis**: the link stops at the first one.
- **There is no configuration**: the escaping, the schemes and the CSS classes cannot be changed yet ([#4](https://github.com/Stanislas-Poisson/KMark/issues/4)).
- A table has no header cell: every cell is a `td`.

## Development

```sh
composer install
composer check   # PHPStan at the maximum level, then PHPUnit
```

## Roadmap

1. Fix the known bugs and clean the application ([#2](https://github.com/Stanislas-Poisson/KMark/issues/2)): done.
2. Add the missing features ([#3](https://github.com/Stanislas-Poisson/KMark/issues/3)).
3. Turn KMark into a Composer package with a configuration, for example to switch the automatic links on or off ([#4](https://github.com/Stanislas-Poisson/KMark/issues/4)).

## License

[MIT](LICENSE). Copyright (c) 2013 Stanislas Poisson.
