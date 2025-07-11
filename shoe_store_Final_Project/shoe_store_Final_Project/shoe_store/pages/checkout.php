<?php
require_once '../includes/header.php';
require_once '../includes/auth.php';

// Only allow logged-in users with items in cart
requireLogin();

$user_id = $_SESSION['user_id'];

// Get user details
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

// Get cart items
$cart_query = "SELECT c.id as cart_id, c.product_id, c.product_type, c.quantity, c.price,
              IF(c.product_type = 'new', p.name, u.name) as name,
              IF(c.product_type = 'new', p.image, u.image) as image
              FROM cart c
              LEFT JOIN products p ON c.product_id = p.id AND c.product_type = 'new'
              LEFT JOIN used_shoes u ON c.product_id = u.id AND c.product_type = 'used'
              WHERE c.user_id = ?";
$cart_stmt = $conn->prepare($cart_query);
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_items = $cart_stmt->get_result();

// Calculate totals
$totals_query = "SELECT SUM(quantity * price) as subtotal, COUNT(*) as items_count 
                FROM cart 
                WHERE user_id = ?";
$totals_stmt = $conn->prepare($totals_query);
$totals_stmt->bind_param("i", $user_id);
$totals_stmt->execute();
$totals = $totals_stmt->get_result()->fetch_assoc();
$totals_stmt->close();

// Redirect if cart is empty
if($cart_items->num_rows == 0) {
    header("Location: cart.php");
    exit;
}

$subtotal = $totals['subtotal'] ?? 0;
$shipping = $subtotal > 50 ? 0 : 5.99; // Free shipping over $50
$tax = $subtotal * 0.08; // Example 8% tax
$total = $subtotal + $shipping + $tax;

// Process checkout form
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize inputs
    $required_fields = [
        'shipping_name', 'shipping_address', 'shipping_city', 
        'shipping_state', 'shipping_zip', 'shipping_phone'
    ];
    
    $errors = [];
    
    foreach($required_fields as $field) {
        if(empty($_POST[$field])) {
            $errors[$field] = 'This field is required';
        }
    }
    
    // Validate payment method
    $payment_method = $_POST['payment_method'] ?? '';
    if(!in_array($payment_method, ['card', 'paypal', 'cod'])) {
        $errors['payment_method'] = 'Invalid payment method';
    }
    
    // Validate card details if payment is by card
    if($payment_method == 'card') {
        $card_number = str_replace(' ', '', $_POST['card_number'] ?? '');
        $card_expiry = $_POST['card_expiry'] ?? '';
        $card_cvv = $_POST['card_cvv'] ?? '';
        
        if(!preg_match('/^\d{16}$/', $card_number)) {
            $errors['card_number'] = 'Invalid card number';
        }
        
        if(!preg_match('/^\d{3,4}$/', $card_cvv)) {
            $errors['card_cvv'] = 'Invalid CVV';
        }
        
        if(!preg_match('/^\d{2}\/\d{2}$/', $card_expiry)) {
            $errors['card_expiry'] = 'Invalid expiry date';
        }
    }
    
    if(!empty($errors)) {
        $error_message = "Please correct the following errors:";
        // You could display the specific errors to the user
    } else {
        // Process the order if no validation errors
        $shipping_name = trim($_POST['shipping_name']);
        $shipping_address = trim($_POST['shipping_address']);
        $shipping_city = trim($_POST['shipping_city']);
        $shipping_state = trim($_POST['shipping_state']);
        $shipping_zip = trim($_POST['shipping_zip']);
        $shipping_phone = trim($_POST['shipping_phone']);
        $billing_same = isset($_POST['billing_same']) ? 1 : 0;
        $billing_name = $billing_same ? $shipping_name : trim($_POST['billing_name']);
        $billing_address = $billing_same ? $shipping_address : trim($_POST['billing_address']);
        $billing_city = $billing_same ? $shipping_city : trim($_POST['billing_city']);
        $billing_state = $billing_same ? $shipping_state : trim($_POST['billing_state']);
        $billing_zip = $billing_same ? $shipping_zip : trim($_POST['billing_zip']);
        $card_last4 = $payment_method == 'card' ? substr($card_number, -4) : null;
        
        // Generate order number
        $order_number = 'ORD-' . strtoupper(uniqid());
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // 1. Create order
            $order_stmt = $conn->prepare("INSERT INTO orders 
                                        (user_id, order_number, total_amount, shipping_name, shipping_address, 
                                        shipping_city, shipping_state, shipping_zip, shipping_phone, 
                                        billing_name, billing_address, billing_city, billing_state, billing_zip, 
                                        payment_method, card_last4)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $order_stmt->bind_param("isssssssssssssss", 
                $user_id, $order_number, $total, $shipping_name, $shipping_address, 
                $shipping_city, $shipping_state, $shipping_zip, $shipping_phone,
                $billing_name, $billing_address, $billing_city, $billing_state, $billing_zip,
                $payment_method, $card_last4
            );
            
            if(!$order_stmt->execute()) {
                throw new Exception("Failed to create order: " . $order_stmt->error);
            }
            
            $order_id = $conn->insert_id;
            $order_stmt->close();
            
            // 2. Add order items and update inventory
            $cart_items->data_seek(0); // Reset pointer
            while($item = $cart_items->fetch_assoc()) {
                // Add to order items
                $item_stmt = $conn->prepare("INSERT INTO order_items 
                                            (order_id, product_id, product_type, quantity, price)
                                            VALUES (?, ?, ?, ?, ?)");
                $item_stmt->bind_param("iisid", $order_id, $item['product_id'], $item['product_type'], $item['quantity'], $item['price']);
                
                if(!$item_stmt->execute()) {
                    throw new Exception("Failed to add order item: " . $item_stmt->error);
                }
                $item_stmt->close();
                
                // Update inventory for new products
                // if($item['product_type'] == 'new') {
                //     $update_stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
                //     $update_stmt->bind_param("iii", $item['quantity'], $item['product_id'], $item['quantity']);
                    
                //     if(!$update_stmt->execute() || $conn->affected_rows == 0) {
                //         throw new Exception("Insufficient stock for product ID: " . $item['product_id']);
                //     }
                //     $update_stmt->close();
                // }
                if($item['product_type'] == 'new') {
    $update_stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
    $update_stmt->bind_param("iii", $item['quantity'], $item['product_id'], $item['quantity']);
    
    if(!$update_stmt->execute() || $conn->affected_rows == 0) {
        throw new Exception("Insufficient stock for product ID: " . $item['product_id']);
    }
    $update_stmt->close();
}
            }
            
            // 3. Clear cart
            $delete_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $delete_stmt->bind_param("i", $user_id);
            
            if(!$delete_stmt->execute()) {
                throw new Exception("Failed to clear cart: " . $delete_stmt->error);
            }
            $delete_stmt->close();
            
            // Commit transaction
            $conn->commit();
            
            // Set order number in session and redirect
            $_SESSION['order_number'] = $order_number;
            header("Location: order_confirmation.php");
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error_message = "Order processing failed: " . $e->getMessage();
            error_log("Checkout Error: " . $e->getMessage());
        }
    }
}
?>

<style>
/* Custom styles for professional checkout tabs */
.checkout-tabs {
    border: none;
    background: #f8f9fa;
    border-radius: 10px;
    padding: 8px;
    margin-bottom: 30px;
}

.checkout-tabs .nav-link {
    border: none;
    background: transparent;
    color: #6c757d;
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 8px;
    margin: 0 4px;
    transition: all 0.3s ease;
    position: relative;
}

.checkout-tabs .nav-link:hover {
    background: #e9ecef;
    color: #495057;
}

.checkout-tabs .nav-link.active {
    background: #007bff;
    color: white;
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
}

.checkout-tabs .nav-link.completed {
    background: #28a745;
    color: white;
}

.checkout-tabs .nav-link.completed::after {
    content: "✓";
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 12px;
}

.checkout-card {
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 12px;
}

.checkout-card .card-header {
    background: linear-gradient(135deg, #007bff, #0056b3);
    border-radius: 12px 12px 0 0;
    border: none;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    border: none;
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
}

.btn-success {
    background: linear-gradient(135deg, #28a745, #1e7e34);
    border: none;
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
}

.order-summary-card {
    position: sticky;
    top: 20px;
}

.step-indicator {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.step-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 10px;
}

.step-number.completed {
    background: #28a745;
}

.step-number.inactive {
    background: #6c757d;
}
</style>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card checkout-card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Secure Checkout</h5>
                </div>
                <div class="card-body">
                    <?php if(isset($error_message)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <ul class="nav nav-pills checkout-tabs" id="checkoutTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="shipping-tab" data-bs-toggle="pill" data-bs-target="#shipping" type="button">
                                <i class="fas fa-truck me-2"></i>Shipping
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="payment-tab" data-bs-toggle="pill" data-bs-target="#payment" type="button">
                                <i class="fas fa-credit-card me-2"></i>Payment
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="review-tab" data-bs-toggle="pill" data-bs-target="#review" type="button">
                                <i class="fas fa-check-circle me-2"></i>Review
                            </button>
                        </li>
                    </ul>
                    
                    <form method="POST" id="checkoutForm">
                        <div class="tab-content" id="checkoutTabsContent">
                            <!-- Shipping Tab -->
                            <div class="tab-pane fade show active" id="shipping" role="tabpanel">
                                <div class="step-indicator">
                                    <div class="step-number">1</div>
                                    <h5 class="mb-0">Shipping Information</h5>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="shipping_name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="shipping_name" name="shipping_name" 
                                               value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="shipping_phone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control" id="shipping_phone" name="shipping_phone" 
                                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="shipping_address" class="form-label">Street Address *</label>
                                    <input type="text" class="form-control" id="shipping_address" name="shipping_address" 
                                           value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="shipping_city" class="form-label">City *</label>
                                        <input type="text" class="form-control" id="shipping_city" name="shipping_city" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="shipping_state" class="form-label">State *</label>
                                        <input type="text" class="form-control" id="shipping_state" name="shipping_state" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="shipping_zip" class="form-label">ZIP Code *</label>
                                        <input type="text" class="form-control" id="shipping_zip" name="shipping_zip" required>
                                    </div>
                                </div>
                                
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="billing_same" name="billing_same" checked>
                                    <label class="form-check-label" for="billing_same">
                                        <i class="fas fa-check me-2"></i>Billing address same as shipping
                                    </label>
                                </div>
                                
                                <div class="text-end">
                                    <button type="button" class="btn btn-primary next-tab" data-next="payment">
                                        Continue to Payment <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Payment Tab -->
                            <div class="tab-pane fade" id="payment" role="tabpanel">
                                <div class="step-indicator">
                                    <div class="step-number inactive">2</div>
                                    <h5 class="mb-0">Payment Method</h5>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="form-check payment-option">
                                            <input class="form-check-input payment-method" type="radio" name="payment_method" id="creditCard" value="card" checked>
                                            <label class="form-check-label w-100" for="creditCard">
                                                <div class="card h-100 text-center p-3">
                                                    <i class="fas fa-credit-card fa-2x text-primary mb-2"></i>
                                                    <strong>Credit/Debit Card</strong>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check payment-option">
                                            <input class="form-check-input payment-method" type="radio" name="payment_method" id="paypal" value="paypal">
                                            <label class="form-check-label w-100" for="paypal">
                                                <div class="card h-100 text-center p-3">
                                                    <i class="fab fa-paypal fa-2x text-primary mb-2"></i>
                                                    <strong>PayPal</strong>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check payment-option">
                                            <input class="form-check-input payment-method" type="radio" name="payment_method" id="cod" value="cod">
                                            <label class="form-check-label w-100" for="cod">
                                                <div class="card h-100 text-center p-3">
                                                    <i class="fas fa-money-bill-wave fa-2x text-primary mb-2"></i>
                                                    <strong>Cash on Delivery</strong>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="cardDetails">
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label for="card_number" class="form-label">Card Number *</label>
                                            <input type="text" class="form-control" id="card_number" name="card_number" 
                                                   placeholder="1234 5678 9012 3456" data-inputmask="'mask': '9999 9999 9999 9999'">
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="card_expiry" class="form-label">Expiry Date *</label>
                                            <input type="text" class="form-control" id="card_expiry" name="card_expiry" 
                                                   placeholder="MM/YY" data-inputmask="'mask': '99/99'">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="card_cvv" class="form-label">CVV *</label>
                                            <input type="text" class="form-control" id="card_cvv" name="card_cvv" 
                                                   placeholder="123" data-inputmask="'mask': '999'">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary prev-tab" data-prev="shipping">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Shipping
                                    </button>
                                    <button type="button" class="btn btn-primary next-tab" data-next="review">
                                        Continue to Review <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Review Tab -->
                            <div class="tab-pane fade" id="review" role="tabpanel">
                                <div class="step-indicator">
                                    <div class="step-number inactive">3</div>
                                    <h5 class="mb-0">Review Your Order</h5>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0"><i class="fas fa-truck me-2"></i>Shipping Information</h6>
                                            </div>
                                            <div class="card-body">
                                                <div id="reviewShipping"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Method</h6>
                                            </div>
                                            <div class="card-body">
                                                <div id="reviewPayment"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Order Summary</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Price</th>
                                                        <th>Qty</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $cart_items->data_seek(0); // Reset pointer
                                                    while($item = $cart_items->fetch_assoc()): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <img src="../assets/images/<?php echo $item['product_type'] == 'new' ? 'uploads/' : 'used_shoes/'; ?><?php echo $item['image']; ?>" 
                                                                         width="50" height="50" class="me-3 rounded">
                                                                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                                                                </div>
                                                            </td>
                                                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                                                            <td><?php echo $item['quantity']; ?></td>
                                                            <td><strong>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong></td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <div class="border-top pt-3">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Subtotal:</span>
                                                <span>$<?php echo number_format($subtotal, 2); ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Shipping:</span>
                                                <span>$<?php echo number_format($shipping, 2); ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Tax:</span>
                                                <span>$<?php echo number_format($tax, 2); ?></span>
                                            </div>
                                            <hr>
                                            <div class="d-flex justify-content-between fw-bold h5">
                                                <span>Total:</span>
                                                <span class="text-primary">$<?php echo number_format($total, 2); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                                    </label>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary prev-tab" data-prev="payment">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Payment
                                    </button>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-lock me-2"></i>Place Secure Order
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card order-summary-card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <span><?php echo $shipping == 0 ? 'FREE' : '$' . number_format($shipping, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax:</span>
                        <span>$<?php echo number_format($tax, 2); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold h5">
                        <span>Total:</span>
                        <span class="text-primary">$<?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <?php if($shipping == 0): ?>
                        <div class="alert alert-success mt-3">
                            <i class="fas fa-truck me-2"></i>Free shipping applied!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="card-title"><i class="fas fa-headset me-2"></i>Need Help?</h6>
                    <p class="small text-muted">Contact our customer support for assistance with your order.</p>
                    <div class="d-grid gap-2">
                        <a href="tel:1234567890" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-phone me-2"></i>(123) 456-7890
                        </a>
                        <a href="mailto:support@stepstyle.com" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-envelope me-2"></i>Email Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-contract me-2"></i>Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6><i class="fas fa-shipping-fast me-2"></i>Shipping Policy</h6>
                <p>We offer standard shipping (3-5 business days) for $5.99 and express shipping (2-3 business days) for $12.99. Free shipping is available for orders over $50.</p>
                
                <h6 class="mt-4"><i class="fas fa-undo me-2"></i>Return Policy</h6>
                <p>You may return most new, unopened items within 30 days of delivery for a full refund. We'll also pay the return shipping costs if the return is a result of our error.</p>
                
                <h6 class="mt-4"><i class="fas fa-credit-card me-2"></i>Payment Methods</h6>
                <p>We accept Visa, MasterCard, American Express, Discover, PayPal, and Cash on Delivery (for select areas).</p>
                
                <h6 class="mt-4"><i class="fas fa-shield-alt me-2"></i>Privacy Policy</h6>
                <p>Your personal information is used only to process your orders and provide you with the best shopping experience. We do not sell or share your information with third parties.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize input masks
    $('#card_number').inputmask('9999 9999 9999 9999');
    $('#card_expiry').inputmask('99/99');
    $('#card_cvv').inputmask('999');
    
    // Hide card details initially if payment method isn't card
    if(!$('#creditCard').is(':checked')) {
        $('#cardDetails').hide();
    }
    
    // Toggle card details based on payment method
    $('.payment-method').change(function() {
        if($(this).val() == 'card') {
            $('#cardDetails').show();
            $('#card_number, #card_expiry, #card_cvv').prop('required', true);
        } else {
            $('#cardDetails').hide();
            $('#card_number, #card_expiry, #card_cvv').prop('required', false);
        }
    });
    
    // Next button click handler
    $('.next-tab').click(function(e) {
        e.preventDefault();
        const currentTab = $(this).closest('.tab-pane');
        const nextTabId = $(this).data('next');
        
        // Validate current tab before proceeding
        let isValid = true;
        currentTab.find('[required]').each(function() {
            if(!$(this).val().trim()) {
                isValid = false;
                $(this).addClass('is-invalid');
                // Focus on first invalid field
                if(isValid) $(this).focus();
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        // Additional validation for payment method
        if(currentTab.attr('id') === 'payment') {
            const paymentMethod = $('input[name="payment_method"]:checked').val();
            if(paymentMethod === 'card') {
                const cardNumber = $('#card_number').val().replace(/\s/g, '');
                if(cardNumber.length !== 16) {
                    isValid = false;
                    $('#card_number').addClass('is-invalid');
                }
                
                const expiry = $('#card_expiry').val();
                if(!expiry.match(/^\d{2}\/\d{2}$/)) {
                    isValid = false;
                    $('#card_expiry').addClass('is-invalid');
                }
                
                const cvv = $('#card_cvv').val();
                if(!cvv.match(/^\d{3,4}$/)) {
                    isValid = false;
                    $('#card_cvv').addClass('is-invalid');
                }
            }
        }
        
        if(isValid) {
            // Update review sections if going to review tab
            if(nextTabId === 'review') {
                updateReviewSections();
            }
            
            // Switch to next tab
            $(`#${nextTabId}-tab`).tab('show');
            
            // Update step indicators
            updateStepIndicators(nextTabId);
            
            // Scroll to top
            $('html, body').animate({scrollTop: 0}, 300);
        } else {
            // Show error message
            $('.validation-error').remove();
            currentTab.prepend('<div class="alert alert-danger validation-error mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Please fill in all required fields correctly.</div>');
        }
    });
    
    // Previous button click handler
    $('.prev-tab').click(function(e) {
        e.preventDefault();
        const prevTabId = $(this).data('prev');
        
        // Switch to previous tab
        $(`#${prevTabId}-tab`).tab('show');
        
        // Update step indicators
        updateStepIndicators(prevTabId);
        
        // Scroll to top
        $('html, body').animate({scrollTop: 0}, 300);
    });
    
    // Update review sections with form data
    function updateReviewSections() {
        // Shipping info
        const shippingHtml = `
            <p class="mb-1"><strong>${$('#shipping_name').val()}</strong></p>
            <p class="mb-1">${$('#shipping_address').val()}</p>
            <p class="mb-1">${$('#shipping_city').val()}, ${$('#shipping_state').val()} ${$('#shipping_zip').val()}</p>
            <p class="mb-0"><i class="fas fa-phone me-2"></i>${$('#shipping_phone').val()}</p>
        `;
        $('#reviewShipping').html(shippingHtml);
        
        // Payment info
        const paymentMethod = $('input[name="payment_method"]:checked').val();
        let paymentHtml = '';
        
        switch(paymentMethod) {
            case 'card':
                const cardNumber = $('#card_number').val();
                paymentHtml = `
                    <p class="mb-1"><i class="fas fa-credit-card me-2"></i><strong>Credit/Debit Card</strong></p>
                    <p class="mb-1">Card ending in ${cardNumber.slice(-4)}</p>
                    <p class="mb-0">Expires ${$('#card_expiry').val()}</p>
                `;
                break;
            case 'paypal':
                paymentHtml = `<p class="mb-0"><i class="fab fa-paypal me-2"></i><strong>PayPal</strong></p>`;
                break;
            case 'cod':
                paymentHtml = `<p class="mb-0"><i class="fas fa-money-bill-wave me-2"></i><strong>Cash on Delivery</strong></p>`;
                break;
        }
        
        $('#reviewPayment').html(paymentHtml);
    }
    
    // Update step indicators based on current tab
    function updateStepIndicators(tabId) {
        // Reset all indicators
        $('.step-number').removeClass('completed').addClass('inactive');
        $('.nav-link').removeClass('completed');
        
        // Mark previous steps as completed
        if(tabId === 'payment' || tabId === 'review') {
            $('#shipping-tab').addClass('completed');
            $('#shipping .step-number').removeClass('inactive').addClass('completed');
        }
        
        if(tabId === 'review') {
            $('#payment-tab').addClass('completed');
            $('#payment .step-number').removeClass('inactive').addClass('completed');
        }
        
        // Highlight current step
        if(tabId === 'shipping') {
            $('#shipping .step-number').removeClass('inactive').removeClass('completed');
        } else if(tabId === 'payment') {
            $('#payment .step-number').removeClass('inactive').removeClass('completed');
        } else if(tabId === 'review') {
            $('#review .step-number').removeClass('inactive').removeClass('completed');
        }
    }
    
    // Form submission handler
    $('#checkoutForm').submit(function(e) {
        if(!$('#terms').is(':checked')) {
            e.preventDefault();
            alert('Please agree to the terms and conditions');
            return false;
        }
        
        // Show loading state
        $(this).find('button[type="submit"]')
            .html('<i class="fas fa-spinner fa-spin me-2"></i>Processing Order...')
            .prop('disabled', true);
    });
    
    // Remove validation errors on input
    $(document).on('input', '.is-invalid', function() {
        $(this).removeClass('is-invalid');
        $('.validation-error').remove();
    });
    
    // Initialize step indicators
    updateStepIndicators('shipping');
});
</script>

<?php require_once '../includes/footer.php'; ?>