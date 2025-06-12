<?php
session_start();
if (!isset($_SESSION['admin@1'])) {
    header('Location: ../landing_page.php');
    exit;
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__FILE__) . '/../db_connection.php';
require_once 'payment_functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT Admin - Payments</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .payment-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-success {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }
        .payment-filters {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 15px;
            margin-bottom: 20px;
        }
        .payment-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .summary-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .summary-card .amount {
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }
        .payment-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-start;
        }

        .payment-actions button {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
            color: white;
        }

        .btn-view {
            background-color: #6c757d;
        }

        .btn-approve {
            background-color: #28a745;
        }

        .btn-reject {
            background-color: #dc3545;
        }

        .btn-view:hover { background-color: #5a6268; }
        .btn-approve:hover { background-color: #218838; }
        .btn-reject:hover { background-color: #c82333; }

        .payment-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
            text-align: center;
            display: inline-block;
            min-width: 100px;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        .page-link {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #333;
            text-decoration: none;
        }
        .page-link.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        #loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .payment-detail-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .payment-detail-row:last-child {
            border-bottom: none;
        }

        .payment-detail-label {
            flex: 0 0 150px;
            font-weight: 500;
            color: #666;
        }

        .payment-detail-value {
            flex: 1;
            color: #333;
        }

        .modal-content {
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .payment-method {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            margin-left: 8px;
        }
        
        .method-cod {
            background-color: #ffd700;
            color: #000;
        }
        
        .method-pickup {
            background-color: #87ceeb;
            color: #000;
        }
        
        .method-neocreds {
            background-color: #98fb98;
            color: #000;
        }
        
        .status-completed {
            background-color: #28a745;
            color: white;
        }
        
        small {
            display: block;
            margin-top: 4px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <?php 
// Admin authentication has already been checked at the top of the file
?>
    
    <div id="loading-overlay">
        <div class="spinner"></div>
    </div>
    
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
                <li onclick="window.location.href='all_product_page.php'">
                    <i class="fas fa-tshirt"></i>
                    <span>All Products</span>
                </li>
                <li onclick="window.location.href='add_new_product_page.php'">
                    <i class="fas fa-plus-square"></i>
                    <span>Add New Product</span>
                </li>
                <li class="active">
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
            <h1 class="page-title">Payments</h1>
            
            <!-- Payment Summary Cards -->
            <div class="payment-summary">
                <div class="summary-card">
                    <h3>Total Revenue</h3>
                    <div class="amount">₱<?php echo number_format(getTotalRevenue(), 2); ?></div>
                </div>
                <div class="summary-card">
                    <h3>Today's Earnings</h3>
                    <div class="amount">₱<?php echo number_format(getTodayEarnings(), 2); ?></div>
                </div>
                <div class="summary-card">
                    <h3>Pending Payments</h3>
                    <div class="amount">₱<?php echo number_format(getPendingPayments(), 2); ?></div>
                </div>
            </div>

            <div class="content-card">
                <!-- Payment Filters -->
                <div class="payment-filters">
                    <input type="text" placeholder="Search by Order ID or Customer" class="search-input" id="search-input">
                    <select class="filter-select" id="status-filter">
                        <option value="">Payment Status</option>
                        <option value="success">Success</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                    <input type="date" class="date-filter" id="date-filter">
                    <button class="btn-apply" id="apply-filters">Apply Filters</button>
                </div>

                <!-- Payments Table -->
                <table>
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="payments-table-body">
                        <?php
                        $payments = getFilteredPayments();
                        foreach ($payments as $payment) {
                            $statusClass = '';
                            switch($payment['status']) {
                                case 'success':
                                    $statusClass = 'status-success';
                                    break;
                                case 'pending':
                                    $statusClass = 'status-pending';
                                    break;
                                case 'failed':
                                    $statusClass = 'status-failed';
                                    break;
                            }
                            ?>
                            <tr>                        <td><?php echo htmlspecialchars($payment['transaction_id']); ?></td>
                        <td><?php echo htmlspecialchars($payment['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($payment['user_name']); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($payment['payment_date'])); ?></td>
                        <td>₱<?php echo number_format($payment['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($payment['payment_method'])); ?></td>
                        <td><span class="payment-status <?php echo $statusClass; ?>"><?php echo htmlspecialchars(ucfirst($payment['status'])); ?></span></td>
                        <td>
                            <div class="payment-actions">
                                <button onclick="viewPaymentDetails('<?php echo htmlspecialchars($payment['transaction_id']); ?>')" class="btn-view">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if ($payment['status'] === 'pending' && ($payment['payment_method'] === 'Cash On Delivery' || $payment['payment_method'] === 'Pickup')): ?>
                                    <button onclick="updatePaymentStatus('<?php echo htmlspecialchars($payment['transaction_id']); ?>', 'success')" class="btn-approve" title="Mark as Paid">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button onclick="updatePaymentStatus('<?php echo htmlspecialchars($payment['transaction_id']); ?>', 'failed')" class="btn-reject" title="Mark as Cancelled">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="pagination" id="pagination">
                    <?php
                    $total = getFilteredPaymentsCount();
                    $total_pages = ceil($total / 10);
                    
                    for ($i = 1; $i <= $total_pages; $i++) {
                        $active = $i == 1 ? 'active' : '';
                        echo "<a href='#' class='page-link $active' data-page='$i'>$i</a>";
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Show loading overlay
        function showLoading() {
            document.getElementById('loading-overlay').style.display = 'flex';
        }

        // Hide loading overlay
        function hideLoading() {
            document.getElementById('loading-overlay').style.display = 'none';
        }

        // Function to update the payments table
        function updatePaymentsTable(data) {
            const tbody = document.getElementById('payments-table-body');
            tbody.innerHTML = '';
            
            data.payments.forEach(payment => {
                let statusClass = '';
                switch(payment.status) {
                    case 'success':
                        statusClass = 'status-success';
                        break;
                    case 'pending':
                        statusClass = 'status-pending';
                        break;
                    case 'failed':
                        statusClass = 'status-failed';
                        break;
                }
                
                tbody.innerHTML += `
                    <tr>
                        <td>${payment.transaction_id}</td>
                        <td>${payment.order_id}</td>
                        <td>${payment.customer_name}</td>
                        <td>${payment.date}</td>
                        <td>₱${payment.amount}</td>
                        <td>${payment.payment_method}</td>
                        <td><span class="payment-status ${statusClass}">${payment.status}</span></td>
                        <td>
                            <span class="payment-details" onclick="viewPaymentDetails('${payment.transaction_id}')">
                                View Details
                            </span>
                        </td>
                    </tr>
                `;
            });
            
            // Update pagination
            const pagination = document.getElementById('pagination');
            pagination.innerHTML = '';
            
            for (let i = 1; i <= data.total_pages; i++) {
                const active = i === currentPage ? 'active' : '';
                pagination.innerHTML += `<a href="#" class="page-link ${active}" data-page="${i}">${i}</a>`;
            }
            
            // Add click events to new pagination links
            document.querySelectorAll('.page-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    currentPage = parseInt(this.dataset.page);
                    loadPayments();
                });
            });
        }

        // Function to load payments with filters
        let currentPage = 1;
        
        function loadPayments() {
            showLoading();
            
            const search = document.getElementById('search-input').value;
            const status = document.getElementById('status-filter').value;
            const date = document.getElementById('date-filter').value;
            
            const url = `filter_payments.php?page=${currentPage}&search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}&date=${encodeURIComponent(date)}`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    updatePaymentsTable(data);
                    hideLoading();
                })
                .catch(error => {
                    console.error('Error:', error);
                    hideLoading();
                });
        }

        // Event listener for filter button
        document.getElementById('apply-filters').addEventListener('click', function() {
            currentPage = 1;
            loadPayments();
        });

        // Event listeners for pagination
        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                currentPage = parseInt(this.dataset.page);
                loadPayments();
            });
        });

        // Function to view payment details
        function updatePaymentStatus(transactionId, newStatus) {
            if (!confirm(`Are you sure you want to mark this payment as ${newStatus}?`)) {
                return;
            }
            
            showLoading();
            
            fetch('update_payment_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `transaction_id=${encodeURIComponent(transactionId)}&status=${encodeURIComponent(newStatus)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadPayments();
                    alert('Payment status updated successfully');
                } else {
                    alert(data.message || 'Error updating payment status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating payment status');
            })
            .finally(() => {
                hideLoading();
            });
        }        function viewPaymentDetails(transactionId) {
            showLoading();
            
            fetch(`get_payment_details.php?transaction_id=${encodeURIComponent(transactionId)}`)
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('payment-details-content');
                    
                    const details = `
                        <div class="payment-detail-row">
                            <div class="payment-detail-label">Transaction ID</div>
                            <div class="payment-detail-value">${data.transaction_id}</div>
                        </div>
                        <div class="payment-detail-row">
                            <div class="payment-detail-label">Order ID</div>
                            <div class="payment-detail-value">${data.order_id}</div>
                        </div>
                        <div class="payment-detail-row">
                            <div class="payment-detail-label">Customer</div>
                            <div class="payment-detail-value">${data.customer_name}</div>
                        </div>
                        <div class="payment-detail-row">
                            <div class="payment-detail-label">Email</div>
                            <div class="payment-detail-value">${data.customer_email}</div>
                        </div>
                        <div class="payment-detail-row">
                            <div class="payment-detail-label">Amount</div>
                            <div class="payment-detail-value">₱${data.amount}</div>
                        </div>
                        <div class="payment-detail-row">
                            <div class="payment-detail-label">Payment Method</div>
                            <div class="payment-detail-value">${data.payment_method}</div>
                        </div>
                        <div class="payment-detail-row">
                            <div class="payment-detail-label">Date</div>
                            <div class="payment-detail-value">${data.payment_date}</div>
                        </div>
                        <div class="payment-detail-row">
                            <div class="payment-detail-label">Status</div>
                            <div class="payment-detail-value">
                                <span class="payment-status ${data.status_class}">${data.status}</span>
                                ${data.payment_method === 'NeoCreds' ? '<br><small>(Auto-completed)</small>' : ''}
                            </div>
                        </div>
                        ${data.delivery_status ? `
                        <div class="payment-detail-row">
                            <div class="payment-detail-label">Delivery Status</div>
                            <div class="payment-detail-value">${data.delivery_status}</div>
                        </div>
                        ` : ''}
                    `;
                    
                    content.innerHTML = details;
                    document.getElementById('payment-modal').style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading payment details');
                })
                .finally(() => {
                    hideLoading();
                });
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('payment-modal');
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    </script>

    <!-- Payment Details Modal -->
    <div id="payment-modal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
        <div class="modal-content" style="background: white; padding: 20px; border-radius: 8px; width: 90%; max-width: 500px; position: relative;">
            <span class="close-button" onclick="document.getElementById('payment-modal').style.display='none'" style="position: absolute; right: 15px; top: 10px; cursor: pointer; font-size: 20px;">&times;</span>
            <h2 style="margin-bottom: 20px;">Payment Details</h2>
            <div id="payment-details-content"></div>
            <div class="modal-footer" style="margin-top: 20px; text-align: right;">
                <button onclick="document.getElementById('payment-modal').style.display='none'" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
            </div>
        </div>
    </div>
</body>
</html>