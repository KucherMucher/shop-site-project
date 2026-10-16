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


---

## Entry 2 | 16 October 2026 | Product page, shopping cart, variants and photos

**Request:** After searching, clicking a product opens a product page with the usual shop features (several photos, sizes and colors, stock) and an "add to cart" button. Also a shopping cart saved locally.

**Tool:** Claude Sonnet 5.5 (Anthropic), same chat as Entry 1.

**What the AI did**
- **Database** (`database/schema.sql`, rewritten): added the tables `product_images` (several photos per product) and `product_variants` (one row per size + color, each with its own stock). Removed the single `stock` column from `products`, because stock now lives in the variants. 21 products, 63 photos and 66 variants of demo data.
- **Placeholder photos:** generated 63 simple SVG images in `assets/img/products/` (3 per product), so the gallery works until real photos are added.
- **Product page** (`product.php`, `assets/js/product.js`): photo gallery with thumbnails, color swatches, size buttons, stock message ("In stock", "Only 3 left", "Out of stock"), quantity box, "Add to cart" button, product description, specs, and "More in this category". Sold-out options are disabled. Shows a clear page if the product does not exist.
- **Cart** (`cart.php`, `assets/js/cart.js`, `api/cart.php`): saved in `localStorage` as variant id + quantity. The cart page asks the server for current price, stock and photo. Quantity can be changed, items removed, and the cart emptied. Shows subtotal, delivery and total. Items that sold out are removed with a message.
- **Cart badge** in the header on every page (`assets/js/common.js`), updated also when the cart changes in another browser tab.
- **Search:** suggestions now open the product page. Product cards show the cover photo and are fully clickable. `api/search.php` now reads stock from the variants and returns the cover photo.
- **Refactor:** the header and footer moved to `partials/` so all three pages share them; JavaScript split into `common.js` + one file per page.
- Updated `README.md` and the CSS.

**How it was tested**
- All JavaScript files were syntax-checked with Node.js.
- The cart functions were run in Node with a fake localStorage: adding, merging the same item, the limit of 10, removing, a broken stored value, and clearing all gave the expected results.
- The PHP pages and the SQL were NOT run by the AI (no PHP or MySQL in its environment). The student must re-import `schema.sql` and test in the browser.

**Still to do by the student**
- Re-import `database/schema.sql` (the old tables are replaced).
- Test: search for a product and click it; pick a color and size; try a sold-out size (for example Tri Cycling Shoes size 46); add to cart; change the quantity; reload and check that the cart is still there; open `product.php?id=999` to see the error page.
- Check the pages on a phone-sized screen.

**Known limits** (good material for the report)
- The cart only exists in one browser. It is not linked to an account.
- There is no checkout yet, so stock is not reduced by a purchase.
- The delivery rule (free from 60 EUR) is invented for the demo.
- All color variants of a product share the same photos.

**Examples of changes the student made to the AI suggestions** (fill in for the report)
- ...
