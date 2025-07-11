<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';

// Only allow admin access
requireAdmin();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$start = ($page > 1) ? ($page * $per_page) - $per_page : 0;

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build base query
$query = "SELECT SQL_CALC_FOUND_ROWS o.id, o.order_number, o.total_amount, o.order_status as order_status, 
          o.payment_method as payment_status, o.created_at, u.name as customer_name
          FROM orders o
          JOIN users u ON o.user_id = u.id";
// Add filters
$where = [];
$params = [];
$types = '';

if($status_filter) {
    $where[] = "o.order_status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if($search_query) {
    $where[] = "(o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $types .= 'sss';
}

if(!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

// Complete query with sorting and pagination
$query .= " ORDER BY o.created_at DESC LIMIT $start, $per_page";

// Prepare and execute
$stmt = $conn->prepare($query);

if($types) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$orders = $stmt->get_result();

// Get total count
$total = $conn->query("SELECT FOUND_ROWS() as total")->fetch_assoc()['total'];
$pages = ceil($total / $per_page);
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Orders</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Order Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $status_filter == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $status_filter == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   placeholder="Order #, Customer Name or Email" value="<?php echo htmlspecialchars($search_query); ?>">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="manage.php" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <?php if($orders->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($order = $orders->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $order['order_number']; ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?php 
                                                        switch($order['payment_status']) {
                                                            case 'paid': echo 'bg-success'; break;
                                                            case 'pending': echo 'bg-warning text-dark'; break;
                                                            case 'failed': echo 'bg-danger'; break;
                                                            default: echo 'bg-secondary';
                                                        }
                                                    ?>">
                                                    <?php echo ucfirst($order['payment_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    <?php 
                                                        switch($order['order_status']) {
                                                            case 'processing': echo 'bg-info'; break;
                                                            case 'shipped': echo 'bg-primary'; break;
                                                            case 'delivered': echo 'bg-success'; break;
                                                            case 'cancelled': echo 'bg-danger'; break;
                                                            default: echo 'bg-secondary';
                                                        }
                                                    ?>">
                                                    <?php echo ucfirst($order['order_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <!-- <?php if($order['order_status'] != 'delivered' && $order['order_status'] != 'cancelled'): ?>
                                                    <button class="btn btn-sm btn-success accept-order" data-id="<?php echo $order['id']; ?>" data-status="<?php echo $order['order_status']; ?>" title="Accept">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger cancel-order" data-id="<?php echo $order['id']; ?>" data-status="<?php echo $order['order_status']; ?>" title="Cancel">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?> -->
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if($pages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mt-4">
                                    <?php if($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search_query); ?>" aria-label="Previous">
                                                <span aria-hidden="true">«</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for($i = 1; $i <= $pages; $i++): ?>
                                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search_query); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if($page < $pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search_query); ?>" aria-label="Next">
                                                <span aria-hidden="true">»</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info text-center py-4">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <h5>No orders found</h5>
                            <p>There are no orders matching your criteria.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
$(document).ready(function() {
    // Accept order
    $('.accept-order').click(function(e) {
        e.preventDefault();
        
        const orderId = $(this).data('id');
        const currentStatus = $(this).data('status');
        
        let trackingNumber = '';
        let shippingCarrier = '';
        
        if(currentStatus !== 'shipped') {
            trackingNumber = prompt('Enter tracking number:');
            if(trackingNumber === null) return;
            
            shippingCarrier = prompt('Enter shipping carrier:');
            if(shippingCarrier === null) return;
        }
        
        $.ajax({
            url: 'update_status.php',
            method: 'POST',
            data: { 
                id: orderId,
                status: 'shipped',
                tracking_number: trackingNumber,
                shipping_carrier: shippingCarrier,
                current_status: currentStatus
            },
            success: function(response) {
                const result = JSON.parse(response);
                if(result.success) {
                    alert('Order status updated to shipped');
                    location.reload();
                } else {
                    alert(result.message);
                }
            }
        });
    });
    
    // Cancel order
    $('.cancel-order').click(function(e) {
        e.preventDefault();
        
        if(confirm('Are you sure you want to cancel this order?')) {
            const orderId = $(this).data('id');
            const currentStatus = $(this).data('status');
            
            $.ajax({
                url: 'update_status.php',
                method: 'POST',
                data: { 
                    id: orderId,
                    status: 'cancelled',
                    current_status: currentStatus
                },
                success: function(response) {
                    const result = JSON.parse(response);
                    if(result.success) {
                        alert('Order status updated to cancelled');
                        location.reload();
                    } else {
                        alert(result.message);
                    }
                }
            });
        }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>