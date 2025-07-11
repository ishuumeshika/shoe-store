<?php
require_once '../../includes/header.php';

// Get category filter if any
$category_filter = isset($_GET['category']) ? $_GET['category'] : null;

// Build query
$query = "SELECT p.*, b.name as brand_name, c.name as category_name 
          FROM products p
          JOIN brands b ON p.brand_id = b.id
          JOIN categories c ON p.category_id = c.id";

if($category_filter) {
    $query .= " WHERE c.name = ?";
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($query);

if($category_filter) {
    $stmt->bind_param("s", $category_filter);
}

$stmt->execute();
$products = $stmt->get_result();
?>

<style>
/* Professional Products Page Styling */
:root {
    --primary-color: #2563eb;
    --primary-dark: #1d4ed8;
    --secondary-color: #64748b;
    --success-color: #10b981;
    --danger-color: #ef4444;
    --warning-color: #f59e0b;
    --light-bg: #f8fafc;
    --white: #ffffff;
    --text-primary: #1e293b;
    --text-secondary: #64748b;
    --border-color: #e2e8f0;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
}

.products-page {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    min-height: 100vh;
    padding: 2rem 0;
}

/* Filters Sidebar */
.filters-sidebar {
    background: var(--white);
    border-radius: 20px;
    padding: 0;
    box-shadow: var(--shadow-xl);
    border: 1px solid var(--border-color);
    position: sticky;
    top: 2rem;
    overflow: hidden;
}

.filters-header {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    padding: 1.5rem 2rem;
    margin: 0;
    border-radius: 0;
    position: relative;
    overflow: hidden;
}

.filters-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    transition: left 0.5s;
}

.filters-header:hover::before {
    left: 100%;
}

.filters-header h5 {
    margin: 0;
    font-weight: 700;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filters-body {
    padding: 2rem;
}

.filter-section {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.filter-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.filter-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.filter-list li {
    margin-bottom: 0.75rem;
}

.filter-list a {
    color: var(--text-secondary);
    text-decoration: none;
    font-weight: 500;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    display: block;
    position: relative;
}

.filter-list a:hover {
    color: var(--primary-color);
    background: rgba(37, 99, 235, 0.05);
    transform: translateX(4px);
}

.filter-list a.active {
    color: var(--primary-color);
    background: rgba(37, 99, 235, 0.1);
    font-weight: 600;
}

.filter-list a.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: var(--primary-color);
    border-radius: 0 2px 2px 0;
}

.brand-filter-item {
    display: flex;
    align-items: center;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    margin-bottom: 0.5rem;
}

.brand-filter-item:hover {
    background: rgba(37, 99, 235, 0.05);
}

.brand-filter-item input[type="checkbox"] {
    margin-right: 0.75rem;
    transform: scale(1.1);
    accent-color: var(--primary-color);
}

.brand-filter-item label {
    margin: 0;
    font-weight: 500;
    color: var(--text-secondary);
    cursor: pointer;
    flex: 1;
}

.price-range-container {
    padding: 1rem 0;
}

.price-range-slider {
    width: 100%;
    height: 6px;
    border-radius: 3px;
    background: var(--border-color);
    outline: none;
    -webkit-appearance: none;
    margin: 1rem 0;
}

.price-range-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--primary-color);
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3);
}

.price-range-slider::-moz-range-thumb {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--primary-color);
    cursor: pointer;
    border: none;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3);
}

.price-range-labels {
    display: flex;
    justify-content: space-between;
    font-size: 0.875rem;
    color: var(--text-secondary);
    font-weight: 500;
}

.apply-filters-btn {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    border: none;
    border-radius: 12px;
    padding: 0.875rem 1.5rem;
    font-weight: 600;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
    width: 100%;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    position: relative;
    overflow: hidden;
}

.apply-filters-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.apply-filters-btn:hover::before {
    left: 100%;
}

.apply-filters-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
}

/* Products Section */
.products-section {
    background: var(--white);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: var(--shadow-xl);
    border: 1px solid var(--border-color);
}

.products-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid var(--border-color);
}

.products-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.sort-container {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.sort-label {
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.sort-select {
    border: 2px solid var(--border-color);
    border-radius: 12px;
    padding: 0.75rem 1rem;
    font-weight: 500;
    background: var(--white);
    color: var(--text-primary);
    min-width: 200px;
    transition: all 0.3s ease;
}

.sort-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    outline: none;
}

/* Product Cards */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
}

.product-card-modern {
    background: var(--white);
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.4s ease;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-md);
    position: relative;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-card-modern:hover {
    transform: translateY(-12px);
    box-shadow: var(--shadow-xl);
}

.product-image-container {
    position: relative;
    aspect-ratio: 1;
    overflow: hidden;
    background: var(--light-bg);
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.product-card-modern:hover .product-image {
    transform: scale(1.1);
}

.sale-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: linear-gradient(135deg, var(--danger-color), #dc2626);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 700;
    z-index: 2;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.product-content {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-brand {
    color: var(--text-secondary);
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.product-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 1rem;
    line-height: 1.3;
    flex: 1;
}

.product-price {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.price-current {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--danger-color);
}

.price-original {
    font-size: 1.125rem;
    color: var(--text-secondary);
    text-decoration: line-through;
    font-weight: 500;
}

.price-regular {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--text-primary);
}

.product-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.product-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.badge-size {
    background: rgba(100, 116, 139, 0.1);
    color: var(--secondary-color);
}

.badge-color {
    background: rgba(6, 182, 212, 0.1);
    color: #0891b2;
}

.product-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-modern {
    border-radius: 12px;
    font-weight: 600;
    padding: 0.75rem 1.25rem;
    font-size: 0.875rem;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    flex: 1;
    text-align: center;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-outline-modern {
    background: transparent;
    border: 2px solid var(--border-color);
    color: var(--text-secondary);
}

.btn-outline-modern:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
    background: rgba(37, 99, 235, 0.05);
    transform: translateY(-2px);
}

.btn-primary-modern {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    border: none;
    color: white;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
}

.btn-primary-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
    background: linear-gradient(135deg, var(--primary-dark), #1e40af);
}

/* No Products Message */
.no-products {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--light-bg);
    border-radius: 16px;
    border: 2px dashed var(--border-color);
}

.no-products-icon {
    width: 80px;
    height: 80px;
    background: rgba(37, 99, 235, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}

.no-products h4 {
    color: var(--text-primary);
    font-weight: 700;
    margin-bottom: 1rem;
}

.no-products p {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

/* Loading Animation */
.loading {
    position: relative;
    overflow: hidden;
}

.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { left: -100%; }
    100% { left: 100%; }
}

/* Responsive Design */
@media (max-width: 768px) {
    .products-page {
        padding: 1rem 0;
    }
    
    .filters-sidebar {
        margin-bottom: 2rem;
        position: static;
    }
    
    .products-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .products-title {
        font-size: 2rem;
        text-align: center;
    }
    
    .sort-container {
        justify-content: center;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .filters-body {
        padding: 1.5rem;
    }
    
    .products-section {
        padding: 1.5rem;
    }
}

@media (max-width: 576px) {
    .product-actions {
        flex-direction: column;
    }
    
    .btn-modern {
        width: 100%;
    }
}
</style>

<div class="products-page">
    <div class="container">
        <div class="row g-4">
            <!-- Filters Sidebar -->
            <div class="col-lg-3">
                <div class="filters-sidebar">
                    <div class="filters-header">
                        <h5>
                            <i class="fas fa-filter"></i>
                            Filters
                        </h5>
                    </div>
                    <div class="filters-body">
                        <!-- Categories Filter -->
                        <div class="filter-section">
                            <h6 class="filter-title">
                                <i class="fas fa-layer-group"></i>
                                Categories
                            </h6>
                            <ul class="filter-list">
                                <li>
                                    <a href="all.php" class="<?php echo !$category_filter ? 'active' : ''; ?>">
                                        All Shoes
                                    </a>
                                </li>
                                <?php
                                $categories = $conn->query("SELECT * FROM categories");
                                while($cat = $categories->fetch_assoc()): ?>
                                    <li>
                                        <a href="all.php?category=<?php echo urlencode($cat['name']); ?>" 
                                           class="<?php echo $category_filter == $cat['name'] ? 'active' : ''; ?>">
                                           <?php echo htmlspecialchars($cat['name']); ?>
                                        </a>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                        
                        <!-- Brands Filter -->
                        <div class="filter-section">
                            <h6 class="filter-title">
                                <i class="fas fa-tags"></i>
                                Brands
                            </h6>
                            <?php
                            $brands = $conn->query("SELECT b.* FROM brands b JOIN products p ON b.id = p.brand_id GROUP BY b.id ORDER BY b.name");
                            while($brand = $brands->fetch_assoc()): ?>
                                <div class="brand-filter-item">
                                    <input class="brand-filter" type="checkbox" value="<?php echo $brand['id']; ?>" id="brand-<?php echo $brand['id']; ?>">
                                    <label for="brand-<?php echo $brand['id']; ?>">
                                        <?php echo htmlspecialchars($brand['name']); ?>
                                    </label>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <!-- Price Range Filter -->
                        <div class="filter-section">
                            <h6 class="filter-title">
                                <i class="fas fa-dollar-sign"></i>
                                Price Range
                            </h6>
                            <div class="price-range-container">
                                <input type="range" class="price-range-slider" min="0" max="3000" step="20" id="priceRange" value="3000">
                                <div class="price-range-labels">
                                    <span>$0</span>
                                    <span id="priceRangeValue">$3000</span>
                                </div>
                            </div>
                        </div>
                        
                        <button class="apply-filters-btn" id="applyFilters">
                            <i class="fas fa-check me-2"></i>
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Products Section -->
            <div class="col-lg-9">
                <div class="products-section">
                    <div class="products-header">
                        <h2 class="products-title">
                            <i class="fas fa-shoe-prints"></i>
                            All Shoes
                            <?php if($category_filter): ?>
                                - <?php echo htmlspecialchars($category_filter); ?>
                            <?php endif; ?>
                        </h2>
                        <div class="sort-container">
                            <label class="sort-label">Sort by:</label>
                            <select class="sort-select" id="sortBy">
                                <option value="newest">Newest First</option>
                                <option value="price_low">Price: Low to High</option>
                                <option value="price_high">Price: High to Low</option>
                                <option value="popular">Most Popular</option>
                                <option value="name">Name A-Z</option>
                            </select>
                        </div>
                    </div>
                    
                    <?php if($products->num_rows > 0): ?>
                        <div class="products-grid" id="productContainer">
                            <?php while($product = $products->fetch_assoc()): ?>
                                <div class="product-card-modern product-col" 
                                     data-brand="<?php echo $product['brand_id']; ?>" 
                                     data-price="<?php echo $product['discount_price'] ?: $product['price']; ?>"
                                     data-name="<?php echo strtolower($product['name']); ?>">
                                    
                                    <?php if($product['discount_price']): ?>
                                        <div class="sale-badge">
                                            Sale
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="product-image-container">
                                        <img src="../../assets/images/uploads/<?php echo $product['image']; ?>" 
                                             class="product-image" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    </div>
                                    
                                    <div class="product-content">
                                        <div class="product-brand">
                                            <?php echo htmlspecialchars($product['brand_name']); ?>
                                        </div>
                                        <h5 class="product-name">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </h5>
                                        
                                        <div class="product-price">
                                            <?php if($product['discount_price']): ?>
                                                <span class="price-current">$<?php echo number_format($product['discount_price'], 2); ?></span>
                                                <span class="price-original">$<?php echo number_format($product['price'], 2); ?></span>
                                            <?php else: ?>
                                                <span class="price-regular">$<?php echo number_format($product['price'], 2); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="product-badges">
                                            <span class="product-badge badge-size">
                                                Size: <?php echo $product['size']; ?>
                                            </span>
                                            <span class="product-badge badge-color">
                                                <?php echo $product['color']; ?>
                                            </span>
                                        </div>
                                        
                                        <div class="product-actions">
                                            <a href="details.php?id=<?php echo $product['id']; ?>" 
                                               class="btn-modern btn-outline-modern">
                                                <i class="fas fa-eye"></i>
                                                View Details
                                            </a>
                                            <!-- <button class="btn-modern btn-primary-modern add-to-cart" 
                                                    data-id="<?php echo $product['id']; ?>" 
                                                    data-type="new">
                                                <i class="fas fa-cart-plus"></i>
                                                Add to Cart
                                            </button> -->
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-products">
                            <div class="no-products-icon">
                                <i class="fas fa-search fa-2x text-primary"></i>
                            </div>
                            <h4>No Products Found</h4>
                            <p>We couldn't find any shoes matching your criteria. Try adjusting your filters or browse all products.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Price range slider
    $('#priceRange').on('input', function() {
        const value = $(this).val();
        $('#priceRangeValue').text('$' + value);
    });
    
    // Apply filters
    $('#applyFilters').click(function() {
        const button = $(this);
        const originalText = button.html();
        
        // Show loading state
        button.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Applying...');
        
        setTimeout(function() {
            const selectedBrands = [];
            $('.brand-filter:checked').each(function() {
                selectedBrands.push($(this).val());
            });
            
            const maxPrice = parseFloat($('#priceRange').val());
            let visibleCount = 0;
            
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
                    $(this).fadeIn(300);
                    visibleCount++;
                } else {
                    $(this).fadeOut(300);
                }
            });
            
            // Update products title with count
            const titleElement = $('.products-title');
            const baseTitle = titleElement.text().split('(')[0].trim();
            titleElement.html(`
                <i class="fas fa-shoe-prints"></i>
                ${baseTitle}
                <small class="text-muted">(${visibleCount} products)</small>
            `);
            
            // Reset button
            button.prop('disabled', false).html(originalText);
            
            // Show success message
            showAlert('success', `Filters applied! Showing ${visibleCount} products.`);
        }, 800);
    });
    
    // Sort products
    $('#sortBy').change(function() {
        const sortBy = $(this).val();
        let $container = $('#productContainer');
        let $items = $container.find('.product-col:visible');
        
        // Show loading animation
        $container.addClass('loading');
        
        setTimeout(function() {
            $items.sort(function(a, b) {
                const priceA = parseFloat($(a).data('price'));
                const priceB = parseFloat($(b).data('price'));
                const nameA = $(a).data('name');
                const nameB = $(b).data('name');
                
                switch(sortBy) {
                    case 'price_low':
                        return priceA - priceB;
                    case 'price_high':
                        return priceB - priceA;
                    case 'name':
                        return nameA.localeCompare(nameB);
                    case 'popular':
                        // For demo purposes, sort by price (you can implement actual popularity logic)
                        return priceB - priceA;
                    case 'newest':
                    default:
                        return 0; // Keep original order
                }
            });
            
            // Fade out, rearrange, fade in
            $container.fadeOut(200, function() {
                $container.html($items);
                $container.removeClass('loading').fadeIn(200);
            });
            
            showAlert('info', `Products sorted by ${$('#sortBy option:selected').text()}`);
        }, 500);
    });
    
    // Add to cart functionality
    $(document).on('click', '.add-to-cart', function() {
        const button = $(this);
        const originalText = button.html();
        
        // Show loading state
        button.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Adding...');
        
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
                    showAlert('success', 'Product added to cart successfully!');
                } else {
                    showAlert('danger', response.message || 'Failed to add product to cart.');
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
    
    // Enhanced alert function
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'danger' ? 'alert-danger' : 
                          type === 'warning' ? 'alert-warning' : 'alert-info';
        
        const icon = type === 'success' ? 'fas fa-check-circle' : 
                    type === 'danger' ? 'fas fa-exclamation-circle' : 
                    type === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';
        
        const alert = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 350px; box-shadow: 0 8px 32px rgba(0,0,0,0.15); border-radius: 12px; border: none;">
                <div class="d-flex align-items-center">
                    <i class="${icon} me-3 fs-5"></i>
                    <div class="flex-grow-1">
                        <strong>${type.charAt(0).toUpperCase() + type.slice(1)}!</strong><br>
                        ${message}
                    </div>
                    <button type="button" class="btn-close ms-3" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        `);
        
        $('body').append(alert);
        
        // Auto dismiss after 4 seconds
        setTimeout(function() {
            alert.alert('close');
        }, 4000);
    }
    
    // Initialize price range display
    $('#priceRange').trigger('input');
});
</script>

<?php require_once '../../includes/footer.php'; ?>
