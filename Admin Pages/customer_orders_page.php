<?php
include '../db.php';

// Get customer statistics
$stats_sql = "SELECT 
    COUNT(DISTINCT o.user_id) as total_customers,
    COUNT(*) as total_orders,
    COALESCE(SUM(oi.quantity * p.product_price), 0) as total_revenue,
    COALESCE(AVG(oi.quantity * p.product_price), 0) as avg_order_value,
    COUNT(DISTINCT DATE(o.order_date)) as active_days
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
LEFT JOIN products p ON oi.product_id = p.id
WHERE o.status != 'cancelled'";

$stats_result = $conn->query($stats_sql);

if (!$stats_result) {
    error_log("MySQL Error: " . $conn->error);
    $stats_result = [
        'total_customers' => 0,
        'total_orders' => 0,
        'total_revenue' => 0,
        'avg_order_value' => 0,
        'active_days' => 0
    ];
} else {
    $stats_result = $stats_result->fetch_assoc();
}

// Get customer list with their order summaries
// Find and replace the SQL query around line 12
$sql = "SELECT 
        o.id,
        o.user_id,
        o.status,
        o.order_date,
        COALESCE(SUM(oi.quantity * p.product_price), 0) as total_spent,
        o.user_email,
        o.user_name,
        COUNT(DISTINCT o.id) as order_count,
        MAX(o.order_date) as last_order_date,
        SUM(CASE WHEN o.status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN o.status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
        SUM(CASE WHEN o.status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
        SUM(CASE WHEN o.status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
        SUM(CASE WHEN o.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
        GROUP_CONCAT(DISTINCT CONCAT(p.product_name, ' (', oi.quantity, ')') SEPARATOR ', ') as order_items
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        LEFT JOIN users u ON o.user_id = u.id
        GROUP BY o.user_id, o.user_email, o.user_name
        ORDER BY last_order_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT Admin - Customer Orders</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-value {
            font-size: 1.8em;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        .stat-label {
            color: #666;
            font-size: 0.9em;
        }

        .customer-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }

        .customer-card:hover {
            transform: translateY(-2px);
        }

        .customer-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .customer-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .customer-avatar {
            width: 50px;
            height: 50px;
            background: #f0f0f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
            color: #666;
        }

        .customer-details h3 {
            margin: 0;
            color: #333;
        }

        .customer-email {
            color: #666;
            font-size: 0.9em;
        }

        .order-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .metric-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }

        .metric-value {
            font-size: 1.2em;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .metric-label {
            font-size: 0.85em;
            color: #666;
        }

        .status-summary {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.85em;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-shipped { background: #d4edda; color: #155724; }
        .status-delivered { background: #c3e6cb; color: #1e7e34; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

        .customer-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.2s;
        }

        .view-orders-btn {
            background: #7ab55c;
            color: white;
        }

        .contact-btn {
            background: #6c757d;
            color: white;
        }

        .search-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .search-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .order-metrics {
                grid-template-columns: repeat(2, 1fr);
            }

            .customer-actions {
                flex-direction: column;
            }

            .action-btn {
                width: 100%;
                justify-content: center;
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
                <li onclick="window.location.href='dashboard_page.php'">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </li>
                <li onclick="window.location.href='manage_order_details_page.php'">
                    <i class="fas fa-list"></i>
                    <span>Manage Orders</span>
                </li>
                <li class="active">
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
            <h1 class="page-title">Customer Orders</h1>

            <!-- Customer Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats_result['total_customers']; ?></div>
                    <div class="stat-label">Total Customers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats_result['total_orders']; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">₱<?php echo number_format($stats_result['total_revenue'], 2); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">₱<?php echo number_format($stats_result['avg_order_value'], 2); ?></div>
                    <div class="stat-label">Average Order Value</div>
                </div>
            </div>

            <!-- Search Section -->
            <div class="search-section">
                <input type="text" id="customerSearch" class="search-input" 
                       placeholder="Search customers by name or email...">
            </div>

            <!-- Customer List -->
            <div class="customer-list">
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $initial = strtoupper(substr($row['user_name'], 0, 1));
                        ?>
                        <div class="customer-card">
                            <div class="customer-header">
                                <div class="customer-info">
                                    <div class="customer-avatar">
                                        <?php echo $initial; ?>
                                    </div>
                                    <div class="customer-details">
                                        <h3><?php echo htmlspecialchars($row['user_name']); ?></h3>
                                        <div class="customer-email"><?php echo htmlspecialchars($row['user_email']); ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="order-metrics">
                                <div class="metric-item">
                                    <div class="metric-value"><?php echo $row['order_count']; ?></div>
                                    <div class="metric-label">Total Orders</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-value">₱<?php echo number_format($row['total_spent'], 2); ?></div>
                                    <div class="metric-label">Total Spent</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-value"><?php echo date('M d, Y', strtotime($row['last_order_date'])); ?></div>
                                    <div class="metric-label">Last Order</div>
                                </div>
                            </div>

                            <div class="status-summary">
                                <?php if ($row['pending_orders'] > 0): ?>
                                    <span class="status-badge status-pending">
                                        <i class="fas fa-clock"></i>
                                        <?php echo $row['pending_orders']; ?> Pending
                                    </span>
                                <?php endif; ?>
                                <?php if ($row['processing_orders'] > 0): ?>
                                    <span class="status-badge status-processing">
                                        <i class="fas fa-cog"></i>
                                        <?php echo $row['processing_orders']; ?> Processing
                                    </span>
                                <?php endif; ?>
                                <?php if ($row['shipped_orders'] > 0): ?>
                                    <span class="status-badge status-shipped">
                                        <i class="fas fa-truck"></i>
                                        <?php echo $row['shipped_orders']; ?> Shipped
                                    </span>
                                <?php endif; ?>
                                <?php if ($row['delivered_orders'] > 0): ?>
                                    <span class="status-badge status-delivered">
                                        <i class="fas fa-check"></i>
                                        <?php echo $row['delivered_orders']; ?> Delivered
                                    </span>
                                <?php endif; ?>
                                <?php if ($row['cancelled_orders'] > 0): ?>
                                    <span class="status-badge status-cancelled">
                                        <i class="fas fa-times"></i>
                                        <?php echo $row['cancelled_orders']; ?> Cancelled
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="customer-actions">
                                <button class="action-btn view-orders-btn" 
                                        onclick="window.location.href='manage_order_details_page.php?user=<?php echo urlencode($row['user_email']); ?>'">
                                    <i class="fas fa-shopping-bag"></i>
                                    View Orders
                                </button>
                                <button class="action-btn contact-btn" 
                                        onclick="contactCustomer('<?php echo htmlspecialchars($row['user_email']); ?>')">
                                    <i class="fas fa-envelope"></i>
                                    Contact Customer
                                </button>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p>No customers found</p>";
                }
                ?>
            </div>
        </main>
    </div>

    <script>
        // Customer search functionality
        document.getElementById('customerSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const customerCards = document.querySelectorAll('.customer-card');

            customerCards.forEach(card => {
                const name = card.querySelector('.customer-details h3').textContent.toLowerCase();
                const email = card.querySelector('.customer-email').textContent.toLowerCase();

                if (name.includes(searchTerm) || email.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        function contactCustomer(email) {
            // Open default email client
            window.location.href = `mailto:${email}`;
        }
    </script>
</body>
</html>

<?php
$conn->close();
?> 