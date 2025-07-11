<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';

// Only allow logged-in users
requireLogin();

$user_id = $_SESSION['user_id'];

// Get user orders
// Change this line in your query:
$orders_query = "SELECT o.id, o.order_number, o.total_amount, o.order_status, o.created_at 
                 FROM orders o 
                 WHERE o.user_id = ? 
                 ORDER BY o.created_at DESC";
$stmt = $conn->prepare($orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <!-- Same user menu as profile.php -->
            <?php include 'user_menu.php'; ?>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">My Orders</h5>
                </div>
                <div class="card-body">
                    <?php if($orders_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($order = $orders_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $order['order_number']; ?></td>
                                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
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
                    <?php else: ?>
                        <div class="alert alert-info">You haven't placed any orders yet.</div>
                        <a href="../../index.php" class="btn btn-primary">Start Shopping</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>