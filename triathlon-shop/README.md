# Transition: triathlon shop

School project (web programming). An online shop for triathlon gear, built with HTML, CSS, JavaScript and PHP + MySQL.

## How to run it locally (Laragon or XAMPP)

1. Copy the `triathlon-shop` folder into `www` (Laragon) or `htdocs` (XAMPP).
2. Start Apache and MySQL.
3. Open phpMyAdmin, go to **Import** and import `database/schema.sql`. It creates the `triathlon_shop` database with demo products.
4. If your MySQL user or password is not `root` with no password, edit `config/db.php`.
5. Open `http://localhost/triathlon-shop/` in the browser.

The page must be opened through `http://localhost/...`. Opening `index.php` by double-clicking will not run the PHP.

## Folder structure

| Path | What it does |
| --- | --- |
| `index.php` | Main page. PHP reads the categories from the database. |
| `assets/css/style.css` | All the styling and the design tokens (colours, fonts). |
| `assets/js/app.js` | Search box, live suggestions, filters, sorting, product cards. |
| `api/search.php` | PHP endpoint that queries the database and returns JSON. |
| `config/db.php` | Database connection (PDO). |
| `database/schema.sql` | Tables and demo data. |

## How the search works

1. The user types in the search box. After a short pause, `app.js` calls `api/search.php?q=...`.
2. `search.php` splits the text into words and uses a prepared statement. Every word must match the product name, brand, description or category.
3. The JSON result is shown as suggestions. Pressing Enter shows the full results with sorting and category filters.
