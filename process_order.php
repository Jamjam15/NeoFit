<?php
session_start();
include 'db.php';

function generateTransactionId() {
    $prefix = 'TXN';
    $timestamp = time();
    $random = mt_rand(1000, 9999);
    return $prefix . $timestamp . $random;
}

// ✅ Handle NeoCreds payment history request (GET method)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'neocreds_payments') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
        exit;
    }

    try {
        $stmt = $conn->prepare("
            SELECT o.id as order_id, o.total_amount as amount, o.order_date
            FROM orders o
            WHERE o.user_id = ? AND o.payment_method = 'NeoCreds'
            ORDER BY o.order_date DESC
        ");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }

        echo json_encode(['status' => 'success', 'transactions' => $transactions]);
    } catch (Exception $e) {
        error_log("Error fetching NeoCreds payments: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch payment history']);
    }

    $conn->close();
    exit;
}

// ✅ Handle checkout (POST method)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $user_name = $_POST['user_name'] ?? '';
    $user_email = $_POST['user_email'] ?? '';
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

        // ✅ Fetch cart items
        if ($cart_id) {
            $sql = "SELECT c.*, p.product_name, p.product_price 
                    FROM cart c
                    JOIN products p ON c.product_id = p.id
                    WHERE c.id = ? AND c.user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $cart_id, $user_id);
        } else {
            $sql = "SELECT c.*, p.product_name, p.product_price 
                    FROM cart c
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
            throw new Exception("Cart is empty");
        }

        // ✅ Stock check
        foreach ($cart_items as $item) {
            switch (strtolower($item['size'])) {
                case 'small':
                    $size_column = 'quantity_small';
                    break;
                case 'medium':
                    $size_column = 'quantity_medium';
                    break;
                case 'large':
                    $size_column = 'quantity_large';
                    break;
                default:
                    throw new Exception("Invalid size '{$item['size']}' for product '{$item['product_name']}'");
            }

            $check_stock_sql = "SELECT $size_column AS stock FROM products WHERE id = ?";
            $check_stock_stmt = $conn->prepare($check_stock_sql);
            $check_stock_stmt->bind_param("i", $item['product_id']);
            $check_stock_stmt->execute();
            $stock_result = $check_stock_stmt->get_result()->fetch_assoc();

            if ($stock_result['stock'] < $item['quantity']) {
                throw new Exception("Not enough stock for '{$item['product_name']}' - Size: {$item['size']}");
            }
        }

        // ✅ NeoCreds balance check
        if ($payment_method === 'NeoCreds') {
            $balance_stmt = $conn->prepare("SELECT neocreds FROM users WHERE id = ?");
            $balance_stmt->bind_param("i", $user_id);
            $balance_stmt->execute();
            $balance_result = $balance_stmt->get_result();
            $user_balance = $balance_result->fetch_assoc()['neocreds'];

            if ($user_balance < $total_amount) {
                throw new Exception('Insufficient NeoCreds balance');
            }

            $update_balance_sql = "UPDATE users SET neocreds = neocreds - ? WHERE id = ?";
            $update_balance_stmt = $conn->prepare($update_balance_sql);
            $update_balance_stmt->bind_param("di", $total_amount, $user_id);

            if (!$update_balance_stmt->execute()) {
                throw new Exception("Error updating NeoCreds balance");
            }
        }

        // ✅ Insert order
        $order_stmt = $conn->prepare("INSERT INTO orders (user_id, user_name, user_email, total_amount, payment_method, delivery_address, contact_number, order_date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $order_stmt->bind_param("issdsss", $user_id, $user_name, $user_email, $total_amount, $payment_method, $delivery_address, $contact_number);
        if (!$order_stmt->execute()) {
            throw new Exception("Error creating order");
        }
        $order_id = $conn->insert_id;

        // ✅ Insert payment
        $transaction_id = generateTransactionId();
        $status = ($payment_method === 'NeoCreds') ? 'success' : 'pending';
        $payment_stmt = $conn->prepare("INSERT INTO payments (transaction_id, order_id, user_name, amount, payment_method, status, payment_date) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
        $payment_stmt->bind_param("sisdss", $transaction_id, $order_id, $user_name, $total_amount, $payment_method, $status);
        if (!$payment_stmt->execute()) {
            throw new Exception("Error creating payment record");
        }

        // ✅ Insert order items and update stock
        $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, size) VALUES (?, ?, ?, ?, ?)");
        foreach ($cart_items as $item) {
            $item_stmt->bind_param("iiids", $order_id, $item['product_id'], $item['quantity'], $item['product_price'], $item['size']);
            if (!$item_stmt->execute()) {
                throw new Exception("Error adding order item");
            }

            switch (strtolower($item['size'])) {
                case 'small': $size_column = 'quantity_small'; break;
                case 'medium': $size_column = 'quantity_medium'; break;
                case 'large': $size_column = 'quantity_large'; break;
            }

            $update_stock_sql = "UPDATE products SET $size_column = $size_column - ? WHERE id = ?";
            $stock_stmt = $conn->prepare($update_stock_sql);
            $stock_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            if (!$stock_stmt->execute()) {
                throw new Exception("Error updating stock for '{$item['product_name']}' - Size: {$item['size']}");
            }
        }

        // ✅ Clear cart
        if ($cart_id) {
            $delete_stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $delete_stmt->bind_param("ii", $cart_id, $user_id);
        } else {
            $delete_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $delete_stmt->bind_param("i", $user_id);
        }

        if (!$delete_stmt->execute()) {
            throw new Exception("Error clearing cart");
        }

        $conn->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $conn->close();
}
?>
