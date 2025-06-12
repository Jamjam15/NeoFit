<?php
session_start();

// Check login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email']) || !isset($_SESSION['user_name'])) {
    die("You must be logged in to place an order.");
}

include 'db.php';

// Get form data
$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'] ?? '';
$product_name = $_POST['product_name'] ?? '';
$product_price = $_POST['product_price'] ?? 0.00;
$size = $_POST['size'] ?? '';
$color = $_POST['color'] ?? '';
$quantity = $_POST['quantity'] ?? 1;
$payment_method = $_POST['payment_method'] ?? 'Unknown';
$delivery_address = $_POST['delivery_address'] ?? 'Unknown';
$contact_number = $_POST['contact_number'] ?? 'Unknown';
$status = 'Pending';

$total_price = $product_price * $quantity;

// Get user data from session
$user_email = $_SESSION['email'];
$user_name = $_SESSION['user_name'];

// Insert order into DB
$sql = "INSERT INTO orders (
            user_id, user_name, user_email,
            product_id, product_name, price,
            size, color, quantity, total,
            payment_method, delivery_address, contact_number, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("issiisdssidsss", 
    $user_id, $user_name, $user_email, 
    $product_id, $product_name, $product_price,
    $size, $color, $quantity, $total_price,
    $payment_method, $delivery_address, $contact_number, $status
);



if ($stmt->execute()) {
    // Update product stock
    $update_stock_sql = "UPDATE products SET ";
    switch ($size) {
        case 'small':
            $update_stock_sql .= "quantity_small = quantity_small - ?";
            break;
        case 'medium':
            $update_stock_sql .= "quantity_medium = quantity_medium - ?";
            break;
        case 'large':
            $update_stock_sql .= "quantity_large = quantity_large - ?";
            break;
    }
    $update_stock_sql .= " WHERE id = ?";

    $update_stock_stmt = $conn->prepare($update_stock_sql);
    $update_stock_stmt->bind_param("ii", $quantity, $product_id);
    $update_stock_stmt->execute();

    echo "<script>
            alert('Order placed successfully!');
            window.location.href = 'product_detail.php?id=$product_id';
          </script>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
