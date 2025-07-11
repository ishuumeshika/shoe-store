<?php ob_start(); ?>
<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StepStyle - Premium Footwear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/adminstyle.css">
    <link rel="stylesheet" href="../../assets/css/adminstyle2.css">
    <link rel="stylesheet" href="../assets/css/adminstyle2.css">
    <link rel="stylesheet" href="../../../assets/css/adminstyle2.css">


    
    <style>
        :root {
            --primary-color: #2c3e50;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --dark-color: #1a252f;
            --light-color: #ecf0f1;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --shadow-light: 0 2px 10px rgba(0,0,0,0.1);
            --shadow-medium: 0 5px 25px rgba(0,0,0,0.15);
            --shadow-heavy: 0 10px 40px rgba(0,0,0,0.2);
            --border-radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 2.6;
            color: #333;
            background-color: #f8f9fa;
        }

        /* Professional Navigation Styles */
        .navbar {
            background: linear-gradient(135deg,rgb(3, 8, 19) 0%, #2a5298 100%) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 0.8rem 0;
            transition: var(--transition);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
        }

        .navbar.scrolled {
            background: linear-gradient(135deg, #1a252f 0%, #2c3e50 100%) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            padding: 0.5rem 0;
        }

        /* Fixed navbar container layout */
        .navbar .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: nowrap;
        }

        .navbar-brand {
            font-weight: 900;
            font-size: 1.8rem;
            color: #fff !important;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            position: relative;
            flex-shrink: 0;
        }

        .navbar-brand::before {
            content: '👟';
            font-size: 2rem;
            animation: bounce 2s infinite;
        }

        .navbar-brand h2 {
            margin: 0;
            background: linear-gradient(45deg, #fff, #3498db);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
            color: #3498db !important;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        .navbar-toggler {
            border: none;
            padding: 0.5rem;
            border-radius: var(--border-radius);
            background: rgba(255, 255, 255, 0.1);
            transition: var(--transition);
        }

        .navbar-toggler:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.3);
        }

        /* Fixed navbar collapse layout */
        .navbar-collapse {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
        }

        /* Center the main navigation */
        .navbar-nav.main-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: row;
            margin: 0 auto;
            gap: 0.5rem;
        }

        /* Right side navigation */
        .navbar-nav.right-nav {
            display: flex;
            align-items: center;
            flex-direction: row;
            margin-left: auto;
            gap: 0.5rem;
        }

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 0.7rem 1.2rem !important;
            border-radius: var(--border-radius);
            margin: 0 0.2rem;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-nav .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: var(--transition);
        }

        .navbar-nav .nav-link:hover::before {
            left: 100%;
        }

        .navbar-nav .nav-link:hover {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .navbar-nav .nav-link.active {
            background: rgba(52, 152, 219, 0.3);
            color: #fff !important;
            box-shadow: 0 3px 10px rgba(52, 152, 219, 0.3);
        }

        /* Search Form Styling */
        .search-form {
            position: relative;
            margin: 0 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .search-input {
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            border-radius: 25px;
            padding: 0.6rem 1.2rem;
            width: 250px;
            transition: var(--transition);
            backdrop-filter: blur(10px);
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .search-input:focus {
            background: rgba(255, 255, 255, 0.25);
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.3);
            outline: none;
            width: 300px;
        }

        .search-form .btn {
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            transition: var(--transition);
        }

        .search-form .btn:hover {
            background: #3498db;
            border-color: #3498db;
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        /* Dropdown Styling */
        .dropdown-menu {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-medium);
            padding: 0.5rem 0;
            margin-top: 0.5rem;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-item {
            padding: 0.7rem 1.5rem;
            color: #2c3e50;
            font-weight: 500;
            transition: var(--transition);
            border-radius: 8px;
            margin: 0 0.5rem;
        }

        .dropdown-item:hover {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: #fff;
            transform: translateX(5px);
        }

        .dropdown-divider {
            margin: 0.5rem 1rem;
            border-color: rgba(0, 0, 0, 0.1);
        }

        /* User Profile Dropdown */
        .nav-item.dropdown .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-item.dropdown .nav-link i {
            font-size: 1.2rem;
        }

        /* Cart Badge */
        .cart-count {
            background: linear-gradient(135deg, #e74c3c, #c0392b) !important;
            border-radius: 50%;
            padding: 0.3rem 0.6rem;
            font-size: 0.75rem;
            font-weight: 700;
            animation: pulse 2s infinite;
            margin-left: 0.3rem;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        /* Professional Button Styles */
        .btn-outline-light {
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: #fff;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-outline-light:hover {
            background: #fff;
            color: #2c3e50;
            border-color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 255, 255, 0.3);
        }

        /* Mobile Responsiveness */
        @media (max-width: 991.98px) {
            .navbar-brand h2 {
                font-size: 1.5rem;
            }

            .search-input {
                width: 200px;
            }

            .search-input:focus {
                width: 220px;
            }

            .navbar-collapse {
                flex-direction: column;
                align-items: stretch;
            }

            .navbar-nav.main-nav,
            .navbar-nav.right-nav {
                flex-direction: column;
                margin: 0;
                width: 100%;
            }

            .navbar-nav {
                background: rgba(0, 0, 0, 0.1);
                border-radius: var(--border-radius);
                padding: 1rem;
                margin-top: 1rem;
                backdrop-filter: blur(10px);
            }

            .navbar-nav .nav-link {
                margin: 0.2rem 0;
                justify-content: flex-start;
            }

            .search-form {
                margin: 1rem 0;
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 767.98px) {
            .search-input {
                width: 100%;
            }

            .search-input:focus {
                width: 100%;
            }

            .search-form {
                flex-direction: column;
                gap: 0.5rem;
            }

            .search-form .btn {
                width: 100%;
                border-radius: var(--border-radius);
                height: auto;
                padding: 0.6rem;
            }
        }

        /* Container Spacing */
        .container.mt-5.pt-4 {
            margin-top: 6rem !important;
            padding-top: 2rem !important;
        }

        /* Loading Animation */
        .navbar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, #3498db, #e74c3c, #27ae60, #f39c12);
            background-size: 400% 100%;
            animation: loading 3s ease infinite;
        }

        @keyframes loading {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Hover Effects for Icons */
        .fas, .fab {
            transition: var(--transition);
        }

        .nav-link:hover .fas,
        .nav-link:hover .fab {
            transform: scale(1.2) rotate(5deg);
        }

        /* Professional Shadows */
        .navbar-nav .nav-item:hover {
            filter: drop-shadow(0 5px 10px rgba(0, 0, 0, 0.2));
        }

        /* Smooth Scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Focus States for Accessibility */
        .nav-link:focus,
        .navbar-brand:focus,
        .btn:focus {
            outline: 3px solid rgba(52, 152, 219, 0.5);
            outline-offset: 2px;
        }

        /* Ensure proper alignment on large screens */
        @media (min-width: 992px) {
            .navbar-collapse {
                display: flex !important;
                align-items: center;
                justify-content: space-between;
                width: 100%;
            }

            .navbar-nav.main-nav {
                flex: 1;
                justify-content: center;
                max-width: none;
            }

            .navbar-nav.right-nav {
                flex: 0 0 auto;
                margin-left: 1rem;
            }

            .search-form {
                flex: 0 0 auto;
                margin: 0;
            }
        }
    </style>
</head>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// Update cart count on page load
$(document).ready(function() {
    $.ajax({
        url: 'functions/cart.php?action=count',
        method: 'GET',
        success: function(response) {
            const result = JSON.parse(response);
            if(result.success) {
                $('.cart-count').text(result.cart_count);
            }
        }
    });

    // Add scroll effect to navbar
    $(window).scroll(function() {
        if ($(window).scrollTop() > 50) {
            $('.navbar').addClass('scrolled');
        } else {
            $('.navbar').removeClass('scrolled');
        }
    });

    // Add active class to current page
    var currentPage = window.location.pathname;
    $('.navbar-nav .nav-link').each(function() {
        var linkPage = $(this).attr('href');
        if (currentPage.includes(linkPage) && linkPage !== '#') {
            $(this).addClass('active');
        }
    });

    // Smooth hover effects
    $('.nav-link').hover(
        function() {
            $(this).find('i').addClass('fa-bounce');
        },
        function() {
            $(this).find('i').removeClass('fa-bounce');
        }
    );
});
</script>

<body>
    <!-- Professional Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="http://localhost/shoe_store/">
                <h2>TrendSole</h2>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Main Navigation - Centered -->
                <ul class="navbar-nav main-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="http://localhost/shoe_store/index.php">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="http://localhost/shoe_store/pages/products/men.php">
                            <i class="fas fa-male"></i> Mens
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="http://localhost/shoe_store/pages/products/women.php">
                            <i class="fas fa-female"></i> Womens
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="http://localhost/shoe_store/pages/products/kid.php">
                            <i class="fas fa-child"></i> Kids
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="http://localhost/shoe_store/pages/used_shoes/browse.php">
                            <i class="fas fa-recycle"></i> Used Shoes
                        </a>
                    </li>
                </ul>
                
                <!-- Search Form -->
                <form class="search-form" action="http://localhost/shoe_store/search.php" method="GET">
                    <input class="form-control search-input" type="search" placeholder="Search premium shoes..." name="query" autocomplete="off">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    <div class="search-results dropdown-menu"></div>
                </form>
                
                <!-- Right Navigation -->
                <ul class="navbar-nav right-nav">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle"></i> 
                                <span><?php echo $_SESSION['user_name']; ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="http://localhost/shoe_store/pages/user/profile.php">
                                        <i class="fas fa-user"></i> Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="http://localhost/shoe_store/pages/user/orders.php">
                                        <i class="fas fa-shopping-bag"></i> My Orders
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="http://localhost/shoe_store/pages/used_shoes/sell.php">
                                        <i class="fas fa-tags"></i> Sell Used Shoes
                                    </a>
                                </li>
                                <?php if($_SESSION['user_role'] == 'admin'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="http://localhost/shoe_store/admin/dashboard.php">
                                            <i class="fas fa-cog"></i> Admin Panel
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="http://localhost/shoe_store/pages/auth/logout.php">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/shoe_store/pages/cart.php">
                                <i class="fas fa-shopping-cart"></i> Cart
                                <span class="badge cart-count">0</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="http://localhost/shoe_store/pages/auth/login.php">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="http://localhost/shoe_store/pages/auth/register.php">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-5 pt-4">