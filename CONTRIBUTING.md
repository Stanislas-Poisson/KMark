# Contributing

Thank you for helping. KMark is a PHP library that turns a text into HTML. It is a proof of concept with no test and no build yet.

---

## Development workflow

| Step | Command / Action | Description |
| :--- | :--- | :--- |
| **1. Issue** | Open or pick an issue | One branch and one pull request per issue. |
| **2. Branch** | `git checkout -b feature/#TICKET-name develop` | Create a branch from `develop`. |
| **3. Code** | *(your IDE)* | Keep the change small and follow the style of the file. |
| **4. Check** | See below | There is no automatic check yet. |
| **5. Commit** | `git commit -m "type(scope): #TICKET subject"` | Use the [Conventional Commits][conventional-commits] format, in English, 72 characters at most. |
| **6. Push** | `git push origin feature/#TICKET-name` | Push and open a pull request to `develop`. |

A pull request needs a review and is merged with a merge commit.

---

## Check a change by hand

Run the example of the README and compare the output with the table, and run `php -l KMark.php`.

PHPUnit tests, static analysis and a CI check will come with the issues of the roadmap in the [README](README.md#roadmap).

---

## Language

Code, comments, commits, issues and pull requests are in English.

[conventional-commits]: https://www.conventionalcommits.org/
