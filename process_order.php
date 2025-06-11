<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$payment_method = $_POST['payment_method'] ?? '';
$delivery_address = $_POST['delivery_address'] ?? '';
$contact_number = $_POST['contact_number'] ?? '';
$cart_id = $_POST['cart_id'] ?? null;

if (empty($payment_method) || empty($delivery_address) || empty($contact_number)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $conn->begin_transaction();

    // Get user details first
    $stmt = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) as user_name, email FROM users WHERE id = ?");
    if (!$stmt) {
        throw new Exception('Failed to prepare user details query: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception('Failed to fetch user details: ' . $stmt->error);
    }
    
    $user_result = $stmt->get_result();
    $user_details = $user_result->fetch_assoc();
    
    if (!$user_details) {
        throw new Exception('User details not found');
    }

    // Get cart items and calculate total
    if ($cart_id) {
        $sql = "SELECT c.*, p.product_price, p.product_name FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.id = ? AND c.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $cart_id, $user_id);
    } else {
        $sql = "SELECT c.*, p.product_price, p.product_name FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to fetch cart items: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $total_amount = 0;
    $cart_items = [];
    
    while ($item = $result->fetch_assoc()) {
        $subtotal = $item['quantity'] * $item['product_price'];
        $total_amount += $subtotal;
        $cart_items[] = $item;
    }

    if (empty($cart_items)) {
        throw new Exception('No items found in cart');
    }

    // Check NeoCreds balance if that's the payment method
    if ($payment_method === 'NeoCreds') {
        $stmt = $conn->prepare("SELECT neocreds FROM users WHERE id = ? FOR UPDATE");
        if (!$stmt) {
            throw new Exception('Failed to prepare balance check query: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to check balance: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {
            throw new Exception('User not found');
        }

        if ($user['neocreds'] < $total_amount) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Insufficient NeoCreds balance']);
            exit;
        }

        // Deduct NeoCreds
        $stmt = $conn->prepare("UPDATE users SET neocreds = neocreds - ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare balance update query: ' . $conn->error);
        }
        
        $stmt->bind_param("di", $total_amount, $user_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to update balance: ' . $stmt->error);
        }
    }

    // Create order with initial status
    $initial_status = ($payment_method === 'NeoCreds') ? 'processing' : 'pending';
    
    $stmt = $conn->prepare("INSERT INTO orders (user_id, user_name, user_email, total_amount, payment_method, delivery_address, contact_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception('Failed to prepare order creation query: ' . $conn->error);
    }
    
    $stmt->bind_param("issdssss", 
        $user_id, 
        $user_details['user_name'],
        $user_details['email'],
        $total_amount, 
        $payment_method, 
        $delivery_address, 
        $contact_number, 
        $initial_status
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create order: ' . $stmt->error);
    }
    
    $order_id = $conn->insert_id;

    // Insert order items
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, size, price) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception('Failed to prepare order items query: ' . $conn->error);
    }
    
    foreach ($cart_items as $item) {
        $stmt->bind_param("iiiss", $order_id, $item['product_id'], $item['quantity'], $item['size'], $item['product_price']);
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert order item: ' . $stmt->error);
        }
    }

    // Delete items from cart
    if ($cart_id) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_id, $user_id);
    } else {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to clear cart: ' . $stmt->error);
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    error_log('Order processing error: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error processing order: ' . $e->getMessage()
    ]);
}