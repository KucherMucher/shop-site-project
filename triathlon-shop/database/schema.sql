-- Triathlon shop: database structure + demo data (version 2: variants and photos)
-- Import this file in phpMyAdmin (Import tab) or run: mysql -u root < schema.sql
-- WARNING: it deletes and recreates the tables, so any data you added by hand is lost.

CREATE DATABASE IF NOT EXISTS triathlon_shop
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE triathlon_shop;

DROP TABLE IF EXISTS product_variants;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;

CREATE TABLE categories (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug       VARCHAR(30)  NOT NULL UNIQUE,
  name       VARCHAR(60)  NOT NULL,
  sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE products (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  name        VARCHAR(120) NOT NULL,
  brand       VARCHAR(60)  NOT NULL,
  description TEXT         NOT NULL,
  price       DECIMAL(8,2) NOT NULL,
  sale_price  DECIMAL(8,2) NULL,          -- NULL = not on sale
  featured    TINYINT(1)   NOT NULL DEFAULT 0,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id),
  INDEX idx_products_name (name),
  INDEX idx_products_brand (brand)
) ENGINE=InnoDB;

-- Photos of a product, in display order (the first one is the cover photo).
CREATE TABLE product_images (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  path       VARCHAR(200) NOT NULL,       -- relative to the site root
  alt        VARCHAR(160) NOT NULL,
  sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
  CONSTRAINT fk_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- One row per buyable combination (size + color). Stock is kept here.
-- A product without options has a single row with size and color NULL.
CREATE TABLE product_variants (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  size       VARCHAR(20)  NULL,
  color      VARCHAR(30)  NULL,
  color_hex  CHAR(7)      NULL,           -- used for the color swatch
  stock      INT UNSIGNED NOT NULL DEFAULT 0,
  CONSTRAINT fk_variants_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  INDEX idx_variants_product (product_id)
) ENGINE=InnoDB;

INSERT INTO categories (slug, name, sort_order) VALUES
  ('swim', 'Swim', 1),
  ('bike', 'Bike', 2),
  ('run', 'Run', 3),
  ('nutrition', 'Nutrition', 4),
  ('tech', 'Tech', 5);

INSERT INTO products (category_id, name, brand, description, price, sale_price, featured) VALUES
(1, 'Flux 2 Triathlon Wetsuit', 'Tideline', 'Full-sleeve open-water wetsuit with flexible shoulders and a quick-release zip for fast transitions.', 249.00, 199.00, 1),
(1, 'Pro Tri Suit', 'Tideline', 'One-piece trisuit with a light bike pad that works for the swim, the ride and the run.', 89.90, NULL, 0),
(1, 'ClearView Swim Goggles', 'Aquaris', 'Wide-view goggles with anti-fog lenses and a comfortable silicone seal for open water.', 24.90, NULL, 1),
(1, 'Silicone Swim Cap', 'Aquaris', 'Durable silicone cap in bright colors so you can be seen in open water.', 8.50, NULL, 0),
(1, 'Pull Buoy', 'Aquaris', 'Foam pull buoy to train your upper body and improve your body position.', 19.90, NULL, 0),
(1, 'Open Water Tow Float', 'SafeSwim', 'Bright inflatable float with a dry bag for keys and phone. Makes you visible to boats.', 29.90, 24.90, 0),
(2, 'Aero Road Helmet', 'Apex', 'Ventilated aerodynamic helmet with a magnetic buckle and a removable visor.', 129.00, NULL, 1),
(2, 'Tri Cycling Shoes', 'Velora', 'Lightweight carbon-soled shoes with one-strap closure and heel loops for quick mounts.', 139.90, NULL, 0),
(2, 'Clip-On Aero Bars', 'Velora', 'Adjustable aluminium aero extensions for a lower, faster position on any road bike.', 89.00, NULL, 1),
(2, 'Road Tyre 700x25', 'Spinwell', 'Puncture-resistant clincher tyre with low rolling resistance for training and racing.', 34.90, NULL, 0),
(2, 'Tubeless Repair Kit', 'Spinwell', 'Plugs, tools and sealant for fixing a flat on the road.', 12.90, NULL, 0),
(2, 'Frame Bottle Cage', 'Cadence', 'Lightweight bottle cage that holds your bottle on rough roads.', 11.50, NULL, 0),
(3, 'Carbon Racing Shoes', 'Stride', 'Responsive carbon-plated shoes made for 10 km and half-marathon race pace.', 179.90, 149.90, 1),
(3, 'Elastic Quick Laces', 'Stride', 'No-tie elastic laces that save time in transition. Two pairs per pack.', 6.90, NULL, 0),
(3, 'Race Number Belt', 'Stride', 'Adjustable belt with toggles to hold your race number and gels.', 14.90, NULL, 1),
(3, 'Lightweight Running Visor', 'Stride', 'Breathable visor that keeps sun and sweat out of your eyes.', 17.90, NULL, 0),
(4, 'Energy Gel Box (12 gels)', 'Fuelio', 'Box of twelve citrus energy gels with 25 g of carbohydrates each.', 21.90, NULL, 1),
(4, 'Electrolyte Tablets', 'Fuelio', 'Effervescent tablets that replace the salts you lose through sweat on long sessions.', 7.50, NULL, 0),
(4, 'Recovery Shake 900 g', 'Fuelio', 'Chocolate protein and carbohydrate powder for recovery after hard training.', 29.90, NULL, 0),
(5, 'GPS Multisport Watch', 'Pulse', 'Triathlon watch with pool, open-water, bike and run modes plus a 20 hour GPS battery.', 349.00, NULL, 1),
(5, 'Heart-Rate Chest Strap', 'Pulse', 'Comfortable strap with accurate heart rate readings on land and in the water.', 59.90, 49.90, 0);

INSERT INTO product_images (product_id, path, alt, sort_order) VALUES
(1, 'assets/img/products/1-1.svg', 'Flux 2 Triathlon Wetsuit, front view', 1),
(1, 'assets/img/products/1-2.svg', 'Flux 2 Triathlon Wetsuit, side view', 2),
(1, 'assets/img/products/1-3.svg', 'Flux 2 Triathlon Wetsuit, detail', 3),
(2, 'assets/img/products/2-1.svg', 'Pro Tri Suit, front view', 1),
(2, 'assets/img/products/2-2.svg', 'Pro Tri Suit, side view', 2),
(2, 'assets/img/products/2-3.svg', 'Pro Tri Suit, detail', 3),
(3, 'assets/img/products/3-1.svg', 'ClearView Swim Goggles, front view', 1),
(3, 'assets/img/products/3-2.svg', 'ClearView Swim Goggles, side view', 2),
(3, 'assets/img/products/3-3.svg', 'ClearView Swim Goggles, detail', 3),
(4, 'assets/img/products/4-1.svg', 'Silicone Swim Cap, front view', 1),
(4, 'assets/img/products/4-2.svg', 'Silicone Swim Cap, side view', 2),
(4, 'assets/img/products/4-3.svg', 'Silicone Swim Cap, detail', 3),
(5, 'assets/img/products/5-1.svg', 'Pull Buoy, front view', 1),
(5, 'assets/img/products/5-2.svg', 'Pull Buoy, side view', 2),
(5, 'assets/img/products/5-3.svg', 'Pull Buoy, detail', 3),
(6, 'assets/img/products/6-1.svg', 'Open Water Tow Float, front view', 1),
(6, 'assets/img/products/6-2.svg', 'Open Water Tow Float, side view', 2),
(6, 'assets/img/products/6-3.svg', 'Open Water Tow Float, detail', 3),
(7, 'assets/img/products/7-1.svg', 'Aero Road Helmet, front view', 1),
(7, 'assets/img/products/7-2.svg', 'Aero Road Helmet, side view', 2),
(7, 'assets/img/products/7-3.svg', 'Aero Road Helmet, detail', 3),
(8, 'assets/img/products/8-1.svg', 'Tri Cycling Shoes, front view', 1),
(8, 'assets/img/products/8-2.svg', 'Tri Cycling Shoes, side view', 2),
(8, 'assets/img/products/8-3.svg', 'Tri Cycling Shoes, detail', 3),
(9, 'assets/img/products/9-1.svg', 'Clip-On Aero Bars, front view', 1),
(9, 'assets/img/products/9-2.svg', 'Clip-On Aero Bars, side view', 2),
(9, 'assets/img/products/9-3.svg', 'Clip-On Aero Bars, detail', 3),
(10, 'assets/img/products/10-1.svg', 'Road Tyre 700x25, front view', 1),
(10, 'assets/img/products/10-2.svg', 'Road Tyre 700x25, side view', 2),
(10, 'assets/img/products/10-3.svg', 'Road Tyre 700x25, detail', 3),
(11, 'assets/img/products/11-1.svg', 'Tubeless Repair Kit, front view', 1),
(11, 'assets/img/products/11-2.svg', 'Tubeless Repair Kit, side view', 2),
(11, 'assets/img/products/11-3.svg', 'Tubeless Repair Kit, detail', 3),
(12, 'assets/img/products/12-1.svg', 'Frame Bottle Cage, front view', 1),
(12, 'assets/img/products/12-2.svg', 'Frame Bottle Cage, side view', 2),
(12, 'assets/img/products/12-3.svg', 'Frame Bottle Cage, detail', 3),
(13, 'assets/img/products/13-1.svg', 'Carbon Racing Shoes, front view', 1),
(13, 'assets/img/products/13-2.svg', 'Carbon Racing Shoes, side view', 2),
(13, 'assets/img/products/13-3.svg', 'Carbon Racing Shoes, detail', 3),
(14, 'assets/img/products/14-1.svg', 'Elastic Quick Laces, front view', 1),
(14, 'assets/img/products/14-2.svg', 'Elastic Quick Laces, side view', 2),
(14, 'assets/img/products/14-3.svg', 'Elastic Quick Laces, detail', 3),
(15, 'assets/img/products/15-1.svg', 'Race Number Belt, front view', 1),
(15, 'assets/img/products/15-2.svg', 'Race Number Belt, side view', 2),
(15, 'assets/img/products/15-3.svg', 'Race Number Belt, detail', 3),
(16, 'assets/img/products/16-1.svg', 'Lightweight Running Visor, front view', 1),
(16, 'assets/img/products/16-2.svg', 'Lightweight Running Visor, side view', 2),
(16, 'assets/img/products/16-3.svg', 'Lightweight Running Visor, detail', 3),
(17, 'assets/img/products/17-1.svg', 'Energy Gel Box (12 gels), front view', 1),
(17, 'assets/img/products/17-2.svg', 'Energy Gel Box (12 gels), side view', 2),
(17, 'assets/img/products/17-3.svg', 'Energy Gel Box (12 gels), detail', 3),
(18, 'assets/img/products/18-1.svg', 'Electrolyte Tablets, front view', 1),
(18, 'assets/img/products/18-2.svg', 'Electrolyte Tablets, side view', 2),
(18, 'assets/img/products/18-3.svg', 'Electrolyte Tablets, detail', 3),
(19, 'assets/img/products/19-1.svg', 'Recovery Shake 900 g, front view', 1),
(19, 'assets/img/products/19-2.svg', 'Recovery Shake 900 g, side view', 2),
(19, 'assets/img/products/19-3.svg', 'Recovery Shake 900 g, detail', 3),
(20, 'assets/img/products/20-1.svg', 'GPS Multisport Watch, front view', 1),
(20, 'assets/img/products/20-2.svg', 'GPS Multisport Watch, side view', 2),
(20, 'assets/img/products/20-3.svg', 'GPS Multisport Watch, detail', 3),
(21, 'assets/img/products/21-1.svg', 'Heart-Rate Chest Strap, front view', 1),
(21, 'assets/img/products/21-2.svg', 'Heart-Rate Chest Strap, side view', 2),
(21, 'assets/img/products/21-3.svg', 'Heart-Rate Chest Strap, detail', 3);

INSERT INTO product_variants (product_id, size, color, color_hex, stock) VALUES
(1, 'S', 'Black', '#1A1D20', 4),
(1, 'M', 'Black', '#1A1D20', 6),
(1, 'L', 'Black', '#1A1D20', 3),
(1, 'XL', 'Black', '#1A1D20', 1),
(2, 'XS', 'Black', '#1A1D20', 2),
(2, 'S', 'Black', '#1A1D20', 4),
(2, 'M', 'Black', '#1A1D20', 5),
(2, 'L', 'Black', '#1A1D20', 3),
(2, 'XL', 'Black', '#1A1D20', 1),
(2, 'XS', 'Blue', '#1F5FBF', 1),
(2, 'S', 'Blue', '#1F5FBF', 3),
(2, 'M', 'Blue', '#1F5FBF', 3),
(2, 'L', 'Blue', '#1F5FBF', 2),
(2, 'XL', 'Blue', '#1F5FBF', 1),
(3, NULL, 'Smoke', '#5C6670', 25),
(3, NULL, 'Clear', '#CFE8EE', 20),
(3, NULL, 'Blue', '#1F5FBF', 15),
(4, NULL, 'Yellow', '#F2C200', 50),
(4, NULL, 'Orange', '#F26B1D', 40),
(4, NULL, 'Pink', '#E8508C', 30),
(5, NULL, NULL, NULL, 35),
(6, NULL, 'Orange', '#F26B1D', 2),
(6, NULL, 'Yellow', '#F2C200', 1),
(7, 'S', 'White', '#F4F4F2', 3),
(7, 'M', 'White', '#F4F4F2', 5),
(7, 'L', 'White', '#F4F4F2', 3),
(7, 'S', 'Black', '#1A1D20', 2),
(7, 'M', 'Black', '#1A1D20', 3),
(7, 'L', 'Black', '#1A1D20', 2),
(8, '39', 'Black', '#1A1D20', 1),
(8, '40', 'Black', '#1A1D20', 1),
(8, '41', 'Black', '#1A1D20', 2),
(8, '42', 'Black', '#1A1D20', 3),
(8, '43', 'Black', '#1A1D20', 2),
(8, '44', 'Black', '#1A1D20', 2),
(8, '45', 'Black', '#1A1D20', 1),
(8, '46', 'Black', '#1A1D20', 0),
(9, NULL, NULL, NULL, 9),
(10, NULL, NULL, NULL, 40),
(11, NULL, NULL, NULL, 0),
(12, NULL, 'Black', '#1A1D20', 40),
(12, NULL, 'Red', '#C3352B', 30),
(13, '40', 'Yellow', '#F2C200', 1),
(13, '41', 'Yellow', '#F2C200', 2),
(13, '42', 'Yellow', '#F2C200', 3),
(13, '43', 'Yellow', '#F2C200', 3),
(13, '44', 'Yellow', '#F2C200', 1),
(13, '40', 'White', '#F4F4F2', 0),
(13, '41', 'White', '#F4F4F2', 2),
(13, '42', 'White', '#F4F4F2', 3),
(13, '43', 'White', '#F4F4F2', 3),
(13, '44', 'White', '#F4F4F2', 2),
(14, NULL, 'Black', '#1A1D20', 90),
(14, NULL, 'Yellow', '#F2C200', 60),
(15, 'S/M', NULL, NULL, 25),
(15, 'L/XL', NULL, NULL, 20),
(16, NULL, 'White', '#F4F4F2', 15),
(16, NULL, 'Black', '#1A1D20', 15),
(17, NULL, NULL, NULL, 80),
(18, NULL, NULL, NULL, 100),
(19, NULL, NULL, NULL, 22),
(20, NULL, 'Black', '#1A1D20', 6),
(20, NULL, 'Blue', '#1F5FBF', 4),
(21, 'XS-S', NULL, NULL, 6),
(21, 'M-L', NULL, NULL, 12),
(21, 'XL', NULL, NULL, 8);
