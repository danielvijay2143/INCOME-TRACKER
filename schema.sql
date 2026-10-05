CREATE DATABASE IF NOT EXISTS `income_tracker` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `income_tracker`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'user') DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Income Entries Table
CREATE TABLE IF NOT EXISTS `income_entries` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `amount` DECIMAL(12, 2) NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `entry_date` DATE NOT NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Insert Admin Account "VIJAY" (Password: 2143) and Default Admin (Password: adminpassword)
INSERT INTO `users` (`username`, `password`, `role`) VALUES
('VIJAY', '$2y$10$wEByKjMvL40w4u9j5Y8O1.Gg6rS7H3E30Z04fH0t3A0b/P9rY0Q5W', 'admin'),
('admin', '$2y$10$w1eP214QfT4R2j8yR4sM..o4H3VfJ608J6H3vI9aG7k2Z0gQ4I6rO', 'admin')
ON DUPLICATE KEY UPDATE `id`=`id`;
