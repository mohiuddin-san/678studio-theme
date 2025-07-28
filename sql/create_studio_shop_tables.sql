-- Studio Shop Manager テーブル作成SQL

-- 1. 店舗基本情報テーブル
CREATE TABLE IF NOT EXISTS `studio_shops` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `nearest_station` varchar(255) DEFAULT NULL,
  `business_hours` varchar(255) DEFAULT NULL,
  `holidays` varchar(255) DEFAULT NULL,
  `map_url` text DEFAULT NULL,
  `company_email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. メインギャラリー画像テーブル
CREATE TABLE IF NOT EXISTS `studio_shop_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) NOT NULL,
  `image_url` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `shop_id` (`shop_id`),
  CONSTRAINT `studio_shop_images_ibfk_1` FOREIGN KEY (`shop_id`) REFERENCES `studio_shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. カテゴリーマスターテーブル
CREATE TABLE IF NOT EXISTS `studio_shop_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. カテゴリー別画像テーブル
CREATE TABLE IF NOT EXISTS `studio_shop_catgorie_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `image_url` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `shop_id` (`shop_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `studio_shop_catgorie_images_ibfk_1` FOREIGN KEY (`shop_id`) REFERENCES `studio_shops` (`id`) ON DELETE CASCADE,
  CONSTRAINT `studio_shop_catgorie_images_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `studio_shop_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;