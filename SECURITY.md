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
- It is a proof of concept. The input is escaped and only links with a known scheme are kept (see the README), but the code has had no security audit. Report any way to get a script, an event attribute or a `javascript:` URL into the output.
- The option `unsafeAllowRawHtml` keeps the HTML of the text on purpose: what it lets through is not a vulnerability. The default settings are in scope.
- A bug in the conversion of a regular text is not a vulnerability: open an issue.

[advisories]: https://github.com/Stanislas-Poisson/KMark/security/advisories/new
[issues]: https://github.com/Stanislas-Poisson/KMark/issues
