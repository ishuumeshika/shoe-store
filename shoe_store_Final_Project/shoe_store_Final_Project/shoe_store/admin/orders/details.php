<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';

// Only allow admin access
requireAdmin();

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage.php');
    exit;
}

$order_id = (int)$_GET['id'];

// Get order details
$order_query = "SELECT o.*, u.name as customer_name, u.email as customer_email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: manage.php');
    exit;
}

// Get order items
$items_query = "SELECT oi.*, p.name as product_name, p.image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$items = $stmt->get_result();
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Order Details #<?php echo $order['order_number']; ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="invoice.php?id=<?php echo $order_id; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-file-invoice"></i> Generate Invoice
                        </a>
                        <a href="manage.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Order Items</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Type</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($item = $items->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($item['image']): ?>
                                                    <img src="../../images/products/<?php echo htmlspecialchars($item['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                         class="img-thumbnail me-3" width="60">
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                                        <small class="text-muted">SKU: <?php echo $item['product_id']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo ucfirst($item['product_type']); ?></td>
                                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-end fw-bold">Subtotal</td>
                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end fw-bold">Shipping</td>
                                            <td>$0.00</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end fw-bold">Total</td>
                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Order Notes</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="update_notes.php">
                                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                <div class="mb-3">
                                    <textarea class="form-control" name="notes" rows="3" 
                                              placeholder="Add private notes about this order..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Notes</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Order Number:</span>
                                    <span><?php echo $order['order_number']; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Date:</span>
                                    <span><?php echo date('M j, Y \a\t g:i A', strtotime($order['created_at'])); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Status:</span>
                                    <span>
                                        <span class="badge 
                                            <?php 
                                                switch($order['status']) {
                                                    case 'processing': echo 'bg-info'; break;
                                                    case 'shipped': echo 'bg-primary'; break;
                                                    case 'delivered': echo 'bg-success'; break;
                                                    case 'cancelled': echo 'bg-danger'; break;
                                                    default: echo 'bg-secondary';
                                                }
                                            ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Payment Method:</span>
                                    <span><?php echo strtoupper($order['payment_method']); ?></span>
                                </li>
                                <?php if ($order['payment_method'] == 'card' && !empty($order['card_last4'])): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Card:</span>
                                    <span>**** **** **** <?php echo $order['card_last4']; ?></span>
                                </li>
                                <?php endif; ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Customer:</span>
                                    <span><?php echo htmlspecialchars($order['customer_name']); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Email:</span>
                                    <span><?php echo htmlspecialchars($order['customer_email']); ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Shipping Address</h5>
                        </div>
                        <div class="card-body">
                            <address>
                                <strong><?php echo htmlspecialchars($order['shipping_name']); ?></strong><br>
                                <?php echo htmlspecialchars($order['shipping_address']); ?><br>
                                <?php echo htmlspecialchars($order['shipping_city']); ?>, 
                                <?php echo htmlspecialchars($order['shipping_state']); ?> 
                                <?php echo htmlspecialchars($order['shipping_zip']); ?><br>
                                <abbr title="Phone">P:</abbr> <?php echo htmlspecialchars($order['shipping_phone']); ?>
                            </address>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Billing Address</h5>
                        </div>
                        <div class="card-body">
                            <address>
                                <strong><?php echo htmlspecialchars($order['billing_name']); ?></strong><br>
                                <?php echo htmlspecialchars($order['billing_address']); ?><br>
                                <?php echo htmlspecialchars($order['billing_city']); ?>, 
                                <?php echo htmlspecialchars($order['billing_state']); ?> 
                                <?php echo htmlspecialchars($order['billing_zip']); ?><br>
                            </address>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>