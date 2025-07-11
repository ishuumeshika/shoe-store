<?php
// Get pending counts for badges
$pending_used_shoes = 0;
$pending_orders = 0;

if (isset($conn)) {
    try {
        // Get pending used shoes count (check if table and column exist)
        $check_used_shoes = $conn->query("SHOW TABLES LIKE 'used_shoes'");
        if ($check_used_shoes && $check_used_shoes->num_rows > 0) {
            $check_status_column = $conn->query("SHOW COLUMNS FROM used_shoes LIKE 'status'");
            if ($check_status_column && $check_status_column->num_rows > 0) {
                $pending_result = $conn->query("SELECT COUNT(*) as count FROM used_shoes WHERE status = 'pending'");
                if ($pending_result) {
                    $pending_used_shoes = $pending_result->fetch_assoc()['count'];
                }
            }
        }
        
        // Get pending orders count (check if table exists and what columns are available)
        $check_orders = $conn->query("SHOW TABLES LIKE 'orders'");
        if ($check_orders && $check_orders->num_rows > 0) {
            // Check what columns exist in orders table
            $columns_result = $conn->query("SHOW COLUMNS FROM orders");
            $has_status = false;
            $has_order_status = false;
            
            if ($columns_result) {
                while ($column = $columns_result->fetch_assoc()) {
                    if ($column['Field'] == 'status') {
                        $has_status = true;
                    }
                    if ($column['Field'] == 'order_status') {
                        $has_order_status = true;
                    }
                }
            }
            
            // Query based on available columns
            if ($has_status) {
                $pending_orders_result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
            } elseif ($has_order_status) {
                $pending_orders_result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'");
            } else {
                // If no status column, just count recent orders (last 24 hours)
                $pending_orders_result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()");
            }
            
            if ($pending_orders_result) {
                $pending_orders = $pending_orders_result->fetch_assoc()['count'];
            }
        }
    } catch (Exception $e) {
        // Silently handle errors - don't break the page
        error_log("Sidebar query error: " . $e->getMessage());
    }
}

// Get current page for active states
$current_page = basename($_SERVER['PHP_SELF']);
$current_path = $_SERVER['PHP_SELF'];
?>

<!-- Professional Admin Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="fas fa-shoe-prints"></i>
            </div>
            <div class="brand-text">
                <h3 class="brand-title">ShoeStore</h3>
                <span class="brand-subtitle">Admin Panel</span>
            </div>
        </div>
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <div class="sidebar-content">
        <!-- User Profile Section -->
        <div class="sidebar-profile">
            <div class="profile-avatar">
                <img src="../../assets/images/avatar.jpg" alt="Admin" onerror="this.src='http://localhost/shoe_store/assets/images/avatar.jpg'">
                <div class="profile-status"></div>
            </div>
            <div class="profile-info">
                <h4 class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin User'); ?></h4>
                <span class="profile-role">Administrator</span>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="sidebar-nav">
            <div class="nav-section">
                <h5 class="nav-section-title">Main</h5>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="http://localhost/shoe_store/admin/dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" data-tooltip="Dashboard">
                            <div class="nav-icon">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                            <span class="nav-text">Dashboard</span>
                            <div class="nav-indicator"></div>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <h5 class="nav-section-title">Inventory</h5>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="http://localhost/shoe_store/admin/products/manage.php" class="nav-link <?php echo str_contains($current_path, '/products/') ? 'active' : ''; ?>" data-tooltip="Products">
                            <div class="nav-icon">
                                <i class="fas fa-shoe-prints"></i>
                            </div>
                            <span class="nav-text">Products</span>
                            <div class="nav-indicator"></div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="http://localhost/shoe_store/admin/used_shoes/manage.php" class="nav-link <?php echo str_contains($current_path, '/used_shoes/') ? 'active' : ''; ?>" data-tooltip="Used Shoes">
                            <div class="nav-icon">
                                <i class="fas fa-recycle"></i>
                            </div>
                            <span class="nav-text">Used Shoes</span>
                            <?php if($pending_used_shoes > 0): ?>
                                <span class="nav-badge"><?php echo $pending_used_shoes; ?></span>
                            <?php endif; ?>
                            <div class="nav-indicator"></div>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <h5 class="nav-section-title">Sales</h5>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="http://localhost/shoe_store/admin/orders/manage.php" class="nav-link <?php echo str_contains($current_path, '/orders/') ? 'active' : ''; ?>" data-tooltip="Orders">
                            <div class="nav-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <span class="nav-text">Orders</span>
                            <?php if($pending_orders > 0): ?>
                                <span class="nav-badge"><?php echo $pending_orders; ?></span>
                            <?php endif; ?>
                            <div class="nav-indicator"></div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../reports/sales.php" class="nav-link disabled <?php echo str_contains($current_path, '/reports/') ? 'active' : ''; ?>" data-tooltip="Reports">
                            <div class="nav-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <span class="nav-text">Reports</span>
                            <div class="nav-indicator"></div>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <h5 class="nav-section-title">Management</h5>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="http://localhost/shoe_store/admin/users/manage.php" class="nav-link <?php echo str_contains($current_path, '/users/') ? 'active' : ''; ?>" data-tooltip="Users">
                            <div class="nav-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <span class="nav-text">Users</span>
                            <div class="nav-indicator"></div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../categories/manage.php" class="nav-link disabled <?php echo str_contains($current_path, '/categories/') ? 'active' : ''; ?>" data-tooltip="Categories">
                            <div class="nav-icon">
                                <i class="fas fa-tags"></i>
                            </div>
                            <span class="nav-text">Categories</span>
                            <div class="nav-indicator"></div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../brands/manage.php" class="nav-link disabled <?php echo str_contains($current_path, '/brands/') ? 'active' : ''; ?>" data-tooltip="Brands">
                            <div class="nav-icon">
                                <i class="fas fa-copyright"></i>
                            </div>
                            <span class="nav-text">Brands</span>
                            <div class="nav-indicator"></div>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <h5 class="nav-section-title">System</h5>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="../settings/general.php" class="nav-link disabled <?php echo str_contains($current_path, '/settings/') ? 'active' : ''; ?>" data-tooltip="Settings">
                            <div class="nav-icon">
                                <i class="fas fa-cog"></i>
                            </div>
                            <span class="nav-text">Settings</span>
                            <div class="nav-indicator"></div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../backup/manage.php" class="nav-link disabled <?php echo str_contains($current_path, '/backup/') ? 'active' : ''; ?>" data-tooltip="Backup">
                            <div class="nav-icon">
                                <i class="fas fa-database"></i>
                            </div>
                            <span class="nav-text">Backup</span>
                            <div class="nav-indicator"></div>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="footer-actions">
            <a href="http://localhost/shoe_store/pages/user/profile.php" class="footer-action" title="My Profile">
                <i class="fas fa-user"></i>
                <span class="action-text">Profile</span>
            </a>
            <a href="" class="footer-action" title="View Store">
                <i class="fas fa-external-link-alt"></i>
                <span class="action-text">Store</span>
            </a>
            <a href="http://localhost/shoe_store/pages/auth/logout.php" class="footer-action logout" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
                <span class="action-text">Logout</span>
            </a>
        </div>
        
        <div class="sidebar-version">
            <span class="version-text">v2.1.0</span>
        </div>
    </div>
</aside>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('adminSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    // Toggle sidebar
    function toggleSidebar() {
        sidebar.classList.toggle('collapsed');
        document.body.classList.toggle('sidebar-collapsed');
        
        // Save state
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }
    
    // Toggle sidebar on mobile
    function toggleMobileSidebar() {
        sidebar.classList.toggle('mobile-open');
        sidebarOverlay.classList.toggle('show');
        document.body.classList.toggle('sidebar-mobile-open');
    }
    
    // Event listeners
    sidebarToggle.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            toggleMobileSidebar();
        } else {
            toggleSidebar();
        }
    });
    
    // Close mobile sidebar when clicking overlay
    sidebarOverlay.addEventListener('click', function() {
        toggleMobileSidebar();
    });
    
    // Load saved state
    const savedState = localStorage.getItem('sidebarCollapsed');
    if (savedState === 'true' && window.innerWidth > 768) {
        sidebar.classList.add('collapsed');
        document.body.classList.add('sidebar-collapsed');
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('mobile-open');
            sidebarOverlay.classList.remove('show');
            document.body.classList.remove('sidebar-mobile-open');
        }
    });
    
    // Add tooltip data attributes for collapsed state
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        const text = link.querySelector('.nav-text');
        if (text && !link.hasAttribute('data-tooltip')) {
            link.setAttribute('data-tooltip', text.textContent.trim());
        }
    });
});
</script>