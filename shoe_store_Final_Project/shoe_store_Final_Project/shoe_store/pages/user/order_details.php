<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';

// Only allow logged-in users
requireLogin();

if(!isset($_GET['id'])) {
    header("Location: orders.php");
    exit;
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get order details
$order_query = "SELECT o.*, u.name as customer_name, u.email, u.phone 
               FROM orders o
               JOIN users u ON o.user_id = u.id
               WHERE o.id = ? AND o.user_id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$order) {
    header("Location: orders.php");
    exit;
}

// Get order items
$items_query = "SELECT oi.*, 
               IF(oi.product_type = 'new', p.name, u.name) as name,
               IF(oi.product_type = 'new', p.image, u.image) as image,
               IF(oi.product_type = 'new', b.name, u.brand) as brand
               FROM order_items oi
               LEFT JOIN products p ON oi.product_id = p.id AND oi.product_type = 'new'
               LEFT JOIN brands b ON p.brand_id = b.id
               LEFT JOIN used_shoes u ON oi.product_id = u.id AND oi.product_type = 'used'
               WHERE oi.order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result();
$stmt->close();

// Calculate shipping and tax (for display purposes)
$subtotal = $order['total_amount'] - ($order['total_amount'] * 0.08) - ($order['total_amount'] > 55.99 ? 0 : 5.99);
$shipping = $order['total_amount'] > 55.99 ? 0 : 5.99;
$tax = $order['total_amount'] * 0.08;
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <?php include 'user_menu.php'; ?>
        </div>
        
        <div class="col-md-9">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Order #<?php echo $order['order_number']; ?></h5>
                    <span class="badge bg-light text-dark">
                        <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Shipping Information</h6>
                            <address>
                                <strong><?php echo $order['shipping_name']; ?></strong><br>
                                <?php echo $order['shipping_address']; ?><br>
                                <?php echo $order['shipping_city']; ?>, <?php echo $order['shipping_state']; ?> <?php echo $order['shipping_zip']; ?><br>
                                Phone: <?php echo $order['shipping_phone']; ?>
                            </address>
                        </div>
                        <div class="col-md-6">
                            <h6>Billing Information</h6>
                            <address>
                                <strong><?php echo $order['billing_name']; ?></strong><br>
                                <?php echo $order['billing_address']; ?><br>
                                <?php echo $order['billing_city']; ?>, <?php echo $order['billing_state']; ?> <?php echo $order['billing_zip']; ?>
                            </address>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Payment Method</h6>
                            <p>
                                <?php echo strtoupper($order['payment_method']); ?>
                                <?php if($order['card_last4']): ?>
                                    (•••• <?php echo $order['card_last4']; ?>)
                                <?php endif; ?>
                            </p>
                            <p>
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
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Order Status</h6>
                            <p>
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
                            </p>
                            <?php if($order['order_status'] == 'shipped' && $order['tracking_number']): ?>
                                <p>
                                    <strong>Tracking Number:</strong> <?php echo $order['tracking_number']; ?><br>
                                    <strong>Carrier:</strong> <?php echo $order['shipping_carrier']; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <h5 class="mb-3">Order Items</h5>
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
                                                <img src="../../assets/images/<?php echo $item['product_type'] == 'new' ? 'uploads/' : 'used_shoes/'; ?><?php echo $item['image']; ?>" 
                                                     width="60" height="60" class="me-3 img-thumbnail">
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                    <small class="text-muted"><?php echo htmlspecialchars($item['brand']); ?></small><br>
                                                    <small class="text-muted"><?php echo ucfirst($item['product_type']); ?> product</small>
                                                </div>
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
                    
                    <div class="row justify-content-end">
                        <div class="col-md-5">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Order Summary</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Shipping:</span>
                                        <span>$<?php echo number_format($shipping, 2); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tax (8%):</span>
                                        <span>$<?php echo number_format($tax, 2); ?></span>
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
                <div class="card-footer">
                    <a href="orders.php" class="btn btn-outline-secondary">Back to Orders</a>
                    <?php if($order['order_status'] == 'processing'): ?>
                        <button class="btn btn-danger float-end cancel-order" data-id="<?php echo $order['id']; ?>">Cancel Order</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Cancel order button
    $('.cancel-order').click(function() {
        if(confirm('Are you sure you want to cancel this order?')) {
            const orderId = $(this).data('id');
            
            $.ajax({
                url: '../../functions/orders.php?action=cancel',
                method: 'POST',
                data: { id: orderId },
                success: function(response) {
                    const result = JSON.parse(response);
                    if(result.success) {
                        alert('Order has been cancelled');
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