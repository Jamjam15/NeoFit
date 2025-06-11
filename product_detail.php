<?php
session_start();
include './db.php'; // Include your database connection

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);

// Get the product id from the URL
$product_id = $_GET['id'];

// Query the database to fetch the product details based on the id
$result = $conn->query("SELECT * FROM products WHERE id = $product_id LIMIT 1");

// Check if the product exists in the database
if ($result->num_rows > 0) {
    // Fetch the product details
    $product = $result->fetch_assoc();
    $productName = $product['product_name'];
    $productPrice = $product['product_price'];
    $productStatus = $product['product_status'];
    $quantitySmall = $product['quantity_small'];
    $quantityMedium = $product['quantity_medium'];
    $quantityLarge = $product['quantity_large'];
    $photoFront = !empty($product['photoFront']) ? "Admin Pages/" . $product['photoFront'] : "";
    $photo1 = !empty($product['photo1']) ? "Admin Pages/" . $product['photo1'] : "";
    $photo2 = !empty($product['photo2']) ? "Admin Pages/" . $product['photo2'] : "";
    $photo3 = !empty($product['photo3']) ? "Admin Pages/" . $product['photo3'] : "";
    $photo4 = !empty($product['photo4']) ? "Admin Pages/" . $product['photo4'] : "";
} else {
    // Handle case when no product is found
    echo "Product not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT - Remo 98</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #ffffff;
            color: #000000;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            padding: 0 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            width: 100%;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #000000;
        }

        .nav {
            display: flex;
            gap: 30px;
        }

        .nav a {
            text-decoration: none;
            color: #000000;
        }

        .search-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background-color: #f5f5f5;
            border-radius: 20px;
            padding: 8px 15px;
        }

        .search-bar input {
            border: none;
            background: transparent;
            outline: none;
            width: 150px;
            color: #000000;
        }

        .search-bar input::placeholder {
            color: #aaa;
        }

        .icon {
            margin-left: 10px;
            color: #000000;
        }

        .product-container {
            display: flex;
            flex-wrap: wrap;
            gap: 50px;
            margin-top: 40px;
            justify-content: center;
        }

        .product-images {
            width: 45%;
            min-width: 300px;
        }

        .main-image {
            width: 100%;
            height: 400px;
            background-color: #f5f5f5;
            margin-bottom: 15px;
            overflow: hidden;
        }

        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .thumbnail-container {
            display: flex;
            gap: 10px;
        }

        .thumbnail {
            width: 70px;
            height: 70px;
            background-color: #f5f5f5;
            overflow: hidden;
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-loading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #55a39b;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .product-info {
            width: 45%;
            min-width: 300px;
        }

        .product-title {
            font-size: 32px;
            margin-bottom: 10px;
            color: #000000;
        }

        .rating {
            color: #ffc107;
            margin-bottom: 15px;
        }

        .price {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .current-price {
            font-size: 28px;
            color: #55a39b;
            font-weight: bold;
        }

        .original-price {
            font-size: 18px;
            color: #999;
            text-decoration: line-through;
        }

        .product-options {
            margin-bottom: 30px;
        }

        .option-group {
            margin-bottom: 20px;
        }

        .option-label {
            font-size: 18px;
            color: #000;
            margin-bottom: 10px;
        }

        select {
            width: 200px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
            color: #000;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
        }

        .quantity-btn {
            width: 40px;
            height: 40px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            color: #000;
            font-size: 18px;
            cursor: pointer;
        }

        .quantity-input {
            width: 60px;
            height: 40px;
            text-align: center;
            border: 1px solid #ddd;
        }

        /* Remove spinner for all browsers */
        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .quantity-input[type="number"] {
            -moz-appearance: textfield; /* Firefox */
            -webkit-appearance: none; /* Chrome, Safari */
            appearance: none; /* Standard syntax */
            padding: 0; /* Remove padding */
        }


        .inventory {
            margin-left: 15px;
            color: #666;
        }

        .buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .cart-btn {
            padding: 15px 30px;
            background-color: #fff;
            color: #000;
            border: 1px solid #ddd;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .buy-btn {
            padding: 15px 40px;
            background-color: #000;
            color: white;
            border: none;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
        }

        .wishlist-btn {
            width: 50px;
            height: 50px;
            background-color: #fff;
            border: 1px solid #ddd;
            color: #ff4d4d;
            font-size: 24px;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .size-chart {
            margin-top: 15px;
            color: #55a39b;
            text-decoration: none;
            cursor: pointer;
        }
        
        /* Footer styles */
        footer {
            padding: 40px 20px;
            background-color: #f5f5f5;
            color: #000;
            margin-top: 60px;
            width: 100%;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
        }

        .footer-column h3 {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .footer-column ul {
            list-style: none;
        }

        .footer-column li {
            margin-bottom: 10px;
        }

        .footer-column a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .footer-column a:hover {
            color: #000;
        }

        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
        }

        .social-icon:hover {
            background-color: #bbb;
        }

        .footer-bottom {
            max-width: 1200px;
            margin: 30px auto 0;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: right;
            font-size: 12px;
            color: #999;
        }
        .user-icon, .cart-icon {
            font-size: 18px;
            cursor: pointer;
            position: relative;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #55a39b;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
            <div class="header">
                <div id="neofitLogo" style="font-size: 24px; cursor: pointer; text-decoration: none; font-family: Arial, sans-serif; font-weight: bold; display: inline-block; color: black;">
                    NEOFIT
                </div>
                <div class="nav">
                    <a href="#">Trending</a>
                    <a href="#">Men</a>
                    <a href="#">Women</a>
                </div>
                <div class="search-container">
                    <div class="search-bar">
                        <span></span>
                        <input type="text" placeholder="Search">
                    </div>
                    <div class="user-icon"><a href="user-settings.php"> <img src="profile.jpg" alt="Profile Icon" width="24" height="24"></a></div>
                    <div class="cart-icon">
                        <a href="cart.php">
                            <img src="cart.jpg" alt="Cart Icon" width="24" height="24">
                            <span class="cart-count">0</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="product-container">
                <div class="product-images">
                    <div class="main-image">
                        <img src="<?php echo $photoFront; ?>" alt="<?php echo $productName; ?>">
                    </div>
                    <?php
                    // Only show thumbnail container if there are actual additional photos
                    $additional_photos = array_filter([$photo1, $photo2, $photo3, $photo4], function($photo) {
                        return !empty($photo) && $photo !== "Admin Pages/";
                    });
                    
                    if (!empty($additional_photos)) {
                        echo '<div class="thumbnail-container">';
                        foreach ($additional_photos as $photo) {
                            echo '<div class="thumbnail"><img src="' . $photo . '" alt="Additional view"></div>';
                        }
                        echo '</div>';
                    }
                    ?>
                </div>

                <div class="product-info">
                    <h1 class="product-title"><?php echo $productName; ?></h1>
                    <div class="rating">
                        4.9 ★★★★★
                    </div>
                    <div class="price">
                        <span class="current-price">₱ <?php echo $productPrice; ?></span>
                        <!-- Assume that you store original price in database as 'product_original_price' -->
                        <span class="original-price">₱ <?php echo $productPrice * 1.4; ?></span> <!-- Example: apply 40% discount -->
                    </div>

                    <form action="payment.php?id=<?= $product_id ?>" method="POST" id="buyForm">
                        <input type="hidden" name="product_id" value="<?= $product_id ?>">
                        <input type="hidden" name="product_name" value="<?php echo $productName; ?>">
                        <input type="hidden" name="price" id="hidden-price" value="<?php echo $productPrice; ?>">

                        <div class="product-options">
                            <!-- Size -->
                            <div class="option-group">
                                <div class="option-label">Size</div>
                                <select name="size" id="size">
                                    <option value="small">Small</option>
                                    <option value="medium">Medium</option>
                                    <option value="large">Large</option>
                                </select>
                            </div>

                            <!-- Quantity -->
                            <div class="option-group">
                                <div class="option-label">Quantity</div>
                                <div class="quantity-selector">
                                    <button type="button" class="quantity-btn" id="decrease">-</button>
                                    <input type="number" class="quantity-input" name="quantity" id="quantity" value="1" min="1" max="2345"> <!-- Example: 2345 as max quantity -->
                                    <button type="button" class="quantity-btn" id="increase">+</button>
                                    <span class="inventory" id="inventory-count"><?php echo $quantitySmall; ?> pieces available</span>
                                </div>
                            </div>
                        </div>

                        <div class="buttons">
                            <button type="button" class="cart-btn" id="addToCartBtn">
                                <span>🛒</span> Add to Cart
                            </button>
                            <button type="button" class="buy-btn" id="buyNowBtn">Buy Now</button>
                            <button type="button" class="wishlist-btn" id="wishlistBtn">♥</button>
                        </div>
                        <a href="#" class="size-chart">click here to see size chart</a>
                    </form>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const quantityInput = document.getElementById("quantity");
        const decreaseBtn = document.getElementById("decrease");
        const increaseBtn = document.getElementById("increase");
        const inventoryCount = document.getElementById("inventory-count");
        const sizeSelect = document.getElementById("size");
        const addToCartBtn = document.getElementById("addToCartBtn");
        const wishlistBtn = document.getElementById("wishlistBtn");
        const buyForm = document.getElementById("buyForm");

        // Prevent form submission on Enter key press
        quantityInput.addEventListener("keydown", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                return false;
            }
        });

        // Set initial inventory data
        let availableQuantities = {
            small: <?php echo $quantitySmall; ?>,
            medium: <?php echo $quantityMedium; ?>,
            large: <?php echo $quantityLarge; ?>
        };

        // Function to update the available inventory count and enable/disable buttons
        function updateInventory() {
            const selectedSize = sizeSelect.value;
            const available = availableQuantities[selectedSize];

            inventoryCount.textContent = `${available} pieces available`;

            // Set max value and adjust quantity if necessary
            quantityInput.setAttribute("max", available);
            quantityInput.value = Math.min(quantityInput.value, available);

            // Enable/disable buttons based on available stock
            increaseBtn.disabled = available <= 0 || quantityInput.value >= available;
            decreaseBtn.disabled = quantityInput.value <= 1;
        }

        // Event listener for size change
        sizeSelect.addEventListener("change", updateInventory);

        // Increase quantity
        increaseBtn.addEventListener("click", function() {
            let current = parseInt(quantityInput.value);
            const max = parseInt(quantityInput.getAttribute("max"));
            if (current < max) {
                quantityInput.value = current + 1;
            }
            updateInventory();
        });

        // Decrease quantity
        decreaseBtn.addEventListener("click", function() {
            let current = parseInt(quantityInput.value);
            if (current > 1) {
                quantityInput.value = current - 1;
            }
            updateInventory();
        });        // Handle manual input in quantity field
        quantityInput.addEventListener("input", function() {
            let value = parseInt(quantityInput.value);
            const max = parseInt(quantityInput.getAttribute("max"));

            if (isNaN(value) || value < 1) {
                quantityInput.value = "1";
            } else if (value > max) {
                alert("Desired quantity exceeds available stock.");
                quantityInput.value = max;
            }
            updateInventory();
        });

        // Prevent form submission on Enter key in quantity input
        quantityInput.addEventListener("keypress", function(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                this.blur(); // Remove focus from the input
            }
        });

        // Prevent zero or negative values when losing focus
        quantityInput.addEventListener("blur", function() {
            if (this.value === "" || parseInt(this.value) < 1) {
                this.value = "1";
                updateInventory();
            }
        });

        // Initialize on page load
        updateInventory();

        // Redirect logo to landing page
        document.getElementById("neofitLogo").addEventListener("click", function() {
            window.location.href = 'landing_page.php';
        });

        // Handle thumbnail clicks
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.addEventListener('click', function() {
                const mainImage = document.querySelector('.main-image img');
                const thumbImage = this.querySelector('img');
                if (mainImage && thumbImage) {
                    mainImage.src = thumbImage.src;
                }
            });
        });

        // Add to Cart functionality
        addToCartBtn.addEventListener("click", function() {
            <?php if (!$is_logged_in): ?>
                window.location.href = 'login.php';
                return;
            <?php endif; ?>            // Check if selected size has available stock
            const selectedSize = sizeSelect.value;
            const available = availableQuantities[selectedSize];
            
            if (available <= 0) {
                alert("Selected size is out of stock");
                return;
            }

            const formData = new FormData();
            formData.append("product_id", <?php echo $product_id; ?>);
            formData.append("size", sizeSelect.value);
            formData.append("quantity", quantityInput.value);

            fetch("add_to_cart.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    updateCartCount();
                } else {
                    alert(data.message || "Error adding item to cart");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Error adding item to cart");
            });
        });

        // Buy Now functionality
        const buyNowBtn = document.getElementById("buyNowBtn");
        buyNowBtn.addEventListener("click", function() {
            <?php if (!$is_logged_in): ?>
                window.location.href = 'login.php';
                return;
            <?php endif; ?>

            const formData = new FormData();
            formData.append("product_id", <?php echo $product_id; ?>);
            formData.append("size", sizeSelect.value);
            formData.append("quantity", quantityInput.value);            // Check if selected size has available stock
            const selectedSize = sizeSelect.value;
            const available = availableQuantities[selectedSize];
            
            if (available <= 0) {
                alert("Selected size is out of stock");
                return;
            }

            fetch("buy_now.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || "Error processing request");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Error processing request");
            });
        });

        // Toggle Favorite functionality
        wishlistBtn.addEventListener("click", function() {
            <?php if (!$is_logged_in): ?>
                window.location.href = 'login.php';
                return;
            <?php endif; ?>

            const formData = new FormData();
            formData.append("product_id", <?php echo $product_id; ?>);

            fetch("toggle_favorite.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.action === 'added') {
                        wishlistBtn.style.backgroundColor = '#ff4d4d';
                        wishlistBtn.style.color = '#fff';
                    } else {
                        wishlistBtn.style.backgroundColor = '#fff';
                        wishlistBtn.style.color = '#ff4d4d';
                    }
                    alert(data.message);
                } else {
                    alert(data.message || "Error updating favorites");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Error updating favorites");
            });
        });

        // Check favorite status on load
        <?php if ($is_logged_in): ?>
        fetch("check_favorite.php?product_id=<?php echo $product_id; ?>")
            .then(response => response.json())
            .then(data => {
                if (data.is_favorite) {
                    wishlistBtn.style.backgroundColor = '#ff4d4d';
                    wishlistBtn.style.color = '#fff';
                }
            })
            .catch(error => console.error("Error:", error));
        <?php endif; ?>

        // Update cart count
        function updateCartCount() {
            fetch('get_cart_count.php')
                .then(response => response.json())
                .then(data => {
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.count;
                        cartCount.style.display = data.count > 0 ? 'flex' : 'none';
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Update cart count on page load
        updateCartCount();
    });
    </script>
