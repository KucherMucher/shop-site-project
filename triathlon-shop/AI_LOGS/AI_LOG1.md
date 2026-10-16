# AI usage log: Transition (triathlon shop)

This file is a running log. A new entry is added after every change made with AI help.
It supports the "use of free AI" section of the project report.

## AI tool identity

| | |
| --- | --- |
| Model | Claude Sonnet 5.5 |
| Made by | Anthropic |
| Used through | Claude chat app (claude.ai) |
| Plan | To be confirmed by the student. The brief only allows free AI, so check that this chat was used on a free plan. |

---

## Entry 1 | 16 October 2026 | First version of the main page and search

**Request:** Start a triathlon shopping site (like Decathlon) with a main page and a working search bar, using HTML, CSS, JavaScript and PHP.

**What the AI did**
- Read the project brief (`enun.html`) and chose the "Loja online" (online shop) theme from it.
- Designed the look: three colours for the three disciplines (teal for swim, grey for bike, yellow for run) and a "race course" strip on the main page that works as category navigation.
- Created the database (`database/schema.sql`): tables `categories` and `products`, and 21 demo products.
- Wrote the PHP: `config/db.php` (connection) and `api/search.php` (search with prepared statements, sorting and category filter, JSON output).
- Wrote `index.php` (page structure, categories loaded from the database), `assets/css/style.css` (responsive layout) and `assets/js/app.js` (live suggestions, keyboard support, results, sorting, empty and error messages).
- Wrote `README.md` with the steps to run the project.

**How it was tested**
- The JavaScript file was checked for syntax errors with Node.js. It passed.
- The PHP and the database were NOT run by the AI, because no PHP or MySQL was available in its environment. The student must test them locally.

**Still to do by the student**
- Run the project in Laragon or XAMPP and check that the search works (try `goggles`, `aero`, `gel` and a search with no result).
- Test on a phone-sized screen.
- Ask a classmate to use the search without help and note what is confusing.

**Examples of changes the student made to the AI suggestions** (fill in for the report)
- ...
