<?php
include 'db.php';

function getProductSoldCount($product_name) {
    global $conn;
    
    $query = "SELECT COALESCE(SUM(oi.quantity), 0) AS total_sold
              FROM order_items oi
              JOIN orders o ON oi.order_id = o.id
              JOIN products p ON p.id = oi.product_id
              WHERE p.product_name = ? AND o.status != 'cancelled'";
              
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $product_name);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    return $row['total_sold'];
}

function getAllProductsSoldCount() {
    global $conn;
    
    $query = "SELECT p.product_name, COALESCE(SUM(oi.quantity), 0) AS total_sold
            FROM products p
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id 
            WHERE o.status != 'cancelled' OR o.status IS NULL
            GROUP BY p.product_name";
              
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        error_log("MySQL Error: " . mysqli_error($conn));
        return array();
    }
    
    $sold_counts = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $sold_counts[$row['product_name']] = $row['total_sold'];
    }
    
    return $sold_counts;
}
?>