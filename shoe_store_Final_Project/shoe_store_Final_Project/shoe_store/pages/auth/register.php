<?php ob_start(); ?>
<?php
require_once '../../includes/header.php';
require_once '../../includes/config.php';

// Check if user is already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

// Initialize variables
$name = $email = $phone = $address = '';
$errors = [];

// Process registration form
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate inputs
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Validation
    if(empty($name)) {
        $errors['name'] = 'Name is required';
    }
    
    if(empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email';
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows > 0) {
            $errors['email'] = 'Email is already taken';
        }
        $stmt->close();
    }
    
    if(empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif(strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    }
    
    if($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    // If no errors, register user
    if(empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $hashed_password, $phone, $address);
        
        if($stmt->execute()) {
            $_SESSION['success_message'] = 'Registration successful! Please login.';
            header("Location: http://localhost/shoe_store/pages/auth/login.php");
            exit;
        } else {
            $errors['database'] = 'Registration failed. Please try again.';
        }
        $stmt->close();
    }
}
?>

<style>
    body {
      
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .auth-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .auth-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
        max-width: 500px;
        width: 100%;
        margin: 20px 0;
    }
    
    .auth-header {
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        color: white;
        padding: 40px 30px 30px;
        text-align: center;
        position: relative;
    }
    
    .auth-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="shoe-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><path d="M10 5c2 0 4 1 5 3s0 4-2 5-4 0-5-2-1-4 1-5z" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23shoe-pattern)"/></svg>') repeat;
        opacity: 0.1;
    }
    
    .auth-header h2 {
        margin: 0;
        font-size: 2rem;
        font-weight: 700;
        position: relative;
        z-index: 1;
    }
    
    .auth-header p {
        margin: 10px 0 0;
        opacity: 0.9;
        font-size: 1.1rem;
        position: relative;
        z-index: 1;
    }
    
    .shoe-icon {
        font-size: 3rem;
        margin-bottom: 15px;
        display: block;
        position: relative;
        z-index: 1;
    }
    
    .auth-body {
        padding: 40px 30px;
        max-height: 70vh;
        overflow-y: auto;
    }
    
    .form-row {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
    }
    
    .form-group {
        margin-bottom: 25px;
        position: relative;
        flex: 1;
    }
    
    .form-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .form-control {
        border: 2px solid #e1e8ed;
        border-radius: 12px;
        padding: 15px 20px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8f9fa;
        width: 100%;
    }
    
    .form-control:focus {
        border-color: #27ae60;
        box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.1);
        background: white;
        outline: none;
    }
    
    .form-control.is-invalid {
        border-color: #e74c3c;
        background: #fdf2f2;
    }
    
    .invalid-feedback {
        color: #e74c3c;
        font-size: 0.875rem;
        margin-top: 5px;
        font-weight: 500;
    }
    
    .btn-register {
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        border: none;
        border-radius: 12px;
        padding: 15px;
        font-size: 1.1rem;
        font-weight: 600;
        color: white;
        width: 100%;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .btn-register:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(39, 174, 96, 0.3);
        background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    }
    
    .auth-links {
        text-align: center;
        margin-top: 30px;
        padding-top: 25px;
        border-top: 1px solid #e1e8ed;
    }
    
    .auth-links p {
        margin: 10px 0;
        color: #5a6c7d;
    }
    
    .auth-links a {
        color: #27ae60;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }
    
    .auth-links a:hover {
        color: #2ecc71;
        text-decoration: underline;
    }
    
    .alert {
        border-radius: 12px;
        padding: 15px 20px;
        margin-bottom: 25px;
        border: none;
        font-weight: 500;
    }
    
    .alert-success {
        background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
        color: white;
    }
    
    .alert-danger {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
        color: white;
    }
    
    .password-strength {
        margin-top: 5px;
        font-size: 0.8rem;
        color: #7f8c8d;
    }
    
    .strength-bar {
        height: 3px;
        background: #ecf0f1;
        border-radius: 2px;
        margin-top: 5px;
        overflow: hidden;
    }
    
    .strength-fill {
        height: 100%;
        transition: all 0.3s ease;
        border-radius: 2px;
    }
    
    @media (max-width: 576px) {
        .auth-container {
            padding: 10px;
        }
        
        .auth-header {
            padding: 30px 20px 20px;
        }
        
        .auth-body {
            padding: 30px 20px;
        }
        
        .form-row {
            flex-direction: column;
            gap: 0;
        }
        
        .shoe-icon {
            font-size: 2.5rem;
        }
        
        .auth-header h2 {
            font-size: 1.75rem;
        }
    }
</style>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="shoe-icon">👟</div>
            <h2>Join Our Store</h2>
            <p>Create your account and start shopping</p>
        </div>
        <div class="auth-body">
            <?php if(isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <strong>✅ Success:</strong> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($errors['database'])): ?>
                <div class="alert alert-danger">
                    <strong>⚠️ Error:</strong> <?php echo $errors['database']; ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="form-group">
                    <label for="name" class="form-label">👤 Full Name</label>
                    <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                           id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" 
                           placeholder="Enter your full name">
                    <?php if(isset($errors['name'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">📧 Email Address</label>
                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                           id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" 
                           placeholder="Enter your email address">
                    <?php if(isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password" class="form-label">🔒 Password</label>
                        <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                               id="password" name="password" placeholder="Create a password">
                        <?php if(isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                        <?php endif; ?>
                        <div class="password-strength">
                            <small>Minimum 6 characters required</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">🔒 Confirm Password</label>
                        <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                               id="confirm_password" name="confirm_password" placeholder="Confirm your password">
                        <?php if(isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">📱 Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($phone); ?>" 
                           placeholder="Enter your phone number (optional)">
                </div>
                
                <div class="form-group">
                    <label for="address" class="form-label">🏠 Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3" 
                              placeholder="Enter your address (optional)"><?php echo htmlspecialchars($address); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-register">Create Account</button>
            </form>
            
            <div class="auth-links">
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>