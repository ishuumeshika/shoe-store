<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';

// Only allow logged-in users
requireLogin();

$user_id = $_SESSION['user_id'];

// Get user orders with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$start = ($page > 1) ? ($page * $per_page) - $per_page : 0;

// Get total orders
$total = $conn->query("SELECT COUNT(*) as total FROM orders WHERE user_id = $user_id")->fetch_assoc()['total'];
$pages = ceil($total / $per_page);

// Get orders with pagination
$query = "SELECT o.id, o.order_number, o.total_amount, o.order_status, o.payment_status, o.created_at 
          FROM orders o
          WHERE o.user_id = $user_id
          ORDER BY o.created_at DESC
          LIMIT $start, $per_page";
$orders = $conn->query($query);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <?php include 'user_menu.php'; ?>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">My Orders</h5>
                </div>
                <div class="card-body">
                    <?php if($orders->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($order = $orders->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $order['order_number']; ?></td>
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
                                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if($pages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php if($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for($i = 1; $i <= $pages; $i++): ?>
                                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if($page < $pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
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
                            <p>You haven't placed any orders yet.</p>
                            <a href="../../index.php" class="btn btn-primary">Start Shopping</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>