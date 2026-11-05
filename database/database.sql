-- =====================================================================
-- TRIVEX · Triathlon Store — Database schema & seed data
-- MySQL 8.0+ / MariaDB 10.6+ · utf8mb4
-- Import: mysql -uroot < database/database.sql
-- =====================================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

DROP DATABASE IF EXISTS `trivex`;
CREATE DATABASE `trivex` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `trivex`;

-- ---------------------------------------------------------------------
-- ROLES
-- ---------------------------------------------------------------------
CREATE TABLE `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(30) NOT NULL,
  `name` VARCHAR(60) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `slug`, `name`, `description`) VALUES
(1, 'admin', 'Administrador', 'Acesso total ao sistema e ao backoffice.'),
(2, 'moderator', 'Moderador', 'Gestão operacional com alterações sujeitas a aprovação.'),
(3, 'company', 'Empresa', 'Parceiro comercial que submete propostas de produtos.'),
(4, 'user', 'Cliente', 'Utilizador registado com histórico de compras e reviews.');

-- ---------------------------------------------------------------------
-- COMPANIES
-- ---------------------------------------------------------------------
CREATE TABLE `companies` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(160) NOT NULL,
  `slug` VARCHAR(180) NOT NULL,
  `vat` VARCHAR(30) DEFAULT NULL,
  `contact_name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(40) DEFAULT NULL,
  `website` VARCHAR(190) DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(120) DEFAULT NULL,
  `postal_code` VARCHAR(20) DEFAULT NULL,
  `country` VARCHAR(80) NOT NULL DEFAULT 'Portugal',
  `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `review_note` VARCHAR(255) DEFAULT NULL,
  `approved_by` INT UNSIGNED DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `companies_slug_unique` (`slug`),
  KEY `companies_status_idx` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `companies` (`id`, `name`, `slug`, `vat`, `contact_name`, `email`, `phone`, `website`, `address`, `city`, `postal_code`, `country`, `status`, `approved_at`, `created_at`) VALUES
(1, 'Aero Performance Lda', 'aero-performance', 'PT509876543', 'Maria Fontes', 'maria@aeroperformance.pt', '+351 912 345 678', 'https://aeroperformance.pt', 'Rua das Ondas 42', 'Cascais', '2750-123', 'Portugal', 'approved', NOW() - INTERVAL 90 DAY, NOW() - INTERVAL 100 DAY),
(2, 'Oceano Norte Sports', 'oceano-norte', 'PT512345678', 'Tiago Almeida', 'tiago@oceanonorte.pt', '+351 934 887 214', 'https://oceanonorte.pt', 'Av. do Mar 210', 'Matosinhos', '4450-789', 'Portugal', 'pending', NULL, NOW() - INTERVAL 2 DAY),
(3, 'Trail & Tide Equipment', 'trail-and-tide', 'PT598765432', 'Sofia Rocha', 'sofia@trailandtide.pt', '+351 961 220 144', 'https://trailandtide.pt', 'Estrada da Serra 8', 'Sintra', '2710-555', 'Portugal', 'approved', NOW() - INTERVAL 40 DAY, NOW() - INTERVAL 50 DAY);

-- ---------------------------------------------------------------------
-- USERS
-- ---------------------------------------------------------------------
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` INT UNSIGNED NOT NULL,
  `company_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(40) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `locale` ENUM('pt','en') NOT NULL DEFAULT 'pt',
  `status` ENUM('pending','active','suspended') NOT NULL DEFAULT 'pending',
  `email_verified_at` DATETIME DEFAULT NULL,
  `verification_token` VARCHAR(128) DEFAULT NULL,
  `verification_expires_at` DATETIME DEFAULT NULL,
  `last_login_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_idx` (`role_id`),
  KEY `users_company_idx` (`company_id`),
  KEY `users_status_idx` (`status`),
  CONSTRAINT `users_role_fk` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `users_company_fk` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `role_id`, `company_id`, `name`, `email`, `password_hash`, `phone`, `locale`, `status`, `email_verified_at`, `created_at`) VALUES
(1, 1, NULL, 'Alex Moreira', 'admin@trivex.pt', '$2y$10$39kOIL.XGaxQTesx0dSa5OY6.ylSfrSUpms9vLv4yTzQY6EtOsIMW', '+351 910 000 001', 'pt', 'active', NOW() - INTERVAL 120 DAY, NOW() - INTERVAL 120 DAY),
(2, 2, NULL, 'Rita Nogueira', 'moderador@trivex.pt', '$2y$10$J201/xj2eq1m.KoAIlryk.1VDusTdGDyb/NV5CBlMheAUS8txcqpy', '+351 910 000 002', 'pt', 'active', NOW() - INTERVAL 100 DAY, NOW() - INTERVAL 100 DAY),
(3, 4, NULL, 'João Silva', 'cliente@trivex.pt', '$2y$10$yDSMx8sMJdml8n7LcuXTYeAdIKpqJXWE83TKVdkmB2BFyl0DNlVTq', '+351 916 552 103', 'pt', 'active', NOW() - INTERVAL 60 DAY, NOW() - INTERVAL 60 DAY),
(4, 3, 1, 'Maria Fontes', 'empresa@trivex.pt', '$2y$10$Gv6Q4Oqrgb.jC0FW9vC3iuLfdfAe1pNjFs5b7dSOxmUDptdTthT5y', '+351 912 345 678', 'pt', 'active', NOW() - INTERVAL 90 DAY, NOW() - INTERVAL 90 DAY),
(5, 3, 2, 'Tiago Almeida', 'tiago@oceanonorte.pt', '$2y$10$Gv6Q4Oqrgb.jC0FW9vC3iuLfdfAe1pNjFs5b7dSOxmUDptdTthT5y', '+351 934 887 214', 'pt', 'pending', NULL, NOW() - INTERVAL 2 DAY),
(6, 4, NULL, 'Inês Carvalho', 'ines@exemplo.pt', '$2y$10$yDSMx8sMJdml8n7LcuXTYeAdIKpqJXWE83TKVdkmB2BFyl0DNlVTq', '+351 927 114 900', 'en', 'active', NOW() - INTERVAL 30 DAY, NOW() - INTERVAL 30 DAY),
(7, 4, NULL, 'Pedro Santos', 'pedro@exemplo.pt', '$2y$10$yDSMx8sMJdml8n7LcuXTYeAdIKpqJXWE83TKVdkmB2BFyl0DNlVTq', NULL, 'pt', 'active', NOW() - INTERVAL 12 DAY, NOW() - INTERVAL 12 DAY),
(8, 4, NULL, 'Ana Ribeiro', 'ana@exemplo.pt', '$2y$10$yDSMx8sMJdml8n7LcuXTYeAdIKpqJXWE83TKVdkmB2BFyl0DNlVTq', NULL, 'pt', 'active', NOW() - INTERVAL 5 DAY, NOW() - INTERVAL 5 DAY);

ALTER TABLE `companies`
  ADD CONSTRAINT `companies_approved_by_fk` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- ---------------------------------------------------------------------
-- CATEGORIES
-- ---------------------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(80) NOT NULL,
  `name_pt` VARCHAR(120) NOT NULL,
  `name_en` VARCHAR(120) NOT NULL,
  `description_pt` VARCHAR(500) DEFAULT NULL,
  `description_en` VARCHAR(500) DEFAULT NULL,
  `icon` VARCHAR(40) DEFAULT 'equipment',
  `image` VARCHAR(255) DEFAULT NULL,
  `position` INT NOT NULL DEFAULT 0,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `slug`, `name_pt`, `name_en`, `description_pt`, `description_en`, `icon`, `image`, `position`) VALUES
(1, 'natacao', 'Natação', 'Swimming', 'Neoprene, óculos, toucas e acessórios para dominar a água.', 'Wetsuits, goggles, caps and accessories to master the water.', 'swim', 'categories/swim.svg', 1),
(2, 'ciclismo', 'Ciclismo', 'Cycling', 'Bicicletas, capacetes e equipamento aerodinâmico para cada pedalada.', 'Bikes, helmets and aero gear for every pedal stroke.', 'bike', 'categories/bike.svg', 2),
(3, 'corrida', 'Corrida', 'Running', 'Sapatos, relógios GPS e acessórios para correr mais longe.', 'Shoes, GPS watches and accessories to run further.', 'run', 'categories/run.svg', 3),
(4, 'nutricao', 'Nutrição', 'Nutrition', 'Géis, barras e hidratação para manter a energia do início ao fim.', 'Gels, bars and hydration to keep your energy from start to finish.', 'nutrition', 'categories/nutrition.svg', 4),
(5, 'equipamento', 'Equipamento', 'Equipment', 'Fatos de triatlo, malas de transição e tudo o que precisa.', 'Tri suits, transition bags and everything you need.', 'equipment', 'categories/equipment.svg', 5);

-- ---------------------------------------------------------------------
-- PRODUCTS
-- ---------------------------------------------------------------------
CREATE TABLE `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL,
  `company_id` INT UNSIGNED DEFAULT NULL,
  `slug` VARCHAR(190) NOT NULL,
  `name_pt` VARCHAR(190) NOT NULL,
  `name_en` VARCHAR(190) NOT NULL,
  `short_pt` VARCHAR(300) DEFAULT NULL,
  `short_en` VARCHAR(300) DEFAULT NULL,
  `description_pt` TEXT,
  `description_en` TEXT,
  `brand` VARCHAR(80) NOT NULL,
  `specs` JSON DEFAULT NULL,
  `base_price` DECIMAL(10,2) NOT NULL,
  `sale_price` DECIMAL(10,2) DEFAULT NULL,
  `promo_ends_at` DATETIME DEFAULT NULL,
  `image` VARCHAR(255) NOT NULL,
  `gallery` JSON DEFAULT NULL,
  `featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_new` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active','pending','rejected') NOT NULL DEFAULT 'active',
  `rejection_note` VARCHAR(255) DEFAULT NULL,
  `submitted_by` INT UNSIGNED DEFAULT NULL,
  `approved_by` INT UNSIGNED DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_category_idx` (`category_id`),
  KEY `products_company_idx` (`company_id`),
  KEY `products_status_idx` (`status`),
  CONSTRAINT `products_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `products_company_fk` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `category_id`, `company_id`, `slug`, `name_pt`, `name_en`, `short_pt`, `short_en`, `description_pt`, `description_en`, `brand`, `specs`, `base_price`, `sale_price`, `promo_ends_at`, `image`, `gallery`, `featured`, `is_new`, `status`, `created_at`) VALUES
(1, 1, 1, 'fato-neoprene-aero-swim-3-8', 'Fato de Neoprene Aero Swim 3.8', 'Aero Swim 3.8 Wetsuit', 'Neoprene de competição com flutuabilidade equilibrada e liberdade total de ombros.', 'Competition wetsuit with balanced buoyancy and total shoulder freedom.', 'Desenhado para triatletas que procuram velocidade na água sem sacrificar conforto. O Aero Swim 3.8 combina painéis de neoprene Yamamoto #39 com costuras seladas e um corte ergonómico que facilita a transição para a bicicleta. A zona dos ombros utiliza um painel de 1,5 mm para máxima amplitude de movimento.', 'Built for triathletes who want speed in the water without sacrificing comfort. The Aero Swim 3.8 combines Yamamoto #39 neoprene panels with sealed seams and an ergonomic cut that speeds up the bike transition. The shoulder area uses a 1.5 mm panel for maximum range of motion.', 'Orca', '[{"label_pt":"Espessura","label_en":"Thickness","value":"1,5–4 mm"},{"label_pt":"Material","label_en":"Material","value":"Yamamoto #39"},{"label_pt":"Fecho","label_en":"Zip","value":"YTZ invertido"},{"label_pt":"Peso","label_en":"Weight","value":"1.1 kg (M)"}]', 449.00, 359.00, DATE_ADD(NOW(), INTERVAL 12 DAY), 'products/wetsuit-aero.svg', '["products/wetsuit-aero.svg","products/wetsuit-aero-2.svg","products/wetsuit-aero-3.svg"]', 1, 0, 'active', NOW() - INTERVAL 80 DAY),
(2, 1, 3, 'oculos-speed-vision', 'Óculos de Natação Speed Vision', 'Speed Vision Swim Goggles', 'Lentes espelhadas com visão panorâmica e vedação suave para treinos longos.', 'Mirrored lenses with panoramic vision and a soft seal for long sessions.', 'Os Speed Vision oferecem um campo de visão ampliado e lentes espelhadas com proteção UV. A vedação em silicone de baixa densidade mantém o conforto em séries longas, enquanto a banda dividida reduz a pressão na nuca.', 'Speed Vision offers an expanded field of view and mirrored UV-protective lenses. The low-density silicone seal keeps you comfortable through long sets, while the split strap reduces pressure on the neck.', 'Arena', '[{"label_pt":"Lentes","label_en":"Lenses","value":"Espelhadas / UV"},{"label_pt":"Proteção","label_en":"Protection","value":"UV 400"},{"label_pt":"Material","label_en":"Material","value":"Silicone"},{"label_pt":"Inclui","label_en":"Includes","value":"Estojo rígido"}]', 39.90, NULL, NULL, 'products/goggles-speed.svg', '["products/goggles-speed.svg","products/goggles-speed-2.svg","products/goggles-speed-3.svg"]', 0, 1, 'active', NOW() - INTERVAL 20 DAY),
(3, 1, 3, 'boia-natacao-pull-buoy-pro', 'Bóia de Natação Pull Buoy Pro', 'Pull Buoy Pro Swim Buoy', 'Flutuabilidade estável para trabalhar técnica de braços e equilíbrio na água.', 'Stable buoyancy to work on arm technique and balance in the water.', 'Bóia de espuma EVA de alta densidade com formato hidrodinâmico. Ideal para séries de pull e trabalho de rotação, sem irritar a pele.', 'High-density EVA foam buoy with a hydrodynamic shape. Ideal for pull sets and rotation work, without irritating the skin.', 'Arena', '[{"label_pt":"Material","label_en":"Material","value":"EVA alta densidade"},{"label_pt":"Peso","label_en":"Weight","value":"180 g"},{"label_pt":"Dimensões","label_en":"Dimensions","value":"22 × 14 cm"}]', 24.90, NULL, NULL, 'products/pull-buoy.svg', '["products/pull-buoy.svg","products/pull-buoy-2.svg","products/pull-buoy-3.svg"]', 0, 0, 'active', NOW() - INTERVAL 65 DAY),
(4, 1, 3, 'touca-silicone-hydro', 'Touca de Silicone Hydro', 'Hydro Silicone Swim Cap', 'Touca durável de silicone com ajuste confortável e resistência ao cloro.', 'Durable silicone cap with a comfortable fit and chlorine resistance.', 'Silicone de alta elasticidade que reduz o arrasto e protege o cabelo. Costuras planas para evitar marcas na testa durante treinos intensos.', 'High-elasticity silicone that reduces drag and protects hair. Flat seams prevent marks on the forehead during hard training.', 'Arena', '[{"label_pt":"Material","label_en":"Material","value":"Silicone 100%"},{"label_pt":"Tamanho","label_en":"Size","value":"Único adulto"},{"label_pt":"Cor","label_en":"Colour","value":"Azul / Preto"}]', 12.90, NULL, NULL, 'products/swim-cap.svg', '["products/swim-cap.svg","products/swim-cap-2.svg","products/swim-cap-3.svg"]', 0, 0, 'active', NOW() - INTERVAL 70 DAY),
(5, 2, 1, 'bicicleta-estrada-aero-cf-8', 'Bicicleta de Estrada Aero CF 8', 'Aero CF 8 Road Bike', 'Quadro de carbono aerodinâmico, gruposet 12v e rodas de perfil 45 mm.', 'Aerodynamic carbon frame, 12-speed groupset and 45 mm profile wheels.', 'Uma máquina de longa distância construída para o triatlo. O quadro em carbono Toray T800 com geometria de endurance equilibra rigidez e conforto. Integração total de cabos, travões de disco hidráulicos e rodas de carbono de 45 mm.', 'A long-distance machine built for triathlon. The Toray T800 carbon frame with endurance geometry balances stiffness and comfort. Fully integrated cables, hydraulic disc brakes and 45 mm carbon wheels.', 'Canyon', '[{"label_pt":"Quadro","label_en":"Frame","value":"Carbono Toray T800"},{"label_pt":"Grupo","label_en":"Groupset","value":"Shimano 105 Di2 12v"},{"label_pt":"Rodas","label_en":"Wheels","value":"Carbono 45 mm"},{"label_pt":"Peso","label_en":"Weight","value":"8,2 kg"}]', 2899.00, 2599.00, DATE_ADD(NOW(), INTERVAL 12 DAY), 'products/bike-road.svg', '["products/bike-road.svg","products/bike-road-2.svg","products/bike-road-3.svg"]', 1, 1, 'active', NOW() - INTERVAL 15 DAY),
(6, 2, 1, 'capacete-aero-vent-pro', 'Capacete Aero Vent Pro', 'Aero Vent Pro Helmet', 'Ventilação agressiva e perfil aerodinâmico, com MIPS e ajuste micrométrico.', 'Aggressive ventilation and aero profile, with MIPS and micro-adjust fit.', 'Testado em túnel de vento para reduzir o arrasto sem comprometer a ventilação. Sistema de proteção rotacional MIPS, fita de queixo magnética e 18 entradas de ar.', 'Wind-tunnel tested to reduce drag without compromising ventilation. MIPS rotational protection, magnetic chin strap and 18 air intakes.', 'Canyon', '[{"label_pt":"Proteção","label_en":"Protection","value":"MIPS"},{"label_pt":"Peso","label_en":"Weight","value":"285 g (M)"},{"label_pt":"Ventilação","label_en":"Vents","value":"18 entradas"},{"label_pt":"Certificação","label_en":"Certification","value":"EN 1078"}]', 149.90, NULL, NULL, 'products/helmet-aero.svg', '["products/helmet-aero.svg","products/helmet-aero-2.svg","products/helmet-aero-3.svg"]', 0, 1, 'active', NOW() - INTERVAL 10 DAY),
(7, 2, 1, 'sapatos-ciclismo-carbono-r3', 'Sapatos de Ciclismo Carbono R3', 'R3 Carbon Cycling Shoes', 'Sola de carbono, fecho BOA duplo e ajuste de competição.', 'Carbon sole, dual BOA closure and race-ready fit.', 'Sola de carbono unidirecional para transferência máxima de potência. Upper em microfibra perfurada e sistema BOA Fit System de duplo dial para micro-ajustes em movimento.', 'Unidirectional carbon sole for maximum power transfer. Perforated microfibre upper and dual-dial BOA Fit System for on-the-fly micro adjustments.', 'Canyon', '[{"label_pt":"Sola","label_en":"Sole","value":"Carbono UD"},{"label_pt":"Fecho","label_en":"Closure","value":"BOA duplo"},{"label_pt":"Peso","label_en":"Weight","value":"235 g"},{"label_pt":"Compatibilidade","label_en":"Cleat","value":"3 furos"}]', 179.90, NULL, NULL, 'products/cycling-shoes.svg', '["products/cycling-shoes.svg","products/cycling-shoes-2.svg","products/cycling-shoes-3.svg"]', 0, 0, 'active', NOW() - INTERVAL 55 DAY),
(8, 2, NULL, 'computador-gps-edge-530', 'Computador GPS Edge 530', 'Edge 530 GPS Computer', 'Navegação, métricas avançadas e autonomia de 20 horas para treinar e competir.', 'Navigation, advanced metrics and 20-hour battery for training and racing.', 'Computador GPS com mapas detalhados, perfis de treino personalizados e integração com sensores ANT+ e Bluetooth. Autonomia até 20 horas em modo de treino.', 'GPS computer with detailed maps, custom training profiles and ANT+/Bluetooth sensor integration. Up to 20 hours of battery in training mode.', 'Garmin', '[{"label_pt":"Ecrã","label_en":"Display","value":"2,6 pol. cor"},{"label_pt":"Autonomia","label_en":"Battery","value":"20 h"},{"label_pt":"Mapas","label_en":"Maps","value":"Europa incluída"},{"label_pt":"Sensores","label_en":"Sensors","value":"ANT+ / BLE"}]', 289.90, NULL, NULL, 'products/bike-computer.svg', '["products/bike-computer.svg","products/bike-computer-2.svg","products/bike-computer-3.svg"]', 1, 0, 'active', NOW() - INTERVAL 45 DAY),
(9, 2, NULL, 'bilhao-termico-750', 'Bidão Térmico 750 ml', 'Thermal Bottle 750 ml', 'Isolamento de dupla parede e válvula de alto débito.', 'Double-wall insulation and high-flow valve.', 'Mantém a bebida fresca até 4 horas. Válvula de silicone com bloqueio e corpo sem BPA, compatível com todos os suportes standard.', 'Keeps drinks cool for up to 4 hours. Silicone valve with lock and BPA-free body, compatible with all standard cages.', 'Elite', '[{"label_pt":"Capacidade","label_en":"Capacity","value":"750 ml"},{"label_pt":"Isolamento","label_en":"Insulation","value":"Dupla parede"},{"label_pt":"Material","label_en":"Material","value":"PP sem BPA"}]', 16.90, NULL, NULL, 'products/bottle.svg', '["products/bottle.svg","products/bottle-2.svg","products/bottle-3.svg"]', 0, 0, 'active', NOW() - INTERVAL 50 DAY),
(10, 3, 3, 'sapatos-corrida-rocket-x3', 'Sapatos de Corrida Rocket X3', 'Rocket X3 Running Shoes', 'Espuma de retorno energético e placa de carbono para dias de competição.', 'Energy-return foam and carbon plate for race day.', 'A escolha para o segmento de corrida do teu triatlo. Entressola em espuma PEBA com placa de carbono e upper ultraleve e respirável. Drop de 6 mm e peso de 198 g.', 'The choice for your triathlon run leg. PEBA foam midsole with a carbon plate and an ultralight, breathable upper. 6 mm drop and 198 g weight.', 'Hoka', '[{"label_pt":"Peso","label_en":"Weight","value":"198 g (42)"},{"label_pt":"Drop","label_en":"Drop","value":"6 mm"},{"label_pt":"Entressola","label_en":"Midsole","value":"PEBA + carbono"},{"label_pt":"Uso","label_en":"Use","value":"Competição"}]', 169.90, 135.90, DATE_ADD(NOW(), INTERVAL 12 DAY), 'products/running-shoes.svg', '["products/running-shoes.svg","products/running-shoes-2.svg","products/running-shoes-3.svg"]', 1, 1, 'active', NOW() - INTERVAL 8 DAY),
(11, 3, 3, 'sapatos-trail-grip-gtx', 'Sapatos Trail Grip GTX', 'Trail Grip GTX Shoes', 'Aderência agressiva e membrana impermeável para trilhos técnicos.', 'Aggressive grip and a waterproof membrane for technical trails.', 'Sola com tacos de 5 mm para lama e rocha, membrana GORE-TEX e proteção reforçada na biqueira. Para treinos de trail em qualquer condição.', '5 mm lugs for mud and rock, GORE-TEX membrane and reinforced toe protection. For trail training in any condition.', 'Hoka', '[{"label_pt":"Membrana","label_en":"Membrane","value":"GORE-TEX"},{"label_pt":"Tacos","label_en":"Lugs","value":"5 mm"},{"label_pt":"Peso","label_en":"Weight","value":"310 g"},{"label_pt":"Drop","label_en":"Drop","value":"4 mm"}]', 154.90, NULL, NULL, 'products/trail-shoes.svg', '["products/trail-shoes.svg","products/trail-shoes-2.svg","products/trail-shoes-3.svg"]', 0, 0, 'active', NOW() - INTERVAL 40 DAY),
(12, 3, NULL, 'relogio-gps-multisport-s9', 'Relógio GPS Multi-Sport S9', 'S9 Multi-Sport GPS Watch', 'Multisport, mapas e métricas de recuperação num corpo de titânio.', 'Multisport, maps and recovery metrics in a titanium body.', 'Transição automática entre modalidades, bateria de 30 dias em smartwatch e 28 horas em GPS. Monitorização de sono, VO2 max e carga de treino.', 'Automatic transition between disciplines, 30-day smartwatch battery and 28 hours in GPS mode. Sleep tracking, VO2 max and training load.', 'Garmin', '[{"label_pt":"Ecrã","label_en":"Display","value":"AMOLED 1,4 pol."},{"label_pt":"Autonomia","label_en":"Battery","value":"28 h GPS"},{"label_pt":"Material","label_en":"Material","value":"Titânio"},{"label_pt":"Resistência","label_en":"Water rating","value":"10 ATM"}]', 399.90, 349.90, DATE_ADD(NOW(), INTERVAL 12 DAY), 'products/gps-watch.svg', '["products/gps-watch.svg","products/gps-watch-2.svg","products/gps-watch-3.svg"]', 1, 0, 'active', NOW() - INTERVAL 60 DAY),
(13, 3, NULL, 'cinto-corrida-race-belt', 'Cinto de Corrida Race Belt', 'Race Belt', 'Cinto elástico com porta-dorsal e bolsos para géis.', 'Elastic belt with race number holder and gel pockets.', 'Cinto leve e ajustável com porta-dorsal rápido e dois bolsos de silicone. Sem costuras que roçam, pensado para provas longas.', 'Lightweight adjustable belt with a quick race-number holder and two silicone pockets. Seam-free to avoid chafing on long races.', 'Zoot', '[{"label_pt":"Material","label_en":"Material","value":"Elastano"},{"label_pt":"Tamanho","label_en":"Size","value":"Único"},{"label_pt":"Bolsos","label_en":"Pockets","value":"2 + dorsal"}]', 19.90, NULL, NULL, 'products/race-belt.svg', '["products/race-belt.svg","products/race-belt-2.svg","products/race-belt-3.svg"]', 0, 0, 'active', NOW() - INTERVAL 75 DAY),
(14, 4, NULL, 'gel-energetico-hydro-40', 'Gel Energético Hydro 40 g', 'Hydro Energy Gel 40 g', 'Gel isotónico com 30 g de carboidratos, fácil de digerir em prova.', 'Isotonic gel with 30 g of carbs, easy to digest on race day.', 'Gel de baixa viscosidade formulado para esforços prolongados. 30 g de carboidratos por unidade, sem necessidade de água. Sabores limão e frutos vermelhos.', 'Low-viscosity gel formulated for prolonged efforts. 30 g of carbs per serving, no water needed. Lemon and red berries flavours.', 'Maurten', '[{"label_pt":"Carboidratos","label_en":"Carbs","value":"30 g"},{"label_pt":"Sódio","label_en":"Sodium","value":"120 mg"},{"label_pt":"Peso","label_en":"Weight","value":"40 g"},{"label_pt":"Sabores","label_en":"Flavours","value":"Limão / Frutos"}]', 2.90, NULL, NULL, 'products/energy-gel.svg', '["products/energy-gel.svg","products/energy-gel-2.svg","products/energy-gel-3.svg"]', 0, 0, 'active', NOW() - INTERVAL 95 DAY),
(15, 4, NULL, 'bebida-isotonica-endurance-1kg', 'Bebida Isotónica Endurance 1 kg', 'Endurance Isotonic Drink 1 kg', 'Reposição de eletrólitos e energia para sessões longas.', 'Electrolyte and energy replenishment for long sessions.', 'Mistura isotónica com maltodextrina e frutose numa proporção 2:1, sódio e potássio. Aproximadamente 40 doses por embalagem.', 'Isotonic mix with maltodextrin and fructose in a 2:1 ratio, sodium and potassium. Approximately 40 servings per pack.', 'Maurten', '[{"label_pt":"Peso","label_en":"Weight","value":"1 kg"},{"label_pt":"Doses","label_en":"Servings","value":"~40"},{"label_pt":"Carboidratos","label_en":"Carbs","value":"80 g/100 g"},{"label_pt":"Sabores","label_en":"Flavours","value":"Neutro / Laranja"}]', 32.90, 27.90, DATE_ADD(NOW(), INTERVAL 12 DAY), 'products/isotonic.svg', '["products/isotonic.svg","products/isotonic-2.svg","products/isotonic-3.svg"]', 0, 0, 'active', NOW() - INTERVAL 85 DAY),
(16, 4, NULL, 'barra-recuperacao-recovery-12', 'Barra de Recuperação Recovery 12x', 'Recovery Bar 12x', 'Proteína e carboidratos numa barra prática para o pós-treino.', 'Protein and carbs in a convenient post-training bar.', 'Barra de 55 g com 20 g de proteína e 30 g de carboidratos. Textura macia e sabores naturais, ideal nos 30 minutos após o esforço.', '55 g bar with 20 g protein and 30 g carbs. Soft texture and natural flavours, ideal within 30 minutes after exercise.', 'Maurten', '[{"label_pt":"Proteína","label_en":"Protein","value":"20 g"},{"label_pt":"Carboidratos","label_en":"Carbs","value":"30 g"},{"label_pt":"Peso","label_en":"Weight","value":"55 g"},{"label_pt":"Embalagem","label_en":"Pack","value":"12 unidades"}]', 24.90, NULL, NULL, 'products/recovery-bar.svg', '["products/recovery-bar.svg","products/recovery-bar-2.svg","products/recovery-bar-3.svg"]', 0, 0, 'active', NOW() - INTERVAL 88 DAY),
(17, 5, 1, 'fato-triatlo-aero-race', 'Fato de Triatlo Aero Race', 'Aero Race Tri Suit', 'Fato de competição com tecido aero, bolsos e almofada para longa distância.', 'Competition suit with aero fabric, pockets and long-distance chamois.', 'Fato de triatlo de uma peça com tecido Coldblack e painéis de compressão. Fecho frontal com autobloqueio, dois bolsos traseiros e almofada de 6 mm para provas de média e longa distância.', 'One-piece tri suit with Coldblack fabric and compression panels. Auto-lock front zip, two rear pockets and 6 mm chamois for middle and long-distance races.', 'Zoot', '[{"label_pt":"Tecido","label_en":"Fabric","value":"Coldblack / Lycra"},{"label_pt":"Almofada","label_en":"Chamois","value":"6 mm"},{"label_pt":"Bolsos","label_en":"Pockets","value":"2 traseiros"},{"label_pt":"Proteção","label_en":"Protection","value":"UPF 50+"}]', 219.90, 179.90, DATE_ADD(NOW(), INTERVAL 12 DAY), 'products/tri-suit.svg', '["products/tri-suit.svg","products/tri-suit-2.svg","products/tri-suit-3.svg"]', 1, 0, 'active', NOW() - INTERVAL 70 DAY),
(18, 5, 3, 'mala-transicao-pro-40l', 'Mala de Transição Pro 40 L', 'Pro 40L Transition Bag', 'Mala impermeável com compartimentos para fato, sapatos e capacete.', 'Waterproof bag with compartments for wetsuit, shoes and helmet.', 'Mala de transição de 40 litros com fundo impermeável, compartimento ventilado para o fato molhado e bolsos organizadores. Alças tipo mochila para caminhar até à zona de transição.', '40-litre transition bag with a waterproof base, ventilated wet-wetsuit compartment and organiser pockets. Backpack straps for walking to transition.', 'Zoot', '[{"label_pt":"Capacidade","label_en":"Capacity","value":"40 L"},{"label_pt":"Material","label_en":"Material","value":"Nylon 600D"},{"label_pt":"Impermeável","label_en":"Waterproof","value":"Fundo reforçado"},{"label_pt":"Peso","label_en":"Weight","value":"780 g"}]', 89.90, NULL, NULL, 'products/transition-bag.svg', '["products/transition-bag.svg","products/transition-bag-2.svg","products/transition-bag-3.svg"]', 0, 0, 'active', NOW() - INTERVAL 35 DAY),
(19, 5, NULL, 'cordoes-transicao-elastic', 'Cordões de Transição Elastic', 'Elastic Transition Laces', 'Cordões elásticos com fecho rápido para calçar sem perder segundos.', 'Elastic laces with quick lock for fast transitions.', 'Cordões elásticos de alta resistência com sistema de aperto rápido e ponteiras metálicas. Compatíveis com a maioria dos sapatos de corrida e triatlo.', 'High-strength elastic laces with a quick-lock system and metal tips. Compatible with most running and triathlon shoes.', 'Zoot', '[{"label_pt":"Material","label_en":"Material","value":"Elastano"},{"label_pt":"Inclui","label_en":"Includes","value":"2 cordões + fechos"},{"label_pt":"Cores","label_en":"Colours","value":"Preto / Azul"}]', 9.90, NULL, NULL, 'products/laces.svg', '["products/laces.svg","products/laces-2.svg","products/laces-3.svg"]', 0, 0, 'active', NOW() - INTERVAL 78 DAY);

-- ---------------------------------------------------------------------
-- PRODUCT VARIANTS
-- ---------------------------------------------------------------------
CREATE TABLE `product_variants` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `sku` VARCHAR(60) NOT NULL,
  `size` VARCHAR(30) NOT NULL,
  `color` VARCHAR(50) NOT NULL,
  `color_hex` VARCHAR(10) NOT NULL DEFAULT '#0B6BFF',
  `stock` INT NOT NULL DEFAULT 0,
  `price_delta` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `variants_sku_unique` (`sku`),
  KEY `variants_product_idx` (`product_id`),
  CONSTRAINT `variants_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_color_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `color` VARCHAR(50) NOT NULL,
  `image` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_color_images_product_idx` (`product_id`),
  KEY `product_color_images_color_idx` (`product_id`, `color`),
  CONSTRAINT `product_color_images_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `product_variants` (`product_id`, `sku`, `size`, `color`, `color_hex`, `stock`, `price_delta`) VALUES
(1, 'TRV-WET-38-S', 'S', 'Preto / Azul', '#0B2A4A', 5, 0),
(1, 'TRV-WET-38-M', 'M', 'Preto / Azul', '#0B2A4A', 8, 0),
(1, 'TRV-WET-38-L', 'L', 'Preto / Azul', '#0B2A4A', 6, 0),
(1, 'TRV-WET-38-XL', 'XL', 'Preto / Azul', '#0B2A4A', 2, 0),
(2, 'TRV-GOG-SPEED-U', 'Único', 'Azul / Espelhado', '#0B6BFF', 24, 0),
(2, 'TRV-GOG-SPEED-K', 'Único', 'Preto / Laranja', '#151B25', 17, 0),
(3, 'TRV-BUOY-PRO-U', 'Único', 'Azul', '#0B6BFF', 30, 0),
(4, 'TRV-CAP-HYD-B', 'Único', 'Azul', '#0B6BFF', 42, 0),
(4, 'TRV-CAP-HYD-K', 'Único', 'Preto', '#151B25', 35, 0),
(5, 'TRV-BIKE-CF8-S', 'S', 'Azul Oceano', '#0B2A4A', 1, 0),
(5, 'TRV-BIKE-CF8-M', 'M', 'Azul Oceano', '#0B2A4A', 2, 0),
(5, 'TRV-BIKE-CF8-L', 'L', 'Preto Carbono', '#171D26', 1, 0),
(6, 'TRV-HELM-AERO-S', 'S', 'Branco', '#F4F7FB', 7, 0),
(6, 'TRV-HELM-AERO-M', 'M', 'Branco', '#F4F7FB', 9, 0),
(6, 'TRV-HELM-AERO-L', 'L', 'Azul Elétrico', '#0B6BFF', 4, 0),
(7, 'TRV-SHOE-R3-41', '41', 'Preto', '#151B25', 3, 0),
(7, 'TRV-SHOE-R3-42', '42', 'Preto', '#151B25', 5, 0),
(7, 'TRV-SHOE-R3-43', '43', 'Preto', '#151B25', 4, 0),
(7, 'TRV-SHOE-R3-44', '44', 'Branco', '#F4F7FB', 2, 0),
(8, 'TRV-GPS-530-U', 'Único', 'Preto', '#151B25', 14, 0),
(9, 'TRV-BOT-750-A', '750 ml', 'Azul Transparente', '#4FA8FF', 40, 0),
(9, 'TRV-BOT-750-K', '750 ml', 'Preto', '#151B25', 31, 0),
(10, 'TRV-RUN-X3-40', '40', 'Azul / Coral', '#0B6BFF', 2, 0),
(10, 'TRV-RUN-X3-41', '41', 'Azul / Coral', '#0B6BFF', 4, 0),
(10, 'TRV-RUN-X3-42', '42', 'Azul / Coral', '#0B6BFF', 6, 0),
(10, 'TRV-RUN-X3-43', '43', 'Branco / Azul', '#F4F7FB', 3, 0),
(10, 'TRV-RUN-X3-44', '44', 'Branco / Azul', '#F4F7FB', 2, 0),
(11, 'TRV-TRAIL-42', '42', 'Verde / Preto', '#2E5E4E', 3, 0),
(11, 'TRV-TRAIL-43', '43', 'Verde / Preto', '#2E5E4E', 4, 0),
(11, 'TRV-TRAIL-44', '44', 'Verde / Preto', '#2E5E4E', 2, 0),
(12, 'TRV-WATCH-S9-U', 'Único', 'Titânio', '#7A8794', 11, 0),
(13, 'TRV-BELT-RACE-U', 'Único', 'Preto', '#151B25', 26, 0),
(14, 'TRV-GEL-HYD-L', '40 g', 'Limão', '#D6F35A', 120, 0),
(14, 'TRV-GEL-HYD-R', '40 g', 'Frutos Vermelhos', '#C92A4A', 96, 0),
(15, 'TRV-ISO-1KG-N', '1 kg', 'Neutro', '#E8EDF2', 18, 0),
(15, 'TRV-ISO-1KG-L', '1 kg', 'Laranja', '#FF8A3D', 12, 0),
(16, 'TRV-BAR-12-MIX', '12 un.', 'Misto', '#8B5E3C', 22, 0),
(17, 'TRV-SUIT-RACE-S', 'S', 'Preto / Azul', '#0B2A4A', 3, 0),
(17, 'TRV-SUIT-RACE-M', 'M', 'Preto / Azul', '#0B2A4A', 5, 0),
(17, 'TRV-SUIT-RACE-L', 'L', 'Preto / Azul', '#0B2A4A', 2, 0),
(18, 'TRV-BAG-40L-B', '40 L', 'Azul', '#0B6BFF', 9, 0),
(18, 'TRV-BAG-40L-K', '40 L', 'Preto', '#151B25', 6, 0),
(19, 'TRV-LACE-EL-K', 'Único', 'Preto', '#151B25', 45, 0),
(19, 'TRV-LACE-EL-B', 'Único', 'Azul', '#0B6BFF', 38, 0);

-- ---------------------------------------------------------------------
-- ADDRESSES
-- ---------------------------------------------------------------------
CREATE TABLE `addresses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `label` VARCHAR(60) NOT NULL DEFAULT 'Casa',
  `name` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(40) DEFAULT NULL,
  `address` VARCHAR(255) NOT NULL,
  `city` VARCHAR(120) NOT NULL,
  `postal_code` VARCHAR(20) NOT NULL,
  `country` VARCHAR(80) NOT NULL DEFAULT 'Portugal',
  `is_default_shipping` TINYINT(1) NOT NULL DEFAULT 0,
  `is_default_billing` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `addresses_user_idx` (`user_id`),
  CONSTRAINT `addresses_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `addresses` (`user_id`, `label`, `name`, `phone`, `address`, `city`, `postal_code`, `country`, `is_default_shipping`, `is_default_billing`) VALUES
(3, 'Casa', 'João Silva', '+351 916 552 103', 'Rua do Triatlo 18, 2.º Dto', 'Lisboa', '1200-401', 'Portugal', 1, 1),
(3, 'Escritório', 'João Silva', '+351 916 552 103', 'Av. da Liberdade 220', 'Lisboa', '1250-147', 'Portugal', 0, 0),
(6, 'Home', 'Inês Carvalho', '+351 927 114 900', 'Rua das Flores 9', 'Porto', '4050-262', 'Portugal', 1, 1);

-- ---------------------------------------------------------------------
-- ORDERS
-- ---------------------------------------------------------------------
CREATE TABLE `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR(30) NOT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `email` VARCHAR(190) NOT NULL,
  `status` ENUM('placed','shipped','received','lost','not_received') NOT NULL DEFAULT 'placed',
  `payment_method` ENUM('card','mbway','multibanco') NOT NULL DEFAULT 'card',
  `payment_status` ENUM('pending','paid','refunded') NOT NULL DEFAULT 'pending',
  `payment_reference` VARCHAR(120) DEFAULT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `shipping` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `discount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(10,2) NOT NULL,
  `shipping_name` VARCHAR(120) NOT NULL,
  `shipping_phone` VARCHAR(40) DEFAULT NULL,
  `shipping_address` VARCHAR(255) NOT NULL,
  `shipping_city` VARCHAR(120) NOT NULL,
  `shipping_postal_code` VARCHAR(20) NOT NULL,
  `shipping_country` VARCHAR(80) NOT NULL DEFAULT 'Portugal',
  `billing_name` VARCHAR(120) DEFAULT NULL,
  `billing_address` VARCHAR(255) DEFAULT NULL,
  `billing_city` VARCHAR(120) DEFAULT NULL,
  `billing_postal_code` VARCHAR(20) DEFAULT NULL,
  `billing_country` VARCHAR(80) DEFAULT NULL,
  `notes` VARCHAR(500) DEFAULT NULL,
  `tracking_code` VARCHAR(60) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_number_unique` (`order_number`),
  KEY `orders_user_idx` (`user_id`),
  KEY `orders_status_idx` (`status`),
  KEY `orders_created_idx` (`created_at`),
  CONSTRAINT `orders_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `email`, `status`, `payment_method`, `payment_status`, `payment_reference`, `subtotal`, `shipping`, `total`, `shipping_name`, `shipping_phone`, `shipping_address`, `shipping_city`, `shipping_postal_code`, `shipping_country`, `billing_name`, `billing_address`, `billing_city`, `billing_postal_code`, `billing_country`, `created_at`) VALUES
(1, 'TRV-2026-1001', 3, 'cliente@trivex.pt', 'placed', 'card', 'paid', 'CARD-•••• 4242', 367.70, 0.00, 367.70, 'João Silva', '+351 916 552 103', 'Rua do Triatlo 18, 2.º Dto', 'Lisboa', '1200-401', 'Portugal', 'João Silva', 'Rua do Triatlo 18, 2.º Dto', 'Lisboa', '1200-401', 'Portugal', NOW() - INTERVAL 1 DAY),
(2, 'TRV-2026-1002', 3, 'cliente@trivex.pt', 'shipped', 'mbway', 'paid', 'MBW-982341', 152.80, 4.90, 157.70, 'João Silva', '+351 916 552 103', 'Rua do Triatlo 18, 2.º Dto', 'Lisboa', '1200-401', 'Portugal', 'João Silva', 'Rua do Triatlo 18, 2.º Dto', 'Lisboa', '1200-401', 'Portugal', NOW() - INTERVAL 9 DAY),
(3, 'TRV-2026-1003', 3, 'cliente@trivex.pt', 'received', 'multibanco', 'paid', 'MB-REF 582 004 331', 424.80, 0.00, 424.80, 'João Silva', '+351 916 552 103', 'Rua do Triatlo 18, 2.º Dto', 'Lisboa', '1200-401', 'Portugal', 'João Silva', 'Rua do Triatlo 18, 2.º Dto', 'Lisboa', '1200-401', 'Portugal', NOW() - INTERVAL 35 DAY),
(4, 'TRV-2026-1004', 6, 'ines@exemplo.pt', 'received', 'card', 'paid', 'CARD-•••• 1881', 184.80, 4.90, 189.70, 'Inês Carvalho', '+351 927 114 900', 'Rua das Flores 9', 'Porto', '4050-262', 'Portugal', 'Inês Carvalho', 'Rua das Flores 9', 'Porto', '4050-262', 'Portugal', NOW() - INTERVAL 22 DAY),
(5, 'TRV-2026-1005', 6, 'ines@exemplo.pt', 'not_received', 'card', 'paid', 'CARD-•••• 1881', 39.90, 4.90, 44.80, 'Inês Carvalho', '+351 927 114 900', 'Rua das Flores 9', 'Porto', '4050-262', 'Portugal', 'Inês Carvalho', 'Rua das Flores 9', 'Porto', '4050-262', 'Portugal', NOW() - INTERVAL 5 DAY);

CREATE TABLE `order_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED DEFAULT NULL,
  `variant_id` INT UNSIGNED DEFAULT NULL,
  `product_name` VARCHAR(190) NOT NULL,
  `variant_label` VARCHAR(100) DEFAULT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `quantity` INT NOT NULL,
  `line_total` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_idx` (`order_id`),
  CONSTRAINT `order_items_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_variant_fk` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `order_items` (`order_id`, `product_id`, `variant_id`, `product_name`, `variant_label`, `unit_price`, `quantity`, `line_total`) VALUES
(1, 1, 2, 'Fato de Neoprene Aero Swim 3.8', 'M · Preto / Azul', 359.00, 1, 359.00),
(1, 14, 33, 'Gel Energético Hydro 40 g', '40 g · Limão', 2.90, 3, 8.70),
(2, 10, 25, 'Sapatos de Corrida Rocket X3', '42 · Azul / Coral', 135.90, 1, 135.90),
(2, 9, 21, 'Bidão Térmico 750 ml', '750 ml · Azul Transparente', 16.90, 1, 16.90),
(3, 12, 31, 'Relógio GPS Multi-Sport S9', 'Único · Titânio', 399.90, 1, 399.90),
(3, 16, 37, 'Barra de Recuperação Recovery 12x', '12 un. · Misto', 24.90, 1, 24.90),
(4, 17, 40, 'Fato de Triatlo Aero Race', 'L · Preto / Azul', 179.90, 1, 179.90),
(5, 2, 5, 'Óculos de Natação Speed Vision', 'Único · Azul / Espelhado', 39.90, 1, 39.90);

-- ---------------------------------------------------------------------
-- REVIEWS
-- ---------------------------------------------------------------------
CREATE TABLE `reviews` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL,
  `title` VARCHAR(150) DEFAULT NULL,
  `body` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reviews_product_user_unique` (`product_id`, `user_id`),
  KEY `reviews_product_idx` (`product_id`),
  CONSTRAINT `reviews_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_rating_check` CHECK (`rating` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `reviews` (`product_id`, `user_id`, `rating`, `title`, `body`, `created_at`) VALUES
(1, 3, 5, 'Excelente flutuabilidade', 'Usei na minha primeira meia distância e a diferença na água foi enorme. Os ombros têm muita liberdade e não roça no pescoço.', NOW() - INTERVAL 20 DAY),
(1, 6, 4, 'Muito bom, veste justo', 'Qualidade topo. Recomendo subir um tamanho se estiverem entre dois. A transição para a bike é rápida.', NOW() - INTERVAL 15 DAY),
(5, 3, 5, 'Máquina de rolar', 'Quadro rígido q.b. e muito confortável em voltas longas. As rodas de 45 mm fazem toda a diferença.', NOW() - INTERVAL 30 DAY),
(10, 6, 4, 'Rápidos e leves', 'Perfeitos para prova. A placa de carbono nota-se, mas são menos estáveis em ritmos lentos.', NOW() - INTERVAL 12 DAY),
(12, 6, 5, 'O melhor companheiro', 'Bateria impecável e transições automáticas. Uso para treino e prova, não podia pedir mais.', NOW() - INTERVAL 8 DAY),
(17, 6, 4, 'Fato muito confortável', 'Tecido fresco e almofada suficiente para 70.3. Os bolsos traseiros são pequenos mas funcionais.', NOW() - INTERVAL 18 DAY),
(2, 3, 5, 'Visão fantástica', 'Não embaciam e a vedação é muito confortável em séries de 2 km.', NOW() - INTERVAL 5 DAY),
(14, 3, 4, 'Fácil de tomar', 'Textura leve e sabor agradável. Uso a cada 40 minutos nas provas longas.', NOW() - INTERVAL 4 DAY);

-- ---------------------------------------------------------------------
-- PAGES (CMS)
-- ---------------------------------------------------------------------
CREATE TABLE `pages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(120) NOT NULL,
  `title_pt` VARCHAR(190) NOT NULL,
  `title_en` VARCHAR(190) NOT NULL,
  `content_pt` LONGTEXT,
  `content_en` LONGTEXT,
  `is_system` TINYINT(1) NOT NULL DEFAULT 0,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `position` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pages` (`id`, `slug`, `title_pt`, `title_en`, `content_pt`, `content_en`, `is_system`, `active`, `position`) VALUES
(1, 'home', 'Página Principal', 'Homepage', NULL, NULL, 1, 1, 1),
(2, 'loja', 'Loja / Categorias', 'Shop / Categories', NULL, NULL, 1, 1, 2),
(3, 'promocoes', 'Promoções de Halloween', 'Halloween Deals', 'Caça às melhores ofertas de Halloween. Descontos assustadoramente bons em equipamento de triatlo.', 'Hunt down the best Halloween deals. Frighteningly good discounts on triathlon gear.', 1, 1, 3),
(4, 'apoio', 'Apoio ao Cliente', 'Customer Support', 'A nossa equipa está disponível para ajudar com encomendas, tamanhos, devoluções e equipamento.', 'Our team is available to help with orders, sizing, returns and gear.', 1, 1, 4),
(5, 'sobre-nos', 'Sobre a TRIVEX', 'About TRIVEX', 'A TRIVEX nasceu da paixão por três disciplinas e de uma comunidade que não pára. Trabalhamos com marcas que partilham o mesmo rigor para levar até ti o equipamento certo, seja na primeira prova de iniciação ou no teu próximo Ironman.', 'TRIVEX was born from a passion for three disciplines and a community that never stops. We work with brands that share the same rigour to bring you the right gear, whether for your first race or your next Ironman.', 0, 1, 5),
(6, 'devolucoes', 'Devoluções e Trocas', 'Returns & Exchanges', 'Tens 30 dias para devolver qualquer artigo não usado, na embalagem original. Contacta o apoio ao cliente para iniciar o processo.', 'You have 30 days to return any unused item in its original packaging. Contact customer support to start the process.', 0, 1, 6),
(7, 'termos', 'Termos e Condições', 'Terms & Conditions', 'Este website é uma demonstração académica. Nenhum pagamento real é processado. Todos os dados de pagamento são simulados.', 'This website is an academic demonstration. No real payment is processed. All payment data is simulated.', 0, 1, 7),
(8, 'guia-transicao', 'Guia de Transição T1 & T2', 'T1 & T2 Transition Guide', 'As transições decidem provas. Perder 30 segundos a calçar as sapatilhas ou a encontrar o capacete pode custar mais do que meses de treino. Este guia resume o essencial para saíres da água e da bicicleta com fluidez.\n\nAntes da prova, percorre o parque de transição. Memoriza a tua posição pela fila de bicicletas e por um ponto fixo, não pelo número. Coloca o capacete com as tiras abertas e os óculos dentro, aponta as sapatilhas para a frente e deixa o fato de banho preparado para ser puxado até à cintura.\n\nEm T1, tira o fato até à cintura enquanto corres, calça o capacete antes de tocar na bicicleta e monta depois da linha. Em T2, descalça antes da linha, pendura a bicicleta pelo selim, calça as sapatilhas e agarra o gel para os primeiros quilómetros a pé. Treina cada transição pelo menos três vezes na semana anterior à prova.', 'Transitions win races. Losing 30 seconds putting on shoes or finding your helmet can cost more than months of training. This guide covers the essentials to leave the water and the bike smoothly.\n\nBefore the race, walk the transition area. Memorise your spot by the bike rack row and a fixed landmark, not the number. Set your helmet with straps open and glasses inside, point your shoes forward and leave your wetsuit ready to be pulled down to the waist.\n\nIn T1, pull the wetsuit to your waist while running, put the helmet on before touching the bike and mount after the line. In T2, dismount before the line, rack the bike by the saddle, put on your shoes and grab a gel for the first kilometres on foot. Practise each transition at least three times in the week before the race.', 0, 1, 8);

-- ---------------------------------------------------------------------
-- CHATS
-- ---------------------------------------------------------------------
CREATE TABLE `chats` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `status` ENUM('open','in_progress','concluded','archived') NOT NULL DEFAULT 'open',
  `moderator_id` INT UNSIGNED DEFAULT NULL,
  `last_message_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_moderator_activity` DATETIME DEFAULT NULL,
  `concluded_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `chats_status_idx` (`status`),
  KEY `chats_user_idx` (`user_id`),
  CONSTRAINT `chats_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chats_moderator_fk` FOREIGN KEY (`moderator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `chats` (`id`, `user_id`, `subject`, `status`, `moderator_id`, `last_message_at`, `last_moderator_activity`, `concluded_at`, `created_at`) VALUES
(1, 3, 'Tamanho do fato de neoprene', 'open', NULL, NOW() - INTERVAL 12 MINUTE, NULL, NULL, NOW() - INTERVAL 18 MINUTE),
(2, 3, 'Estado da encomenda TRV-2026-1002', 'archived', 2, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY - INTERVAL 20 MINUTE);

CREATE TABLE `chat_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `chat_id` INT UNSIGNED NOT NULL,
  `sender_id` INT UNSIGNED DEFAULT NULL,
  `sender_type` ENUM('user','moderator','system') NOT NULL,
  `body` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `chat_messages_chat_idx` (`chat_id`),
  CONSTRAINT `chat_messages_chat_fk` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_messages_sender_fk` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `chat_messages` (`chat_id`, `sender_id`, `sender_type`, `body`, `created_at`) VALUES
(1, 3, 'user', 'Olá! Tenho 1,78 m e 74 kg. Que tamanho devo escolher no Aero Swim 3.8?', NOW() - INTERVAL 18 MINUTE),
(2, 3, 'user', 'Boa tarde, podem confirmar quando chega a minha encomenda?', NOW() - INTERVAL 2 DAY - INTERVAL 20 MINUTE),
(2, 2, 'moderator', 'Boa tarde João! A encomenda já saiu do armazém e deve chegar em 24 horas. O código de seguimento é CTT-982341.', NOW() - INTERVAL 2 DAY - INTERVAL 15 MINUTE),
(2, 3, 'user', 'Perfeito, muito obrigado pela ajuda!', NOW() - INTERVAL 2 DAY - INTERVAL 10 MINUTE),
(2, 2, 'moderator', 'Sempre ao dispor. Bom treino!', NOW() - INTERVAL 2 DAY - INTERVAL 5 MINUTE),
(2, NULL, 'system', 'Conversa concluída pelo moderador.', NOW() - INTERVAL 2 DAY);

-- ---------------------------------------------------------------------
-- PENDING MODIFICATIONS (Moderator → Admin approval)
-- ---------------------------------------------------------------------
CREATE TABLE `pending_modifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entity_type` VARCHAR(60) NOT NULL,
  `entity_id` INT UNSIGNED NOT NULL,
  `action` VARCHAR(60) NOT NULL,
  `payload` JSON NOT NULL,
  `summary` VARCHAR(255) NOT NULL,
  `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `requested_by` INT UNSIGNED NOT NULL,
  `reviewed_by` INT UNSIGNED DEFAULT NULL,
  `review_note` VARCHAR(500) DEFAULT NULL,
  `reviewed_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pending_status_idx` (`status`),
  KEY `pending_entity_idx` (`entity_type`, `entity_id`),
  CONSTRAINT `pending_requested_fk` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pending_reviewed_fk` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pending_modifications` (`id`, `entity_type`, `entity_id`, `action`, `payload`, `summary`, `status`, `requested_by`, `created_at`) VALUES
(1, 'variant_stock', 2, 'update', '{"stock": 10}', 'Reposição de stock: Fato Aero Swim 3.8 · M (+2)', 'pending', 2, NOW() - INTERVAL 3 HOUR);

-- ---------------------------------------------------------------------
-- NOTIFICATIONS
-- ---------------------------------------------------------------------
CREATE TABLE `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `type` VARCHAR(40) NOT NULL DEFAULT 'info',
  `title` VARCHAR(190) NOT NULL,
  `message` VARCHAR(500) DEFAULT NULL,
  `link` VARCHAR(255) DEFAULT NULL,
  `read_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `notifications_user_idx` (`user_id`, `read_at`),
  CONSTRAINT `notifications_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `notifications` (`user_id`, `type`, `title`, `message`, `link`, `created_at`) VALUES
(1, 'modification', 'Nova alteração pendente', 'Rita Nogueira propôs uma reposição de stock no produto Aero Swim 3.8 (M).', '/admin/aprovacoes', NOW() - INTERVAL 3 HOUR),
(1, 'company', 'Empresa à espera de aprovação', 'Oceano Norte Sports submeteu um pedido de conta de parceiro.', '/admin/empresas', NOW() - INTERVAL 2 DAY),
(2, 'chat', 'Novo chat de suporte', 'João Silva iniciou uma conversa sobre tamanhos de neoprene.', '/admin/chats/1', NOW() - INTERVAL 18 MINUTE);

-- ---------------------------------------------------------------------
-- EMAIL LOG (local mail viewer)
-- ---------------------------------------------------------------------
CREATE TABLE `email_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `to_email` VARCHAR(190) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `body` LONGTEXT NOT NULL,
  `driver` VARCHAR(20) NOT NULL DEFAULT 'log',
  `status` VARCHAR(20) NOT NULL DEFAULT 'logged',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `email_log_created_idx` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
