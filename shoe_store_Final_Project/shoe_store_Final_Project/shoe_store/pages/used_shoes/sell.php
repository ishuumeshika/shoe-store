<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';

// Only allow logged-in users
requireLogin();

$user_id = $_SESSION['user_id'];
$errors = [];
$success = false;

// Process form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize inputs
    $name = trim($_POST['name']);
    $brand = trim($_POST['brand']);
    $size = trim($_POST['size']);
    $color = trim($_POST['color']);
    $gender = $_POST['gender'];
    $category = trim($_POST['category']);
    $shoe_condition = $_POST['shoe_condition'];
    $price = (float)$_POST['price'];
    $quantity = (int)$_POST['quantity'];
    $description = trim($_POST['description']);
    
    // Validation
    if(empty($name)) {
        $errors['name'] = 'Shoe name is required';
    }
    
    if(empty($brand)) {
        $errors['brand'] = 'Brand is required';
    }
    
    if(empty($size)) {
        $errors['size'] = 'Size is required';
    }
    
    if(empty($color)) {
        $errors['color'] = 'Color is required';
    }
    
    if($price <= 0) {
        $errors['price'] = 'Price must be greater than 0';
    }
    
    if($quantity <= 0) {
        $errors['quantity'] = 'Quantity must be at least 1';
    }
    
    // Handle file upload
    $image = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        
        if(!in_array($file_type, $allowed_types)) {
            $errors['image'] = 'Only JPG, PNG, and GIF images are allowed';
        } else {
            $upload_dir = '../../assets/images/used_shoes/';
            $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image = uniqid('used_') . '.' . $file_ext;
            
            if(!move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image)) {
                $errors['image'] = 'Failed to upload image';
            }
        }
    } else {
        $errors['image'] = 'Shoe image is required';
    }
    
    // If no errors, insert listing
    if(empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO used_shoes 
                               (user_id, name, brand, size, color, gender, category, shoe_condition, 
                               price, quantity, description, image)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssssdiss", 
            $user_id, $name, $brand, $size, $color, $gender, $category, $shoe_condition,
            $price, $quantity, $description, $image
        );
        
        if($stmt->execute()) {
            $success = true;
            $_SESSION['success_message'] = 'Your used shoes listing has been submitted for approval!';
            header("Location: my_listings.php");
            exit;
        } else {
            $errors['database'] = 'Failed to submit listing. Please try again.';
        }
        $stmt->close();
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <?php include '../user/user_menu.php'; ?>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Sell Your Used Shoes</h5>
                </div>
                <div class="card-body">
                    <?php if($success): ?>
                        <div class="alert alert-success">Your used shoes listing has been submitted for approval!</div>
                    <?php endif; ?>
                    
                    <?php if(!empty($errors['database'])): ?>
                        <div class="alert alert-danger"><?php echo $errors['database']; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Shoe Name/Model</label>
                                    <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                           id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                    <?php if(isset($errors['name'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="brand" class="form-label">Brand</label>
                                    <input type="text" class="form-control <?php echo isset($errors['brand']) ? 'is-invalid' : ''; ?>" 
                                           id="brand" name="brand" value="<?php echo htmlspecialchars($_POST['brand'] ?? ''); ?>" required>
                                    <?php if(isset($errors['brand'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['brand']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="size" class="form-label">Size</label>
                                        <input type="text" class="form-control <?php echo isset($errors['size']) ? 'is-invalid' : ''; ?>" 
                                               id="size" name="size" value="<?php echo htmlspecialchars($_POST['size'] ?? ''); ?>" required>
                                        <?php if(isset($errors['size'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['size']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="color" class="form-label">Color</label>
                                        <input type="text" class="form-control <?php echo isset($errors['color']) ? 'is-invalid' : ''; ?>" 
                                               id="color" name="color" value="<?php echo htmlspecialchars($_POST['color'] ?? ''); ?>" required>
                                        <?php if(isset($errors['color'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['color']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="gender" class="form-label">Gender</label>
                                        <select class="form-select" id="gender" name="gender" required>
                                            <option value="men" <?php echo ($_POST['gender'] ?? '') == 'men' ? 'selected' : ''; ?>>Men</option>
                                            <option value="women" <?php echo ($_POST['gender'] ?? '') == 'women' ? 'selected' : ''; ?>>Women</option>
                                            <option value="kids" <?php echo ($_POST['gender'] ?? '') == 'kids' ? 'selected' : ''; ?>>Kids</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="category" class="form-label">Category</label>
                                        <input type="text" class="form-control" id="category" name="category" 
                                               value="<?php echo htmlspecialchars($_POST['category'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shoe_condition" class="form-label">Condition</label>
                                    <select class="form-select" id="shoe_condition" name="shoe_condition" required>
                                        <option value="new" <?php echo ($_POST['shoe_condition'] ?? '') == 'new' ? 'selected' : ''; ?>>New (never worn)</option>
                                        <option value="like_new" <?php echo ($_POST['shoe_condition'] ?? '') == 'like_new' ? 'selected' : ''; ?>>Like New (minimal wear)</option>
                                        <option value="good" <?php echo ($_POST['shoe_condition'] ?? '') == 'good' ? 'selected' : ''; ?>>Good (visible wear but still functional)</option>
                                        <option value="fair" <?php echo ($_POST['shoe_condition'] ?? '') == 'fair' ? 'selected' : ''; ?>>Fair (significant wear but usable)</option>
                                    </select>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="price" class="form-label">Price ($)</label>
                                        <input type="number" step="0.01" class="form-control <?php echo isset($errors['price']) ? 'is-invalid' : ''; ?>" 
                                               id="price" name="price" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
                                        <?php if(isset($errors['price'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['price']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="quantity" class="form-label">Quantity</label>
                                        <input type="number" class="form-control <?php echo isset($errors['quantity']) ? 'is-invalid' : ''; ?>" 
                                               id="quantity" name="quantity" min="1" value="<?php echo htmlspecialchars($_POST['quantity'] ?? 1); ?>" required>
                                        <?php if(isset($errors['quantity'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['quantity']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="image" class="form-label">Upload Photos</label>
                                    <input type="file" class="form-control <?php echo isset($errors['image']) ? 'is-invalid' : ''; ?>" 
                                           id="image" name="image" accept="image/*" required>
                                    <?php if(isset($errors['image'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['image']; ?></div>
                                    <?php endif; ?>
                                    <small class="text-muted">Upload clear photos of the shoes from multiple angles</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Submit Listing</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>