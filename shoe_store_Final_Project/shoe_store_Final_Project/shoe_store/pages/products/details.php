<?php
require_once '../../includes/header.php';

if(!isset($_GET['id'])) {
    header("Location: men.php");
    exit;
}

$product_id = $_GET['id'];

// Get product details
$stmt = $conn->prepare("SELECT p.*, b.name as brand_name, c.name as category_name 
                       FROM products p
                       JOIN brands b ON p.brand_id = b.id
                       JOIN categories c ON p.category_id = c.id
                       WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    header("Location: men.php");
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();

// Get related products
$related_query = "SELECT p.*, b.name as brand_name 
                 FROM products p
                 JOIN brands b ON p.brand_id = b.id
                 WHERE p.gender = ? AND p.category_id = ? AND p.id != ?
                 ORDER BY RAND() LIMIT 4";
$stmt = $conn->prepare($related_query);
$stmt->bind_param("sii", $product['gender'], $product['category_id'], $product_id);
$stmt->execute();
$related_products = $stmt->get_result();
?>

<style>
/* Professional Product Details Styles */
.product-detail-container {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    min-height: 100vh;
    padding: 2rem 0;
}

.professional-breadcrumb {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.professional-breadcrumb .breadcrumb {
    margin: 0;
    background: none;
    padding: 0;
}

.professional-breadcrumb .breadcrumb-item a {
    color: #64748b;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.professional-breadcrumb .breadcrumb-item a:hover {
    color: #1e293b;
    transform: translateY(-1px);
}

.professional-breadcrumb .breadcrumb-item.active {
    color: #1e293b;
    font-weight: 600;
}

.product-gallery {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: sticky;
    top: 2rem;
}

.main-product-image {
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    background: #f8fafc;
    aspect-ratio: 1;
    margin-bottom: 1.5rem;
}

.main-product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.main-product-image:hover img {
    transform: scale(1.05);
}

.thumbnail-gallery {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
}

.thumbnail-item {
    aspect-ratio: 1;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    background: #f8fafc;
}

.thumbnail-item:hover {
    border-color: #3b82f6;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

.thumbnail-item.active {
    border-color: #1e40af;
    box-shadow: 0 8px 25px rgba(30, 64, 175, 0.4);
}

.thumbnail-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-info {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.product-brand {
    color: #64748b;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: 0.5rem;
}

.product-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 1rem;
    line-height: 1.2;
}

.price-container {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 16px;
    border: 1px solid #e2e8f0;
}

.current-price {
    font-size: 2.25rem;
    font-weight: 800;
    color: #dc2626;
}

.original-price {
    font-size: 1.25rem;
    color: #64748b;
    text-decoration: line-through;
}

.discount-badge {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-size: 0.875rem;
    font-weight: 600;
    box-shadow: 0 4px 14px rgba(220, 38, 38, 0.4);
}

.product-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 2rem;
}

.product-badge {
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-size: 0.875rem;
    font-weight: 600;
    border: none;
    color: white;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
}

.badge-category {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.badge-size {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
}

.badge-color {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
}

.badge-stock {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.badge-out-stock {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.product-description {
    font-size: 1.125rem;
    line-height: 1.75;
    color: #475569;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f8fafc;
    border-radius: 16px;
    border-left: 4px solid #3b82f6;
}

.purchase-form {
    background: #f8fafc;
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    display: block;
}

.form-control, .form-select {
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus, .form-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    outline: none;
}

.btn-primary-custom {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border: none;
    border-radius: 12px;
    padding: 1rem 2rem;
    font-weight: 600;
    font-size: 1.125rem;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
    width: 100%;
    margin-bottom: 1rem;
}

.btn-primary-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(59, 130, 246, 0.4);
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}

.btn-secondary-custom {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    border: none;
    border-radius: 12px;
    padding: 1rem 2rem;
    font-weight: 600;
    font-size: 1.125rem;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 10px 25px rgba(100, 116, 139, 0.3);
    width: 100%;
}

.btn-secondary-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(100, 116, 139, 0.4);
    background: linear-gradient(135deg, #475569 0%, #334155 100%);
}

.btn-outline-custom {
    background: transparent;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 1rem 2rem;
    font-weight: 600;
    font-size: 1.125rem;
    color: #374151;
    transition: all 0.3s ease;
    width: 100%;
}

.btn-outline-custom:hover {
    border-color: #3b82f6;
    color: #3b82f6;
    background: rgba(59, 130, 246, 0.05);
    transform: translateY(-2px);
}

.product-tabs-container {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.2);
    margin-bottom: 3rem;
}

.professional-nav-tabs {
    border: none;
    background: #f8fafc;
    padding: 0.5rem;
}

.professional-nav-tabs .nav-link {
    border: none;
    border-radius: 12px;
    padding: 1rem 2rem;
    font-weight: 600;
    color: #64748b;
    transition: all 0.3s ease;
    margin: 0 0.25rem;
}

.professional-nav-tabs .nav-link.active {
    background: white;
    color: #3b82f6;
    box-shadow: 0 4px 14px rgba(59, 130, 246, 0.2);
}

.professional-nav-tabs .nav-link:hover {
    color: #3b82f6;
    background: rgba(59, 130, 246, 0.05);
}

.tab-content-professional {
    padding: 2.5rem;
}

.tab-pane h5 {
    color: #1e293b;
    font-weight: 700;
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
}

.related-products-section {
    margin-top: 4rem;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1e293b;
    text-align: center;
    margin-bottom: 3rem;
    position: relative;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -1rem;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border-radius: 2px;
}

.product-card-professional {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    position: relative;
}

.product-card-professional:hover {
    transform: translateY(-8px);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
}

.product-card-image {
    position: relative;
    aspect-ratio: 1;
    overflow: hidden;
}

.product-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card-professional:hover .product-card-image img {
    transform: scale(1.1);
}

.product-card-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 2;
}

.product-card-body {
    padding: 1.5rem;
}

.product-card-brand {
    color: #64748b;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.product-card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 1rem;
}

.product-card-price {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.product-card-footer {
    padding: 0 1.5rem 1.5rem;
    display: flex;
    gap: 0.75rem;
}

.btn-sm-custom {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
    transition: all 0.3s ease;
    flex: 1;
}

/* Loading states */
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

/* Alert styles */
.custom-alert {
    position: fixed;
    top: 2rem;
    right: 2rem;
    z-index: 9999;
    min-width: 300px;
    border-radius: 12px;
    border: none;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.alert-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.alert-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .product-detail-container {
        padding: 1rem 0;
    }
    
    .product-gallery,
    .product-info,
    .product-tabs-container {
        margin-bottom: 1.5rem;
        padding: 1.5rem;
    }
    
    .product-title {
        font-size: 2rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
}
</style>

<div class="product-detail-container">
    <div class="container">
        <!-- Professional Breadcrumb -->
        <nav aria-label="breadcrumb" class="professional-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="../../index.php">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?php echo $product['gender']; ?>.php">
                        <?php echo ucfirst($product['gender']); ?>'s Shoes
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?php echo $product['gender']; ?>.php?category=<?php echo urlencode($product['category_name']); ?>">
                        <?php echo $product['category_name']; ?>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo $product['name']; ?>
                </li>
            </ol>
        </nav>
        
        <div class="row">
            <!-- Product Gallery -->
            <div class="col-lg-6">
                <div class="product-gallery">
                    <div class="main-product-image" id="mainImage">
                        <img src="../../assets/images/uploads/<?php echo $product['image']; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="main-image">
                    </div>
                    
                    <div class="thumbnail-gallery">
                        <?php for($i = 0; $i < 4; $i++): ?>
                            <div class="thumbnail-item <?php echo $i === 0 ? 'active' : ''; ?>" 
                                 onclick="changeMainImage(this)">
                                <img src="../../assets/images/uploads/<?php echo $product['image']; ?>" 
                                     alt="Product view <?php echo $i + 1; ?>">
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            
            <!-- Product Information -->
            <div class="col-lg-6">
                <div class="product-info">
                    <div class="product-brand"><?php echo htmlspecialchars($product['brand_name']); ?></div>
                    <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <!-- Price Section -->
                    <div class="price-container">
                        <?php if($product['discount_price']): ?>
                            <span class="current-price">$<?php echo number_format($product['discount_price'], 2); ?></span>
                            <span class="original-price">$<?php echo number_format($product['price'], 2); ?></span>
                            <span class="discount-badge">
                                Save <?php echo round(100 - ($product['discount_price'] / $product['price'] * 100)); ?>%
                            </span>
                        <?php else: ?>
                            <span class="current-price">$<?php echo number_format($product['price'], 2); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Product Badges -->
                    <div class="product-badges">
                        <span class="product-badge badge-category"><?php echo $product['category_name']; ?></span>
                        <span class="product-badge badge-size">Size: <?php echo $product['size']; ?></span>
                        <span class="product-badge badge-color">Color: <?php echo $product['color']; ?></span>
                        <?php if($product['quantity'] > 0): ?>
                            <span class="product-badge badge-stock">
                                <i class="fas fa-check me-1"></i>In Stock
                            </span>
                        <?php else: ?>
                            <span class="product-badge badge-out-stock">
                                <i class="fas fa-times me-1"></i>Out of Stock
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Product Description -->
                    <div class="product-description">
                        <i class="fas fa-info-circle me-2 text-blue-500"></i>
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
                    
                    <!-- Purchase Form -->
                    <form class="add-to-cart-form">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="product_type" value="new">
                        
                        <div class="purchase-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="size" class="form-label">
                                            <i class="fas fa-ruler me-2"></i>Size
                                        </label>
                                        <select class="form-select" id="size" name="size" required>
                                            <option value="<?php echo $product['size']; ?>"><?php echo $product['size']; ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="quantity" class="form-label">
                                            <i class="fas fa-shopping-cart me-2"></i>Quantity
                                        </label>
                                        <input type="number" class="form-control" id="quantity" name="quantity" 
                                               min="1" max="<?php echo $product['quantity']; ?>" value="1" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-3">
                                <?php if($product['quantity'] > 0): ?>
                                    <button type="submit" class="btn btn-primary-custom">
                                        <i class="fas fa-shopping-cart me-2"></i>
                                        Add to Cart
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary-custom" disabled>
                                        <i class="fas fa-ban me-2"></i>
                                        Out of Stock
                                    </button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-outline-custom">
                                    <i class="far fa-heart me-2"></i>
                                    Add to Wishlist
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Product Tabs -->
        <div class="product-tabs-container">
            <ul class="nav nav-tabs professional-nav-tabs" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="details-tab" data-bs-toggle="tab" 
                            data-bs-target="#details" type="button" role="tab">
                        <i class="fas fa-info-circle me-2"></i>Details
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" 
                            data-bs-target="#shipping" type="button" role="tab">
                        <i class="fas fa-truck me-2"></i>Shipping & Returns
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" 
                            data-bs-target="#reviews" type="button" role="tab">
                        <i class="fas fa-star me-2"></i>Reviews
                    </button>
                </li>
            </ul>
            
            <div class="tab-content tab-content-professional" id="productTabsContent">
                <div class="tab-pane fade show active" id="details" role="tabpanel">
                    <h5><i class="fas fa-clipboard-list me-2 text-blue-500"></i>Product Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-4"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light p-4 rounded-3">
                                <h6 class="fw-bold mb-3">Specifications</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fas fa-tag me-2 text-blue-500"></i>
                                        <strong>Brand:</strong> <?php echo htmlspecialchars($product['brand_name']); ?>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-layer-group me-2 text-green-500"></i>
                                        <strong>Category:</strong> <?php echo htmlspecialchars($product['category_name']); ?>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-palette me-2 text-purple-500"></i>
                                        <strong>Color:</strong> <?php echo htmlspecialchars($product['color']); ?>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-ruler me-2 text-orange-500"></i>
                                        <strong>Size:</strong> <?php echo htmlspecialchars($product['size']); ?>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-venus-mars me-2 text-pink-500"></i>
                                        <strong>Gender:</strong> <?php echo ucfirst($product['gender']); ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="shipping" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-shipping-fast me-2 text-blue-500"></i>Shipping Information</h5>
                            <div class="bg-light p-4 rounded-3 mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-truck text-blue-500 me-3 fs-4"></i>
                                    <div>
                                        <h6 class="mb-1">Standard Shipping</h6>
                                        <p class="mb-0 text-muted">3-5 business days - $5.99</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-rocket text-green-500 me-3 fs-4"></i>
                                    <div>
                                        <h6 class="mb-1">Express Shipping</h6>
                                        <p class="mb-0 text-muted">2-3 business days - $12.99</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-gift text-purple-500 me-3 fs-4"></i>
                                    <div>
                                        <h6 class="mb-1">Free Shipping</h6>
                                        <p class="mb-0 text-muted">On orders over $50</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-undo me-2 text-orange-500"></i>Returns Policy</h5>
                            <div class="bg-light p-4 rounded-3">
                                <ul class="list-unstyled">
                                    <li class="mb-3">
                                        <i class="fas fa-calendar-alt me-2 text-blue-500"></i>
                                        30-day return window
                                    </li>
                                    <li class="mb-3">
                                        <i class="fas fa-tags me-2 text-green-500"></i>
                                        Original tags must be attached
                                    </li>
                                    <li class="mb-3">
                                        <i class="fas fa-shoe-prints me-2 text-purple-500"></i>
                                        Unworn condition required
                                    </li>
                                    <li>
                                        <i class="fas fa-money-bill-wave me-2 text-orange-500"></i>
                                        Full refund or exchange
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="reviews" role="tabpanel">
                    <h5><i class="fas fa-star me-2 text-yellow-500"></i>Customer Reviews</h5>
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="fas fa-info-circle me-3 fs-4"></i>
                        <div>
                            <strong>Be the first to review!</strong><br>
                            Share your experience with this product to help other customers.
                        </div>
                    </div>
                    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#reviewModal">
                        <i class="fas fa-edit me-2"></i>Write a Review
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if($related_products->num_rows > 0): ?>
            <div class="related-products-section">
                <h2 class="section-title">You May Also Like</h2>
                
                <div class="row">
                    <?php while($related = $related_products->fetch_assoc()): ?>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="product-card-professional">
                                <?php if($related['discount_price']): ?>
                                    <div class="product-card-badge">Sale</div>
                                <?php endif; ?>
                                
                                <div class="product-card-image">
                                    <img src="../../assets/images/uploads/<?php echo $related['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($related['name']); ?>">
                                </div>
                                
                                <div class="product-card-body">
                                    <div class="product-card-brand"><?php echo htmlspecialchars($related['brand_name']); ?></div>
                                    <h5 class="product-card-title"><?php echo htmlspecialchars($related['name']); ?></h5>
                                    
                                    <div class="product-card-price">
                                        <?php if($related['discount_price']): ?>
                                            <span class="fw-bold text-danger fs-5">$<?php echo number_format($related['discount_price'], 2); ?></span>
                                            <span class="text-muted text-decoration-line-through">$<?php echo number_format($related['price'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="fw-bold fs-5">$<?php echo number_format($related['price'], 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="product-card-footer">
                                    <a href="detail.php?id=<?php echo $related['id']; ?>" 
                                       class="btn btn-outline-primary btn-sm-custom">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                    <button class="btn btn-primary btn-sm-custom add-to-cart" 
                                            data-id="<?php echo $related['id']; ?>" data-type="new">
                                        <i class="fas fa-cart-plus me-1"></i>Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header" style="border: none; padding: 2rem 2rem 1rem;">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-star me-2 text-yellow-500"></i>Write a Review
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1rem 2rem 2rem;">
                <form id="reviewForm">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Rating</label>
                        <div class="rating d-flex gap-2 fs-4">
                            <i class="far fa-star text-muted" data-rating="1" style="cursor: pointer;"></i>
                            <i class="far fa-star text-muted" data-rating="2" style="cursor: pointer;"></i>
                            <i class="far fa-star text-muted" data-rating="3" style="cursor: pointer;"></i>
                            <i class="far fa-star text-muted" data-rating="4" style="cursor: pointer;"></i>
                            <i class="far fa-star text-muted" data-rating="5" style="cursor: pointer;"></i>
                            <input type="hidden" name="rating" id="ratingValue" value="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reviewTitle" class="form-label fw-bold">Title</label>
                        <input type="text" class="form-control" id="reviewTitle" name="title" 
                               placeholder="Summarize your review" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reviewText" class="form-label fw-bold">Review</label>
                        <textarea class="form-control" id="reviewText" name="review" rows="4" 
                                  placeholder="Share your experience with this product..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border: none; padding: 1rem 2rem 2rem;">
                <button type="button" class="btn btn-outline-custom me-2" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="submitReview">
                    <i class="fas fa-paper-plane me-2"></i>Submit Review
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Thumbnail image switching
    window.changeMainImage = function(thumbnail) {
        // Remove active class from all thumbnails
        $('.thumbnail-item').removeClass('active');
        // Add active class to clicked thumbnail
        $(thumbnail).addClass('active');
        
        // Change main image
        const newSrc = $(thumbnail).find('img').attr('src');
        $('.main-image').attr('src', newSrc);
    };
    
    // Rating stars functionality
    $('.rating i').hover(function() {
        const rating = $(this).data('rating');
        $('.rating i').each(function() {
            if($(this).data('rating') <= rating) {
                $(this).removeClass('far text-muted').addClass('fas text-warning');
            } else {
                $(this).removeClass('fas text-warning').addClass('far text-muted');
            }
        });
    }).click(function() {
        const rating = $(this).data('rating');
        $('#ratingValue').val(rating);
        
        // Keep the selected rating highlighted
        $('.rating i').each(function() {
            if($(this).data('rating') <= rating) {
                $(this).removeClass('far text-muted').addClass('fas text-warning');
            } else {
                $(this).removeClass('fas text-warning').addClass('far text-muted');
            }
        });
    });
    
    // Reset rating on mouse leave
    $('.rating').mouseleave(function() {
        const currentRating = $('#ratingValue').val();
        $('.rating i').each(function() {
            if($(this).data('rating') <= currentRating) {
                $(this).removeClass('far text-muted').addClass('fas text-warning');
            } else {
                $(this).removeClass('fas text-warning').addClass('far text-muted');
            }
        });
    });
    
    // Add to cart form - main product
    $('.add-to-cart-form').submit(function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Adding...');
        
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
                    showAlert('success', 'Product added to cart successfully!');
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
            .html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Adding...');
        
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
    
    // Professional alert function
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
        
        const alert = $(`
            <div class="alert ${alertClass} custom-alert alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="${icon} me-3 fs-5"></i>
                    <div>
                        <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong><br>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
                </div>
            </div>
        `);
        
        $('body').append(alert);
        
        // Auto dismiss after 4 seconds
        setTimeout(function() {
            alert.alert('close');
        }, 4000);
    }
    
    // Submit review
    $('#submitReview').click(function() {
        const rating = $('#ratingValue').val();
        if(rating == 0) {
            showAlert('danger', 'Please select a rating before submitting.');
            return;
        }
        
        // Here you would typically submit the review via AJAX
        showAlert('success', 'Thank you for your review! It will be published after moderation.');
        $('#reviewModal').modal('hide');
        $('#reviewForm')[0].reset();
        $('#ratingValue').val(0);
        $('.rating i').removeClass('fas text-warning').addClass('far text-muted');
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>
