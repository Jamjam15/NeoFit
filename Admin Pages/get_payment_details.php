<?php
require_once '../db_connection.php';
require_once 'payment_functions.php';

// Start session to check admin login
session_start();
if (!isset($_SESSION['admin@1'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

if (!isset($_GET['transaction_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Transaction ID is required']);
    exit;
}

$transaction_id = $_GET['transaction_id'];

try {    $query = "SELECT 
                p.*,
                o.user_name,
                o.user_email,
                o.order_status as delivery_status,
                DATE_FORMAT(p.payment_date, '%M %d, %Y %H:%i') as formatted_date
            FROM payments p 
            LEFT JOIN orders o ON p.order_id = o.id 
            WHERE p.transaction_id = ?";
            
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception("Error preparing query: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "s", $transaction_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result) {
        throw new Exception("Error getting result: " . mysqli_error($conn));
    }
    
    $payment = mysqli_fetch_assoc($result);
    
    if (!$payment) {
        http_response_code(404);
        echo json_encode(['error' => 'Payment not found']);
        exit;
    }
    
    // Add status class for styling
    $status_class = '';
    switch($payment['status']) {
        case 'success':
            $status_class = 'status-success';
            break;
        case 'pending':
            $status_class = 'status-pending';
            break;
        case 'failed':
            $status_class = 'status-failed';
            break;
    }
      // Format the response data
    $response = [
        'transaction_id' => $payment['transaction_id'],
        'order_id' => $payment['order_id'],
        'customer_name' => $payment['user_name'],
        'customer_email' => $payment['user_email'],
        'amount' => number_format($payment['amount'], 2),
        'payment_method' => $payment['payment_method'],
        'status' => ucfirst($payment['status']),
        'payment_date' => $payment['formatted_date'],
        'status_class' => $status_class,
        'delivery_status' => $payment['delivery_status'] ? ucfirst($payment['delivery_status']) : null
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error in get_payment_details.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
    exit;
}
?>