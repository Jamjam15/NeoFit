<?php

// Connect Php to Database
try {  
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli('localhost', 'root', '', 'neofit');
    if($conn->connect_error){
        die("Failed To Connect: " . $conn->connect_error);
    }
} catch (mysqli_sql_exception $e) {
    die("Error: " . $e->getMessage());
}

// Drop existing orders and order_items tables to avoid conflicts
$conn->query("DROP TABLE IF EXISTS order_items");
$conn->query("DROP TABLE IF EXISTS orders");

// Create users table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `first_name` varchar(50) NOT NULL,
    `last_name` varchar(50) NOT NULL,
    `email` varchar(100) NOT NULL,
    `password` varchar(255) NOT NULL,
    `security_question` varchar(50) NOT NULL,
    `security_answer` varchar(255) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `address` varchar(255) DEFAULT NULL,
    `contact` varchar(11) DEFAULT NULL,
    `neocreds` DECIMAL(10,2) DEFAULT 0.00,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_email` (`email`)
)";

if (!$conn->query($sql)) {
    die("Error creating users table: " . $conn->error);
}

// Add neocreds column if it doesn't exist
$sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS neocreds DECIMAL(10,2) DEFAULT 0.00";
if (!$conn->query($sql)) {
    die("Error adding neocreds column: " . $conn->error);
}

// Create neocreds_transactions table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `neocreds_transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `user_name` VARCHAR(100) NOT NULL,
    `user_email` VARCHAR(100) NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `status` ENUM('pending', 'approved', 'denied') DEFAULT 'pending',
    `request_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `process_date` TIMESTAMP NULL,
    `processed_by` VARCHAR(50) NULL,
    `admin_notes` TEXT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if (!$conn->query($sql)) {
    die("Error creating neocreds_transactions table: " . $conn->error);
}

// Create orders table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `orders` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `user_name` varchar(255) DEFAULT NULL,
    `user_email` varchar(255) DEFAULT NULL,
    `total_amount` decimal(10,2) NOT NULL,
    `payment_method` varchar(50) NOT NULL,
    `delivery_address` text NOT NULL,
    `contact_number` varchar(20) NOT NULL,
    `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($sql)) {
    die("Error creating orders table: " . $conn->error);
}

// Create order_items table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `order_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `quantity` int(11) NOT NULL,
    `size` varchar(10) NOT NULL,
    `price` decimal(10,2) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `order_id` (`order_id`),
    KEY `product_id` (`product_id`),
    CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($sql)) {
    die("Error creating order_items table: " . $conn->error);
}

// Drop the unverified_users table if it exists
$sql = "DROP TABLE IF EXISTS `unverified_users`";
if (!$conn->query($sql)) {
    die("Error dropping unverified_users table: " . $conn->error);
}

?>