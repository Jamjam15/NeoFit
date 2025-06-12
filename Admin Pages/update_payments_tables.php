<?php
require_once '../db_connection.php';

try {
    // Add user_name column to payments table if it doesn't exist
    $check_column = "SHOW COLUMNS FROM payments LIKE 'user_name'";
    $result = mysqli_query($conn, $check_column);
    
    if (!$result || mysqli_num_rows($result) == 0) {
        $alter_query = "ALTER TABLE payments ADD COLUMN user_name VARCHAR(255) NOT NULL AFTER order_id";
        if (!mysqli_query($conn, $alter_query)) {
            throw new Exception("Error adding user_name column: " . mysqli_error($conn));
        }
    }

    // Ensure the payments table has the correct structure
    $update_query = "CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        transaction_id VARCHAR(100) NOT NULL UNIQUE,
        order_id INT NOT NULL,
        user_name VARCHAR(255) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id)
    ) ENGINE=InnoDB";
    
    if (!mysqli_query($conn, $update_query)) {
        throw new Exception("Error updating payments table: " . mysqli_error($conn));
    }

    echo "Payments tables updated successfully";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
