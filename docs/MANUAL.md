# TRIVEX — Instruction Manual

**Project:** TRIVEX — Triathlon Store  
**Version:** 1.0  
**Date:** November 2026  
**Audience:** developers, operators, administrators and demo users

---

## 1. What is TRIVEX?

TRIVEX is a self-contained triathlon e-commerce platform built with **PHP 8**, **MySQL** and **vanilla JavaScript**. It includes:

- a public storefront;
- a customer account area;
- a company/partner portal;
- an administrative backoffice;
- a support chat;
- PT/EN localisation and light/dark themes.

The project does **not** require Composer, npm, CDNs, frontend frameworks or external images at runtime. All visual assets are local SVGs, except images uploaded by companies.

---

## 2. Requirements

| Component | Requirement |
|---|---|
| PHP | 8.1+ (8.3 recommended) |
| PHP extensions | `pdo_mysql`, `mbstring`, `openssl` |
| Database | MySQL 8.0+ or MariaDB 10.6+ |
| Web server | Laragon/Apache with `mod_rewrite`, or the PHP built-in server |
| Browser | Modern browser with JavaScript enabled |

No `gd` extension is required. Uploaded images are validated with `getimagesize()`.

---

## 3. Installation

### 3.1 Place the project

Recommended Laragon path:

```text
C:\laragon\www\projeto
```

The root `.htaccess` rewrites requests to `public/index.php`. Alternatively, point the virtual host directly to `public/`.

### 3.2 Configure the environment

Copy `.env.example` to `.env` and adjust:

```dotenv
APP_NAME="TRIVEX"
APP_ENV=local
APP_DEBUG=true
APP_URL=
APP_TIMEZONE=Europe/Lisbon

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=trivex
DB_USERNAME=root
DB_PASSWORD=

MAIL_DRIVER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=
MAIL_FROM=hello@trivex.test
MAIL_FROM_NAME="TRIVEX"

CURRENCY=EUR
```

Notes:

- Leave `APP_URL` empty for automatic base-URL detection.
- Keep `MAIL_DRIVER=log` for offline demos.
- Never commit real credentials.

### 3.3 Create and seed the database

Start MySQL, then run:

```bash
php database/install.php
```

> **Warning:** this command drops and recreates the `trivex` database. Do not run it on production data.

Expected seed summary:

```text
roles        4
users        8
products     19
variants     44
orders       5
```

The current demo database may contain extra test data created after installation.

### 3.4 Start the application

With Laragon/Apache:

```text
http://projeto.test
```

With the PHP built-in server:

```bash
php -S 127.0.0.1:8090 -t public public/router.php
```

Open:

```text
http://127.0.0.1:8090
```

> The `public/router.php` argument is required for friendly routes on the built-in server.

### 3.5 Verify the installation

```bash
php -l public/index.php
php -l src/routes.php
php database/scan_lang.php
```

Then open the homepage and check that CSS, JavaScript and product SVGs load.

---

## 4. Demo accounts

| Role | Email | Password | Notes |
|---|---|---|---|
| Administrator | `admin@trivex.pt` | `Admin123!` | Full access, approvals, moderators, mailbox |
| Moderator | `moderador@trivex.pt` | `Mod12345!` | Operational backoffice; changes require approval |
| Customer | `cliente@trivex.pt` | `Cliente123!` | Verified account, orders and chat |
| Company | `empresa@trivex.pt` | `Empresa123!` | Approved company with panel access |
| Pending company | `tiago@oceanonorte.pt` | `Empresa123!` | Cannot sign in until admin approval |

Suggested demo flow:

1. Sign in as **admin**.
2. Approve *Oceano Norte Sports* in **Admin → Companies**.
3. Sign in as `tiago@oceanonorte.pt`.
4. Submit or edit a product in the company portal.

---

## 5. Using the storefront

### 5.1 Language and theme

- Use **PT / EN** in the header to switch language.
- Use the sun/moon control to switch light/dark theme.
- The choice is stored in session/local storage and persists between pages.

### 5.2 Catalog and search

Routes:

| Page | Route |
|---|---|
| Homepage | `/` |
| Catalog | `/loja` |
| Category | `/categoria/{slug}` |
| Search | `/pesquisa?q=...` |
| Promotions | `/promocoes` |
| CMS page | `/pagina/{slug}` |
| Instant search API | `/api/pesquisa` |

Catalog filters include text search, category, price range, brand, size, availability and promotion status. Sorting and pagination are server-side.

### 5.3 Product page

Route: `/produto/{slug}`

The product page supports:

- main image and thumbnail gallery;
- colour and size selection;
- live price, SKU and stock display;
- quantity limits based on stock;
- specifications table;
- reviews;
- related products;
- colour-specific galleries when configured by the company.

When a colour is selected, `product.js` rebuilds the main image and thumbnails from the variant’s `images` array.

### 5.4 Cart and checkout

Routes:

| Action | Route |
|---|---|
| View cart | `/carrinho` |
| Add item | `POST /carrinho/adicionar` |
| Update quantity | `POST /carrinho/atualizar` |
| Remove item | `POST /carrinho/remover` |
| Clear cart | `POST /carrinho/limpar` |
| Checkout | `/checkout` |
| Success | `/checkout/sucesso/{number}` |

Checkout works for guests and authenticated customers. The server:

1. validates the submitted data;
2. validates stock again;
3. creates the order and order items inside a transaction;
4. locks the variant row with `FOR UPDATE`;
5. decrements stock;
6. simulates the payment;
7. sends/records the confirmation email;
8. clears the cart.

Payment methods are simulated:

- **Card** → `paid`
- **MB WAY** → `paid`
- **Multibanco** → `pending` with a generated reference

### 5.5 Customer account

Routes:

| Page | Route |
|---|---|
| Dashboard | `/conta` |
| Orders | `/conta/encomendas` |
| Order detail | `/conta/encomendas/{id}` |
| Profile | `/conta/perfil` |
| Addresses | `/conta/moradas` |
| Support | `/suporte` |

Customers can manage addresses, update their profile, view order history and submit one review per product.

---

## 6. Company portal

### 6.1 Access

A company account must be:

1. registered;
2. approved by an administrator;
3. signed in with the company owner account.

Routes:

| Page | Route |
|---|---|
| Dashboard | `/empresa/painel` |
| Products | `/empresa/produtos` |
| New product | `/empresa/produtos/novo` |
| Edit product | `/empresa/produtos/{id}/editar` |

If the company is not approved, the middleware redirects to `/empresa/pendente`.

### 6.2 Dashboard

The dashboard shows:

- total products;
- active and pending products;
- total stock;
- units sold;
- revenue;
- recent products and related orders.

### 6.3 Product list

The product list shows the main image, name, brand, product ID, stock, price, status and creation date.

Available actions:

- **Edit** — opens the edit form.
- **View in store** — visible when the product is active.

Statuses:

| Status | Meaning |
|---|---|
| `active` | Published in the storefront |
| `pending` | Awaiting admin review |
| `rejected` | Rejected with an optional note |

### 6.4 Create a product

Open **My products → Submit new product** or `/empresa/produtos/novo`.

Required information:

- Portuguese and English names;
- category;
- base price;
- brand;
- descriptions;
- at least one variant;
- main image.

On submit, the product is created with status `pending`.

### 6.5 Edit a product

Open **My products → Edit** or `/empresa/produtos/{id}/editar`.

The edit form allows the company to update:

- names, descriptions, brand and category;
- base price and sale price;
- specifications;
- variants and stock;
- main image;
- gallery images;
- per-colour photos.

Behaviour:

- editing an `active` product keeps it `active`;
- editing a `rejected` product moves it back to `pending` and clears the rejection note;
- existing variants are updated in place;
- removed variants are deactivated (`active = 0`) rather than deleted, preserving order history references.

### 6.6 Variants

Each variant row contains:

- size;
- colour;
- stock;
- optional hidden `variant_id[]` for existing variants.

Use **Add variant** to append a row. Use the remove control to deactivate an existing variant or remove a new row.

### 6.7 Technical specifications

Specifications are key/value pairs:

- name;
- value.

Use **Add technical specification** to append a row. They are stored as JSON in `products.specs`.

### 6.8 Main image

The main image is stored in `products.image`.

- Click the upload area to choose an image.
- The selected image is previewed immediately.
- The upload icon changes when a preview exists.
- Existing images can be kept or replaced.

Validation:

- allowed image types;
- maximum size;
- real image check with `getimagesize()`;
- random filename.

### 6.9 Gallery

Additional gallery images are stored as a JSON array in `products.gallery`.

- Select one or more images.
- New images are previewed before saving.
- Existing gallery images can be removed individually.
- New images can be removed before submitting.

The main image and gallery are combined on the storefront.

### 6.10 Per-colour photos

Per-colour photos are stored in `product_color_images`, keyed by the exact colour string used in the variants.

Workflow:

1. Add or edit variant colours.
2. The form automatically creates one photo card per unique colour.
3. Upload one or more images per colour.
4. Save the product.
5. On the storefront, selecting that colour switches the main image and thumbnails to its photos.

Important:

- The colour string is the key. `Azul Oceano` and `azul oceano` are treated as the same colour for matching in the form, but keep the spelling consistent.
- Removing all photos for a colour leaves the default main image/gallery in use.

### 6.11 Product status rules

| Action | Result |
|---|---|
| Company creates a product | `pending` |
| Admin approves | `active` |
| Admin rejects | `rejected` + note |
| Company edits an active product | remains `active` |
| Company edits a rejected product | becomes `pending` |

---

## 7. Backoffice

### 7.1 Admin dashboard

Route: `/admin`

Shows sales metrics, orders, users, stock alerts and chats.

### 7.2 Users

Route: `/admin/utilizadores`

- search users;
- view purchase statistics;
- change user status.

Admins apply changes immediately. Moderator changes go to the approval queue.

### 7.3 Stock

Route: `/admin/stock`

- search variants;
- filter low/out-of-stock;
- update stock.

### 7.4 Orders

Routes:

| Page | Route |
|---|---|
| List | `/admin/encomendas` |
| Detail | `/admin/encomendas/{id}` |
| Update status | `POST /admin/encomendas/{id}/estado` |

Order statuses:

`placed`, `shipped`, `received`, `lost`, `not_received`.

### 7.5 Companies

Route: `/admin/empresas`

- approve or reject companies;
- activate the owner when approved;
- add a review note;
- notify the company owner.

### 7.6 Proposals

Route: `/admin/propostas`

- review products submitted by companies;
- approve → product becomes `active`;
- reject → product becomes `rejected` with a note;
- the company is notified.

### 7.7 Pages

Route: `/admin/paginas`

- activate/deactivate CMS pages;
- the homepage is protected and cannot be deactivated.

### 7.8 Moderators

Route: `/admin/moderadores`

Admin-only. Create, list, activate or suspend moderator accounts.

### 7.9 Approvals

Route: `/admin/aprovacoes`

Admin-only queue for moderator changes.

Actions:

- approve → apply the change and mark it approved;
- reject → mark it rejected and add a note;
- the requesting moderator is notified.

### 7.10 Notifications

Route: `/admin/notificacoes`

Feed of system events for staff.

### 7.11 Mailbox

Route: `/admin/mailbox`

Admin-only. Shows emails recorded in `email_log`.

### 7.12 Chats

Routes:

| Page | Route |
|---|---|
| Active queue | `/admin/chats` |
| Archive | `/admin/chats/arquivo` |
| Conversation | `/admin/chats/{id}` |

Actions:

- join a conversation;
- send messages;
- heartbeat while assigned;
- leave;
- conclude.

---

## 8. Moderator workflow

Moderators can access operational areas but cannot apply sensitive changes directly.

When a moderator changes:

- variant stock;
- order status;
- user status;
- company status;
- product status;
- CMS page activation;

the change is written to `pending_modifications` and admins are notified.

The admin then approves or rejects it in **Admin → Approvals**.

Exception: support chat actions are applied immediately.

---

## 9. Customer support chat

Customer route: `/suporte`

States:

| State | Meaning |
|---|---|
| `open` | Waiting for staff |
| `in_progress` | Assigned to a moderator/admin |
| `concluded` | Resolved |
| `archived` | Archived after 10 minutes |

Rules:

1. Customer opens a conversation → `open`.
2. Staff joins → `in_progress`.
3. Staff heartbeat keeps the assignment.
4. Leaving returns it to `open`.
5. No heartbeat for 2 minutes → sweep returns it to `open`.
6. Conclude → `concluded`.
7. After 10 minutes → `archived`.

The customer sees only their conversations. Staff see the global queue.

---

## 10. Email configuration

The driver is selected with `MAIL_DRIVER`.

### 10.1 Log driver (default)

```dotenv
MAIL_DRIVER=log
```

Emails are:

- rendered as HTML;
- stored in `email_log`;
- written to `storage/mail/`;
- visible in **Admin → Mailbox**.

This is the safest mode for offline demos.

### 10.2 Mailpit

```dotenv
MAIL_DRIVER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
```

Open Mailpit at `http://127.0.0.1:8025`.

### 10.3 Gmail SMTP

1. Enable 2-step verification on the Google account.
2. Create an **app password**.
3. Set:

```dotenv
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-account@gmail.com
MAIL_PASSWORD=xxxxxxxxxxxxxxxx
MAIL_ENCRYPTION=tls
MAIL_FROM=your-account@gmail.com
MAIL_FROM_NAME="TRIVEX"
```

For port 465 use:

```dotenv
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
```

Notes:

- Remove spaces from the app password before pasting.
- `MAIL_FROM` must be the authenticated account or a verified alias.
- Restart PHP after changing `.env`.
- Check **Admin → Mailbox** for `sent` or `failed`.

### 10.4 Email troubleshooting

| Symptom | Check |
|---|---|
| `SMTP username and password are not configured` | Fill `MAIL_USERNAME` and `MAIL_PASSWORD` |
| Connection refused | Check host, port and firewall |
| TLS/certificate error | Check PHP OpenSSL CA configuration |
| Gmail authentication error | Use an app password, not the normal password |
| Message sent but not in inbox | Check spam, `MAIL_FROM`, and Gmail alias rules |
| `email_log.status = failed` | Read `storage/mail.log` and the mailbox error |

---

## 11. Database administration

### 11.1 Tables

The schema has **17 tables**:

`roles`, `companies`, `users`, `categories`, `products`, `product_variants`, `product_color_images`, `addresses`, `orders`, `order_items`, `reviews`, `pages`, `chats`, `chat_messages`, `pending_modifications`, `notifications`, `email_log`.

### 11.2 Backup

```bash
mysqldump -u root trivex > trivex-backup.sql
```

### 11.3 Restore

```bash
mysql -u root trivex < trivex-backup.sql
```

### 11.4 Reset

```bash
php database/install.php
```

This is destructive.

---

## 12. Maintenance

### 12.1 Uploads

Uploaded product images are stored in:

```text
public/assets/img/uploads/products/
```

Keep this directory writable by PHP. Do not delete files still referenced by `products.image`, `products.gallery` or `product_color_images.image`.

### 12.2 Logs

| Log | Location |
|---|---|
| Mail errors | `storage/mail.log` |
| Logged emails | `storage/mail/` |
| PHP server output | terminal running `php -S` |

### 12.3 Translations

Translation files:

```text
lang/pt.php
lang/en.php
```

Run the language scanner:

```bash
php database/scan_lang.php
```

Some dynamic keys may appear as false positives.

### 12.4 Static assets

All CSS and JavaScript are local:

```text
public/assets/css/
public/assets/js/
public/assets/img/
```

No build step is required. After editing CSS/JS, hard-refresh the browser.

---

## 13. Troubleshooting

| Problem | Likely cause | Solution |
|---|---|---|
| 404 on friendly routes | Built-in server started without router | Use `php -S 127.0.0.1:8090 -t public public/router.php` |
| Database connection error | MySQL stopped or wrong `.env` | Start MySQL, verify `DB_*` values |
| CSS/JS changes not visible | Browser cache | Hard refresh (`Ctrl+F5`) |
| Upload rejected | Type/size invalid | Use JPEG/PNG/WebP under the configured limit |
| Company redirected to pending page | Company not approved | Approve it in **Admin → Companies** |
| Moderator change not applied | Approval workflow | Approve it in **Admin → Approvals** |
| Emails not in Gmail | Driver still `log` or missing app password | Configure SMTP and restart PHP |
| Product colour photos not switching | Colour strings differ | Use the same colour spelling in variants and photos |
| Session lost after restart | PHP session storage reset | Sign in again |

---

## 14. Security checklist

- Use strong passwords and unique accounts.
- Keep `.env` out of version control.
- Use HTTPS in production.
- Keep `APP_DEBUG=false` in production.
- Restrict database permissions.
- Back up the database and uploads regularly.
- Review admin/moderator accounts periodically.
- Do not expose `storage/` or raw database dumps.
- Validate uploaded files and keep PHP/MySQL patched.
- Rotate Gmail app passwords if compromised.

---

## 15. Test checklist

### Syntax

```bash
php -l public/index.php
php -l src/routes.php
node --check public/assets/js/company.js
node --check public/assets/js/product.js
```

### Functional smoke test

1. Open `/` and switch PT/EN and light/dark.
2. Search for a product and apply filters.
3. Open a product, change colour and size, add to cart.
4. Complete checkout as a guest.
5. Confirm the order and the email in **Admin → Mailbox**.
6. Sign in as a customer and view `/conta/encomendas`.
7. Open a support chat as a customer.
8. Sign in as staff and reply/conclude the chat.
9. Sign in as a moderator, change stock, confirm it is pending.
10. Sign in as admin and approve the change.
11. Sign in as a company and create a product with variants, specs, main image, gallery and colour photos.
12. Edit the product and verify that variants keep their IDs and stock updates in place.
13. Approve the product as admin and verify colour switching on the storefront.

### Responsive check

Verify at approximately:

- 1440px
- 1024px
- 768px
- 390px

Check for horizontal document overflow, clipped buttons and unreadable tables.

---

## 16. Key file map

| Path | Purpose |
|---|---|
| `public/index.php` | Front controller and security headers |
| `public/router.php` | Router for the PHP built-in server |
| `src/bootstrap.php` | Environment, autoloader, config, session, locale |
| `src/routes.php` | All application routes |
| `src/Core/` | Router, middleware, auth, session, CSRF, database, mailer, view |
| `src/Controllers/` | Request orchestration |
| `src/Models/` | PDO data access |
| `src/Support/` | ModificationService, MailService, helpers, icons |
| `src/Views/` | PHP templates and layouts |
| `lang/` | PT/EN dictionaries |
| `public/assets/css/` | App, account, admin, company, auth styles |
| `public/assets/js/` | App, product, company, admin and chat scripts |
| `database/database.sql` | Schema and seed data |
| `docs/diagrams.md` | Mermaid architecture and business diagrams |
| `docs/REPORT.md` | Project report |
| `docs/MANUAL.md` | This manual |

---

## 17. FAQ

**Can I run the project without Composer or npm?**  
Yes. There is no build step and no runtime dependency on external packages.

**Can I use Gmail for real emails?**  
Yes, with a Google app password and `MAIL_DRIVER=smtp`. See section 10.3.

**Why does a moderator change say “pending”?**  
That is the approval workflow. An admin must approve it in **Admin → Approvals**.

**Why can’t a company sign in?**  
The company must be approved by an admin first.

**Why did a product disappear from the storefront?**  
It may be `pending` or `rejected`. Check **Admin → Proposals** or the company product list.

**Where are uploaded images stored?**  
`public/assets/img/uploads/products/`.

**How do I reset the demo?**  
Run `php database/install.php`. This deletes the current database.

---

**TRIVEX** — swim · bike · run
