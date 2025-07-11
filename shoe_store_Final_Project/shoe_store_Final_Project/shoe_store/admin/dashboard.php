<?php
require_once '../includes/header.php';
require_once '../includes/auth.php';

// Only allow admin access
requireAdmin();

// Get statistics
$users_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch_assoc()['count'];
$products_count = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$orders_count = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$pending_used_shoes = $conn->query("SELECT COUNT(*) as count FROM used_shoes WHERE status = 'pending'")->fetch_assoc()['count'];

// Get recent orders
$recent_orders = $conn->query("SELECT o.id, o.order_number, u.name as customer, o.total_amount, o.order_status, o.created_at
                               FROM orders o 
                               JOIN users u ON o.user_id = u.id 
                               ORDER BY o.created_at DESC LIMIT 5");

// Get monthly sales data (placeholder for chart)
$monthly_sales = $conn->query("SELECT MONTH(created_at) as month, SUM(total_amount) as total 
                               FROM orders 
                               WHERE YEAR(created_at) = YEAR(CURDATE()) 
                               GROUP BY MONTH(created_at) 
                               ORDER BY month");
?>

<!-- Professional Admin Dashboard -->
<div class="admin-dashboard">
    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <!-- <aside class="dashboard-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-shoe-prints"></i>
                    <span class="logo-text">Admin Panel</span>
                </div>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link active">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="products/manage.php" class="nav-link">
                            <i class="fas fa-shoe-prints"></i>
                            <span class="nav-text">Products</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="orders/manage.php" class="nav-link">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="nav-text">Orders</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="used_shoes/manage.php" class="nav-link">
                            <i class="fas fa-recycle"></i>
                            <span class="nav-text">Used Shoes</span>
                            <?php if($pending_used_shoes > 0): ?>
                                <span class="nav-badge"><?php echo $pending_used_shoes; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="reports/sales.php" class="nav-link">
                            <i class="fas fa-chart-line"></i>
                            <span class="nav-text">Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="users/manage.php" class="nav-link">
                            <i class="fas fa-users"></i>
                            <span class="nav-text">Users</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="settings/general.php" class="nav-link">
                            <i class="fas fa-cog"></i>
                            <span class="nav-text">Settings</span>
                        </a>
                    </li>
                </ul>
                
                <div class="sidebar-footer">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="../pages/user/profile.php" class="nav-link">
                                <i class="fas fa-user"></i>
                                <span class="nav-text">My Profile</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../pages/auth/logout.php" class="nav-link">
                                <i class="fas fa-sign-out-alt"></i>
                                <span class="nav-text">Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </aside> -->

        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="dashboard-main">
            <!-- Top Header -->
            <header class="dashboard-header">
                <div class="header-left">
                    <h1 class="page-title">Dashboard</h1>
                    <p class="page-subtitle">Welcome back! Here's what's happening with your store.</p>
                </div>
                <div class="header-right">
                    <div class="header-actions">
                        <button class="btn btn-outline">
                            <i class="fas fa-download"></i>
                            Export
                        </button>
                        <button class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Add Product
                        </button>
                    </div>
                    <div class="date-filter">
                        <button class="date-filter-btn">
                            <i class="fas fa-calendar"></i>
                            This Week
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Statistics Cards -->
            <section class="stats-section">
                <div class="stats-grid">
                    <div class="stat-card customers">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h3 class="stat-title">Total Customers</h3>
                                <div class="stat-number"><?php echo number_format($users_count); ?></div>
                                <div class="stat-change positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>+12% from last month</span>
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="stat-footer">
                            <a href="users/manage.php" class="stat-link">
                                View All Customers
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="stat-card products">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h3 class="stat-title">Total Products</h3>
                                <div class="stat-number"><?php echo number_format($products_count); ?></div>
                                <div class="stat-change positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>+8% from last month</span>
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-shoe-prints"></i>
                            </div>
                        </div>
                        <div class="stat-footer">
                            <a href="products/manage.php" class="stat-link">
                                Manage Products
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="stat-card orders">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h3 class="stat-title">Total Orders</h3>
                                <div class="stat-number"><?php echo number_format($orders_count); ?></div>
                                <div class="stat-change positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>+23% from last month</span>
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                        <div class="stat-footer">
                            <a href="orders/manage.php" class="stat-link">
                                View All Orders
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="stat-card pending">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h3 class="stat-title">Pending Reviews</h3>
                                <div class="stat-number"><?php echo number_format($pending_used_shoes); ?></div>
                                <?php if($pending_used_shoes > 0): ?>
                                    <div class="stat-change urgent">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <span>Requires attention</span>
                                    </div>
                                <?php else: ?>
                                    <div class="stat-change neutral">
                                        <i class="fas fa-check"></i>
                                        <span>All caught up!</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-recycle"></i>
                            </div>
                        </div>
                        <div class="stat-footer">
                            <a href="used_shoes/manage.php" class="stat-link">
                                Review Listings
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Recent Orders -->
                <section class="content-card recent-orders">
                    <div class="card-header">
                        <div class="card-title">
                            <h2>Recent Orders</h2>
                            <p>Latest customer orders and their status</p>
                        </div>
                        <div class="card-actions">
                            <a href="orders/manage.php" class="btn btn-outline btn-sm">View All</a>
                        </div>
                    </div>
                    
                    <div class="card-content">
                        <?php if($recent_orders->num_rows > 0): ?>
                            <div class="orders-table">
                                <div class="table-header">
                                    <div class="table-row">
                                        <div class="table-cell">Order</div>
                                        <div class="table-cell">Customer</div>
                                        <div class="table-cell">Amount</div>
                                        <div class="table-cell">Status</div>
                                        <div class="table-cell">Date</div>
                                        <div class="table-cell">Action</div>
                                    </div>
                                </div>
                                <div class="table-body">
                                    <?php while($order = $recent_orders->fetch_assoc()): ?>
                                        <div class="table-row">
                                            <div class="table-cell">
                                                <div class="order-info">
                                                    <span class="order-number">#<?php echo $order['order_number']; ?></span>
                                                </div>
                                            </div>
                                            <div class="table-cell">
                                                <div class="customer-info">
                                                    <span class="customer-name"><?php echo htmlspecialchars($order['customer']); ?></span>
                                                </div>
                                            </div>
                                            <div class="table-cell">
                                                <span class="amount">$<?php echo number_format($order['total_amount'], 2); ?></span>
                                            </div>
                                            <div class="table-cell">
                                                <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                                    <?php echo ucfirst($order['order_status']); ?>
                                                </span>
                                            </div>
                                            <div class="table-cell">
                                                <span class="order-date"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></span>
                                            </div>
                                            <div class="table-cell">
                                                <a href="orders/details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">
                                                    View
                                                </a>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-shopping-cart"></i>
                                <h3>No Recent Orders</h3>
                                <p>When customers place orders, they'll appear here.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Sales Chart -->
                <section class="content-card sales-chart">
                    <div class="card-header">
                        <div class="card-title">
                            <h2>Sales Overview</h2>
                            <p>Monthly sales performance</p>
                        </div>
                        <div class="card-actions">
                            <select class="chart-filter">
                                <option>Last 12 months</option>
                                <option>Last 6 months</option>
                                <option>Last 3 months</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="card-content">
                        <div class="chart-container">
                            <canvas id="salesChart" width="400" height="200"></canvas>
                        </div>
                        <div class="chart-legend">
                            <div class="legend-item">
                                <div class="legend-color primary"></div>
                                <span>Sales Revenue</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color secondary"></div>
                                <span>Target</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Quick Actions -->
                <section class="content-card quick-actions">
                    <div class="card-header">
                        <div class="card-title">
                            <h2>Quick Actions</h2>
                            <p>Common administrative tasks</p>
                        </div>
                    </div>
                    
                    <div class="card-content">
                        <div class="actions-grid">
                            <a href="products/add.php" class="action-item">
                                <div class="action-icon">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="action-content">
                                    <h4>Add Product</h4>
                                    <p>Add new shoes to inventory</p>
                                </div>
                            </a>
                            
                            <a href="orders/manage.php" class="action-item">
                                <div class="action-icon">
                                    <i class="fas fa-truck"></i>
                                </div>
                                <div class="action-content">
                                    <h4>Process Orders</h4>
                                    <p>Update order status</p>
                                </div>
                            </a>
                            
                            <a href="used_shoes/manage.php" class="action-item">
                                <div class="action-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="action-content">
                                    <h4>Review Listings</h4>
                                    <p>Approve used shoe listings</p>
                                </div>
                            </a>
                            
                            <a href="reports/sales.php" class="action-item">
                                <div class="action-icon">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <div class="action-content">
                                    <h4>View Reports</h4>
                                    <p>Analyze sales data</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</div>

<!-- Chart.js for Sales Chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Sidebar toggle functionality
    $('#sidebarToggle').click(function() {
        $('.dashboard-sidebar').toggleClass('collapsed');
        $('.dashboard-main').toggleClass('expanded');
    });
    
    // Initialize sales chart
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Sales Revenue',
                data: [
                    <?php 
                    $sales_data = array_fill(0, 12, 0);
                    while($sale = $monthly_sales->fetch_assoc()) {
                        $sales_data[$sale['month'] - 1] = $sale['total'];
                    }
                    echo implode(',', $sales_data);
                    ?>
                ],
                borderColor: '#1a1a1a',
                backgroundColor: 'rgba(26, 26, 26, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f0f0f0'
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            elements: {
                point: {
                    radius: 6,
                    hoverRadius: 8,
                    backgroundColor: '#1a1a1a',
                    borderColor: '#fff',
                    borderWidth: 2
                }
            }
        }
    });
    
    // Auto-refresh dashboard data every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);
});
</script>

<?php require_once '../includes/footer.php'; ?>