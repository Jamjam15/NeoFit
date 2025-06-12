<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../db.php';

// Get order ID from URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$print_mode = isset($_GET['print']) && $_GET['print'] === 'true';

// Fetch order details with product information
$sql = "SELECT o.*, 
               p.product_name as product_display_name,
               p.product_price as price, 
               p.photoFront as product_image,
               oi.quantity,
               oi.size,
               COALESCE(o.payment_method, 'N/A') as payment_method,
               (oi.quantity * p.product_price) as item_total
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE o.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage_order_details_page.php");
    exit();
}

$order = $result->fetch_assoc();

// Format values
$tracking_number = str_pad($order['id'], 8, '0', STR_PAD_LEFT);
$order_date = date('F d, Y', strtotime($order['order_date']));
$total_amount = $order['item_total'];
$unit_price = $order['price'] ?? $order['unit_price'];

// Format size and payment method
$size = $order['size'];
if (empty($size) || $size === '0' || $size === 'N/A') {
    $size = 'Not Specified';
}

$payment_method = $order['payment_method'];
if (empty($payment_method) || $payment_method === '0') {
    $payment_method = 'Not Specified';
}

// If in print mode, show only the waybill
if ($print_mode):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT - Waybill #<?php echo $tracking_number; ?></title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        .waybill {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 0 auto;
            box-sizing: border-box;
            border: 2px solid #000;
        }
        .waybill-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
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
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
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
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 50px;
        }
        .signature-box {
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 5px;
            font-size: 12px;
        }
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        .qr-code img {
            max-width: 100px;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .waybill {
                border: 2px solid #000 !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="waybill">
        <div class="waybill-header">
            <div class="waybill-logo">NEOFIT</div>
            <div class="waybill-title">WAYBILL</div>
            <div class="waybill-tracking">Tracking #: <?php echo $tracking_number; ?></div>
        </div>

        <div class="waybill-sections">
            <div class="waybill-section">
                <h3>Sender</h3>
                <p><strong>NEOFIT</strong></p>
                <p>123 Main Street</p>
                <p>Manila, Philippines</p>
                <p>Contact: (02) 123-4567</p>
            </div>

            <div class="waybill-section">
                <h3>Recipient</h3>
                <p><strong><?php echo htmlspecialchars($order['user_name']); ?></strong></p>
                <p><?php 
                    $address_parts = array(
                        $order['house_details'],
                        $order['barangay'],
                        $order['city'],
                        $order['region']
                    );
                    $full_address = implode(', ', array_filter($address_parts));
                    echo htmlspecialchars($full_address);
                ?></p>
                <p>Contact: <?php echo htmlspecialchars($order['contact_number']); ?></p>
                <p>Email: <?php echo htmlspecialchars($order['user_email']); ?></p>
            </div>
        </div>

        <div class="package-details">
            <h3>Package Details</h3>
            <p><strong>Product:</strong> <?php echo htmlspecialchars($order['product_display_name']); ?></p>
            <p><strong>Size:</strong> <?php echo strtoupper($size); ?></p>
            <p><strong>Quantity:</strong> <?php echo (int)$order['quantity']; ?> pc(s)</p>
            <p><strong>Unit Price:</strong> ₱<?php echo number_format($unit_price, 2); ?></p>
            <p><strong>Total Amount:</strong> ₱<?php echo number_format($total_amount, 2); ?></p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($payment_method); ?></p>
            <?php if (strtolower($payment_method) === 'cod'): ?>
            <p><strong>Amount to Collect:</strong> ₱<?php echo number_format($total_amount, 2); ?></p>
            <?php endif; ?>
        </div>

        <div class="qr-code">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?php echo urlencode('NEOFIT-ORDER-' . $tracking_number); ?>" alt="Tracking QR Code">
            <p>Order #<?php echo $tracking_number; ?></p>
        </div>

        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">
                    <p>Received in Good Condition:</p>
                    <p>_________________________</p>
                    <p>Recipient's Signature</p>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    <p>Delivered by:</p>
                    <p>_________________________</p>
                    <p>Courier's Signature</p>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    <p>Date Received:</p>
                    <p>_________________________</p>
                    <p>DD/MM/YYYY</p>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Auto-print when loaded
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
<?php 
exit();
endif;

// Format the unit price
$unit_price = $order['price'] ?? $order['unit_price'];
$total_amount = $order['total'];

// Format size and payment method for display
$size = $order['size'];
if (empty($size) || $size === '0' || $size === 'N/A') {
    $size = 'Not Specified';
}

$payment_method = $order['payment_method'];
if (empty($payment_method) || $payment_method === '0') {
    $payment_method = 'Not Specified';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT Admin - Order Details</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .order-details-container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .order-id {
            font-size: 1.5em;
            font-weight: bold;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 1em;
            font-weight: 500;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .detail-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }

        .section-title {
            font-size: 1.2em;
            font-weight: 500;
            margin-bottom: 15px;
            color: #333;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .detail-label {
            color: #666;
        }

        .detail-value {
            font-weight: 500;
            text-align: right;
        }

        .product-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .product-details {
            display: flex;
            gap: 20px;
            align-items: start;
        }

        .product-info {
            flex-grow: 1;
        }

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

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .action-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .edit-btn {
            background: #7ab55c;
            color: white;
        }

        .print-btn {
            background: #6c757d;
            color: white;
        }

        @media (max-width: 768px) {
            .details-grid {
                grid-template-columns: 1fr;
            }

            .product-details {
                flex-direction: column;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Waybill Print Styles */
        @media print {
            header, .sidebar, .back-button, .action-buttons, .order-details-container {
                display: none !important;
            }

            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
                font-family: Arial, sans-serif !important;
            }

            .container {
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .main-content {
                margin: 0 !important;
                padding: 20px !important;
                width: 100% !important;
            }

            .waybill {
                display: block !important;
                padding: 20px;
                border: 2px solid #000;
                margin: 20px;
                page-break-inside: avoid;
            }

            .waybill-header {
                display: flex !important;
                justify-content: space-between;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
                margin-bottom: 20px;
            }

            .waybill-logo {
                font-size: 24px;
                font-weight: bold;
            }

            .waybill-title {
                font-size: 20px;
                font-weight: bold;
                text-align: center;
            }

            .waybill-tracking {
                font-size: 16px;
            }

            .waybill-sections {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 20px !important;
                margin-bottom: 20px;
            }

            .waybill-section {
                border: 1px solid #000;
                padding: 10px;
            }

            .waybill-section h3 {
                margin: 0 0 10px 0;
                font-size: 14px;
                text-transform: uppercase;
                border-bottom: 1px solid #000;
                padding-bottom: 5px;
            }

            .waybill-section p {
                margin: 5px 0;
                font-size: 12px;
            }

            .waybill-product {
                border: 1px solid #000;
                padding: 10px;
                margin-bottom: 20px;
            }

            .waybill-footer {
                display: grid !important;
                grid-template-columns: 1fr 1fr 1fr !important;
                gap: 20px !important;
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid #000;
            }

            .signature-box {
                border-top: 1px solid #000;
                margin-top: 50px;
                padding-top: 5px;
                text-align: center;
                font-size: 12px;
            }

            .barcode {
                text-align: center;
                margin: 20px 0;
            }

            .barcode img {
                max-width: 200px;
            }
        }

        /* Hide waybill in normal view */
        .waybill {
            display: none;
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
                <li onclick="window.location.href='settings.php'">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </li>
            </ul>
        </aside>
        
        <main class="main-content">
            <!-- Add waybill format -->
            <div class="waybill">
                <div class="waybill-header">
                    <div class="waybill-logo">NEOFIT</div>
                    <div class="waybill-title">WAYBILL</div>
                    <div class="waybill-tracking">
                        Tracking #: <?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?>
                    </div>
                </div>

                <div class="waybill-sections">
                    <div class="waybill-section">
                        <h3>Sender</h3>
                        <p>NEOFIT</p>
                        <p>123 Main Street</p>
                        <p>Manila, Philippines</p>
                        <p>Contact: (02) 123-4567</p>
                    </div>

                    <div class="waybill-section">
                        <h3>Recipient</h3>
                        <p><strong><?php echo htmlspecialchars($order['user_name']); ?></strong></p>
                        <p><?php 
                            $address_parts = array(
                                $order['house_details'],
                                $order['barangay'],
                                $order['city'],
                                $order['region']
                            );
                            $full_address = implode(', ', array_filter($address_parts));
                            echo htmlspecialchars($full_address);
                        ?></p>
                        <p>Contact: <?php echo htmlspecialchars($order['contact_number']); ?></p>
                        <p>Email: <?php echo htmlspecialchars($order['user_email']); ?></p>
                    </div>
                </div>

                <div class="waybill-product">
                    <h3>Package Details</h3>
                    <p><strong>Product:</strong> <?php echo htmlspecialchars($order['product_display_name']); ?></p>
                    <p><strong>Size:</strong> <?php echo strtoupper($size); ?></p>
                    <p><strong>Quantity:</strong> <?php echo (int)$order['quantity']; ?> pc(s)</p>
                    <p><strong>Unit Price:</strong> ₱<?php echo number_format($unit_price, 2); ?></p>
                    <p><strong>Total Amount:</strong> ₱<?php echo number_format($total_amount, 2); ?></p>
                    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($payment_method); ?></p>
                    <?php if (strtolower($payment_method) === 'cod'): ?>
                    <p><strong>Amount to Collect:</strong> ₱<?php echo number_format($total_amount, 2); ?></p>
                    <?php endif; ?>
                </div>

                <div class="barcode">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" alt="Tracking QR Code">
                    <p>Order #<?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?></p>
                </div>

                <div class="waybill-footer">
                    <div class="signature-box">
                        <p>Received in Good Condition:</p>
                        <p>_________________________</p>
                        <p>Recipient's Signature</p>
                    </div>
                    <div class="signature-box">
                        <p>Delivered by:</p>
                        <p>_________________________</p>
                        <p>Courier's Signature</p>
                    </div>
                    <div class="signature-box">
                        <p>Date Received:</p>
                        <p>_________________________</p>
                        <p>DD/MM/YYYY</p>
                    </div>
                </div>
            </div>

            <a href="manage_order_details_page.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>

            <div class="order-details-container">
                <div class="order-header">
                    <div class="order-id">Order #<?php echo $order['id']; ?></div>
                    <div class="status-badge status-<?php echo strtolower($order['status']); ?>">
                        <?php echo htmlspecialchars($order['status']); ?>
                    </div>
                </div>

                <div class="details-grid">
                    <div class="detail-section">
                        <h3 class="section-title">Product Information</h3>
                        <div class="product-details">
                            <?php if ($order['product_image']): ?>
                            <img src="<?php echo htmlspecialchars($order['product_image']); ?>" 
                                 alt="Product" class="product-image">
                            <?php endif; ?>
                            <div class="product-info">
                                <div class="detail-row">
                                    <span class="detail-label">Product Name</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($order['product_display_name']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Size</span>
                                    <span class="detail-value"><?php echo strtoupper($size); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Quantity</span>
                                    <span class="detail-value"><?php echo $order['quantity']; ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Unit Price</span>
                                    <span class="detail-value">₱<?php echo number_format($unit_price, 2); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Total Amount</span>
                                    <span class="detail-value">₱<?php echo number_format($total_amount, 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3 class="section-title">Customer Information</h3>
                        <div class="detail-row">
                            <span class="detail-label">Name</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['user_name']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['user_email']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Contact Number</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['contact_number']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Delivery Address</span>
                            <span class="detail-value"><?php 
                                $address_parts = array(
                                    $order['house_details'],
                                    $order['barangay'],
                                    $order['city'],
                                    $order['region']
                                );
                                $full_address = implode(', ', array_filter($address_parts));
                                echo htmlspecialchars($full_address);
                            ?></span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3 class="section-title">Order Information</h3>
                        <div class="detail-row">
                            <span class="detail-label">Order Date</span>
                            <span class="detail-value"><?php echo date('F d, Y', strtotime($order['order_date'])); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Payment Method</span>
                            <span class="detail-value"><?php echo htmlspecialchars($payment_method); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['status']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <button class="action-btn print-btn" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Order
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Enhance print functionality
        document.querySelector('.print-btn').addEventListener('click', function(e) {
            e.preventDefault();
            window.print();
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?> 