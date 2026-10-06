---
layout: home
hero:
  name: "KMark"
  tagline: The original Markdown to HTML, with an id and CSS classes on the elements, and the elements of the styles of your choice.
  actions:
    - theme: brand
      text: Get started
      link: /guide/readme
    - theme: alt
      text: Reference
      link: /reference/
features:
  - title: The Markdown you know
    details: Headings, lists, quotes, tables, code in backticks with its language, links and images, as in the original Markdown.
  - title: An id and classes
    details: "Write {#id .class} after a heading, a paragraph, a list item, a link or a code span, or on a line of its own for a block."
  - title: Your own elements
    details: "Bold, italic, underline and strikethrough are b, i, u and del. Choose other elements or a span with a class with the option tags."
---

## Quick start

```bash
composer require stanislas-poisson/kmark
```

```php
use KMark\Convert;

echo Convert::toHtml("# Title {#top}\n\nSome **bold** text and a [link](https://example.com){.external}.");
```

```html
<h1 id="top">Title</h1>
<p>Some <b>bold</b> text and a <a href="https://example.com" class="external">link</a>.</p>
```

The [guide](/guide/readme) has the whole syntax and every option, and the [reference](/reference/) every class.
