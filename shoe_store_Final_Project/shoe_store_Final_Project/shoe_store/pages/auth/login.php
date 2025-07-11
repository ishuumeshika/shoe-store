<?php
require_once '../../includes/header.php';

// Check if user is already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

// Initialize variables
$email = '';
$errors = [];

// Process login form
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validation
    if(empty($email)) {
        $errors['email'] = 'Email is required';
    }
    
    if(empty($password)) {
        $errors['password'] = 'Password is required';
    }
    
    // If no errors, attempt login
    if(empty($errors)) {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if(password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect based on role
                if($user['role'] == 'admin') {
                    header("Location: ../../admin/dashboard.php");
                } else {
                    header("Location: ../../index.php");
                }
                exit;
            } else {
                $errors['login'] = 'Invalid email or password';
            }
        } else {
            $errors['login'] = 'Invalid email or password';
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
        max-width: 450px;
        width: 100%;
    }
    
    .auth-header {
        background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
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
    }
    
    .form-group {
        margin-bottom: 25px;
        position: relative;
    }
    
    .form-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }
    
    .form-control {
        border: 2px solid #e1e8ed;
        border-radius: 12px;
        padding: 15px 20px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }
    
    .form-control:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
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
    
    .form-check {
        margin: 20px 0;
    }
    
    .form-check-input {
        margin-right: 10px;
        transform: scale(1.2);
    }
    
    .form-check-label {
        color: #5a6c7d;
        font-weight: 500;
    }
    
    .btn-login {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
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
    
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(52, 152, 219, 0.3);
        background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
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
        color: #3498db;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }
    
    .auth-links a:hover {
        color: #2980b9;
        text-decoration: underline;
    }
    
    .alert {
        border-radius: 12px;
        padding: 15px 20px;
        margin-bottom: 25px;
        border: none;
        font-weight: 500;
    }
    
    .alert-danger {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
        color: white;
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
            <h2>Welcome Back</h2>
            <p>Sign in to your shoe store account</p>
        </div>
        <div class="auth-body">
            <?php if(isset($errors['login'])): ?>
                <div class="alert alert-danger">
                    <strong>⚠️ Login Failed:</strong> <?php echo $errors['login']; ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="form-group">
                    <label for="email" class="form-label">📧 Email Address</label>
                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                           id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" 
                           placeholder="Enter your email address">
                    <?php if(isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">🔒 Password</label>
                    <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                           id="password" name="password" placeholder="Enter your password">
                    <?php if(isset($errors['password'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me for 30 days</label>
                </div>
                
                <button type="submit" class="btn btn-login">Sign In</button>
            </form>
            
            <div class="auth-links">
                <p>New to our store? <a href="register.php">Create an account</a></p>
                <p><a href="forgot_password.php">🔑 Forgot your password?</a></p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>