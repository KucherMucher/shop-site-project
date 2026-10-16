# Transition: triathlon shop

School project (web programming). An online shop for triathlon gear, built with HTML, CSS, JavaScript and PHP + MySQL.

## How to run it locally (Laragon or XAMPP)

1. Copy the `triathlon-shop` folder into `www` (Laragon) or `htdocs` (XAMPP).
2. Start Apache and MySQL.
3. Open phpMyAdmin, go to **Import** and import `database/schema.sql`. It deletes and recreates the `triathlon_shop` tables, so re-import it whenever the file changes.
4. If your MySQL user or password is not `root` with no password, edit `config/db.php`.
5. Open `http://localhost/triathlon-shop/` in the browser.

The pages must be opened through `http://localhost/...`. Opening `index.php` by double-clicking will not run the PHP.

## Folder structure

| Path | What it does |
| --- | --- |
| `index.php` | Main page: hero, featured products, search results. |
| `product.php` | Product page: photos, color and size, stock, add to cart. |
| `cart.php` | Shopping cart page. |
| `partials/header.php`, `partials/footer.php` | Top and bottom of every page (search box, cart link, categories). |
| `assets/css/style.css` | All the styling and the design tokens (colors, fonts). |
| `assets/js/common.js` | Shared code: cart (localStorage), cart badge, live search suggestions, product cards. |
| `assets/js/app.js` | Main page: search results, filters, sorting. |
| `assets/js/product.js` | Product page: gallery, option choice, stock, add to cart. |
| `assets/js/cart.js` | Cart page: quantities, remove, totals. |
| `api/search.php` | PHP endpoint: product search (JSON). |
| `api/cart.php` | PHP endpoint: current price, stock and photo for the items in the cart (JSON). |
| `config/db.php`, `config/helpers.php` | Database connection and small PHP helpers. |
| `database/schema.sql` | Tables and demo data. |
| `assets/img/products/` | Product photos (placeholders for now). |
| `docs/AI_LOG.md` | Log of the AI help used, for the project report. |

## Database

- `categories`: Swim, Bike, Run, Nutrition, Tech.
- `products`: name, brand, description, price, optional sale price, featured flag.
- `product_images`: the photos of a product, in order. The first one is the cover photo.
- `product_variants`: one row per size and color combination, with its own stock. A product without options has one row with size and color empty.

## How things work

**Search.** The user types in the search box, `common.js` calls `api/search.php?q=...` and shows suggestions. Clicking a suggestion opens the product page. Pressing Enter shows all results on the main page.

**Product page.** PHP loads the product, its photos and its variants. `product.js` builds the color and size buttons. Combinations that are sold out are disabled, and the stock line and quantity limit follow the chosen variant.

**Cart.** The cart is saved in the browser (`localStorage`, key `transition.cart`) as a list of variant id + quantity. It stays after closing the browser, but only on that browser. The cart page asks `api/cart.php` for the current prices and stock, so old prices never show up. If an item sold out in the meantime, it is removed with a message. Limits: at most 10 of one item, and never more than the stock.

Delivery in the cart is a demo rule: free from 60 EUR, otherwise 4.90 EUR (constants at the top of `assets/js/cart.js`).

## Adding real photos

1. Put the image files in `assets/img/products/` (for example `7-1.jpg`).
2. Update the `path` in the `product_images` table (or in `schema.sql`) for that product.
