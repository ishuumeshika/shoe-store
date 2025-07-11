<?php
require_once '../../includes/auth.php';

// Only show menu for logged-in users
if (!isset($_SESSION['user_id'])) {
    return;
}
?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">User Menu</h5>
    </div>
    <div class="list-group list-group-flush">
        <a href="../user/profile.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
            <i class="fas fa-user me-2"></i>Profile
        </a>
        <a href="../user/orders.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-bag me-2"></i>My Orders
        </a>
        <a href="../used_shoes/sell.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'sell.php' ? 'active' : ''; ?>">
            <i class="fas fa-dollar-sign me-2"></i>Sell Used Shoes
        </a>
        <a href="../used_shoes/my_listings.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'my_listings.php' ? 'active' : ''; ?>">
            <i class="fas fa-list me-2"></i>My Listings
        </a>
        <a href="../auth/logout.php" class="list-group-item list-group-item-action text-danger">
            <i class="fas fa-sign-out-alt me-2"></i>Logout
        </a>
    </div>
</div>