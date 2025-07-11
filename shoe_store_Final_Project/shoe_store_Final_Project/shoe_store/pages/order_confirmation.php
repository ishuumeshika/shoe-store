<?php
require_once '../includes/header.php';
require_once '../includes/auth.php';

// Only allow logged-in users
requireLogin();

// Check if order number is set
if(!isset($_SESSION['order_number'])) {
    header("Location: ../index.php");
    exit;
}

$order_number = $_SESSION['order_number'];
unset($_SESSION['order_number']); // Clear the session variable

// Get order details
$order_query = "SELECT o.*, u.name as customer_name, u.email 
               FROM orders o
               JOIN users u ON o.user_id = u.id
               WHERE o.order_number = ? AND o.user_id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("si", $order_number, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get order items
$items_query = "SELECT oi.*, 
               IF(oi.product_type = 'new', p.name, u.name) as name,
               IF(oi.product_type = 'new', p.image, u.image) as image
               FROM order_items oi
               LEFT JOIN products p ON oi.product_id = p.id AND oi.product_type = 'new'
               LEFT JOIN used_shoes u ON oi.product_id = u.id AND oi.product_type = 'used'
               WHERE oi.order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order['id']);
$stmt->execute();
$order_items = $stmt->get_result();
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card text-center">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0">Order Confirmed!</h3>
                </div>
                <div class="card-body">
                    <i class="fas fa-check-circle fa-5x text-success mb-4"></i>
                    <h4 class="card-title">Thank You for Your Order</h4>
                    <p class="card-text">Your order has been placed successfully. We've sent a confirmation email to <strong><?php echo $order['email']; ?></strong>.</p>
                    
                    <div class="alert alert-info text-start">
                        <h5>Order Details</h5>
                        <p><strong>Order Number:</strong> <?php echo $order['order_number']; ?></p>
                        <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                        <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                        <p><strong>Payment Method:</strong> <?php echo strtoupper($order['payment_method']); ?>
                            <?php if($order['card_last4']): ?>
                                (•••• <?php echo $order['card_last4']; ?>)
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <h5 class="mt-4">Shipping Information</h5>
                    <address class="mb-4">
                        <?php echo $order['shipping_name']; ?><br>
                        <?php echo $order['shipping_address']; ?><br>
                        <?php echo $order['shipping_city']; ?>, <?php echo $order['shipping_state']; ?> <?php echo $order['shipping_zip']; ?><br>
                        Phone: <?php echo $order['shipping_phone']; ?>
                    </address>
                    
                    <a href="../index.php" class="btn btn-primary me-2">Continue Shopping</a>
                    <a href="user/orders.php" class="btn btn-outline-secondary">View My Orders</a>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($item = $order_items->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="../assets/images/<?php echo $item['product_type'] == 'new' ? 'uploads/' : 'used_shoes/'; ?><?php echo $item['image']; ?>" 
                                                     width="50" height="50" class="me-2">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </div>
                                        </td>
                                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($order['total_amount'] - ($order['total_amount'] * 0.08) - ($order['total_amount'] > 55.99 ? 0 : 5.99), 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <span>$<?php echo number_format($order['total_amount'] > 55.99 ? 0 : 5.99, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax (8%):</span>
                        <span>$<?php echo number_format($order['total_amount'] * 0.08, 2); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total:</span>
                        <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>