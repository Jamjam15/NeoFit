<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user's address and contact information
$stmt = $conn->prepare("SELECT house_details, barangay, city, region, contact FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($house_details, $barangay, $city, $region, $contact);
$stmt->fetch();
$stmt->close();

// Concatenate address parts
$address = '';
if (!empty($house_details) && !empty($barangay) && !empty($city) && !empty($region)) {
    $address = $house_details . ', ' . $barangay . ', ' . $city . ', ' . $region;
}

// Form input values
$product_id = $_POST['product_id'] ?? '';
$product_name = $_POST['product_name'] ?? 'Unknown Product';
$product_price = isset($_POST['price']) ? $_POST['price'] : '0.00';
$selected_size = $_POST['size'] ?? 'Not Selected';
$selected_quantity = $_POST['quantity'] ?? '1';
$total_price = number_format((float)$product_price * (int)$selected_quantity, 2);

if ($product_id) {
    echo "<h2>Product ID: " . htmlspecialchars($product_id) . "</h2>";
} else {
    echo "<h2>No product selected.</h2>";
}

// Check if address and contact are empty
$has_address = !empty($address);
$has_contact = !empty($contact);
$can_submit = $has_address && $has_contact;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout Page</title>
    <style>
        .error-message {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h2>Checkout Form</h2>

    <?php if (!$can_submit): ?>
        <div class="error-message">
            <?php if (!$has_address): ?>
                <p>Please add your delivery address in your profile settings before proceeding.</p>
            <?php endif; ?>
            <?php if (!$has_contact): ?>
                <p>Please add your contact number in your profile settings before proceeding.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form action="add_order.php" method="POST" id="orderForm">
        <input type="hidden" name="product_id" value="<?= htmlspecialchars($_GET['id'] ?? '') ?>">
        <input type="hidden" name="delivery_address" value="<?= htmlspecialchars($address) ?>">
        <input type="hidden" name="contact_number" value="<?= htmlspecialchars($contact) ?>">

        <label for="payment-method">Choose Payment Method:</label><br>
        <select name="payment_method" id="payment-method" required>
            <option value="NeoCreds">NeoCreds</option>
            <option value="Cash On Delivery">Cash On Delivery</option>
            <option value="Pick Up">Pick Up</option>
        </select>

        <h3>Delivery Address: <?= htmlspecialchars($address ?: 'Not Set'); ?></h3>
        <h3>Contact Number: <?= htmlspecialchars($contact ?: 'Not Set'); ?></h3>

        <h3>Order Summary</h3>
        <p><strong>Product:</strong> <?= htmlspecialchars($product_name); ?></p>
        <p><strong>Price:</strong> ₱<?= htmlspecialchars($product_price); ?></p>
        <p><strong>Size:</strong> <?= htmlspecialchars($selected_size); ?></p>
        <p><strong>Quantity:</strong> <?= htmlspecialchars($selected_quantity); ?></p>
        <p><strong>Total:</strong> ₱<?= $total_price; ?></p>

        <!-- Hidden fields -->
        <input type="hidden" name="product_name" value="<?= htmlspecialchars($product_name); ?>">
        <input type="hidden" name="product_price" value="<?= htmlspecialchars($product_price); ?>">
        <input type="hidden" name="size" value="<?= htmlspecialchars($selected_size); ?>">
        <input type="hidden" name="quantity" value="<?= htmlspecialchars($selected_quantity); ?>">
        <input type="hidden" name="total" value="<?= $total_price; ?>">

        <br><br>
        <button type="submit" <?= $can_submit ? '' : 'disabled' ?>>Submit Order</button>
    </form>

    <?php if (!$can_submit): ?>
    <script>
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            if (!<?= json_encode($can_submit) ?>) {
                e.preventDefault();
                alert('Please add both delivery address and contact number before submitting the order.');
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
