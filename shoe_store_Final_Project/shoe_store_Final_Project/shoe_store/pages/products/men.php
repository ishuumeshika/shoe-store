<?php
require_once '../../includes/header.php';

// Get category filter if any
$category_filter = isset($_GET['category']) ? $_GET['category'] : null;

// Build query
$query = "SELECT p.*, b.name as brand_name, c.name as category_name 
          FROM products p
          JOIN brands b ON p.brand_id = b.id
          JOIN categories c ON p.category_id = c.id
          WHERE p.gender = 'men'";

if($category_filter) {
    $query .= " AND c.name = ?";
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($query);

if($category_filter) {
    $stmt->bind_param("s", $category_filter);
}

$stmt->execute();
$products = $stmt->get_result();
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <!-- Filters Sidebar -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Filters</h5>
                </div>
                <div class="card-body">
                    <h6>Categories</h6>
                    <ul class="list-unstyled">
                        <li><a href="men.php" class="text-decoration-none <?php echo !$category_filter ? 'fw-bold' : ''; ?>">All Men's Shoes</a></li>
                        <?php
                        $categories = $conn->query("SELECT * FROM categories WHERE type = 'men'");
                        while($cat = $categories->fetch_assoc()): ?>
                            <li>
                                <a href="men.php?category=<?php echo urlencode($cat['name']); ?>" 
                                   class="text-decoration-none <?php echo $category_filter == $cat['name'] ? 'fw-bold' : ''; ?>">
                                   <?php echo htmlspecialchars($cat['name']); ?>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                    
                    <hr>
                    
                    <h6>Brands</h6>
                    <?php
                    $brands = $conn->query("SELECT b.* FROM brands b JOIN products p ON b.id = p.brand_id WHERE p.gender = 'men' GROUP BY b.id");
                    while($brand = $brands->fetch_assoc()): ?>
                        <div class="form-check">
                            <input class="form-check-input brand-filter" type="checkbox" value="<?php echo $brand['id']; ?>" id="brand-<?php echo $brand['id']; ?>">
                            <label class="form-check-label" for="brand-<?php echo $brand['id']; ?>">
                                <?php echo htmlspecialchars($brand['name']); ?>
                            </label>
                        </div>
                    <?php endwhile; ?>
                    
                    <hr>
                    
                    <h6>Price Range</h6>
                    <div class="mb-3">
                        <input type="range" class="form-range" min="0" max="3000" step="20" id="priceRange">
                        <div class="d-flex justify-content-between">
                            <span>$0</span>
                            <span>$3000</span>
                        </div>
                    </div>
                    
                    <button class="btn btn-sm btn-primary w-100" id="applyFilters">Apply Filters</button>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Men's Shoes</h2>
                <div>
                    <select class="form-select form-select-sm" style="width: auto;" id="sortBy">
                        <option value="newest">Newest First</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                        <option value="popular">Most Popular</option>
                    </select>
                </div>
            </div>
            
            <?php if($products->num_rows > 0): ?>
                <div class="row" id="productContainer">
                    <?php while($product = $products->fetch_assoc()): ?>
                        <div class="col-md-4 mb-4 product-col" data-brand="<?php echo $product['brand_id']; ?>" data-price="<?php echo $product['discount_price'] ?: $product['price']; ?>">
                            <div class="card h-100 product-card">
                                <?php if($product['discount_price']): ?>
                                    <div class="badge bg-danger position-absolute" style="top: 0.5rem; right: 0.5rem">Sale</div>
                                <?php endif; ?>
                                <img src="../../assets/images/uploads/<?php echo $product['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
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
                                        <span class="badge bg-secondary">Size: <?php echo $product['size']; ?></span>
                                        <span class="badge bg-info text-dark">Color: <?php echo $product['color']; ?></span>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <a href="details.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-dark">View Details</a>
                                    <!-- <button class="btn btn-sm btn-dark add-to-cart" data-id="<?php echo $product['id']; ?>" data-type="new">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button> -->
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No men's shoes found in this category.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Filter by brand
    $('#applyFilters').click(function() {
        const selectedBrands = [];
        $('.brand-filter:checked').each(function() {
            selectedBrands.push($(this).val());
        });
        
        const maxPrice = $('#priceRange').val();
        
        $('.product-col').each(function() {
            const brandId = $(this).data('brand');
            const price = parseFloat($(this).data('price'));
            let show = true;
            
            if(selectedBrands.length > 0 && !selectedBrands.includes(brandId.toString())) {
                show = false;
            }
            
            if(price > maxPrice) {
                show = false;
            }
            
            if(show) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Sort products
    $('#sortBy').change(function() {
        const sortBy = $(this).val();
        let $container = $('#productContainer');
        let $items = $container.find('.product-col');
        
        $items.sort(function(a, b) {
            const priceA = parseFloat($(a).data('price'));
            const priceB = parseFloat($(b).data('price'));
            
            switch(sortBy) {
                case 'price_low':
                    return priceA - priceB;
                case 'price_high':
                    return priceB - priceA;
                case 'newest':
                default:
                    return 0; // Already sorted by newest in PHP
            }
        });
        
        $container.html($items);
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>