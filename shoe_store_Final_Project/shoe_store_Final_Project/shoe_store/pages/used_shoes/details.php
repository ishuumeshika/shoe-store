<?php
require_once '../../includes/header.php';


// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: browse.php');
    exit;
}

$shoe_id = (int)$_GET['id'];

// Get shoe details
$shoe_query = "SELECT u.*, us.name as seller_name, us.email as seller_email, us.phone as seller_phone
               FROM used_shoes u
               JOIN users us ON u.user_id = us.id
               WHERE u.id = ? AND u.status = 'approved'";
$stmt = $conn->prepare($shoe_query);
$stmt->bind_param('i', $shoe_id);
$stmt->execute();
$shoe = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$shoe) {
    header('Location: browse.php');
    exit;
}

// Get other shoes from same seller
$other_query = "SELECT * FROM used_shoes 
                WHERE user_id = ? AND id != ? AND status = 'approved'
                ORDER BY created_at DESC LIMIT 4";
$stmt = $conn->prepare($other_query);
$stmt->bind_param('ii', $shoe['user_id'], $shoe_id);
$stmt->execute();
$other_shoes = $stmt->get_result();
$stmt->close();
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-6">
            <!-- Main Image -->
            <div class="mb-4">
                <img src="../../assets/images/used_shoes/<?php echo $shoe['image']; ?>" 
                     class="img-fluid rounded shadow" alt="<?php echo htmlspecialchars($shoe['name']); ?>">
            </div>
            
            <!-- Thumbnails -->
            <div class="row g-2">
                <div class="col-3">
                    <img src="../../assets/images/used_shoes/<?php echo $shoe['image']; ?>" 
                         class="img-thumbnail" style="cursor: pointer;" 
                         onclick="$('.card-img-top').attr('src', $(this).attr('src'))">
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="browse.php">Used Shoes</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($shoe['name']); ?></li>
                </ol>
            </nav>
            
            <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($shoe['name']); ?></h1>
            
            <div class="d-flex align-items-center mb-3">
                <span class="badge bg-primary me-2"><?php echo htmlspecialchars($shoe['brand']); ?></span>
                <span class="badge bg-secondary me-2">Size: <?php echo htmlspecialchars($shoe['size']); ?></span>
                <span class="badge bg-success"><?php echo ucfirst(str_replace('_', ' ', $shoe['shoe_condition'])); ?></span>
            </div>
            
            <div class="mb-4">
                <h3 class="text-danger fw-bold">$<?php echo number_format($shoe['price'], 2); ?></h3>
                <?php if ($shoe['quantity'] > 0): ?>
                    <span class="text-success">In Stock (<?php echo $shoe['quantity']; ?> available)</span>
                <?php else: ?>
                    <span class="text-danger">Out of Stock</span>
                <?php endif; ?>
            </div>
            
            <div class="mb-4">
                <h5 class="fw-bold">Description</h5>
                <p><?php echo nl2br(htmlspecialchars($shoe['description'])); ?></p>
            </div>
            
            <div class="row mb-4">
                <div class="col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="card-title fw-bold"><i class="fas fa-info-circle me-2"></i>Details</h6>
                            <ul class="list-unstyled small">
                                <li><strong>Gender:</strong> <?php echo ucfirst($shoe['gender']); ?></li>
                                <li><strong>Color:</strong> <?php echo htmlspecialchars($shoe['color']); ?></li>
                                <li><strong>Category:</strong> <?php echo htmlspecialchars($shoe['category']); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="card-title fw-bold"><i class="fas fa-user me-2"></i>Seller Info</h6>
                            <ul class="list-unstyled small">
                                <li><strong>Name:</strong> <?php echo htmlspecialchars($shoe['seller_name']); ?></li>
                                <li><strong>Contact:</strong> <?php echo htmlspecialchars($shoe['seller_email']); ?></li>
                                <?php if ($shoe['seller_phone']): ?>
                                    <li><strong>Phone:</strong> <?php echo htmlspecialchars($shoe['seller_phone']); ?></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Add to Cart Form -->
            <form class="add-to-cart-form">
                <input type="hidden" name="product_id" value="<?php echo $shoe['id']; ?>">
                <input type="hidden" name="product_type" value="used">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" 
                               min="1" max="<?php echo $shoe['quantity']; ?>" value="1" required>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <?php if($shoe['quantity'] > 0): ?>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg" disabled>Out of Stock</button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-outline-secondary">
                        <i class="far fa-heart"></i> Add to Wishlist
                    </button>
                </div>
            </form>
            
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i>
                This is a used item. Please review condition details before purchasing.
            </div>
        </div>
    </div>
    
    <!-- More from this seller -->
    <?php if ($other_shoes->num_rows > 0): ?>
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="fw-bold mb-4">More from this seller</h3>
            <div class="row g-4">
                <?php while ($other = $other_shoes->fetch_assoc()): ?>
                <div class="col-md-3">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        <img src="../../../assets/images/used_shoes/<?php echo $other['image']; ?>" 
                             class="card-img-top" alt="<?php echo htmlspecialchars($other['name']); ?>" 
                             style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($other['name']); ?></h5>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">$<?php echo number_format($other['price'], 2); ?></span>
                                <span class="badge bg-secondary"><?php echo $other['size']; ?></span>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="details.php?id=<?php echo $other['id']; ?>" class="btn btn-sm btn-outline-dark w-100">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Add to cart form - main product
    $('.add-to-cart-form').submit(function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...');
        
        $.ajax({
            url: '../../functions/cart.php?action=add',
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    // Update cart count in navbar
                    $('.cart-count').text(response.cart_count);
                    
                    // Show success message
                    showAlert('success', 'Product added to cart!');
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function(xhr, status, error) {
                showAlert('danger', 'Error adding to cart. Please try again.');
                console.error("AJAX Error:", status, error);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Add to cart button - related products
    $(document).on('click', '.add-to-cart', function() {
        const button = $(this);
        const originalText = button.html();
        
        // Show loading state
        button.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        
        $.ajax({
            url: '../../functions/cart.php?action=add',
            method: 'POST',
            dataType: 'json',
            data: {
                product_id: button.data('id'),
                product_type: button.data('type'),
                quantity: 1
            },
            success: function(response) {
                if(response.success) {
                    // Update cart count in navbar
                    $('.cart-count').text(response.cart_count);
                    
                    // Show success message
                    showAlert('success', 'Product added to cart!');
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function(xhr, status, error) {
                showAlert('danger', 'Error adding to cart. Please try again.');
                console.error("AJAX Error:", status, error);
            },
            complete: function() {
                button.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Function to show alert messages
    function showAlert(type, message) {
        const alert = $('<div class="alert alert-' + type + ' alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;">' +
                       message + 
                       '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
        
        $('body').append(alert);
        
        // Auto dismiss after 3 seconds
        setTimeout(function() {
            alert.alert('close');
        }, 3000);
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>