<?php

include '../db.php';

// Fetch low stock threshold from admin_settings
$threshold = 5; // default value
$result_threshold = $conn->query("SELECT setting_value FROM admin_settings WHERE setting_key = 'low_stock_threshold'");
if ($result_threshold && $row_threshold = $result_threshold->fetch_assoc()) {
    $threshold = (int)$row_threshold['setting_value'];
}

// Get filters from URL parameters
$category = isset($_GET['category']) ? $_GET['category'] : 'All';
$status = isset($_GET['status']) ? $_GET['status'] : 'All';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build the base SQL query
$sql = "SELECT p.*, 
        (p.quantity_small + p.quantity_medium + p.quantity_large) as total_stock
        FROM products p";

// Build WHERE clause
$where_conditions = [];
$params = [];
$param_types = '';

if ($category !== 'All') {
    $where_conditions[] = "p.product_category = ?";
    $params[] = $category;
    $param_types .= 's';
}

if ($status !== 'All') {
    $where_conditions[] = "p.product_status = ?";
    $params[] = $status;
    $param_types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "p.product_name LIKE ?";
    $params[] = "%$search%";
    $param_types .= 's';
}

if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}

// Add ORDER BY clause based on sort parameter
switch ($sort) {
    case 'name_desc':
        $sql .= " ORDER BY p.product_name DESC";
        break;
    case 'price_asc':
        $sql .= " ORDER BY p.product_price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.product_price DESC";
        break;
    case 'stock_asc':
        $sql .= " ORDER BY total_stock ASC";
        break;
    case 'stock_desc':
        $sql .= " ORDER BY total_stock DESC";
        break;
    default: // name_asc
        $sql .= " ORDER BY p.product_name ASC";
}

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get total product count
$count_sql = "SELECT COUNT(*) as total FROM products";
$count_result = $conn->query($count_sql);
$total_count = $count_result->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT Admin - All Products</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn-add-product {
            background-color: #4d8d8b;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s ease;
        }

        .btn-add-product:hover {
            background-color: #3c7c7a;
        }

        .filters-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-label {
            color: #666;
            font-weight: 500;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: 500;
        }

        .status-live {
            background-color: #d4edda;
            color: #155724;
        }

        .status-unpublished {
            background-color: #f8d7da;
            color: #721c24;
        }

        .stock-warning {
            color: #856404;
            background-color: #ffe082;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 0.95em;
            font-weight: bold;
            display: inline-block;
            white-space: nowrap;
            margin-top: 2px;
        }

        .product-image {
            width: 40px;
            height: 40px;
            border-radius: 4px;
            object-fit: cover;
        }

        .product-name-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .action-cell {
            display: flex;
            gap: 5px;
        }

        .btn-action {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.3s ease;
        }

        .btn-edit {
            background-color: #ffc107;
            color: #000;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .btn-view {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-toggle {
            background-color: #6c757d;
            color: white;
        }

        .btn-action:hover {
            opacity: 0.9;
        }

        .search-filter {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .search-input {
            width: 300px;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
        }

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-top: 1px solid #eee;
            margin-top: 20px;
        }

        .bulk-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .price-column {
            text-align: right;
            font-family: 'Arial', sans-serif;
            white-space: nowrap;
        }

        .currency-symbol {
            font-family: 'Arial Unicode MS', 'Arial', sans-serif;
            margin-right: 1px;
        }

        .low-stock-row {
            background-color: #fff3cd !important;
        }

        .stock-cell {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            height: 100%;
        }
        .stock-number {
            display: block;
        }

        table tr, table td, table th {
            vertical-align: middle;
        }
        table tr {
            height: 60px;
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
                <li onclick="window.location.href='customer_orders_page.php'">
                    <i class="fas fa-users"></i>
                    <span>Customer Orders</span>
                </li>
                <li class="active">
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
            <div class="action-buttons">
                <button class="btn-add-product" onclick="window.location.href='add_new_product_page.php'">
                    <i class="fas fa-plus"></i>
                    Add New Product
                </button>
            </div>

            <div class="search-filter">
                <div class="filters-row">
                    <div class="filter-group">
                        <label class="filter-label">Search:</label>
                        <input type="text" class="search-input" id="searchInput" 
                               placeholder="Search products..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Category:</label>
                        <select class="filter-select" id="categoryFilter">
                            <option value="All" <?php echo $category === 'All' ? 'selected' : ''; ?>>All Categories</option>
                            <option value="Men" <?php echo $category === 'Men' ? 'selected' : ''; ?>>Men</option>
                            <option value="Women" <?php echo $category === 'Women' ? 'selected' : ''; ?>>Women</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Status:</label>
                        <select class="filter-select" id="statusFilter">
                            <option value="All" <?php echo $status === 'All' ? 'selected' : ''; ?>>All Status</option>
                            <option value="live" <?php echo $status === 'live' ? 'selected' : ''; ?>>Live</option>
                            <option value="unpublished" <?php echo $status === 'unpublished' ? 'selected' : ''; ?>>Unpublished</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Sort By:</label>
                        <select class="filter-select" id="sortFilter">
                            <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                            <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                            <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price (Low-High)</option>
                            <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price (High-Low)</option>
                            <option value="stock_asc" <?php echo $sort === 'stock_asc' ? 'selected' : ''; ?>>Stock (Low-High)</option>
                            <option value="stock_desc" <?php echo $sort === 'stock_desc' ? 'selected' : ''; ?>>Stock (High-Low)</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="content-card">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Small</th>
                            <th>Medium</th>
                            <th>Large</th>
                            <th>Total Stock</th>
                            <th class="price-column">Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productList">
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $total_stock = $row['total_stock'];
                            $status_class = strtolower($row['product_status']) === 'live' ? 'status-live' : 'status-unpublished';
                            ?>
                            <tr<?php if ($total_stock <= $threshold) echo ' class="low-stock-row"'; ?>>
                                <td><input type="checkbox" class="product-select" value="<?php echo $row['id']; ?>"></td>
                                <td class="product-name-cell">
                                    <img src="<?php 
                                        $image_path = $row['photoFront'];
                                        // If the path starts with ../uploads/, keep it as is
                                        if (strpos($image_path, '../uploads/') === 0) {
                                            echo $image_path;
                                        } else {
                                            // Otherwise, assume it's in the uploads directory
                                            echo '../uploads/' . basename($image_path);
                                        }
                                    ?>" 
                                         alt="<?php echo htmlspecialchars($row['product_name']); ?>" 
                                         class="product-image"
                                         onerror="this.src='../uploads/placeholder.jpg'">
                                    <span><?php echo htmlspecialchars($row['product_name']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($row['product_category']); ?></td>
                                <td><?php echo $row['quantity_small']; ?></td>
                                <td><?php echo $row['quantity_medium']; ?></td>
                                <td><?php echo $row['quantity_large']; ?></td>
                                <td style="text-align: center;">
                                    <?php echo $total_stock; ?>
                                    <?php if ($total_stock <= $threshold): ?>
                                        <br><span class="stock-warning">Low Stock</span>
                                    <?php else: ?>
                                        <br><span class="stock-warning" style="visibility:hidden;">Low Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td class="price-column">
                                    <span class="currency-symbol">₱</span><?php echo number_format($row['product_price'], 2); ?>
                                </td>
                                <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $row['product_status']; ?></span></td>
                                <td class="action-cell">
                                    <button class="btn-action btn-view" onclick="viewProduct(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-action btn-toggle" onclick="toggleStatus(<?php echo $row['id']; ?>, '<?php echo $row['product_status']; ?>')" title="Toggle Status">
                                        <i class="fas fa-toggle-on"></i>
                                    </button>
                                    <button class="btn-action btn-edit" onclick="window.location.href='edit_product.php?id=<?php echo $row['id']; ?>'">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-delete" onclick="deleteProduct(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='10' style='text-align: center;'>No products found</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>

                <div class="table-footer">
                    <div class="bulk-actions">
                        <select class="filter-select" id="bulkActionSelect">
                            <option value="">Bulk Actions</option>
                            <option value="delete">Delete Selected</option>
                            <option value="publish">Set as Live</option>
                            <option value="unpublish">Set as Unpublished</option>
                        </select>
                        <button class="btn-apply" onclick="applyBulkAction()">Apply</button>
                    </div>
                    <div class="product-count" id="productCount">
                        <?php echo $result->num_rows . " product" . ($result->num_rows != 1 ? "s" : ""); ?>
                        <?php if ($result->num_rows !== $total_count): ?>
                            of <?php echo $total_count; ?> total
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Toggle Product Status Function
        function toggleStatus(id, currentStatus) {
            const newStatus = currentStatus.toLowerCase() === 'live' ? 'unpublished' : 'live';
            
            fetch('toggle_product_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error toggling product status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error toggling product status');
            });
        }

        // Select All Functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.product-select').forEach(checkbox => {
                checkbox.checked = isChecked;
            });
        });

        // Real-time Search with Debouncing
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 500);
        });

        // Filter Change Handlers
        ['categoryFilter', 'statusFilter', 'sortFilter'].forEach(filterId => {
            document.getElementById(filterId).addEventListener('change', function() {
                applyFilters();
            });
        });

        function applyFilters() {
            const search = document.getElementById('searchInput').value;
            const category = document.getElementById('categoryFilter').value;
            const status = document.getElementById('statusFilter').value;
            const sort = document.getElementById('sortFilter').value;

            // Build URL with filters
            const params = new URLSearchParams({
                search: search,
                category: category,
                status: status,
                sort: sort
            });

            // Reload page with new filters
            window.location.href = `all_product_page.php?${params.toString()}`;
        }

        function viewProduct(id) {
            window.location.href = `view_product.php?id=${id}`;
        }

        function deleteProduct(id) {
            if (confirm('Are you sure you want to delete this product?')) {
                fetch(`delete_product.php?id=${id}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Error deleting product');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting product');
                });
            }
        }

        function applyBulkAction() {
            const action = document.getElementById('bulkActionSelect').value;
            if (!action) {
                alert('Please select an action');
                return;
            }

            const selectedProducts = Array.from(document.querySelectorAll('.product-select:checked')).map(cb => cb.value);
            if (selectedProducts.length === 0) {
                alert('Please select at least one product');
                return;
            }

            const actionText = {
                'delete': 'delete',
                'publish': 'publish',
                'unpublish': 'unpublish'
            }[action];

            if (confirm(`Are you sure you want to ${actionText} the selected products?`)) {
                // Show loading state
                const bulkActionBtn = document.querySelector('.btn-apply');
                const originalText = bulkActionBtn.textContent;
                bulkActionBtn.textContent = 'Processing...';
                bulkActionBtn.disabled = true;

                // Log the request data
                console.log('Sending request:', {
                    action: action,
                    products: selectedProducts
                });

                fetch('bulk_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: action,
                        products: selectedProducts
                    })
                })
                .then(response => {
                    // Log the raw response
                    console.log('Raw response:', response);
                    return response.text();
                })
                .then(text => {
                    // Log the response text
                    console.log('Response text:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        throw new Error('Invalid JSON response: ' + text);
                    }
                })
                .then(data => {
                    console.log('Parsed response:', data);
                    if (data.success) {
                        alert(data.message || 'Operation completed successfully');
                        location.reload();
                    } else {
                        alert(data.message || 'Error performing bulk action');
                        // Reset button state
                        bulkActionBtn.textContent = originalText;
                        bulkActionBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error performing bulk action: ' + error.message);
                    // Reset button state
                    bulkActionBtn.textContent = originalText;
                    bulkActionBtn.disabled = false;
                });
            }
        }
    </script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
