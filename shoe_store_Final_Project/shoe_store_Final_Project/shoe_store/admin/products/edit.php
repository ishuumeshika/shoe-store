<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

// Only allow admin access
requireAdmin();

// Verify product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage.php");
    exit;
}

$product_id = (int)$_GET['id'];

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables
$errors = [];
$product = [];

// Define upload directory
$upload_dir = __DIR__ . '/../../assets/images/uploads/';

// Get product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage.php");
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();

// Get brands and categories for dropdowns
$brands = $conn->query("SELECT * FROM brands ORDER BY name") or die($conn->error);
$categories = $conn->query("SELECT * FROM categories ORDER BY name") or die($conn->error);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    // Sanitize and validate inputs
    $product['name'] = trim($_POST['name']);
    $product['brand_id'] = (int)$_POST['brand_id'];
    $product['category_id'] = (int)$_POST['category_id'];
    $product['description'] = trim($_POST['description']);
    $product['price'] = (float)$_POST['price'];
    $product['discount_price'] = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
    $product['size'] = trim($_POST['size']);
    $product['color'] = trim($_POST['color']);
    $product['gender'] = $_POST['gender'];
    $product['quantity'] = (int)$_POST['quantity'];

    // Validation
    if (empty($product['name'])) {
        $errors['name'] = 'Product name is required';
    } elseif (strlen($product['name']) > 255) {
        $errors['name'] = 'Product name must be less than 255 characters';
    }

    if ($product['brand_id'] <= 0) {
        $errors['brand_id'] = 'Please select a brand';
    }

    if ($product['category_id'] <= 0) {
        $errors['category_id'] = 'Please select a category';
    }

    if ($product['price'] <= 0) {
        $errors['price'] = 'Price must be greater than 0';
    }

    if ($product['discount_price'] !== null && $product['discount_price'] >= $product['price']) {
        $errors['discount_price'] = 'Discount price must be less than regular price';
    }

    if (empty($product['size'])) {
        $errors['size'] = 'Size is required';
    }

    if (empty($product['color'])) {
        $errors['color'] = 'Color is required';
    }

    if ($product['quantity'] < 0) {
        $errors['quantity'] = 'Quantity cannot be negative';
    }

    // Handle file upload if new image is provided
    $new_image_uploaded = false;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['image']['type'];
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file_type, $allowed_types)) {
            $errors['image'] = 'Only JPG, PNG, GIF, and WebP images are allowed';
        } elseif ($_FILES['image']['size'] > $max_size) {
            $errors['image'] = 'Image size must be less than 2MB';
        } else {
            $new_image = 'product_' . time() . '_' . uniqid() . '.' . $file_ext;
            $target_path = $upload_dir . $new_image;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                // Delete old image if it exists
                if (!empty($product['image']) && file_exists($upload_dir . $product['image'])) {
                    unlink($upload_dir . $product['image']);
                }
                $product['image'] = $new_image;
                $new_image_uploaded = true;
            } else {
                $errors['image'] = 'Failed to upload image';
            }
        }
    }

    // If no errors, update product
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE products SET 
                              name = ?, brand_id = ?, category_id = ?, description = ?, 
                              price = ?, discount_price = ?, size = ?, color = ?, 
                              gender = ?, quantity = ?" . ($new_image_uploaded ? ", image = ?" : "") . "
                              WHERE id = ?") or die($conn->error);
        
        if ($new_image_uploaded) {
            $stmt->bind_param("siisddsssssi", 
                $product['name'],
                $product['brand_id'],
                $product['category_id'],
                $product['description'],
                $product['price'],
                $product['discount_price'],
                $product['size'],
                $product['color'],
                $product['gender'],
                $product['quantity'],
                $product['image'],
                $product_id
            );
        } else {
            $stmt->bind_param("siisddssssi", 
                $product['name'],
                $product['brand_id'],
                $product['category_id'],
                $product['description'],
                $product['price'],
                $product['discount_price'],
                $product['size'],
                $product['color'],
                $product['gender'],
                $product['quantity'],
                $product_id
            );
        }

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Product updated successfully!';
            header("Location: manage.php");
            exit;
        } else {
            $errors['database'] = 'Failed to update product. Error: ' . $conn->error;
            // Delete new image if database update failed
            if ($new_image_uploaded && file_exists($upload_dir . $product['image'])) {
                unlink($upload_dir . $product['image']);
            }
        }
        $stmt->close();
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/../../admin/includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Product</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="manage.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Products
                    </a>
                </div>
            </div>

            <?php if (!empty($errors['database'])): ?>
                <div class="alert alert-danger"><?= $errors['database'] ?></div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                           id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                                    <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback"><?= $errors['name'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="brand_id" class="form-label">Brand <span class="text-danger">*</span></label>
                                        <select class="form-select <?= isset($errors['brand_id']) ? 'is-invalid' : '' ?>" 
                                                id="brand_id" name="brand_id" required>
                                            <option value="">Select Brand</option>
                                            <?php while ($brand = $brands->fetch_assoc()): ?>
                                                <option value="<?= $brand['id'] ?>" 
                                                    <?= $product['brand_id'] == $brand['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($brand['name']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <?php if (isset($errors['brand_id'])): ?>
                                            <div class="invalid-feedback"><?= $errors['brand_id'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                        <select class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" 
                                                id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php while ($category = $categories->fetch_assoc()): ?>
                                                <option value="<?= $category['id'] ?>" 
                                                    <?= $product['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <?php if (isset($errors['category_id'])): ?>
                                            <div class="invalid-feedback"><?= $errors['category_id'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="5"><?= htmlspecialchars($product['description']) ?></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="price" class="form-label">Price ($) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0.01" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>" 
                                               id="price" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
                                        <?php if (isset($errors['price'])): ?>
                                            <div class="invalid-feedback"><?= $errors['price'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="discount_price" class="form-label">Discount Price ($)</label>
                                        <input type="number" step="0.01" min="0" class="form-control <?= isset($errors['discount_price']) ? 'is-invalid' : '' ?>" 
                                               id="discount_price" name="discount_price" value="<?= htmlspecialchars($product['discount_price'] ?? '') ?>">
                                        <?php if (isset($errors['discount_price'])): ?>
                                            <div class="invalid-feedback"><?= $errors['discount_price'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="size" class="form-label">Size <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?= isset($errors['size']) ? 'is-invalid' : '' ?>" 
                                               id="size" name="size" value="<?= htmlspecialchars($product['size']) ?>" required>
                                        <?php if (isset($errors['size'])): ?>
                                            <div class="invalid-feedback"><?= $errors['size'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="color" class="form-label">Color <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?= isset($errors['color']) ? 'is-invalid' : '' ?>" 
                                               id="color" name="color" value="<?= htmlspecialchars($product['color']) ?>" required>
                                        <?php if (isset($errors['color'])): ?>
                                            <div class="invalid-feedback"><?= $errors['color'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                        <select class="form-select" id="gender" name="gender" required>
                                            <option value="men" <?= $product['gender'] == 'men' ? 'selected' : '' ?>>Men</option>
                                            <option value="women" <?= $product['gender'] == 'women' ? 'selected' : '' ?>>Women</option>
                                            <option value="kids" <?= $product['gender'] == 'kids' ? 'selected' : '' ?>>Kids</option>
                                            <option value="unisex" <?= $product['gender'] == 'unisex' ? 'selected' : '' ?>>Unisex</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity in Stock <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control <?= isset($errors['quantity']) ? 'is-invalid' : '' ?>" 
                                           id="quantity" name="quantity" min="0" value="<?= htmlspecialchars($product['quantity']) ?>" required>
                                    <?php if (isset($errors['quantity'])): ?>
                                        <div class="invalid-feedback"><?= $errors['quantity'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label for="image" class="form-label">Product Image</label>
                                    <input type="file" class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>" 
                                           id="image" name="image" accept="image/*">
                                    <?php if (isset($errors['image'])): ?>
                                        <div class="invalid-feedback"><?= $errors['image'] ?></div>
                                    <?php endif; ?>
                                    <div class="mt-2">
                                        <p class="mb-1">Current Image:</p>
                                        <img src="../../../assets/images/uploads/<?= $product['image'] ?>" 
                                             alt="Current Product Image" class="img-thumbnail" style="max-height: 150px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <button type="reset" class="btn btn-outline-secondary me-md-2">Reset Changes</button>
                            <button type="submit" class="btn btn-primary">Update Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Client-side validation and image preview
(function() {
    'use strict';
    
    // Form validation
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
    
    // Image preview for new image
    document.getElementById('image').addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        const file = e.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = `
                    <p class="mb-1">New Image Preview:</p>
                    <img src="${e.target.result}" alt="Preview" class="img-thumbnail" style="max-height: 150px;">
                    <p class="text-muted mt-1">Current image will be replaced</p>
                `;
            }
            
            reader.readAsDataURL(file);
        }
    });
})();
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>