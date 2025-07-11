<?php
require_once 'includes/header.php';
require_once 'includes/config.php';

// Get search query
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Initialize variables
$results = [];
$error = '';
$search_performed = false;

if (!empty($query)) {
    $search_performed = true;
    
    // Split query into keywords
    $keywords = preg_split('/\s+/', $query);
    $keywords = array_filter($keywords);
    
    if (count($keywords) > 0) {
        // Build search conditions
        $conditions = [];
        $params = [];
        $types = '';
        
        foreach ($keywords as $keyword) {
            $conditions[] = "(p.name LIKE ? OR p.description LIKE ? OR b.name LIKE ? OR c.name LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $types .= 'ssss';
        }
        
        // Prepare and execute query
        $sql = "SELECT p.*, b.name as brand_name, c.name as category_name 
                FROM products p
                JOIN brands b ON p.brand_id = b.id
                JOIN categories c ON p.category_id = c.id
                WHERE " . implode(' AND ', $conditions);
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <form action="search.php" method="GET" class="d-flex">
                        <input type="text" class="form-control me-2" name="query" 
                               placeholder="Search for shoes, brands, categories..." 
                               value="<?php echo htmlspecialchars($query); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </form>
                </div>
            </div>
            
            <?php if ($search_performed): ?>
                <h2 class="mb-4">Search Results for "<?php echo htmlspecialchars($query); ?>"</h2>
                
                <?php if (count($results) > 0): ?>
                    <div class="row">
                        <?php foreach ($results as $product): ?>
                            <div class="col-md-3 mb-4">
                                <div class="card h-100 product-card">
                                    <?php if($product['discount_price']): ?>
                                        <div class="badge bg-danger position-absolute" style="top: 0.5rem; right: 0.5rem">Sale</div>
                                    <?php endif; ?>
                                    <img src="assets/images/uploads/<?php echo $product['image']; ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <div class="card-body">
                                        <div class="text-muted small mb-1"><?php echo htmlspecialchars($product['brand_name']); ?></div>
                                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <?php if($product['discount_price']): ?>
                                                <span class="text-danger fw-bold">$<?php echo number_format($product['discount_price'], 2); ?></span>
                                                <span class="text-muted text-decoration-line-through">$<?php echo number_format($product['price'], 2); ?></span>
                                            <?php else: ?>
                                                <span class="fw-bold">$<?php echo number_format($product['price'], 2); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-secondary"><?php echo $product['category_name']; ?></span>
                                            <span class="badge bg-info text-dark"><?php echo ucfirst($product['gender']); ?></span>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <a href="pages/products/detail.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-sm btn-outline-dark">View Details</a>
                                        <!-- <button class="btn btn-sm btn-dark add-to-cart" 
                                                data-id="<?php echo $product['id']; ?>" 
                                                data-type="new">
                                            <i class="fas fa-shopping-cart"></i> Add to Cart
                                        </button> -->
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No products found matching your search criteria.
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h5>Search Tips:</h5>
                            <ul>
                                <li>Try different keywords</li>
                                <li>Check your spelling</li>
                                <li>Search by brand, category, or product name</li>
                                <li>Use fewer keywords to broaden your search</li>
                            </ul>
                            <a href="pages/products/all.php" class="btn btn-primary">Browse All Products</a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php elseif ($search_performed): ?>
                <div class="alert alert-warning">
                    Please enter a search term.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Add to cart functionality
    $('.add-to-cart').click(function() {
        const productId = $(this).data('id');
        const productType = $(this).data('type');
        const button = $(this);
        const originalText = button.html();
        
        // Show loading state
        button.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm" role="status"></span>');
        
        $.ajax({
            url: 'functions/cart.php?action=add',
            method: 'POST',
            data: {
                product_id: productId,
                product_type: productType,
                quantity: 1
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    // Update cart count
                    $('.cart-count').text(response.cart_count);
                    
                    // Show success message
                    const alert = $('<div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;">' +
                                   'Item added to cart!' +
                                   '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
                    $('body').append(alert);
                    setTimeout(() => alert.alert('close'), 3000);
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Error adding to cart. Please try again.');
            },
            complete: function() {
                button.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>