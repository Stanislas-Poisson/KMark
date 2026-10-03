# Changelog

All notable changes are listed here. The format follows [Keep a Changelog](https://keepachangelog.com/), and the project will follow [Semantic Versioning](https://semver.org/) once it has a first release. There is no release yet.

## Unreleased

### Added

- Text styles (`*bold*`, `-italic-`, `_underline_`, `~strikethrough~`), the line break and the bare URLs ([#3](https://github.com/Stanislas-Poisson/KMark/issues/3)).
- The `Options` object and `Convert::toHtml()` ([#4](https://github.com/Stanislas-Poisson/KMark/issues/4)).

### Changed

- The converter is rewritten block by block and split into small classes under `src/`. The HTML written in the text is escaped, and links and images keep only a safe URL ([#2](https://github.com/Stanislas-Poisson/KMark/issues/2)).
- The class moved from `KMark.php` to `src/Convert.php` (PSR-4, `KMark\Convert`).

### Fixed

- The id and classes on links, paragraphs and headings, the nested lists, and the code blocks ([#2](https://github.com/Stanislas-Poisson/KMark/issues/2)).
