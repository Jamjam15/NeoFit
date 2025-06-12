<?php
session_start();
include 'db.php';

$user_name = $_SESSION['user_name'] ?? 'Guest';
$user_email = $_SESSION['email'];


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user's address, contact information, and neocreds balance
$stmt = $conn->prepare("SELECT house_details, barangay, city, region, contact, neocreds FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($house_details, $barangay, $city, $region, $contact, $neocreds);
$stmt->fetch();
$stmt->close();

// Concatenate address parts
$address = '';
if (!empty($house_details) && !empty($barangay) && !empty($city) && !empty($region)) {
    $address = $house_details . ', ' . $barangay . ', ' . $city . ', ' . $region;
}

// Check if we're checking out a specific cart item or the entire cart
$cart_id = $_GET['cart_id'] ?? null;

if ($cart_id) {
    // Single item checkout
    $sql = "SELECT c.*, p.product_name, p.product_price, p.photoFront 
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.id = ? AND c.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cart_id, $user_id);
} else {
    // Full cart checkout
    $sql = "SELECT c.*, p.product_name, p.product_price, p.photoFront 
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
            ORDER BY c.added_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

$total_amount = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT - Checkout</title>
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
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .header {
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            grid-column: 1 / -1;
        }

        .checkout-title {
            font-size: 24px;
            font-weight: bold;
        }

        .checkout-section {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #000;
        }

        .address-info, .contact-info {
            margin-bottom: 20px;
        }

        .info-label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #666;
        }

        .info-value {
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }

        .error-message {
            color: #ff4d4d;
            margin-top: 5px;
        }

        .order-items {
            margin-bottom: 20px;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
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

        .payment-method {
            margin-bottom: 20px;
        }

        .payment-select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 10px;
        }

        .order-summary {
            position: sticky;
            top: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
        }

        .summary-total {
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .place-order-btn {
            width: 100%;
            padding: 15px;
            background-color: #000;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
        }

        .place-order-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .place-order-btn:not(:disabled):hover {
            opacity: 0.9;
        }

        .edit-link {
            color: #55a39b;
            text-decoration: none;
            font-size: 14px;
            margin-left: 10px;
        }

        .edit-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="checkout-title">Checkout</h1>
        </div>

        <div class="main-content">
            <div class="checkout-section">
                <h2 class="section-title">Shipping Information</h2>
                <div class="address-info">
                    <div class="info-label">
                        Delivery Address
                        <a href="user-settings.php" class="edit-link">Edit</a>
                    </div>
                    <div class="info-value">
                        <?php if ($address): ?>
                            <?php echo htmlspecialchars($address); ?>
                        <?php else: ?>
                            <span class="error-message">Please add your delivery address in your profile settings.</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="contact-info">
                    <div class="info-label">
                        Contact Number
                        <a href="user-settings.php" class="edit-link">Edit</a>
                    </div>
                    <div class="info-value">
                        <?php if ($contact): ?>
                            <?php echo htmlspecialchars($contact); ?>
                        <?php else: ?>
                            <span class="error-message">Please add your contact number in your profile settings.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="checkout-section">
                <h2 class="section-title">Order Items</h2>
                <div class="order-items">
                    <?php 
                    while ($item = $result->fetch_assoc()):
                        $subtotal = $item['quantity'] * $item['product_price'];
                        $total_amount += $subtotal;
                    ?>
                        <div class="order-item">
                            <img src="Admin Pages/<?php echo $item['photoFront']; ?>" alt="<?php echo $item['product_name']; ?>" class="item-image">
                            <div class="item-details">
                                <div class="item-name"><?php echo $item['product_name']; ?></div>
                                <div class="item-size">Size: <?php echo strtoupper($item['size']); ?></div>
                                <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                                <div class="item-price">₱<?php echo number_format($item['product_price'], 2); ?></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="checkout-section">
                <h2 class="section-title">Payment Method</h2>
                <div class="payment-method">
                    <select name="payment_method" id="payment-method" class="payment-select" onchange="handlePaymentMethodChange()">
                        <option value="">Select Payment Method</option>
                        <option value="NeoCreds">NeoCreds <span id="neocreds-balance"></span></option>

                        <option value="Cash On Delivery">Cash On Delivery</option>
                        <option value="Pick Up">Pick Up</option>
                    </select>
                    <div id="neocreds-balance-display" style="display: none; margin-top: 10px; color: #666;">
                        Your NeoCreds Balance: <span id="balance-amount">₱0.00</span>
                    </div>
                </div>
                <div id="neocreds-info" style="margin-top: 10px; display: none;">
                    <div class="info-value" style="background-color: #f9f9f9; padding: 10px; border-radius: 4px;">
                        <div style="margin-bottom: 5px;">Current Balance: ₱<span id="current-balance"><?php echo number_format($neocreds, 2); ?></span></div>
                        <div>Order Total: ₱<span id="order-total"><?php echo number_format($total_amount, 2); ?></span></div>
                        <div style="margin-top: 5px; font-weight: bold;" id="balance-status"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="order-summary checkout-section">
            <h2 class="section-title">Order Summary</h2>
            <div class="summary-row">
                <span>Subtotal</span>
                <span>₱<?php echo number_format($total_amount, 2); ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping Fee</span>
                <span>Free</span>
            </div>
            <div class="summary-row summary-total">
                <span>Total</span>
                <span>₱<?php echo number_format($total_amount, 2); ?></span>
            </div>
            <button id="place-order-btn" class="place-order-btn" <?php echo (!$address || !$contact) ? 'disabled' : ''; ?>>
                Place Order
            </button>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script>
        let userNeocredsBalance = 0;

        // Fetch NeoCreds balance when page loads
        window.addEventListener('DOMContentLoaded', function() {
            fetchNeocredsBalance();
        });

        // Fetch user's NeoCreds balance
        function fetchNeocredsBalance() {
            fetch('process_neocreds.php?action=balance')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        userNeocredsBalance = parseFloat(data.balance);
                        document.getElementById('balance-amount').textContent = '₱' + userNeocredsBalance.toFixed(2);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Handle payment method change
        function handlePaymentMethodChange() {
            const paymentMethod = document.getElementById('payment-method').value;
            const balanceDisplay = document.getElementById('neocreds-balance-display');
            balanceDisplay.style.display = paymentMethod === 'NeoCreds' ? 'block' : 'none';
        }

        document.getElementById('place-order-btn').addEventListener('click', function(e) {

            if (!this.disabled) {
                const paymentMethod = document.getElementById('payment-method').value;
                if (!paymentMethod) {
                    alert('Please select a payment method');
                    return;
                }

                // Check NeoCreds balance if selected as payment method
                if (paymentMethod === 'NeoCreds') {
                    const totalAmount = <?php echo $total_amount; ?>;
                    if (userNeocredsBalance < totalAmount) {
                        alert('Insufficient NeoCreds balance. Your balance: ₱' + userNeocredsBalance.toFixed(2) + '\nRequired: ₱' + totalAmount.toFixed(2));
                        return;
                    }
                }

                const formData = new FormData();
                formData.append('payment_method', paymentMethod);
                formData.append('delivery_address', <?php echo json_encode($address); ?>);
                formData.append('contact_number', <?php echo json_encode($contact); ?>);
                formData.append('user_name', <?php echo json_encode($user_name); ?>);
                formData.append('user_email', <?php echo json_encode($user_email); ?>);
                <?php if ($cart_id): ?>
                formData.append('cart_id', <?php echo $cart_id; ?>);
                <?php endif; ?>

                fetch('process_order.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Order placed successfully!');
                        window.location.href = 'orders.php';
                    } else {
                        alert(data.message || 'Error processing order');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while processing your order');
                });
            }
        });

        // Trigger change event to show initial NeoCreds info if selected
        document.getElementById('payment-method').dispatchEvent(new Event('change'));
    </script>
</body>
</html>