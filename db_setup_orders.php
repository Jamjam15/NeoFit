<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

// Create orders table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `orders` (
    `id` int(11) NOT NULL AUTO_INCREMENT,    `user_id` int(11) NOT NULL,
    `user_name` varchar(100) NOT NULL,
    `user_email` varchar(100) NOT NULL,
    `delivery_address` text NOT NULL,
    `contact_number` varchar(20) NOT NULL,
    `payment_method` varchar(50) NOT NULL,
    `status` enum('Pending','Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
    `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql)) {
    echo "Orders table created successfully or already exists.<br>";
} else {
    echo "Error creating orders table: " . $conn->error . "<br>";
}

// Create products table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `products` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_name` varchar(255) NOT NULL,
    `description` text,
    `product_price` decimal(10,2) NOT NULL,
    `photoFront` varchar(255),
    `photoBack` varchar(255),
    `category` varchar(50),
    `quantity_small` int(11) DEFAULT 0,
    `quantity_medium` int(11) DEFAULT 0,
    `quantity_large` int(11) DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_product_name` (`product_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql)) {
    echo "Products table created successfully or already exists.<br>";
} else {
    echo "Error creating products table: " . $conn->error . "<br>";
}

// Create order_items table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `order_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `size` varchar(10) NOT NULL,
    `quantity` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql)) {
    echo "Order items table created successfully or already exists.<br>";
} else {
    echo "Error creating order items table: " . $conn->error . "<br>";
}

$conn->close();
echo "Database setup completed.";
?> 