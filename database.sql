CREATE DATABASE IF NOT EXISTS `pro_ecommerce` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pro_ecommerce`;

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Электроника'),
(2, 'Книги'),
(3, 'Одежда');

CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'assets/placeholder.png',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `name`, `description`, `price`, `category_id`, `image_url`) VALUES
(1, 'Смартфон Pro X', 'Флагманский смартфон с тройной камерой и OLED-дисплеем.', '799.99', 1, 'assets/placeholder.png'),
(2, 'Полное собрание сочинений', 'Коллекционное издание в кожаном переплете.', '120.00', 2, 'assets/placeholder.png'),
(3, 'Худи из органического хлопка', 'Удобное и стильное худи на каждый день.', '49.99', 3, 'assets/placeholder.png'),
(4, 'Ноутбук UltraBook 14', 'Тонкий и мощный ноутбук для профессионалов.', '1450.00', 1, 'assets/placeholder.png');