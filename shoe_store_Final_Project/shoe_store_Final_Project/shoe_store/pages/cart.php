<?php
require_once '../includes/header.php';
require_once '../includes/auth.php';

// Only allow logged-in users
requireLogin();

$user_id = $_SESSION['user_id'];

// Get cart items with product details
$cart_query = "SELECT c.id as cart_id, c.product_id, c.product_type, c.quantity, c.price,
               IF(c.product_type = 'new', p.name, u.name) as name,
               IF(c.product_type = 'new', p.image, u.image) as image,
               IF(c.product_type = 'new', b.name, u.brand) as brand,
               IF(c.product_type = 'new', p.quantity, 1) as max_quantity
               FROM cart c
               LEFT JOIN products p ON c.product_id = p.id AND c.product_type = 'new'
               LEFT JOIN brands b ON p.brand_id = b.id
               LEFT JOIN used_shoes u ON c.product_id = u.id AND c.product_type = 'used'
               WHERE c.user_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();

// Calculate totals
$totals_query = "SELECT SUM(quantity * price) as subtotal, COUNT(*) as items_count 
                FROM cart 
                WHERE user_id = ?";
$stmt = $conn->prepare($totals_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$totals = $stmt->get_result()->fetch_assoc();
$stmt->close();

$subtotal = $totals['subtotal'] ?? 0;
$shipping = $subtotal > 0 ? ($subtotal > 50 ? 0 : 5.99) : 0;
$tax = $subtotal * 0.08; // Example 8% tax
$total = $subtotal + $shipping + $tax;
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Shopping Cart (<?php echo $totals['items_count'] ?? 0; ?> items)</h5>
                </div>
                <div class="card-body">
                    <?php if($cart_items->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($item = $cart_items->fetch_assoc()): ?>
                                        <tr class="cart-item" data-cart-id="<?php echo $item['cart_id']; ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="../assets/images/<?php echo $item['product_type'] == 'new' ? 'uploads/' : 'used_shoes/'; ?><?php echo $item['image']; ?>" 
                                                         class="img-thumbnail me-3" width="80" height="80">
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars($item['brand']); ?></small>
                                                        <div>
                                                            <small class="text-muted"><?php echo ucfirst($item['product_type']); ?> product</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                                            <td>
                                                <input type="number" class="form-control quantity-input" 
                                                       value="<?php echo $item['quantity']; ?>" 
                                                       min="1" max="<?php echo $item['max_quantity']; ?>"
                                                       style="width: 70px;">
                                            </td>
                                            <td class="item-total">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger remove-item">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                            <h5>Your cart is empty</h5>
                            <p>Browse our collection and find something you like!</p>
                            <a href="../index.php" class="btn btn-primary">Continue Shopping</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if($cart_items->num_rows > 0): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Shipping Options</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="shipping" id="standardShipping" checked>
                            <label class="form-check-label" for="standardShipping">
                                Standard Shipping (3-5 business days) - $5.99
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="shipping" id="expressShipping">
                            <label class="form-check-label" for="expressShipping">
                                Express Shipping (2-3 business days) - $12.99
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="shipping" id="freeShipping" <?php echo $subtotal > 50 ? 'checked' : 'disabled'; ?>>
                            <label class="form-check-label" for="freeShipping">
                                Free Shipping (orders over $50) - $0.00
                            </label>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span class="subtotal">$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <span class="shipping">$<?php echo number_format($shipping, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax (8%):</span>
                        <span class="tax">$<?php echo number_format($tax, 2); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total:</span>
                        <span class="total">$<?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <?php if($cart_items->num_rows > 0): ?>
                        <a href="checkout.php" class="btn btn-primary w-100 mt-3">Proceed to Checkout</a>
                        <a href="../index.php" class="btn btn-outline-secondary w-100 mt-2">Continue Shopping</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Secure Payment</h6>
                    <p class="small text-muted">All transactions are secure and encrypted.</p>
                    <div class="d-flex justify-content-between">
                        <i class="fab fa-cc-visa fa-2x text-muted"></i>
                        <i class="fab fa-cc-mastercard fa-2x text-muted"></i>
                        <i class="fab fa-cc-amex fa-2x text-muted"></i>
                        <i class="fab fa-cc-discover fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Update quantity
    $('.quantity-input').change(function() {
        const cartItem = $(this).closest('.cart-item');
        const cartId = cartItem.data('cart-id');
        const quantity = $(this).val();
        
        $.ajax({
            url: '../functions/cart.php?action=update',
            method: 'POST',
            data: {
                cart_id: cartId,
                quantity: quantity
            },
            success: function(response) {
                const result = JSON.parse(response);
                if(result.success) {
                    // Update item total
                    cartItem.find('.item-total').text('$' + result.item_total);
                    
                    // Update summary
                    $('.subtotal').text('$' + result.subtotal);
                    
                    // Recalculate shipping, tax, total
                    const subtotal = parseFloat(result.subtotal);
                    const shipping = subtotal > 50 ? 0 : 5.99;
                    const tax = subtotal * 0.08;
                    const total = subtotal + shipping + tax;
                    
                    $('.shipping').text('$' + shipping.toFixed(2));
                    $('.tax').text('$' + tax.toFixed(2));
                    $('.total').text('$' + total.toFixed(2));
                    
                    // Update cart count in navbar
                    $('.cart-count').text(result.cart_count);
                    
                    // Update free shipping radio
                    if(subtotal > 50) {
                        $('#freeShipping').prop('disabled', false).prop('checked', true);
                    } else {
                        $('#freeShipping').prop('disabled', true);
                        $('#standardShipping').prop('checked', true);
                    }
                } else {
                    alert(result.message);
                    location.reload();
                }
            }
        });
    });
    
    // Remove item
    $('.remove-item').click(function() {
        if(confirm('Are you sure you want to remove this item from your cart?')) {
            const cartItem = $(this).closest('.cart-item');
            const cartId = cartItem.data('cart-id');
            
            $.ajax({
                url: '../functions/cart.php?action=remove',
                method: 'POST',
                data: {
                    cart_id: cartId
                },
                success: function(response) {
                    const result = JSON.parse(response);
                    if(result.success) {
                        // Remove item row
                        cartItem.remove();
                        
                        // Update summary
                        $('.subtotal').text('$' + result.subtotal);
                        
                        // Recalculate shipping, tax, total
                        const subtotal = parseFloat(result.subtotal);
                        const shipping = subtotal > 50 ? 0 : 5.99;
                        const tax = subtotal * 0.08;
                        const total = subtotal + shipping + tax;
                        
                        $('.shipping').text('$' + shipping.toFixed(2));
                        $('.tax').text('$' + tax.toFixed(2));
                        $('.total').text('$' + total.toFixed(2));
                        
                        // Update cart count in navbar
                        $('.cart-count').text(result.cart_count);
                        
                        // Update free shipping radio
                        if(subtotal > 50) {
                            $('#freeShipping').prop('disabled', false).prop('checked', true);
                        } else {
                            $('#freeShipping').prop('disabled', true);
                            $('#standardShipping').prop('checked', true);
                        }
                        
                        // If cart is empty, reload page to show empty cart message
                        if(result.cart_count == 0) {
                            location.reload();
                        }
                    } else {
                        alert(result.message);
                    }
                }
            });
        }
    });
    
    // Shipping option change
    $('input[name="shipping"]').change(function() {
        const shippingValue = $(this).attr('id') == 'freeShipping' ? 0 : 
                            $(this).attr('id') == 'expressShipping' ? 12.99 : 5.99;
        
        $('.shipping').text('$' + shippingValue.toFixed(2));
        
        const subtotal = parseFloat($('.subtotal').text().replace('$', ''));
        const tax = subtotal * 0.08;
        const total = subtotal + shippingValue + tax;
        
        $('.tax').text('$' + tax.toFixed(2));
        $('.total').text('$' + total.toFixed(2));
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>