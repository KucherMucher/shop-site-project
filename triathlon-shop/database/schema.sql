-- Triathlon shop: database structure + demo data
-- Import this file in phpMyAdmin (Import tab) or run: mysql -u root < schema.sql

CREATE DATABASE IF NOT EXISTS triathlon_shop
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE triathlon_shop;

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
  stock       INT UNSIGNED NOT NULL DEFAULT 0,
  featured    TINYINT(1)   NOT NULL DEFAULT 0,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id),
  INDEX idx_products_name (name),
  INDEX idx_products_brand (brand)
) ENGINE=InnoDB;

INSERT INTO categories (slug, name, sort_order) VALUES
  ('swim',      'Swim',      1),
  ('bike',      'Bike',      2),
  ('run',       'Run',       3),
  ('nutrition', 'Nutrition', 4),
  ('tech',      'Tech',      5);

INSERT INTO products (category_id, name, brand, description, price, sale_price, stock, featured) VALUES
-- Swim
(1, 'Flux 2 Triathlon Wetsuit', 'Tideline', 'Full-sleeve open-water wetsuit with flexible shoulders and a quick-release zip for fast transitions.', 249.00, 199.00, 14, 1),
(1, 'Pro Tri Suit', 'Tideline', 'One-piece trisuit with a light bike pad that works for the swim, the ride and the run.', 89.90, NULL, 25, 0),
(1, 'ClearView Swim Goggles', 'Aquaris', 'Wide-view goggles with anti-fog lenses and a comfortable silicone seal for open water.', 24.90, NULL, 60, 1),
(1, 'Silicone Swim Cap', 'Aquaris', 'Durable silicone cap in bright colours so you can be seen in open water.', 8.50, NULL, 120, 0),
(1, 'Pull Buoy', 'Aquaris', 'Foam pull buoy to train your upper body and improve your body position.', 19.90, NULL, 35, 0),
(1, 'Open Water Tow Float', 'SafeSwim', 'Bright inflatable float with a dry bag for keys and phone. Makes you visible to boats.', 29.90, 24.90, 3, 0),
-- Bike
(2, 'Aero Road Helmet', 'Apex', 'Ventilated aerodynamic helmet with a magnetic buckle and a removable visor.', 129.00, NULL, 18, 1),
(2, 'Tri Cycling Shoes', 'Velora', 'Lightweight carbon-soled shoes with one-strap closure and heel loops for quick mounts.', 139.90, NULL, 12, 0),
(2, 'Clip-On Aero Bars', 'Velora', 'Adjustable aluminium aero extensions for a lower, faster position on any road bike.', 89.00, NULL, 9, 1),
(2, 'Road Tyre 700x25', 'Spinwell', 'Puncture-resistant clincher tyre with low rolling resistance for training and racing.', 34.90, NULL, 40, 0),
(2, 'Tubeless Repair Kit', 'Spinwell', 'Plugs, tools and sealant for fixing a flat on the road.', 12.90, NULL, 0, 0),
(2, 'Frame Bottle Cage', 'Cadence', 'Lightweight bottle cage that holds your bottle on rough roads.', 11.50, NULL, 70, 0),
-- Run
(3, 'Carbon Racing Shoes', 'Stride', 'Responsive carbon-plated shoes made for 10 km and half-marathon race pace.', 179.90, 149.90, 20, 1),
(3, 'Elastic Quick Laces', 'Stride', 'No-tie elastic laces that save time in transition. Two pairs per pack.', 6.90, NULL, 150, 0),
(3, 'Race Number Belt', 'Stride', 'Adjustable belt with toggles to hold your race number and gels.', 14.90, NULL, 45, 1),
(3, 'Lightweight Running Visor', 'Stride', 'Breathable visor that keeps sun and sweat out of your eyes.', 17.90, NULL, 30, 0),
-- Nutrition
(4, 'Energy Gel Box (12 gels)', 'Fuelio', 'Box of twelve citrus energy gels with 25 g of carbohydrates each.', 21.90, NULL, 80, 1),
(4, 'Electrolyte Tablets', 'Fuelio', 'Effervescent tablets that replace the salts you lose through sweat on long sessions.', 7.50, NULL, 100, 0),
(4, 'Recovery Shake 900 g', 'Fuelio', 'Chocolate protein and carbohydrate powder for recovery after hard training.', 29.90, NULL, 22, 0),
-- Tech
(5, 'GPS Multisport Watch', 'Pulse', 'Triathlon watch with pool, open-water, bike and run modes plus a 20 hour GPS battery.', 349.00, NULL, 10, 1),
(5, 'Heart-Rate Chest Strap', 'Pulse', 'Comfortable strap with accurate heart rate readings on land and in the water.', 59.90, 49.90, 26, 0);
