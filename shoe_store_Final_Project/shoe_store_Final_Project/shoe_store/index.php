<?php
// index.php
require_once 'includes/header.php';
?>

<!-- Full-Width Hero Slider -->
<section class="hero-slider position-relative">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="assets/images/slider1.jpg" class="d-block w-100" alt="Summer Collection" style="height: 600px; object-fit: cover;">
                <div class="carousel-caption">
                    <div class="hero-content">
                        <h1 class="hero-title">Summer Collection 2025</h1>
                        <p class="hero-subtitle">Step into style with our latest seasonal footwear</p>
                        <a href="pages/products/men.php" class="btn btn-hero">Shop Now</a>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <img src="assets/images/slider2.jpg" class="d-block w-100" alt="Limited Edition" style="height: 600px; object-fit: cover;">
                <div class="carousel-caption">
                    <div class="hero-content">
                        <h1 class="hero-title">Limited Edition</h1>
                        <p class="hero-subtitle">Exclusive designs crafted for the bold</p>
                        <a href="pages/products/women.php" class="btn btn-hero">Explore Now</a>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <img src="assets/images/slider3.jpg" class="d-block w-100" alt="Kids Collection" style="height: 600px; object-fit: cover;">
                <div class="carousel-caption">
                    <div class="hero-content">
                        <h1 class="hero-title">Kids Collection</h1>
                        <p class="hero-subtitle">Comfort and fun for every young adventurer</p>
                        <a href="pages/products/kids.php" class="btn btn-hero">View Collection</a>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</section>

<!-- Featured Products -->
<section class="featured-products">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Featured Products</h2>
            <p class="section-subtitle">Discover our handpicked selection of premium footwear</p>
        </div>
        <div class="row g-4">
            <?php
            // Fetch 4 random featured products
            $featured_query = "SELECT p.*, b.name as brand_name 
                              FROM products p 
                              JOIN brands b ON p.brand_id = b.id 
                              ORDER BY RAND() LIMIT 4";
            $featured_result = $conn->query($featured_query);
            
            if($featured_result->num_rows > 0):
                while($product = $featured_result->fetch_assoc()):
            ?>
            <div class="col-lg-3 col-md-6">
                <div class="product-card">
                    <div class="product-image-wrapper">
                        <?php if($product['discount_price']): ?>
                            <div class="product-badge sale-badge">Sale</div>
                        <?php else: ?>
                            <div class="product-badge new-badge">New</div>
                        <?php endif; ?>
                        <img src="assets/images/uploads/<?php echo $product['image']; ?>" 
                             class="product-image" 
                             alt="<?php echo $product['name']; ?>">
                        <div class="product-overlay">
                            <a href="pages/products/details.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-outline-light btn-sm">Quick View</a>
                        </div>
                    </div>
                    <div class="product-info">
                        <div class="product-brand"><?php echo $product['brand_name']; ?></div>
                        <h5 class="product-name"><?php echo $product['name']; ?></h5>
                        <div class="product-rating">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="rating-text">(4.8)</span>
                        </div>
                        <div class="product-price">
                            <?php if($product['discount_price']): ?>
                                <span class="price-current">$<?php echo $product['discount_price']; ?></span>
                                <span class="price-original">$<?php echo $product['price']; ?></span>
                            <?php else: ?>
                                <span class="price-current">$<?php echo $product['price']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="product-actions">
                        <a href="pages/products/details.php?id=<?php echo $product['id']; ?>" 
                           class="btn btn-outline-dark btn-sm">View Details</a>
                    </div>
                </div>
            </div>
            <?php 
                endwhile;
            else:
                echo "<div class='col-12'><p class='text-center text-muted'>No featured products found.</p></div>";
            endif;
            ?>
        </div>
        <div class="text-center mt-5">
            <a href="pages/products/all.php" class="btn btn-outline-dark btn-lg">View All Products</a>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="why-choose-us">
    <div class="container">
        <div class="section-header text-center">
            <div>
                <hr>

                <h2 class="section-title mt-5">Why Choose Us</h2>
                <p class="section-subtitle">At TrendSole, we bring you the latest in footwear fashion with a perfect blend of
                comfort, quality, and style. Whether you're looking for casual sneakers, formal shoes, or athletic wear, our
                carefully curated collection meets every need and taste. We source from trusted brands and offer competitive 
                prices to ensure you get the best value. With fast shipping, easy returns, and top-notch customer service, shopping with 
                TrendSole is smooth, secure, and satisfying. Step up your shoe game—choose TrendSole today!</p>
                <hr class="mt-5">
            </div>
        </div>
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="benefits-list">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="benefit-content">
                            <h5 class="benefit-title">Premium Quality</h5>
                            <p class="benefit-description">Handpicked shoes from top brands for unmatched durability and comfort.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <div class="benefit-content">
                            <h5 class="benefit-title">Fast & Reliable Shipping</h5>
                            <p class="benefit-description">Delivered to your door with speed and care, tracking included.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="benefit-content">
                            <h5 class="benefit-title">Competitive Pricing</h5>
                            <p class="benefit-description">Best prices guaranteed with regular discounts and exclusive offers.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="feature-image-wrapper">
                    <div class="feature-image-bg"></div>
                    <img src="assets/images/shoe-placeholder.jpg" 
                         class="feature-image" 
                         alt="Featured Shoe">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Used Shoes Gallery -->
<section class="used-shoes-gallery">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="section-title">Quality Pre-Owned Shoes</h2>
                <p class="section-subtitle">Sustainable fashion at unbeatable prices</p>
            </div>
            <a href="pages/used_shoes/browse.php" class="btn btn-outline-dark">View All</a>
        </div>
        <div class="row g-4">
            <?php
            // Fetch 4 approved used shoes
            $used_query = "SELECT u.*, us.name as user_name 
                           FROM used_shoes u 
                           JOIN users us ON u.user_id = us.id 
                           WHERE u.status = 'approved' 
                           ORDER BY created_at DESC LIMIT 4";
            $used_result = $conn->query($used_query);
            
            if($used_result->num_rows > 0):
                while($used = $used_result->fetch_assoc()):
            ?>
            <div class="col-lg-3 col-md-6">
                <div class="used-shoe-card">
                    <div class="used-shoe-image-wrapper">
                        <div class="used-shoe-badges">
                            <span class="badge bg-primary">Pre-owned</span>
                            <span class="badge bg-dark"><?php echo $used['size']; ?></span>
                        </div>
                        <img src="assets/images/used_shoes/<?php echo $used['image']; ?>" 
                             class="used-shoe-image" 
                             alt="<?php echo $used['name']; ?>">
                        <div class="used-shoe-overlay">
                            <a href="pages/used_shoes/details.php?id=<?php echo $used['id']; ?>" 
                               class="btn btn-outline-light btn-sm">Quick View</a>
                        </div>
                    </div>
                    <div class="used-shoe-info">
                        <div class="condition-info">
                            <span class="condition-label">Condition:</span>
                            <span class="condition-value"><?php echo ucfirst($used['shoe_condition']); ?></span>
                        </div>
                        <h5 class="used-shoe-name"><?php echo $used['name']; ?></h5>
                        <div class="used-shoe-rating">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <span class="rating-text">(4.2)</span>
                        </div>
                        <div class="used-shoe-price">
                            <span class="price-current">$<?php echo $used['price']; ?></span>
                        </div>
                        <div class="seller-info">Sold by: <?php echo $used['user_name']; ?></div>
                    </div>
                    <div class="used-shoe-actions">
                        <a href="pages/used_shoes/details.php?id=<?php echo $used['id']; ?>" 
                           class="btn btn-outline-dark btn-sm">View Details</a>
                        <!-- <button class="btn btn-dark btn-sm add-to-cart" 
                                data-id="<?php echo $used['id']; ?>" 
                                data-type="used">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button> -->
                    </div>
                </div>
            </div>
            <?php 
                endwhile;
            else:
                echo "<div class='col-12'><p class='text-center text-muted'>No used shoes available at the moment.</p></div>";
            endif;
            ?>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>