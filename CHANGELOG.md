# Changelog

All notable changes are listed here. The format follows [Keep a Changelog](https://keepachangelog.com/), and the project follows [Semantic Versioning](https://semver.org/) from the version 1.0.0.

## 1.0.1 - 2026-10-06

### Added

- A documentation site on GitHub Pages: the guide (the README) and the reference of every class, read from the source, for each released version ([#28](https://github.com/Stanislas-Poisson/KMark/issues/28)).

## 1.0.0 - 2026-10-04

The first stable version: KMark was a proof of concept, and is now a tested library with a configuration, published as a Composer package.

### Added

- The escape character: a backslash before a marker writes it as it is ([#26](https://github.com/Stanislas-Poisson/KMark/issues/26)).
- Header cells and aligned columns in a table: the lines before the line of dashes are `<th>` in a `<thead>`, the colons of the line of dashes align the columns ([#26](https://github.com/Stanislas-Poisson/KMark/issues/26)).
- A link or an image URL can hold one level of parentheses ([#26](https://github.com/Stanislas-Poisson/KMark/issues/26)).
- Text styles (`*bold*`, `-italic-`, `_underline_`, `~strikethrough~`), the line break and the bare URLs ([#3](https://github.com/Stanislas-Poisson/KMark/issues/3)).
- The `Options` object and `Convert::toHtml()` ([#4](https://github.com/Stanislas-Poisson/KMark/issues/4)).
- The quality tools of the other zairakai projects: Pint, PHPStan with the strict rules, Rector, PHP Insights at 100 %, markdownlint, a `Makefile` and Git hooks ([#14](https://github.com/Stanislas-Poisson/KMark/issues/14)), now taken from php-dev-tools instead of a copy ([#16](https://github.com/Stanislas-Poisson/KMark/issues/16)).

### Changed

- The converter is rewritten block by block and split into small classes under `src/`. The HTML written in the text is escaped, and links and images keep only a safe URL ([#2](https://github.com/Stanislas-Poisson/KMark/issues/2)).
- The class moved from `KMark.php` to `src/Convert.php` (PSR-4, `KMark\Convert`).
- `Convert` is immutable: `setText()` is now `withText()`, and `withText()` and `convert()` return a new object.

### Fixed

- The id and classes on links, paragraphs and headings, the nested lists, and the code blocks ([#2](https://github.com/Stanislas-Poisson/KMark/issues/2)).
