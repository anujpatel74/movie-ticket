-- ========================================================
-- CINEMA BOOKING MIGRATION 001
-- Safe Schema Baseline Creation (Non-Destructive)
-- Engine: InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- ========================================================

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `status` ENUM('active', 'inactive', 'banned') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Administrators Table
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('superadmin', 'admin', 'manager') DEFAULT 'admin',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Cities Table
CREATE TABLE IF NOT EXISTS `cities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `state` VARCHAR(100) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Theatres Table
CREATE TABLE IF NOT EXISTS `theatres` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `city_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `address` TEXT NOT NULL,
  `contact_phone` VARCHAR(20) DEFAULT NULL,
  `facilities` VARCHAR(255) DEFAULT 'Dolby Atmos, 4K Laser, Recliner Seats, Cafeteria, Valet Parking',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_theatre_city` (`city_id`),
  CONSTRAINT `fk_theatres_city` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Screens Table
CREATE TABLE IF NOT EXISTS `screens` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `theatre_id` INT NOT NULL,
  `screen_name` VARCHAR(60) NOT NULL,
  `screen_type` VARCHAR(50) DEFAULT 'IMAX 2D',
  `total_seats` INT DEFAULT 60,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_screens_theatre` (`theatre_id`),
  CONSTRAINT `fk_screens_theatre` FOREIGN KEY (`theatre_id`) REFERENCES `theatres` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Seats Table
CREATE TABLE IF NOT EXISTS `seats` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `screen_id` INT NOT NULL,
  `seat_row` VARCHAR(5) NOT NULL,
  `seat_number` INT NOT NULL,
  `seat_code` VARCHAR(10) NOT NULL,
  `seat_type` ENUM('regular', 'premium', 'recliner') DEFAULT 'regular',
  `is_active` TINYINT(1) DEFAULT 1,
  UNIQUE KEY `uq_screen_seat` (`screen_id`, `seat_code`),
  INDEX `idx_seats_screen` (`screen_id`),
  CONSTRAINT `fk_seats_screen` FOREIGN KEY (`screen_id`) REFERENCES `screens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Movies Table
CREATE TABLE IF NOT EXISTS `movies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL UNIQUE,
  `tmdb_id` INT DEFAULT NULL,
  `description` TEXT NOT NULL,
  `duration_mins` INT NOT NULL,
  `release_date` DATE NOT NULL,
  `genre` VARCHAR(120) NOT NULL,
  `language` VARCHAR(60) NOT NULL,
  `rating` DECIMAL(3, 1) DEFAULT 8.5,
  `certificate` VARCHAR(10) DEFAULT 'UA',
  `poster_url` VARCHAR(500) NOT NULL,
  `backdrop_url` VARCHAR(500) NOT NULL,
  `trailer_url` VARCHAR(500) DEFAULT NULL,
  `status` ENUM('now_showing', 'coming_soon') DEFAULT 'now_showing',
  `is_featured` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_movies_status` (`status`),
  INDEX `idx_movies_slug` (`slug`),
  INDEX `idx_movies_tmdb` (`tmdb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Shows Table
CREATE TABLE IF NOT EXISTS `shows` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `movie_id` INT NOT NULL,
  `theatre_id` INT NOT NULL,
  `screen_id` INT NOT NULL,
  `show_date` DATE NOT NULL,
  `show_time` TIME NOT NULL,
  `regular_price` DECIMAL(10, 2) NOT NULL DEFAULT 180.00,
  `premium_price` DECIMAL(10, 2) NOT NULL DEFAULT 280.00,
  `recliner_price` DECIMAL(10, 2) NOT NULL DEFAULT 420.00,
  `status` ENUM('active', 'cancelled', 'completed') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_shows_movie_date` (`movie_id`, `show_date`),
  INDEX `idx_shows_theatre` (`theatre_id`),
  INDEX `idx_shows_screen` (`screen_id`),
  CONSTRAINT `fk_shows_movie` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_shows_theatre` FOREIGN KEY (`theatre_id`) REFERENCES `theatres` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_shows_screen` FOREIGN KEY (`screen_id`) REFERENCES `screens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Bookings Table
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_reference` VARCHAR(30) NOT NULL UNIQUE,
  `user_id` INT NOT NULL,
  `show_id` INT NOT NULL,
  `total_seats` INT NOT NULL,
  `subtotal` DECIMAL(10, 2) NOT NULL,
  `convenience_fee` DECIMAL(10, 2) NOT NULL DEFAULT 35.00,
  `tax_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `grand_total` DECIMAL(10, 2) NOT NULL,
  `booking_status` ENUM('confirmed', 'cancelled', 'pending') DEFAULT 'confirmed',
  `payment_status` ENUM('paid', 'pending', 'failed', 'refunded') DEFAULT 'paid',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_bookings_user` (`user_id`),
  INDEX `idx_bookings_show` (`show_id`),
  INDEX `idx_bookings_ref` (`booking_reference`),
  CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bookings_show` FOREIGN KEY (`show_id`) REFERENCES `shows` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Booking Seats Concurrency Table
CREATE TABLE IF NOT EXISTS `booking_seats` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT NOT NULL,
  `show_id` INT NOT NULL,
  `seat_id` INT NOT NULL,
  `seat_code` VARCHAR(10) NOT NULL,
  `seat_type` ENUM('regular', 'premium', 'recliner') DEFAULT 'regular',
  `price` DECIMAL(10, 2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_show_seat_booked` (`show_id`, `seat_id`),
  INDEX `idx_booking_seats_booking` (`booking_id`),
  CONSTRAINT `fk_booking_seats_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_booking_seats_show` FOREIGN KEY (`show_id`) REFERENCES `shows` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_booking_seats_seat` FOREIGN KEY (`seat_id`) REFERENCES `seats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Payments Table
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT NOT NULL,
  `transaction_ref` VARCHAR(60) NOT NULL UNIQUE,
  `payment_method` ENUM('upi', 'card', 'netbanking') NOT NULL,
  `amount` DECIMAL(10, 2) NOT NULL,
  `payment_status` ENUM('success', 'pending', 'failed', 'refunded') DEFAULT 'success',
  `card_network` VARCHAR(40) DEFAULT NULL,
  `masked_account` VARCHAR(60) DEFAULT NULL,
  `payment_response` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_payments_booking` (`booking_id`),
  CONSTRAINT `fk_payments_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

