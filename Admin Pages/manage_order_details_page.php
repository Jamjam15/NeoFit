<?php
include '../db.php';

// Get filters from URL
$user_filter = isset($_GET['user']) ? $_GET['user'] : null;
$status_filter = isset($_GET['status']) ? $_GET['status'] : null;
$search_term = isset($_GET['search']) ? $_GET['search'] : null;
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : null;
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : null;

// Base query
$sql = "SELECT o.*, 
               p.product_name as product_display_name,
               p.photoFront as product_image,
               oi.quantity,
               oi.product_id,
               oi.size,
               p.product_price as price,
               (oi.quantity * p.product_price) as item_total
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE 1=1";
$params = [];
$types = "";

// Add filters
if ($user_filter) {
    $sql .= " AND o.user_email = ?";
    $params[] = $user_filter;
    $types .= "s";
}

if ($status_filter) {
    $sql .= " AND o.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($search_term) {
    $search_term = "%$search_term%";
    $sql .= " AND (o.user_name LIKE ? OR p.product_name LIKE ? OR o.id LIKE ?)";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
    $types .= "sss";
}

if ($date_from) {
    $sql .= " AND DATE(o.order_date) >= ?";
    $params[] = $date_from;
    $types .= "s";
}

if ($date_to) {
    $sql .= " AND DATE(o.order_date) <= ?";
    $params[] = $date_to;
    $types .= "s";
}

// Add sorting
$sql .= " ORDER BY o.order_date DESC";

// Prepare and execute query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get order statistics
$stats_sql = "SELECT 
    COUNT(DISTINCT o.id) as total_orders,
    COUNT(DISTINCT o.user_name) as unique_customers,
    COALESCE(SUM(oi.quantity * p.product_price), 0) as total_revenue,
    COUNT(DISTINCT CASE WHEN o.status = 'Pending' THEN o.id END) as pending_orders,
    COUNT(DISTINCT CASE WHEN o.status = 'Processing' THEN o.id END) as processing_orders,
    COUNT(DISTINCT CASE WHEN o.status = 'Shipped' THEN o.id END) as shipped_orders,
    COUNT(DISTINCT CASE WHEN o.status = 'Delivered' THEN o.id END) as delivered_orders,
    COUNT(DISTINCT CASE WHEN o.status = 'Cancelled' THEN o.id END) as cancelled_orders
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
LEFT JOIN products p ON oi.product_id = p.id";
$stats_result = $conn->query($stats_sql)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT Admin - Order Management</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .filters-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .filter-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-label {
            font-size: 0.9em;
            color: #666;
        }

        .filter-input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9em;
        }

        /* Filter status dropdown styling to match group */
        .filter-input[name="status"] {
            padding: 8px 32px 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9em;
            background: white url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="6"><path d="M0 0h12L6 6z" fill="%23666"/></svg>') no-repeat;
            background-position: right 12px center;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 160px;
        }

        .filter-input[name="status"]:focus {
            outline: none;
            border-color: #7ab55c;
            box-shadow: 0 0 0 2px rgba(122, 181, 92, 0.1);
        }

        .filter-input[name="status"]:hover {
            border-color: #7ab55c;
        }

        .filter-input[name="status"] option {
            padding: 8px;
            background: white;
            color: #333;
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
        }

        .filter-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.2s;
        }

        .apply-btn {
            background: #7ab55c;
            color: white;
        }

        .reset-btn {
            background: #6c757d;
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-value {
            font-size: 1.5em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9em;
            color: #666;
        }

        .order-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }

        .order-card:hover {
            transform: translateY(-2px);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .order-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .product-info, .customer-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }

        .product-info {
            display: flex;
            gap: 15px;
        }

        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }

        .print-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .print-btn:hover {
            background: #5a6268;
        }

        .status-select {
            padding: 8px 32px 8px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: white url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="6"><path d="M0 0h12L6 6z" fill="%23666"/></svg>') no-repeat;
            background-position: right 12px center;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 160px;
        }

        .status-select:focus {
            outline: none;
            border-color: #7ab55c;
            box-shadow: 0 0 0 3px rgba(122, 181, 92, 0.1);
        }

        .status-select:hover {
            border-color: #7ab55c;
        }

        /* Status-specific colors */
        .status-select option[value="Pending"] {
            color: #856404;
            background-color: #fff3cd;
        }

        .status-select option[value="Processing"] {
            color: #004085;
            background-color: #cce5ff;
        }

        .status-select option[value="Shipped"] {
            color: #155724;
            background-color: #d4edda;
        }

        .status-select option[value="Delivered"] {
            color: #1e7e34;
            background-color: #c3e6cb;
        }

        .status-select option[value="Cancelled"] {
            color: #721c24;
            background-color: #f8d7da;
        }

        /* Status colors for the select itself */
        .status-select.status-pending {
            color: #856404;
            border-color: #ffeeba;
            background-color: #fff3cd;
        }

        .status-select.status-processing {
            color: #004085;
            border-color: #b8daff;
            background-color: #cce5ff;
        }

        .status-select.status-shipped {
            color: #155724;
            border-color: #c3e6cb;
            background-color: #d4edda;
        }

        .status-select.status-delivered {
            color: #1e7e34;
            border-color: #a3d7a8;
            background-color: #c3e6cb;
        }

        .status-select.status-cancelled {
            color: #721c24;
            border-color: #f5c6cb;
            background-color: #f8d7da;
        }

        .order-date {
            color: #666;
            margin-left: 10px;
            font-size: 0.9em;
        }

        .order-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.2s;
        }

        .view-btn { background: #6c757d; color: white; }
        .edit-btn { background: #7ab55c; color: white; }
        .delete-btn { background: #dc3545; color: white; }

        .back-button {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }

        .back-button:hover {
            background: #5a6268;
        }

        .status-menu {
            position: absolute;
            background: white;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: none;
            z-index: 100;
        }

        .status-menu.active {
            display: block;
        }

        .status-option {
            padding: 8px 16px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .status-option:hover {
            background-color: #f8f9fa;
        }

        @media (max-width: 768px) {
            .filters-grid {
                grid-template-columns: 1fr;
            }

            .order-details {
                grid-template-columns: 1fr;
            }

            .order-actions {
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
                <li class="active">
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
            <?php if ($user_filter): ?>
                <a href="customer_orders_page.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to All Customers
                </a>
            <?php endif; ?>

            <h1 class="page-title">
                <?php 
                if ($user_filter) {
                    $user_name = $result->num_rows > 0 ? $result->fetch_array()['user_name'] : 'Unknown User';
                    $result->data_seek(0); // Reset result pointer
                    echo "Orders for " . htmlspecialchars($user_name);
                } else {
                    echo "Order Management";
                }
                ?>
            </h1>

            <!-- Order Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats_result['total_orders']; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats_result['unique_customers']; ?></div>
                    <div class="stat-label">Unique Customers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">₱<?php echo number_format($stats_result['total_revenue'], 2); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats_result['pending_orders']; ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats_result['processing_orders']; ?></div>
                    <div class="stat-label">Processing</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats_result['shipped_orders']; ?></div>
                    <div class="stat-label">Shipped</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats_result['delivered_orders']; ?></div>
                    <div class="stat-label">Delivered</div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="filters-section">
                <form id="filterForm" method="GET">
                    <div class="filters-grid">
                        <div class="filter-item">
                            <label class="filter-label">Search</label>
                            <input type="text" name="search" class="filter-input" 
                                   placeholder="Order ID, Customer, or Product" 
                                   value="<?php echo htmlspecialchars($search_term ?? ''); ?>">
                        </div>
                        <div class="filter-item">
                            <label class="filter-label">Status</label>
                            <select name="status" class="filter-input">
                                <option value="">All Statuses</option>
                                <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Processing" <?php echo $status_filter === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="Shipped" <?php echo $status_filter === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="Delivered" <?php echo $status_filter === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="filter-item">
                            <label class="filter-label">Date From</label>
                            <input type="date" name="date_from" class="filter-input" 
                                   value="<?php echo htmlspecialchars($date_from ?? ''); ?>">
                        </div>
                        <div class="filter-item">
                            <label class="filter-label">Date To</label>
                            <input type="date" name="date_to" class="filter-input" 
                                   value="<?php echo htmlspecialchars($date_to ?? ''); ?>">
                        </div>
                    </div>
                    <div class="filter-buttons">
                        <button type="submit" class="filter-btn apply-btn">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <button type="button" class="filter-btn reset-btn" onclick="resetFilters()">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Orders List -->
            <div class="orders-list">
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <strong>Order #<?php echo $row['id']; ?></strong>
                                    <span class="order-date"><?php echo date('F d, Y', strtotime($row['order_date'])); ?></span>
                                </div>
                                <select class="status-select" 
                                        onchange="updateOrderStatus(<?php echo $row['id']; ?>, this.value)"
                                        data-order-id="<?php echo $row['id']; ?>">
                                    <?php
                                    $statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
                                    foreach ($statuses as $status) {
                                        $selected = ($status === $row['status']) ? 'selected' : '';
                                        echo "<option value=\"$status\" $selected>$status</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="order-details">
                                <div class="product-info">
                                    <img src="<?php echo $row['product_image']; ?>" alt="Product" class="product-image">
                                    <div>
                                        <h4><?php echo htmlspecialchars($row['product_display_name']); ?></h4>
                                        <p>Size: <?php echo strtoupper($row['size'] ?? 'N/A'); ?></p>
                                        <p>Quantity: <?php echo $row['quantity']; ?></p>
                                        <p>Unit Price: ₱<?php echo number_format($row['price'], 2); ?></p>
                                        <p>Total: ₱<?php echo number_format($row['item_total'], 2); ?></p>
                                        <p>Payment: <?php echo htmlspecialchars($row['payment_method'] ?? 'N/A'); ?></p>
                                    </div>
                                </div>
                                <div class="customer-info">
                                    <h4>Customer Details</h4>
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($row['user_name']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($row['user_email']); ?></p>
                                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($row['contact_number']); ?></p>
                                    <p><strong>Address:</strong> <?php echo htmlspecialchars($row['delivery_address']); ?></p>
                                </div>
                            </div>
                            <div class="order-actions">
                                <button class="action-btn print-btn" onclick="printWaybill(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-print"></i> Print Waybill
                                </button>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p>No orders found</p>";
                }
                ?>
            </div>
        </main>
    </div>

    <script>
        function resetFilters() {
            document.querySelectorAll('.filter-input').forEach(input => {
                input.value = '';
            });
            document.getElementById('filterForm').submit();
        }

        function printWaybill(orderId) {
            // Open the waybill in a new window for printing
            const printWindow = window.open(`view_order.php?id=${orderId}&print=true`, '_blank');
            printWindow.onload = function() {
                printWindow.print();
            };
        }

        function updateOrderStatus(orderId, newStatus) {
            // Send AJAX request to update status
            fetch('update_order_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `order_id=${orderId}&status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the select element's class based on the new status
                    const select = document.querySelector(`select[data-order-id="${orderId}"]`);
                    if (select) {
                        // Remove all existing status classes
                        select.classList.remove('status-pending', 'status-processing', 'status-shipped', 'status-delivered', 'status-cancelled');
                        // Add the new status class
                        select.classList.add(`status-${newStatus.toLowerCase()}`);
                    }
                    
                    // Show success message
                    alert('Order status updated successfully');
                } else {
                    alert(data.message || 'Error updating order status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating order status');
            });
        }

        // Add this function to set initial status colors
        function initializeStatusColors() {
            document.querySelectorAll('.status-select').forEach(select => {
                const currentStatus = select.value.toLowerCase();
                select.classList.add(`status-${currentStatus}`);
            });
        }

        // Call this when the page loads
        document.addEventListener('DOMContentLoaded', initializeStatusColors);

        function viewOrderDetails(orderId) {
            // Implement view details functionality
            window.location.href = `view_order.php?id=${orderId}`;
        }

        function editOrder(orderId) {
            // Implement edit order functionality
            window.location.href = `edit_order.php?id=${orderId}`;
        }

        function deleteOrder(orderId) {
            if (confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
                // Send AJAX request to delete order
                fetch('delete_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `order_id=${orderId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove order card from UI
                        const orderCard = document.querySelector(`[data-order-id="${orderId}"]`).closest('.order-card');
                        orderCard.remove();
                        alert('Order deleted successfully');
                    } else {
                        alert('Failed to delete order');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the order');
                });
            }
        }

        // Close status menus when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.status-container')) {
                document.querySelectorAll('.status-menu').forEach(menu => {
                    menu.classList.remove('active');
                });
            }
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>