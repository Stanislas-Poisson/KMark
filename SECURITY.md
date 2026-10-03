# Security Policy

## Reporting vulnerabilities

| Channel | Description | Contact / Link |
| :--- | :--- | :--- |
| **Private report** | Preferred channel for sensitive reports. | [Report a vulnerability][advisories] |
| **Issues** | Non-sensitive problems. | [Open an issue][issues] |
| **Email** | Alternative contact. | `security@the-white-rabbits.fr` |

Please **do not disclose a vulnerability publicly** until it has been reviewed and fixed.

---

## Supported versions

KMark is a proof of concept and has no release yet. Fixes are made on `main`.

---

## Scope

- KMark is a PHP class. It has no web server, no authentication and no user account, and it stores nothing.
- It is a proof of concept: **the input is not escaped**, so raw HTML such as `<script>` goes through to the output. Do not use it on text you do not trust until this is fixed ([#2](https://github.com/Stanislas-Poisson/KMark/issues/2)).
- A bug in the conversion of a regular text is not a vulnerability: open an issue.

[advisories]: https://github.com/Stanislas-Poisson/KMark/security/advisories/new
[issues]: https://github.com/Stanislas-Poisson/KMark/issues
