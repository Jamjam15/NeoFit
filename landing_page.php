<?php
session_start();

// Redirect to login if session has ended
if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit();
}

$user_email = $_SESSION['email'];

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Alexandria&display=swap" rel="stylesheet">

    <title>NEOFIT - Elevate Your Streetwear Game</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Alexandria', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #1b1b1b;
            color: #fff;
        }

        /* Header Styles */
        header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background-color: #FFFFFF;
            color: #000;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #000000;
            text-decoration: none;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 25px;
        }

        nav a {
            text-decoration: none;
            color: #1E1E1E;
            font-size: 14px;
            transition: color 0.3s;
        }

        nav a:hover {
            color: #666;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .search-container {
            position: relative;
        }

        .search-input {
            padding: 8px 12px;
            border-radius: 20px;
            border: 1px solid #eee;
            background-color: #f5f5f5;
            width: 180px;
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

        /* Main Content */
        main {
            flex: 1;
            padding: 0;
            background-color: #fff;
            color: #000;
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            height: 60vh; /* Controls height of visible video */
            overflow: hidden;
        }

        .hero-video {
            position: absolute;
            top: -20%; /* Moves video up to crop bottom */
            left: 0;
            width: 100%;
            height: auto;
            min-height: 100%;
            object-fit: cover;
            object-position: center top;
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 0 20px;
            color: white;
            background: rgba(0, 0, 0, 0.4); /* Optional overlay for readability */
        }

        .hero-heading {
            font-size: 48px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 30px;
        }

        .cta-button {
            padding: 12px 40px;
            border: 2px transparent;
            background: transparent;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .cta-button:hover {
            background:rgb(255, 255, 255);
            color: #000;
        }

        .hero-subheading {
            font-size: 24px;
            margin-bottom: 40px;
            text-transform: uppercase;
        }

        .cta-button1 {
            padding: 12px 40px;
            border: 2px solid #ffffff;
            background: transparent;
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .cta-button1:hover {
            background: #ffffff;
            color: #1e1e1e;
            border: white;
        }

          /* Slider Styles */
          .slider-container {
            width: 100%;
            height: 500px;
            position: relative;
            overflow: hidden;
        }

        .slider {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.5s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color:rgb(141, 139, 139);
        }

        .slide.active {
            opacity: 1;
        }

        .slide-content {
            text-align: left;
            padding: 0 10%;
            max-width: 600px;
        }

        .slide-heading {
            font-size: 3rem;
            text-transform: uppercase;
            line-height: 1.2;
            margin-bottom: 20px;
        }
        
        .cta-button {
            display: inline-block;
            padding: 12px 30px;
            background-color: black;
            color: white;
            text-decoration: none;
            font-weight: bold;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        .cta-button:hover {
            background-color:rgb(255, 255, 255);
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            z-index: -1;
        }

        .slider-controls {
            position: absolute;
            bottom: 20px;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2;
        }

        .prev-btn, .next-btn {
            background: transparent;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            margin: 0 10px;
            font-size: 24px;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: background 0.3s;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
        }

        .prev-btn {
            left: 20px;
        }

        .next-btn {
            right: 20px;
        }

        .dots {
            display: flex;
            gap: 8px;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.5);
            cursor: pointer;
            transition: background 0.3s;
        }

        .dot.active {
            background: black;
        }
        /* Product Section */
        .product-section {
            padding: 60px 20px;
            text-align: center;
            background: #FFFFFF;
        }

        .section-title {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 40px;
            text-transform: uppercase;
        }

        .category-title {
            font-size: 24px;
            font-weight: bold;
            margin: 40px 0 30px;
            text-transform: uppercase;
            text-align: left;
            padding-left: 20px;
            border-left: 4px solid #000;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto 40px;
        }

        .product-card {
            background-color: #fff;
            border-radius: 4px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            box-shadow: 0 1px 20px rgba(0, 0, 0, 0.2);
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .product-card.selected {
            border: 2px solid #00a0a0;
        }

        /* Add these new styles for product links */
        .product-link {
            text-decoration: none !important;
            color: inherit !important;
        }

        .product-link:hover, 
        .product-link:visited, 
        .product-link:active {
            text-decoration: none !important;
            color: inherit !important;
        }

        .product-image {
            width: 100%;
            height: 250px;
            overflow: hidden;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
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

        .browse-all {
            display: inline-block;
            padding: 12px 30px;
            border: 1px solid #000;
            background: transparent;
            color: #000;
            font-size: 16px;
            margin-top: 40px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-transform: lowercase;
        }

        .browse-all:hover {
            background: #000;
            color: #fff;
        }

        /* Category sections */
        #trending-section, #men-section, #women-section {
            padding: 60px 20px;
            scroll-margin-top: 70px; /* Adds space for the sticky header */
        }

        /* Feature Section */
        .feature-section {
            background-color: #fff;
            padding: 80px 20px;
            text-align: center;
        }

        /* Empower section */
        .empower-section {
            width: 100%;
            height: 500px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-image: url('12.jpg'); /* Add your image */
            background-size: cover;         /* Cover the whole section */
            background-position: center;    /* Center the image */
            background-repeat: no-repeat;   /* Prevent repeat */
            padding: 0 10%;
            box-sizing: border-box;
            color: white;                   /* Optional: text color for visibility */
        }

        .empower-content {
            text-align: left;
            max-width: 600px;
        }

        .empower-heading {
            font-size: 3rem;
            text-transform: uppercase;
            line-height: 1.2;
            margin-bottom: 0px;
            color: #ffffff
        }

        .empower-subheading {
            font-size: 3rem;
            line-height: 1.2;
            margin-bottom: 20px;
            color: #ffffff;
            width: 500px;
        }

        /* Footer */
        footer {
            padding: 40px 20px;
            background-color: #f5f5f5;
            color: #000;
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
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .content-container {
                flex-direction: column;
                height: auto;
                padding: 10px;
            }
            
            .image-container {
                max-width: 100%;
                height: auto;
                padding: 10px;
                margin-bottom: 20px;
            }
            
            .slider-container {
                height: 350px; /* Smaller height on mobile */
            }
            
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .hero-heading {
                font-size: 32px;
            }
            
            .empower-subheading {
                width: 100%;
                font-size: 2rem;
            }

            .footer-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .product-grid {
                grid-template-columns: 1fr;
            }

            .footer-container {
                grid-template-columns: 1fr;
            }
        }

        /* Add these styles to your existing CSS */
        .nav-link {
            position: relative;
            transition: color 0.3s;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background-color: #000;
            transition: width 0.3s;
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }

        .product-section {
            scroll-margin-top: 100px; /* Adjust this value based on your header height */
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <a class="logo" href="landing_page.php">NEOFIT</a>
            <nav>
                <ul>
                    <li><a href="#trending-section" class="nav-link" data-category="trending">All Products</a></li>
                    <li><a href="#men-section" class="nav-link" data-category="men">Men</a></li>
                    <li><a href="#women-section" class="nav-link" data-category="women">Women</a></li>
                </ul>
            </nav>
            <div class="header-right">                <div class="search-container">
                    <input type="text" id="search-input" class="search-input" placeholder="Search" autocomplete="off">
                </div>
                <div class="user-icon"><a href="user-settings.php"> <img src="profile.jpg" alt="Profile Icon" width="24" height="24"></a></div>
                <div class="cart-icon">
                    <a href="cart.php">
                        <img src="cart.jpg" alt="Cart Icon" width="24" height="24">
                        <span class="cart-count">0</span>
                    </a>
                </div>
                <div class="shopping-bag-icon">
                    <a href="orders.php">
                        <img src="shopping-bag.png" alt="Shopping Bag Icon" width="24" height="24">
                    </a>
                </div>
                <div class="favorites-icon">
                    <a href="favorites.php">
                        <img src="favorites.png" alt="Favorites Icon" width="24" height="24">
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero-section">
            <video autoplay muted loop playsinline class="hero-video">
                <source src="vid1.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div class="hero-content">
                <h1 class="hero-heading">ELEVATE YOUR<br>STREETWEAR GAME</h1>
                <a href="#trending-section" class="cta-button" id="hero-shop-button">SHOP NOW</a>
            </div>
        </section>

        <!-- Featured Products Section -->
        <section class="product-section" id="trending-section">
            <h2 class="section-title" style="color: #1e1e1e;">ALL PRODUCTS</h2>
            <div class="product-grid">
                <?php
                    // Connect to Database
                    include './db.php';
                    include 'product_stats.php';

                    // Get all products' sold counts
                    $sold_counts = getAllProductsSoldCount();

                    // Fetch Trending Products (products with highest sold count)
                    
                    // With this corrected version:
                    $sql = "SELECT p.*, COALESCE(SUM(oi.quantity), 0) as total_sold 
                            FROM products p 
                            LEFT JOIN order_items oi ON p.id = oi.product_id
                            LEFT JOIN orders o ON oi.order_id = o.id AND o.status != 'cancelled'
                            WHERE p.product_status = 'live'
                            GROUP BY p.id, p.product_name, p.product_price, p.photoFront, p.product_status 
                            ORDER BY total_sold DESC 
                            LIMIT 4";
                    $result = $conn->query($sql);

                    // Display Trending Products
                    if ($result->num_rows > 0) {
                        while($product = $result->fetch_assoc()) {
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

                            echo '
                            <div class="product-card" id="product-box-' . $id . '">
                                <a href="' . $link . '" class="product-link">
                                    <div class="product-image">
                                        <img src="' . $photoFront . '" alt="' . $productName . '">
                                    </div>
                                    <div class="product-info">
                                        <h3 class="product-name">' . $productName . '</h3>
                                        <span class="product-price">₱ ' . $productPrice . '</span>
                                        <span class="product-sold">' . $formatted_sold_count . ' sold</span>
                                    </div>
                                </a>
                            </div>';
                        }
                    }
                ?>
            </div>
        </section>

        <section class="product-section" id="men-section">
            <h2 class="section-title" style="color: #1e1e1e;">MEN'S COLLECTION</h2>
            <div class="product-grid">
                <?php
                    // Fetch Men's Products
                    $sql = "SELECT * FROM products WHERE product_category = 'men' AND product_status = 'live'";
                    $result = $conn->query($sql);

                    // Display Men's Products
                    if ($result->num_rows > 0) {
                        while($product = $result->fetch_assoc()) {
                            $id = $product['id'];
                            $productName = $product['product_name'];
                            $productPrice = $product['product_price'];
                            $photoFront = "Admin Pages/" . $product['photoFront'];
                            $link = 'product_detail.php?id=' . $id;
                            
                            // Get the sold count for this product
                            $sold_count = $sold_counts[$productName] ?? 0;
                            
                            // Format the sold count
                            $formatted_sold_count = $sold_count;
                            if ($sold_count >= 1000) {
                                $formatted_sold_count = number_format($sold_count/1000, 1) . 'k';
                            }

                            echo '
                            <div class="product-card" id="product-box-' . $id . '">
                                <a href="' . $link . '" class="product-link">
                                    <div class="product-image">
                                        <img src="' . $photoFront . '" alt="' . $productName . '">
                                    </div>
                                    <div class="product-info">
                                        <h3 class="product-name">' . $productName . '</h3>
                                        <span class="product-price">₱ ' . $productPrice . '</span>
                                        <span class="product-sold">' . $formatted_sold_count . ' sold</span>
                                    </div>
                                </a>
                            </div>';
                        }
                    }
                ?>
            </div>
        </section>

        <section class="product-section" id="women-section">
            <h2 class="section-title" style="color: #1e1e1e;">WOMEN'S COLLECTION</h2>
            <div class="product-grid">
                <?php
                    // Fetch Women's Products
                    $sql = "SELECT * FROM products WHERE product_category = 'women' AND product_status = 'live'";
                    $result = $conn->query($sql);

                    // Display Women's Products
                    if ($result->num_rows > 0) {
                        while($product = $result->fetch_assoc()) {
                            $id = $product['id'];
                            $productName = $product['product_name'];
                            $productPrice = $product['product_price'];
                            $photoFront = "Admin Pages/" . $product['photoFront'];
                            $link = 'product_detail.php?id=' . $id;
                            
                            // Get the sold count for this product
                            $sold_count = $sold_counts[$productName] ?? 0;
                            
                            // Format the sold count
                            $formatted_sold_count = $sold_count;
                            if ($sold_count >= 1000) {
                                $formatted_sold_count = number_format($sold_count/1000, 1) . 'k';
                            }

                            echo '
                            <div class="product-card" id="product-box-' . $id . '">
                                <a href="' . $link . '" class="product-link">
                                    <div class="product-image">
                                        <img src="' . $photoFront . '" alt="' . $productName . '">
                                    </div>
                                    <div class="product-info">
                                        <h3 class="product-name">' . $productName . '</h3>
                                        <span class="product-price">₱ ' . $productPrice . '</span>
                                        <span class="product-sold">' . $formatted_sold_count . ' sold</span>
                                    </div>
                                </a>
                            </div>';
                        }
                    }
                ?>
            </div>
            <a href="browse_all_collection.php" class="browse-all" id="browse-all-button">browse all collection</a>
        </section>

       
        <!-- Slider Section -->
    <section class="slider-container">
        <div class="slider">
            <div class="slide">
                <img src="/api/placeholder/1200/500" alt="Trending Streetwear">
                <div class="slide-content">
                    <h2 class="slide-heading"></h2>
                    
                </div>
            </div>
            <div class="slide">
                <img src="/api/placeholder/1200/500" alt="New Arrivals">
                <div class="slide-content">
                    <h2 class="slide-heading"></h2>
                   
                </div>
            </div>
            <div class="slide">
                <img src="/api/placeholder/1200/500" alt="Seasonal Essentials">
                <div class="slide-content">
                    <h2 class="slide-heading"></h2>
                    
                </div>
            </div>
        </div>
        <div class="slider-controls">
            <button class="prev-btn">&#10094;</button>
            <div class="dots">
                <div class="dot active"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
            <button class="next-btn">&#10095;</button>
        </div>
    </section>
        <section class="empower-section">
            <div class="empower-content">
                <div class="text-content">
                    <h2 class="empower-heading">Empower</h2>
                    <h3 class="empower-subheading">SELF-EXPRESSION<br>WITH NEOFIT</h3>
                    <a href="#" class="cta-button1">Learn More</a>
                </div>
            </div>
        </section>

    </main>

    <?php include 'footer.php'; ?>
    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    const headerOffset = 100; // Adjust this value based on your header height
                    const elementPosition = targetElement.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Add active class to nav links based on scroll position
        window.addEventListener('scroll', function() {
            const sections = document.querySelectorAll('.product-section');
            const navLinks = document.querySelectorAll('.nav-link');
            
            let currentSection = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (pageYOffset >= sectionTop - 150) {
                    currentSection = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href').substring(1) === currentSection) {
                    link.classList.add('active');
                }
            });
        });
    </script>

    <script>
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
        }        // Update cart count on page load
        updateCartCount();

        // Real-time search functionality
        const searchInput = document.getElementById('search-input');
        const allProducts = document.querySelectorAll('.product-card');
        const productSections = document.querySelectorAll('.product-section');

        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            let hasResults = false;

            // Show all sections initially
            productSections.forEach(section => {
                section.style.display = 'block';
            });

            if (searchTerm === '') {
                // If search is empty, show all products
                allProducts.forEach(product => {
                    product.style.display = 'block';
                });
                return;
            }

            allProducts.forEach(product => {
                const productName = product.querySelector('.product-name').textContent.toLowerCase();
                if (productName.includes(searchTerm)) {
                    product.style.display = 'block';
                    hasResults = true;
                } else {
                    product.style.display = 'none';
                }
            });

            // Hide sections that have no visible products
            productSections.forEach(section => {
                const visibleProducts = section.querySelectorAll('.product-card[style="display: block;"]');
                if (visibleProducts.length === 0) {
                    section.style.display = 'none';
                }
            });

            // Scroll to first visible section if there are results
            if (hasResults) {
                const firstVisibleSection = document.querySelector('.product-section[style="display: block;"]');
                if (firstVisibleSection) {
                    firstVisibleSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    </script>
</body>
</html>