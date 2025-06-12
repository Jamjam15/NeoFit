<?php
require_once dirname(__FILE__) . '/../db_connection.php';

// Get total revenue from successful payments
function getTotalRevenue() {
    global $conn;
    try {
        $query = "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'success'";
        $result = mysqli_query($conn, $query);
        if (!$result) {
            error_log("Error getting total revenue: " . mysqli_error($conn));
            return 0;
        }
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ?? 0;
    } catch (Exception $e) {
        error_log("Exception getting total revenue: " . $e->getMessage());
        return 0;
    }
}

// Get today's earnings from successful payments
function getTodayEarnings() {
    global $conn;
    try {
        $query = "SELECT COALESCE(SUM(amount), 0) as total FROM payments 
                WHERE status = 'success' 
                AND DATE(payment_date) = CURDATE()";
        $result = mysqli_query($conn, $query);
        if (!$result) {
            error_log("Error getting today's earnings: " . mysqli_error($conn));
            return 0;
        }
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ?? 0;
    } catch (Exception $e) {
        error_log("Exception getting today's earnings: " . $e->getMessage());
        return 0;
    }
}

// Get total amount of pending payments
function getPendingPayments() {
    global $conn;
    try {
        $query = "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'pending'";
        $result = mysqli_query($conn, $query);
        if (!$result) {
            error_log("Error getting pending payments: " . mysqli_error($conn));
            return 0;
        }
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ?? 0;
    } catch (Exception $e) {
        error_log("Exception getting pending payments: " . $e->getMessage());
        return 0;
    }
}

// Get filtered payments with pagination
function getFilteredPayments($search = '', $status = '', $date = '', $page = 1, $limit = 10) {
    global $conn;
    try {
        $offset = ($page - 1) * $limit;
        $where_clauses = [];
        $params = [];
        
        if (!empty($search)) {
            $where_clauses[] = "(p.transaction_id LIKE ? OR o.user_name LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if (!empty($status)) {
            if (!in_array($status, ['pending', 'success', 'failed'])) {
                error_log("Invalid status filter attempted: $status");
                return [];
            }
            $where_clauses[] = "p.status = ?";
            $params[] = $status;
        }
        
        if (!empty($date)) {
            $where_clauses[] = "DATE(p.payment_date) = ?";
            $params[] = $date;
        }
        
        $where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";
        
        $query = "SELECT p.*, o.user_name, o.user_email, 
                                (SELECT SUM(oi2.quantity * pr2.product_price) 
                                 FROM order_items oi2 
                                 JOIN products pr2 ON oi2.product_id = pr2.id 
                                 WHERE oi2.order_id = o.id) as order_total,
                        u.first_name, u.last_name, u.email
                FROM payments p 
                JOIN orders o ON p.order_id = o.id 
                JOIN users u ON o.user_id = u.id 
                $where_sql 
                ORDER BY p.payment_date DESC 
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            error_log("Error preparing filtered payments query: " . mysqli_error($conn));
            return [];
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            if (!mysqli_stmt_bind_param($stmt, $types, ...$params)) {
                error_log("Error binding parameters: " . mysqli_error($conn));
                return [];
            }
        }
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Error executing filtered payments query: " . mysqli_error($conn));
            return [];
        }
        
        $result = mysqli_stmt_get_result($stmt);
        if (!$result) {
            error_log("Error getting result set: " . mysqli_error($conn));
            return [];
        }
        
        $payments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $payments[] = $row;
        }
        
        return $payments;
        
    } catch (Exception $e) {
        error_log("Exception in getFilteredPayments: " . $e->getMessage());
        return [];
    }
}

// Get total count of filtered payments
function getFilteredPaymentsCount($search = '', $status = '', $date = '') {
    global $conn;
    try {
        $where_clauses = [];
        $params = [];
        
        if (!empty($search)) {
            $where_clauses[] = "(p.transaction_id LIKE ? OR o.user_name LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if (!empty($status)) {
            if (!in_array($status, ['pending', 'success', 'failed'])) {
                error_log("Invalid status filter attempted: $status");
                return 0;
            }
            $where_clauses[] = "p.status = ?";
            $params[] = $status;
        }
        
        if (!empty($date)) {
            $where_clauses[] = "DATE(p.payment_date) = ?";
            $params[] = $date;
        }
        
        $where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";
        
        $query = "SELECT COUNT(*) as total 
                FROM payments p 
                JOIN orders o ON p.order_id = o.id 
                JOIN users u ON o.user_id = u.id 
                $where_sql";
        
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            error_log("Error preparing filtered payments count query: " . mysqli_error($conn));
            return 0;
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            if (!mysqli_stmt_bind_param($stmt, $types, ...$params)) {
                error_log("Error binding parameters: " . mysqli_error($conn));
                return 0;
            }
        }
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Error executing filtered payments count query: " . mysqli_error($conn));
            return 0;
        }
        
        $result = mysqli_stmt_get_result($stmt);
        if (!$result) {
            error_log("Error getting result set: " . mysqli_error($conn));
            return 0;
        }
        
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ?? 0;
        
    } catch (Exception $e) {
        error_log("Exception in getFilteredPaymentsCount: " . $e->getMessage());
        return 0;
    }
}

// Update payment status
function updatePaymentStatus($transaction_id, $new_status) {
    global $conn;
    
    if (!in_array($new_status, ['pending', 'success', 'failed'])) {
        error_log("Invalid payment status attempted: $new_status");
        return false;
    }
    
    try {
        mysqli_begin_transaction($conn);
        
        // Get current status for logging
        $current_stmt = mysqli_prepare($conn, "SELECT status FROM payments WHERE transaction_id = ?");
        mysqli_stmt_bind_param($current_stmt, "s", $transaction_id);
        mysqli_stmt_execute($current_stmt);
        $result = mysqli_stmt_get_result($current_stmt);
        $current = mysqli_fetch_assoc($result);
        
        if (!$current) {
            mysqli_rollback($conn);
            error_log("Transaction ID not found: $transaction_id");
            return false;
        }
        
        // Update payment status
        $update_stmt = mysqli_prepare($conn, "UPDATE payments SET status = ? WHERE transaction_id = ?");
        mysqli_stmt_bind_param($update_stmt, "ss", $new_status, $transaction_id);
        $success = mysqli_stmt_execute($update_stmt);
        
        if (!$success) {
            mysqli_rollback($conn);
            error_log("Failed to update payment status: " . mysqli_error($conn));
            return false;
        }
        
        // Log the status change
        $log_stmt = mysqli_prepare($conn, 
            "INSERT INTO payment_status_logs (transaction_id, old_status, new_status, changed_by, changed_at) 
             VALUES (?, ?, ?, ?, NOW())"
        );
        $admin = $_SESSION['admin@1'] ?? 'system';
        mysqli_stmt_bind_param($log_stmt, "ssss", 
            $transaction_id, 
            $current['status'], 
            $new_status, 
            $admin
        );
        $log_success = mysqli_stmt_execute($log_stmt);
        
        if (!$log_success) {
            mysqli_rollback($conn);
            error_log("Failed to log payment status change: " . mysqli_error($conn));
            return false;
        }
        
        mysqli_commit($conn);
        return true;
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Exception in updatePaymentStatus: " . $e->getMessage());
        return false;
    }
}

?>