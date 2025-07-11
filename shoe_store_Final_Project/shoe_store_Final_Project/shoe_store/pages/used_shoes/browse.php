<?php
require_once '../../includes/header.php';

// Get filters
$gender_filter = isset($_GET['gender']) ? $_GET['gender'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$size_filter = isset($_GET['size']) ? $_GET['size'] : '';
$condition_filter = isset($_GET['condition']) ? $_GET['condition'] : '';

// Build query
$query = "SELECT u.*, us.name as seller_name 
          FROM used_shoes u
          JOIN users us ON u.user_id = us.id
          WHERE u.status = 'approved'";

$params = [];
$types = '';

if($gender_filter) {
    $query .= " AND u.gender = ?";
    $params[] = $gender_filter;
    $types .= 's';
}

if($category_filter) {
    $query .= " AND u.category = ?";
    $params[] = $category_filter;
    $types .= 's';
}

if($size_filter) {
    $query .= " AND u.size = ?";
    $params[] = $size_filter;
    $types .= 's';
}

if($condition_filter) {
    $query .= " AND u.condition = ?";
    $params[] = $condition_filter;
    $types .= 's';
}

$query .= " ORDER BY u.created_at DESC";

// Prepare and execute
$stmt = $conn->prepare($query);

if($types) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$shoes = $stmt->get_result();
$stmt->close();

// Get unique values for filters
$categories = $conn->query("SELECT DISTINCT category FROM used_shoes WHERE status = 'approved' ORDER BY category");
$sizes = $conn->query("SELECT DISTINCT size FROM used_shoes WHERE status = 'approved' ORDER BY size");
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" id="usedShoesFilter">
                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">All Genders</option>
                                <option value="men" <?php echo $gender_filter == 'men' ? 'selected' : ''; ?>>Men</option>
                                <option value="women" <?php echo $gender_filter == 'women' ? 'selected' : ''; ?>>Women</option>
                                <option value="kids" <?php echo $gender_filter == 'kids' ? 'selected' : ''; ?>>Kids</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All Categories</option>
                                <?php while($cat = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                        <?php echo $category_filter == $cat['category'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="size" class="form-label">Size</label>
                            <select class="form-select" id="size" name="size">
                                <option value="">All Sizes</option>
                                <?php while($size = $sizes->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($size['size']); ?>" 
                                        <?php echo $size_filter == $size['size'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($size['size']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="condition" class="form-label">Condition</label>
                            <select class="form-select" id="condition" name="condition">
                                <option value="">All Conditions</option>
                                <option value="new" <?php echo $condition_filter == 'new' ? 'selected' : ''; ?>>New</option>
                                <option value="like_new" <?php echo $condition_filter == 'like_new' ? 'selected' : ''; ?>>Like New</option>
                                <option value="good" <?php echo $condition_filter == 'good' ? 'selected' : ''; ?>>Good</option>
                                <option value="fair" <?php echo $condition_filter == 'fair' ? 'selected' : ''; ?>>Fair</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                        <a href="browse.php" class="btn btn-outline-secondary w-100 mt-2">Reset Filters</a>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Used Shoes Marketplace</h2>
                <div>
                    <span class="badge bg-primary"><?php echo $shoes->num_rows; ?> Listings</span>
                </div>
            </div>
            
            <?php if($shoes->num_rows > 0): ?>
                <div class="row">
                    <?php while($shoe = $shoes->fetch_assoc()): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 product-card">
                                <img src="../../assets/images/used_shoes/<?php echo $shoe['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($shoe['name']); ?>">
                                <div class="card-body">
                                    <div class="text-muted small mb-1"><?php echo htmlspecialchars($shoe['brand']); ?></div>
                                    <h5 class="card-title"><?php echo htmlspecialchars($shoe['name']); ?></h5>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold">$<?php echo number_format($shoe['price'], 2); ?></span>
                                        <span class="badge bg-secondary">Size: <?php echo $shoe['size']; ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge bg-info text-dark"><?php echo ucfirst(str_replace('_', ' ', $shoe['shoe_condition'])); ?></span>
                                        <span class="badge bg-primary"><?php echo ucfirst($shoe['gender']); ?></span>
                                    </div>
                                    <small class="text-muted">Sold by: <?php echo htmlspecialchars($shoe['seller_name']); ?></small>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <a href="details.php?id=<?php echo $shoe['id']; ?>" class="btn btn-sm btn-outline-dark">View Details</a>

                                    <!-- <button class="btn btn-sm btn-dark add-to-cart" data-id="<?php echo $shoe['id']; ?>" data-type="used">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button> -->
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center py-4">
                    <i class="fas fa-shoe-prints fa-3x text-muted mb-3"></i>
                    <h5>No Used Shoes Found</h5>
                    <p>There are currently no used shoes matching your criteria.</p>
                    <a href="browse.php" class="btn btn-primary">Reset Filters</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Add to cart functionality
    $('.add-to-cart').click(function() {
        const shoeId = $(this).data('id');
        const shoeType = $(this).data('type');
        
        $.ajax({
            url: '../../functions/cart.php?action=add',
            method: 'POST',
            data: {
                product_id: shoeId,
                product_type: shoeType,
                quantity: 1
            },
            success: function(response) {
                const result = JSON.parse(response);
                if(result.success) {
                    // Update cart count
                    $('.cart-count').text(result.cart_count);
                    
                    // Show success message
                    alert('Item added to cart!');
                } else {
                    alert(result.message);
                }
            }
        });
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>