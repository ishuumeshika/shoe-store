<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';

// Only allow logged-in users
requireLogin();

// Get current user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Initialize variables
$errors = [];
$success = false;

// Process profile update form
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if(empty($name)) {
        $errors['name'] = 'Name is required';
    }
    
    if(empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email';
    } else {
        // Check if email is already taken by another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows > 0) {
            $errors['email'] = 'Email is already taken';
        }
        $stmt->close();
    }
    
    // Password change logic
    $password_changed = false;
    if(!empty($new_password)) {
        if(empty($current_password)) {
            $errors['current_password'] = 'Current password is required to change password';
        } elseif(!password_verify($current_password, $user['password'])) {
            $errors['current_password'] = 'Current password is incorrect';
        }
        
        if(strlen($new_password) < 6) {
            $errors['new_password'] = 'Password must be at least 6 characters';
        }
        
        if($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        if(empty($errors)) {
            $password_changed = true;
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        }
    }
    
    // Update profile if no errors
    if(empty($errors)) {
        if($password_changed) {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $name, $email, $phone, $address, $hashed_password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $email, $phone, $address, $user_id);
        }
        
        if($stmt->execute()) {
            // Update session variables
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            $success = true;
            $_SESSION['success_message'] = 'Profile updated successfully!';
            header("Refresh:0"); // Refresh to show updated data
        } else {
            $errors['database'] = 'Failed to update profile. Please try again.';
        }
        $stmt->close();
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">User Menu</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="profile.php" class="list-group-item list-group-item-action active">Profile</a>
                    <a href="orders.php" class="list-group-item list-group-item-action">My Orders</a>
                    <a href="../used_shoes/sell.php" class="list-group-item list-group-item-action">Sell Used Shoes</a>
                    <a href="../used_shoes/my_listings.php" class="list-group-item list-group-item-action">My Listings</a>
                    <a href="../auth/logout.php" class="list-group-item list-group-item-action text-danger">Logout</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">My Profile</h5>
                </div>
                <div class="card-body">
                    <?php if($success): ?>
                        <div class="alert alert-success">Profile updated successfully!</div>
                    <?php endif; ?>
                    
                    <?php if(!empty($errors['database'])): ?>
                        <div class="alert alert-danger"><?php echo $errors['database']; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                       id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>">
                                <?php if(isset($errors['name'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                       id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                <?php if(isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="1"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        <h5 class="mb-3">Change Password</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control <?php echo isset($errors['current_password']) ? 'is-invalid' : ''; ?>" 
                                       id="current_password" name="current_password">
                                <?php if(isset($errors['current_password'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['current_password']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control <?php echo isset($errors['new_password']) ? 'is-invalid' : ''; ?>" 
                                       id="new_password" name="new_password">
                                <?php if(isset($errors['new_password'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['new_password']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                       id="confirm_password" name="confirm_password">
                                <?php if(isset($errors['confirm_password'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>