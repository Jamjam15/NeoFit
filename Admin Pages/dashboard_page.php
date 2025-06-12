<?php
include '../db.php';

// Get today's sales and orders
$today = date('Y-m-d');
$sql_today_sales = "SELECT 
                    COALESCE(SUM(oi.quantity * p.product_price), 0) as today_sales, 
                    COUNT(DISTINCT o.id) as today_orders,
                    COUNT(DISTINCT o.user_name) as unique_customers
                    FROM orders o
                    LEFT JOIN order_items oi ON o.id = oi.order_id
                    LEFT JOIN products p ON oi.product_id = p.id
                    WHERE DATE(o.order_date) = ?";
$stmt = $conn->prepare($sql_today_sales);
$stmt->bind_param("s", $today);
$stmt->execute();
$today_result = $stmt->get_result()->fetch_assoc();

// Get yesterday's sales for comparison
$yesterday = date('Y-m-d', strtotime('-1 day'));
$sql_yesterday_sales = "SELECT 
                       COALESCE(SUM(oi.quantity * p.product_price), 0) as yesterday_sales,
                       COUNT(DISTINCT o.id) as yesterday_orders 
                       FROM orders o
                       LEFT JOIN order_items oi ON o.id = oi.order_id
                       LEFT JOIN products p ON oi.product_id = p.id
                       WHERE DATE(o.order_date) = ?";
$stmt = $conn->prepare($sql_yesterday_sales);
$stmt->bind_param("s", $yesterday);
$stmt->execute();
$yesterday_result = $stmt->get_result()->fetch_assoc();

// Calculate sales and orders trends
$sales_trend = 0;
$orders_trend = 0;
if ($yesterday_result['yesterday_sales'] > 0) {
    $sales_trend = (($today_result['today_sales'] - $yesterday_result['yesterday_sales']) / $yesterday_result['yesterday_sales']) * 100;
}
if ($yesterday_result['yesterday_orders'] > 0) {
    $orders_trend = (($today_result['today_orders'] - $yesterday_result['yesterday_orders']) / $yesterday_result['yesterday_orders']) * 100;
}

// Get total orders and revenue for last 30 days
$last_month = date('Y-m-d', strtotime('-30 days'));
$sql_month_stats = "SELECT 
                    COUNT(DISTINCT o.id) as total_orders, 
                    COALESCE(SUM(oi.quantity * p.product_price), 0) as total_sales,
                    COUNT(DISTINCT o.user_name) as unique_customers,
                    COUNT(DISTINCT DATE(o.order_date)) as active_days
                    FROM orders o
                    LEFT JOIN order_items oi ON o.id = oi.order_id
                    LEFT JOIN products p ON oi.product_id = p.id
                    WHERE o.order_date >= ?";
$stmt = $conn->prepare($sql_month_stats);
$stmt->bind_param("s", $last_month);
$stmt->execute();
$month_result = $stmt->get_result()->fetch_assoc();

// Calculate key metrics
$avg_order_value = 0;
$daily_sales_avg = 0;
$customer_frequency = 0;

if ($month_result['total_orders'] > 0) {
    $avg_order_value = $month_result['total_sales'] / $month_result['total_orders'];
}
if ($month_result['active_days'] > 0) {
    $daily_sales_avg = $month_result['total_sales'] / $month_result['active_days'];
}
if ($month_result['unique_customers'] > 0) {
    $customer_frequency = $month_result['total_orders'] / $month_result['unique_customers'];
}

// Get recent activity (last 10 orders/events)
$sql_recent = "SELECT 
               o.id, 
               o.user_name, 
               p.product_name, 
               o.status, 
               o.order_date, 
               (oi.quantity * p.product_price) as item_total,
               oi.size,
               oi.quantity
               FROM orders o
               LEFT JOIN order_items oi ON o.id = oi.order_id
               LEFT JOIN products p ON oi.product_id = p.id
               ORDER BY o.order_date DESC 
               LIMIT 10";
$recent_result = $conn->query($sql_recent);

// Get low stock items with more details
$sql_low_stock = "SELECT 
                  id,
                  product_name,
                  product_price,
                  quantity_small,
                  quantity_medium,
                  quantity_large,
                  (quantity_small + quantity_medium + quantity_large) as total_stock,
                  product_status,
                  product_category
                  FROM products 
                  WHERE (quantity_small + quantity_medium + quantity_large) <= 5
                  ORDER BY (quantity_small + quantity_medium + quantity_large) ASC
                  LIMIT 5";
$low_stock_result = $conn->query($sql_low_stock);

// Get best selling products
$sql_best_sellers = "SELECT 
                     p.product_name,
                     COUNT(DISTINCT o.id) as order_count,
                     SUM(oi.quantity) as units_sold,
                     SUM(oi.quantity * p.product_price) as revenue
                     FROM orders o
                     LEFT JOIN order_items oi ON o.id = oi.order_id
                     LEFT JOIN products p ON oi.product_id = p.id
                     WHERE o.order_date >= ?
                     GROUP BY p.product_name, p.id
                     ORDER BY units_sold DESC
                     LIMIT 5";
$stmt = $conn->prepare($sql_best_sellers);
$stmt->bind_param("s", $last_month);
$stmt->execute();
$best_sellers_result = $stmt->get_result();
$sql_low_stock = "SELECT product_name, 
                         quantity_small + quantity_medium + quantity_large as total_stock,
                         product_status,
                         id
                  FROM products 
                  WHERE (quantity_small + quantity_medium + quantity_large) <= 5
                  ORDER BY (quantity_small + quantity_medium + quantity_large) ASC
                  LIMIT 5";
$low_stock_result = $conn->query($sql_low_stock);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT Admin - Dashboard</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 1em;
        }

        .value {
            font-size: 1.8em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .trend {
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .trend.up { color: #28a745; }
        .trend.down { color: #dc3545; }

        .stat-icon {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 2em;
            opacity: 0.1;
        }

        .dashboard-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header h3 {
            margin: 0;
            color: #333;
        }

        .best-sellers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .product-card {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            transition: transform 0.2s ease;
        }

        .product-card:hover {
            transform: translateY(-2px);
        }

        .product-info h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 1em;
        }

        .product-stats {
            display: flex;
            justify-content: space-between;
            color: #666;
            font-size: 0.9em;
        }

        .activity-feed {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .activity-header h3 {
            margin: 0;
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s ease;
        }

        .activity-item:hover {
            background-color: #f8f9fa;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .order-id {
            font-weight: 500;
            color: #333;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 500;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-shipped { background: #d4edda; color: #155724; }
        .status-delivered { background: #c3e6cb; color: #1e7e34; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

        .activity-content p {
            margin: 5px 0;
            color: #666;
        }

        .activity-time {
            color: #999;
            font-size: 0.85em;
            margin-top: 5px;
        }

        .low-stock-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .size-stock {
            display: flex;
            gap: 15px;
            margin-bottom: 8px;
        }

        .size-stock span {
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            color: #666;
        }

        .stock-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: 500;
            display: inline-block;
        }

        .stock-badge.out-of-stock {
            background: #f8d7da;
            color: #721c24;
        }

        .stock-badge.low-stock {
            background: #fff3cd;
            color: #856404;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .category-badge {
            background: #e9ecef;
            color: #495057;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .price {
            font-weight: 500;
            color: #28a745;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }

            .best-sellers-grid,
            .low-stock-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <h1>NEOFIT</h1>
            <span class="admin-tag">Admin</span>
        </div>
        <div class="user-icon">
            <i class="fas fa-user-circle"></i>
        </div>
    </header>

        <div class="container">
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li class="active">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </li>
                <li onclick="window.location.href='manage_order_details_page.php'">
                    <i class="fas fa-list"></i>
                    <span>Manage Orders</span>
                </li>
                <li onclick="window.location.href='customer_orders_page.php'">
                    <i class="fas fa-users"></i>
                    <span>Customer Orders</span>
                </li>
                <li onclick="window.location.href='all_product_page.php'">
                    <i class="fas fa-tshirt"></i>
                    <span>All Products</span>
                </li>
                <li onclick="window.location.href='add_new_product_page.php'">
                    <i class="fas fa-plus-square"></i>
                    <span>Add New Product</span>
                </li>
                <li onclick="window.location.href='payments_page.php'">
                    <i class="fas fa-credit-card"></i>
                    <span>Payments</span>
                </li>
                <li onclick="window.location.href='neocreds_page.php'">
                    <i class="fas fa-coins"></i>
                    <span>NeoCreds</span>
                </li>
                <li onclick="window.location.href='settings.php'">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </li>
            </ul>
        </aside>
        
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Dashboard Overview</h1>
                <div class="date-range">
                    <i class="fas fa-calendar"></i>
                    <span><?php echo date('F j, Y'); ?></span>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <!-- Today's Performance -->
                <div class="stat-card">
                    <h3>Today's Sales</h3>
                    <div class="value">₱<?php echo number_format($today_result['today_sales'], 2); ?></div>
                    <div class="trend <?php echo $sales_trend >= 0 ? 'up' : 'down'; ?>">
                        <i class="fas fa-<?php echo $sales_trend >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                        <?php echo abs(round($sales_trend, 1)); ?>% vs yesterday
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <h3>Today's Orders</h3>
                    <div class="value"><?php echo $today_result['today_orders']; ?></div>
                    <div class="trend <?php echo $orders_trend >= 0 ? 'up' : 'down'; ?>">
                        <i class="fas fa-<?php echo $orders_trend >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                        <?php echo abs(round($orders_trend, 1)); ?>% vs yesterday
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <h3>Unique Customers</h3>
                    <div class="value"><?php echo $today_result['unique_customers']; ?></div>
                    <div class="trend">
                        <i class="fas fa-users"></i>
                        Today's customers
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <h3>Avg Order Value</h3>
                    <div class="value">₱<?php echo number_format($avg_order_value, 2); ?></div>
                    <div class="trend">
                        <i class="fas fa-chart-bar"></i>
                        30-day average
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                </div>

                <!-- Monthly Performance -->
                <div class="stat-card">
                    <h3>30-Day Revenue</h3>
                    <div class="value">₱<?php echo number_format($month_result['total_sales'], 2); ?></div>
                    <div class="trend">
                        <i class="fas fa-calendar"></i>
                        ₱<?php echo number_format($daily_sales_avg, 2); ?> daily avg
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <h3>30-Day Orders</h3>
                    <div class="value"><?php echo $month_result['total_orders']; ?></div>
                    <div class="trend">
                        <i class="fas fa-shopping-bag"></i>
                        <?php echo $month_result['active_days']; ?> active days
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <h3>Customer Base</h3>
                    <div class="value"><?php echo $month_result['unique_customers']; ?></div>
                    <div class="trend">
                        <i class="fas fa-repeat"></i>
                        <?php echo number_format($customer_frequency, 1); ?> orders per customer
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>

                <!-- NeoCreds Stats -->
                <div class="stat-card">
                    <h3>Total NeoCreds</h3>
                    <?php
                    // Get NeoCreds stats
                    $neocreds_result = $conn->query("
                        SELECT 
                            COUNT(*) as total_requests,
                            COALESCE(SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END), 0) as total_processed
                        FROM neocreds_transactions
                    ");
                    $neocreds_stats = $neocreds_result->fetch_assoc();
                    ?>
                    <div class="value">₱<?php echo number_format($neocreds_stats['total_processed'], 2); ?></div>
                    <div class="trend">
                        <i class="fas fa-coins"></i>
                        <?php echo $neocreds_stats['total_requests']; ?> total requests
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
            </div>

            <!-- Best Sellers Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3>Best Selling Products (30 Days)</h3>
                    <a href="all_product_page.php" style="text-decoration: none; color: #7ab55c;">View All Products</a>
                </div>
                <div class="best-sellers-grid">
                    <?php
                    if ($best_sellers_result->num_rows > 0) {
                        while ($product = $best_sellers_result->fetch_assoc()) {
                            ?>
                            <div class="product-card">
                                <div class="product-info">
                                    <h4><?php echo htmlspecialchars($product['product_name']); ?></h4>
                                    <div class="product-stats">
                                        <span><i class="fas fa-box"></i> <?php echo $product['units_sold']; ?> units</span>
                                        <span><i class="fas fa-dollar-sign"></i> ₱<?php echo number_format($product['revenue'], 2); ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<p>No sales data available</p>";
                    }
                    ?>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="activity-feed">
                <div class="activity-header">
                    <h3>Recent Orders</h3>
                    <a href="manage_order_details_page.php" style="text-decoration: none; color: #7ab55c;">View All Orders</a>
                </div>
                
                <?php
                if ($recent_result->num_rows > 0) {
                    while ($row = $recent_result->fetch_assoc()) {
                        $time_ago = time_elapsed_string($row['order_date']);
                        $status_class = strtolower($row['status']);
                        ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="activity-content">
                                <div class="order-header">
                                    <span class="order-id">Order #<?php echo $row['id']; ?></span>
                                    <span class="status-badge <?php echo $status_class; ?>"><?php echo $row['status']; ?></span>
                                </div>
                                <p>
                                    <strong><?php echo htmlspecialchars($row['user_name']); ?></strong> ordered                                    <?php echo $row['quantity']; ?>x <?php echo htmlspecialchars($row['product_name']); ?> 
                                    (<?php echo strtoupper($row['size']); ?>) - 
                                    ₱<?php echo number_format($row['item_total'], 2); ?>
                                </p>
                                <div class="activity-time"><?php echo $time_ago; ?></div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p>No recent orders</p>";
                }
                ?>
            </div>

            <!-- Low Stock Items -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3>Low Stock Items</h3>
                    <a href="all_product_page.php" style="text-decoration: none; color: #7ab55c;">Manage Inventory</a>
                </div>
                <div class="low-stock-grid">
                    <?php
                    if ($low_stock_result->num_rows > 0) {
                        while ($product = $low_stock_result->fetch_assoc()) {
                            $stock_status = $product['total_stock'] === 0 ? 'out-of-stock' : 'low-stock';
                            ?>
                            <div class="product-card">
                                <div class="product-info">
                                    <h4><?php echo htmlspecialchars($product['product_name']); ?></h4>
                                    <div class="product-stats">
                                        <div class="size-stock">
                                            <span>S: <?php echo $product['quantity_small']; ?></span>
                                            <span>M: <?php echo $product['quantity_medium']; ?></span>
                                            <span>L: <?php echo $product['quantity_large']; ?></span>
                                        </div>
                                        <span class="stock-badge <?php echo $stock_status; ?>">
                                            <?php echo $product['total_stock']; ?> total units
                                        </span>
                                    </div>
                                    <div class="product-footer">
                                        <span class="category-badge">
                                            <i class="fas fa-tag"></i>
                                            <?php echo ucfirst($product['product_category']); ?>
                                        </span>
                                        <span class="price">
                                            ₱<?php echo number_format($product['product_price'], 2); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<p>No low stock items</p>";
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Add any JavaScript functionality here
    </script>
</body>
</html>

<?php
// Helper function to format time elapsed
function time_elapsed_string($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->d > 0) {
        return $diff->d . " day" . ($diff->d > 1 ? "s" : "") . " ago";
    }
    if ($diff->h > 0) {
        return $diff->h . " hour" . ($diff->h > 1 ? "s" : "") . " ago";
    }
    if ($diff->i > 0) {
        return $diff->i . " minute" . ($diff->i > 1 ? "s" : "") . " ago";
    }
    return "just now";
}

$conn->close();
?>