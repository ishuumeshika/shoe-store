<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';

requireAdmin();

$errors = [];
$success = false;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $role = $_POST['role'];
    
    // Validation
    if(empty($name)) $errors['name'] = 'Name is required';
    if(empty($email)) $errors['email'] = 'Email is required';
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email';
    if(empty($password)) $errors['password'] = 'Password is required';
    elseif(strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters';
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if($stmt->get_result()->num_rows > 0) {
        $errors['email'] = 'Email already exists';
    }
    
    if(empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $hashed_password, $phone, $address, $role);
        
        if($stmt->execute()) {
            $success = true;
            $_SESSION['success_message'] = 'User added successfully!';
            header("Location: manage.php");
            exit;
        } else {
            $errors['database'] = 'Failed to add user';
        }
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Add New User</h1>
            </div>
            
            <?php if($success): ?>
                <div class="alert alert-success">User added successfully!</div>
            <?php elseif(isset($errors['database'])): ?>
                <div class="alert alert-danger"><?= $errors['database'] ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                   name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                            <?php if(isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?= $errors['name'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                   name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            <?php if(isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= $errors['email'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                   name="password" required>
                            <?php if(isset($errors['password'])): ?>
                                <div class="invalid-feedback"><?= $errors['password'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" required>
                                <option value="customer" <?= ($_POST['role'] ?? '') == 'customer' ? 'selected' : '' ?>>Customer</option>
                                <option value="admin" <?= ($_POST['role'] ?? '') == 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="manage.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </main>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>