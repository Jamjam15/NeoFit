<?php
session_start();

// Redirect to login if session has ended
if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit();
}

include 'db.php';
include 'product_stats.php';

$user_id = $_SESSION['user_id'];

// Get all products' sold counts
$sold_counts = getAllProductsSoldCount();

// Fetch user's favorite products

// To this:
$sql = "SELECT p.*, COALESCE(SUM(oi.quantity), 0) as total_sold 
        FROM products p 
        LEFT JOIN order_items oi ON p.id = oi.product_id
        LEFT JOIN orders o ON oi.order_id = o.id AND o.status != 'cancelled'
        WHERE p.id IN (SELECT product_id FROM favorites WHERE user_id = ?)
        GROUP BY p.id, p.product_name, p.product_price, p.photoFront, p.product_status";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites - NEOFIT</title>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Alexandria', sans-serif;
        }

        body {
            background-color: #fff;
            color: #1e1e1e;
        }

        .page-title {
            text-align: center;
            padding: 40px 0;
            font-size: 32px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .no-favorites {
            text-align: center;
            padding: 40px;
            font-size: 18px;
            color: #666;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto 40px;
            padding: 0 20px;
        }

        .product-card {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            box-shadow: 0 1px 20px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .product-link {
            text-decoration: none;
            color: inherit;
        }

        .product-image {
            width: 100%;
            height: 250px;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .product-info {
            padding: 15px;
            text-align: left;
        }

        .product-name {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .product-price {
            font-size: 16px;
            font-weight: bold;
            color: #00a0a0;
            display: inline-block;
        }

        .product-sold {
            font-size: 14px;
            color: #999;
            float: right;
        }

        .remove-favorite {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 30px;
            height: 30px;
            background-color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            border: none;
            color: #ff4d4d;
            font-size: 20px;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .remove-favorite:hover {
            background-color: #ff4d4d;
            color: #fff;
        }

        .browse-products {
            display: inline-block;
            padding: 12px 30px;
            border: 1px solid #000;
            background: transparent;
            color: #000;
            font-size: 16px;
            margin: 40px auto;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-transform: lowercase;
            display: block;
            width: fit-content;
        }

        .browse-products:hover {
            background: #000;
            color: #fff;
        }

        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <h1 class="page-title">My Favorites</h1>

        <?php if ($result->num_rows === 0): ?>
            <div class="no-favorites">
                <p>You haven't added any products to your favorites yet.</p>
                <a href="landing_page.php" class="browse-products">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php while($product = $result->fetch_assoc()): 
                    $id = $product['id'];
                    $productName = $product['product_name'];
                    $productPrice = $product['product_price'];
                    $photoFront = "Admin Pages/" . $product['photoFront'];
                    $link = 'product_detail.php?id=' . $id;
                    $sold_count = $product['total_sold'];
                    
                    // Format the sold count
                    $formatted_sold_count = $sold_count;
                    if ($sold_count >= 1000) {
                        $formatted_sold_count = number_format($sold_count/1000, 1) . 'k';
                    }
                ?>
                    <div class="product-card" id="product-box-<?php echo $id; ?>">
                        <button class="remove-favorite" onclick="removeFavorite(<?php echo $id; ?>)">×</button>
                        <a href="<?php echo $link; ?>" class="product-link">
                            <div class="product-image">
                                <img src="<?php echo $photoFront; ?>" alt="<?php echo $productName; ?>">
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?php echo $productName; ?></h3>
                                <span class="product-price">₱ <?php echo $productPrice; ?></span>
                                <span class="product-sold"><?php echo $formatted_sold_count; ?> sold</span>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        function removeFavorite(productId) {
            const formData = new FormData();
            formData.append("product_id", productId);

            fetch("toggle_favorite.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the product card from the grid
                    const productBox = document.getElementById(`product-box-${productId}`);
                    productBox.remove();                    // Check if there are any products left
                    const productGrid = document.querySelector('.product-grid');
                    const remainingProducts = document.querySelectorAll('.product-card');
                    
                    if (remainingProducts.length === 0) {
                        // Replace the empty grid with the no-favorites message
                        const main = document.querySelector('main');
                        main.innerHTML = `
                            <h1 class="page-title">My Favorites</h1>
                            <div class="no-favorites">
                                <p>You haven't added any products to your favorites yet.</p>
                                <a href="landing_page.php" class="browse-products">Browse Products</a>
                            </div>
                        `;
                    }
                } else {
                    alert(data.message || "Error removing from favorites");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Error removing from favorites");
            });
        }

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
    </script>
</body>
</html>
