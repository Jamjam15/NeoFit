<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get active tab from URL parameter, default to 'all'
$active_tab = isset($_GET['status']) ? $_GET['status'] : 'all';

// Get user's orders based on status filter
$sql = "SELECT o.*, p.photoFront
        FROM orders o
        LEFT JOIN products p ON o.product_id = p.id
        WHERE o.user_id = ?";

// Add status filter if not showing all
if ($active_tab !== 'all') {
    $sql .= " AND o.status = ?";
}
$sql .= " ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
if ($active_tab !== 'all') {
    $stmt->bind_param("is", $user_id, $active_tab);
} else {
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT - My Orders</title>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Alexandria', sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 24px;
            font-weight: bold;
        }

        .continue-shopping {
            color: #55a39b;
            text-decoration: none;
        }

        .orders-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .order-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .order-header {
            padding: 15px 20px;
            background-color: #f9f9f9;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-id {
            font-weight: bold;
            color: #000;
        }

        .order-date {
            color: #666;
            font-size: 14px;
        }

        .order-content {
            padding: 20px;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .item-size {
            color: #666;
            font-size: 14px;
        }

        .item-price {
            color: #55a39b;
            font-weight: bold;
        }

        .order-footer {
            padding: 15px 20px;
            background-color: #f9f9f9;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-total {
            font-weight: bold;
            font-size: 18px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-shipped {
            background-color: #d4edda;
            color: #155724;
        }

        .status-delivered {
            background-color: #55a39b;
            color: #fff;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .shipping-info {
            margin-top: 15px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }

        .shipping-info h4 {
            margin-bottom: 10px;
            color: #666;
        }

        .empty-orders {
            text-align: center;
            padding: 40px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .empty-orders h2 {
            margin-bottom: 10px;
        }

        .empty-orders p {
            color: #666;
            margin-bottom: 20px;
        }

        .shop-now-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #55a39b;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .shop-now-btn:hover {
            background-color: #478c85;
        }

        .order-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            overflow-x: auto;
            padding-bottom: 5px;
        }

        .tab {
            padding: 10px 20px;
            background-color: #f5f5f5;
            border-radius: 20px;
            color: #666;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .tab:hover {
            background-color: #e0e0e0;
            color: #333;
        }

        .tab.active {
            background-color: #55a39b;
            color: white;
        }

        @media (max-width: 768px) {
            .order-tabs {
                padding-bottom: 10px;
            }
            
            .tab {
                padding: 8px 16px;
                font-size: 13px;
            }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow: auto;
        }

        .modal-content {
            background-color: #fff;
            margin: 2% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 800px;
            position: relative;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-height: 95vh;
            overflow-y: auto;
        }

        .close {
            display: none; /* Hide the X button */
        }

        .waybill {
            width: 100%;
            max-width: 210mm;
            padding: 20px;
            margin: 0 auto;
            box-sizing: border-box;
            border: 2px solid #000;
            background: white;
            font-size: 14px;
        }

        .receipt-actions {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .print-btn, .close-btn {
            background: #55a39b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .close-btn {
            background: #666;
        }

        .print-btn:hover {
            background: #478c85;
        }

        .close-btn:hover {
            background: #555;
        }

        @media screen and (min-width: 768px) {
            .waybill {
                padding: 40px;
            }
        }

        .waybill-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
            gap: 10px;
        }

        @media screen and (min-width: 768px) {
            .waybill-header {
                flex-direction: row;
                justify-content: space-between;
                text-align: left;
                padding-bottom: 10px;
                gap: 0;
            }
        }

        .waybill-logo {
            font-size: 24px;
            font-weight: bold;
        }

        .waybill-title {
            font-size: 28px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 4px;
        }

        .waybill-tracking {
            font-size: 16px;
            font-weight: bold;
        }

        .waybill-sections {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        @media screen and (min-width: 768px) {
            .waybill-sections {
                grid-template-columns: 1fr 1fr;
            }
        }

        .waybill-section {
            border: 1px solid #000;
            padding: 15px;
        }

        .waybill-section h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }

        .waybill-section p {
            margin: 5px 0;
            font-size: 14px;
        }

        .package-details {
            border: 1px solid #000;
            padding: 15px;
            margin-bottom: 30px;
        }

        .package-details h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }

        .signatures {
            display: none;
        }

        .signature-box {
            display: none;
        }

        .signature-line {
            display: none;
        }

        .qr-code {
            text-align: center;
            margin: 20px 0 30px 0;
        }

        .qr-code img {
            max-width: 100px;
        }

        /* View Receipt Button Styles */
        .view-receipt-btn {
            background: #55a39b;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-left: 10px;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 2px 4px rgba(85, 163, 155, 0.2);
        }

        .view-receipt-btn i {
            font-size: 16px;
        }

        .view-receipt-btn:hover {
            background: #478c85;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(85, 163, 155, 0.3);
        }

        .view-receipt-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(85, 163, 155, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="page-title">My Orders</h1>
            <a href="landing_page.php" class="continue-shopping">Continue Shopping</a>
        </div>

        <div class="order-tabs">
            <a href="?status=all" class="tab <?php echo $active_tab === 'all' ? 'active' : ''; ?>">
                All Orders
            </a>
            <a href="?status=pending" class="tab <?php echo $active_tab === 'pending' ? 'active' : ''; ?>">
                Pending
            </a>
            <a href="?status=processing" class="tab <?php echo $active_tab === 'processing' ? 'active' : ''; ?>">
                Processing
            </a>
            <a href="?status=shipped" class="tab <?php echo $active_tab === 'shipped' ? 'active' : ''; ?>">
                Shipped
            </a>
            <a href="?status=delivered" class="tab <?php echo $active_tab === 'delivered' ? 'active' : ''; ?>">
                Delivered
            </a>
            <a href="?status=cancelled" class="tab <?php echo $active_tab === 'cancelled' ? 'active' : ''; ?>">
                Cancelled
            </a>
        </div>

        <div class="orders-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($order = $result->fetch_assoc()): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <span class="order-id">Order #<?php echo $order['id']; ?></span>
                            <span class="order-date"><?php echo date('F j, Y', strtotime($order['order_date'])); ?></span>
                        </div>

                        <div class="order-content">
                            <div class="order-item">
                                <img src="Admin Pages/<?php echo $order['photoFront']; ?>" alt="<?php echo $order['product_name']; ?>" class="item-image">
                                <div class="item-details">
                                    <div class="item-name"><?php echo $order['product_name']; ?></div>
                                    <div class="item-size">Size: <?php echo strtoupper($order['size']); ?></div>
                                    <div class="item-quantity">Quantity: <?php echo $order['quantity']; ?></div>
                                    <div class="item-price">₱<?php echo number_format($order['price'], 2); ?></div>
                                </div>
                            </div>

                            <div class="shipping-info">
                                <h4>Shipping Information</h4>
                                <p><?php echo htmlspecialchars($order['delivery_address']); ?></p>
                                <p>Contact: <?php echo htmlspecialchars($order['contact_number']); ?></p>
                                <p>Payment Method: <?php echo htmlspecialchars($order['payment_method']); ?></p>
                            </div>
                        </div>

                                                    <div class="order-footer">
                                <div class="order-total">Total: ₱<?php echo number_format($order['total'], 2); ?></div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo $order['status']; ?>
                                    </div>
                                    <button onclick="viewReceipt(<?php echo $order['id']; ?>)" class="view-receipt-btn">
                                        <i class="fas fa-file-invoice"></i> View Receipt
                                    </button>
                                </div>
                            </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-orders">
                    <h2>No orders yet</h2>
                    <p>Start shopping to see your orders here.</p>
                    <a href="landing_page.php" class="shop-now-btn">Shop Now</a>
                </div>
            <?php endif; ?>
        </div>

                    <!-- Receipt Modal -->
            <div id="receiptModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <div id="receiptContent">
                        <div class="waybill">
                            <!-- Receipt content will be loaded here -->
                        </div>
                        <div class="receipt-actions">
                            <button onclick="downloadReceipt()" class="print-btn">
                                <i class="fas fa-download"></i> Download Receipt
                            </button>
                            <button onclick="document.getElementById('receiptModal').style.display='none'" class="close-btn">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add html2canvas library -->
            <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Get modal elements
        const modal = document.getElementById('receiptModal');
        const closeBtn = document.getElementsByClassName('close')[0];

        // Close modal when clicking the X
        closeBtn.onclick = function() {
            modal.style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        function viewReceipt(orderId) {
            // Fetch order details
            fetch(`get_order_receipt.php?order_id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const order = data.order;
                        const tracking_number = String(order.id).padStart(8, '0');
                        
                        // Format the receipt HTML
                        const waybillHTML = `
                            <div class="waybill-header">
                                <div class="waybill-logo">NEOFIT</div>
                                <div class="waybill-title">ORDER RECEIPT</div>
                                <div class="waybill-tracking">Order #: ${tracking_number}</div>
                            </div>

                            <div class="waybill-sections">
                                <div class="waybill-section">
                                    <h3>From</h3>
                                    <p><strong>NEOFIT</strong></p>
                                    <p>123 Main Street</p>
                                    <p>Manila, Philippines</p>
                                    <p>Contact: (02) 123-4567</p>
                                </div>

                                <div class="waybill-section">
                                    <h3>Ship To</h3>
                                    <p><strong>${order.user_name}</strong></p>
                                    <p>${order.delivery_address}</p>
                                    <p>Contact: ${order.contact_number}</p>
                                    <p>Email: ${order.user_email}</p>
                                </div>
                            </div>

                            <div class="package-details">
                                <h3>Order Details</h3>
                                <p><strong>Product:</strong> ${order.product_name}</p>
                                <p><strong>Size:</strong> ${order.size.toUpperCase()}</p>
                                <p><strong>Quantity:</strong> ${order.quantity} pc(s)</p>
                                <p><strong>Unit Price:</strong> ₱${parseFloat(order.price).toFixed(2)}</p>
                                <p><strong>Total Amount:</strong> ₱${parseFloat(order.total).toFixed(2)}</p>
                                <p><strong>Payment Method:</strong> ${order.payment_method}</p>
                                ${order.payment_method.toLowerCase() === 'cod' ? 
                                    `<p><strong>Amount to Collect:</strong> ₱${parseFloat(order.total).toFixed(2)}</p>` : 
                                    ''}
                            </div>

                            <div class="qr-code">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=NEOFIT-ORDER-${tracking_number}" alt="Tracking QR Code">
                                <p>Order #${tracking_number}</p>
                            </div>`;
                        
                        // Update modal content
                        document.querySelector('.waybill').innerHTML = waybillHTML;
                        
                        // Show modal
                        modal.style.display = 'block';
                    } else {
                        alert('Error loading receipt');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading receipt');
                });
        }

        function downloadReceipt() {
            const waybill = document.querySelector('.waybill');
            
            // Set white background
            const originalBackground = waybill.style.background;
            waybill.style.background = 'white';
            
            html2canvas(waybill, {
                scale: 2, // Higher quality
                backgroundColor: '#ffffff',
                logging: false,
                useCORS: true
            }).then(canvas => {
                // Restore original background
                waybill.style.background = originalBackground;
                
                // Convert to PNG and download
                const image = canvas.toDataURL('image/png');
                const a = document.createElement('a');
                a.href = image;
                a.download = `NEOFIT-Receipt-${document.querySelector('.waybill-tracking').textContent.replace('Order #: ', '')}.png`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            });
        }
    </script>
</body>
</html>