<?php
require_once '../db_connection.php';

try {
    // Create payments table if not exists    $query = "CREATE TABLE IF NOT EXISTS payments (
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
    
    if (!mysqli_query($conn, $query)) {
        throw new Exception("Error creating payments table: " . mysqli_error($conn));
    }
    
    // Create payment_status_logs table if not exists
    $query = "CREATE TABLE IF NOT EXISTS payment_status_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        transaction_id VARCHAR(100) NOT NULL,
        old_status ENUM('pending', 'success', 'failed'),
        new_status ENUM('pending', 'success', 'failed'),
        changed_by VARCHAR(100) NOT NULL,
        changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (transaction_id) REFERENCES payments(transaction_id)
    ) ENGINE=InnoDB";
    
    if (!mysqli_query($conn, $query)) {
        throw new Exception("Error creating payment_status_logs table: " . mysqli_error($conn));
    }
    
    // Add indexes for better performance
    $indexes = [
        "CREATE INDEX idx_payment_status ON payments(status)",
        "CREATE INDEX idx_payment_date ON payments(payment_date)",
        "CREATE INDEX idx_payment_method ON payments(payment_method)",
        "CREATE INDEX idx_transaction_id ON payments(transaction_id)",
        "CREATE INDEX idx_order_id ON payments(order_id)",
        "CREATE INDEX idx_status_log_date ON payment_status_logs(changed_at)",
        "CREATE INDEX idx_status_log_transaction ON payment_status_logs(transaction_id)"
    ];
    
    foreach ($indexes as $index) {
        mysqli_query($conn, $index);
        // Ignore errors as the indexes might already exist
    }
    
    echo "Payment tables and indexes initialized successfully!\n";
    
} catch (Exception $e) {
    error_log("Error in init_payments.php: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}
?>