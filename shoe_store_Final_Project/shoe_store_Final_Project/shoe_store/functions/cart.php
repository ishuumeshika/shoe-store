<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if(!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Add to cart
if($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate required fields
        if(!isset($_POST['product_id']) || !isset($_POST['product_type'])) {
            throw new Exception('Invalid product data');
        }

        $product_id = (int)$_POST['product_id'];
        $product_type = in_array($_POST['product_type'], ['new', 'used']) ? $_POST['product_type'] : 'new';
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        
        // Validate quantity
        if($quantity < 1) {
            throw new Exception('Quantity must be at least 1');
        }
        
        // Check product availability
        if($product_type == 'new') {
            $stmt = $conn->prepare("SELECT id, price, discount_price, quantity FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
            
            if(!$product) {
                throw new Exception('Product not found');
            }
            
            if($product['quantity'] < $quantity) {
                throw new Exception('Not enough stock available');
            }
            
            $price = $product['discount_price'] ?: $product['price'];
        } else {
            $stmt = $conn->prepare("SELECT id, price FROM used_shoes WHERE id = ? AND status = 'approved'");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
            
            if(!$product) {
                throw new Exception('Used product not available');
            }
            
            $price = $product['price'];
            $quantity = 1; // Used products typically have quantity 1
        }
        
        // Check if item already in cart
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND product_type = ?");
        $stmt->bind_param("iis", $user_id, $product_id, $product_type);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        
        if($existing) {
            // Update quantity
            $new_quantity = $existing['quantity'] + $quantity;
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_quantity, $existing['id']);
            $stmt->execute();
        } else {
            // Add new item
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, product_type, quantity, price) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisid", $user_id, $product_id, $product_type, $quantity, $price);
            $stmt->execute();
        }
        
        // Get updated cart count
        $stmt = $conn->prepare("SELECT COALESCE(SUM(quantity), 0) as count FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $cart_count = $stmt->get_result()->fetch_assoc()['count'];
        
        echo json_encode(['success' => true, 'cart_count' => $cart_count]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Other actions (update, remove, count) can remain the same as your original code
// ...

// Update cart item
if($action == 'update' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $cart_id = (int)$_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];
    
    if($quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'Quantity must be at least 1']);
        exit;
    }
    
    // Verify item belongs to user
    $item = $conn->query("SELECT c.*, 
                         IF(c.product_type = 'new', p.quantity, 1) as max_quantity
                         FROM cart c
                         LEFT JOIN products p ON c.product_id = p.id AND c.product_type = 'new'
                         LEFT JOIN used_shoes u ON c.product_id = u.id AND c.product_type = 'used'
                         WHERE c.id = $cart_id AND c.user_id = $user_id")->fetch_assoc();
    
    if(!$item) {
        echo json_encode(['success' => false, 'message' => 'Item not found in cart']);
        exit;
    }
    
    if($quantity > $item['max_quantity']) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
        exit;
    }
    
    $conn->query("UPDATE cart SET quantity = $quantity WHERE id = $cart_id");
    
    // Calculate new totals
    $cart = $conn->query("SELECT SUM(quantity * price) as total, SUM(quantity) as count 
                         FROM cart 
                         WHERE user_id = $user_id")->fetch_assoc();
    
    $item_total = $quantity * $item['price'];
    
    echo json_encode([
        'success' => true,
        'item_total' => number_format($item_total, 2),
        'subtotal' => number_format($cart['total'], 2),
        'cart_count' => $cart['count']
    ]);
    exit;
}

// Remove from cart
if($action == 'remove' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $cart_id = (int)$_POST['cart_id'];
    
    // Verify item belongs to user
    $item = $conn->query("SELECT * FROM cart WHERE id = $cart_id AND user_id = $user_id")->fetch_assoc();
    
    if(!$item) {
        echo json_encode(['success' => false, 'message' => 'Item not found in cart']);
        exit;
    }
    
    $conn->query("DELETE FROM cart WHERE id = $cart_id");
    
    // Calculate new totals
    $cart = $conn->query("SELECT SUM(quantity * price) as total, SUM(quantity) as count 
                         FROM cart 
                         WHERE user_id = $user_id")->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'subtotal' => number_format($cart['total'] ?? 0, 2),
        'cart_count' => $cart['count'] ?? 0
    ]);
    exit;
}


// Get cart contents
if($action == 'view' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        // Get cart items with product details
        $stmt = $conn->prepare("
            SELECT c.*, 
                   p.name as product_name, 
                   p.image as product_image,
                   b.name as brand_name,
                   p.quantity as product_stock
            FROM cart c
            LEFT JOIN products p ON c.product_id = p.id AND c.product_type = 'new'
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE c.user_id = ?
            ORDER BY c.added_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Calculate totals
        $stmt = $conn->prepare("
            SELECT 
                SUM(quantity) as total_items,
                SUM(quantity * price) as subtotal
            FROM cart 
            WHERE user_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $totals = $stmt->get_result()->fetch_assoc();

        echo json_encode([
            'success' => true,
            'items' => $items,
            'totals' => $totals
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}




// Get cart count (for AJAX)
if($action == 'count') {
    $cart_count = $conn->query("SELECT SUM(quantity) as count FROM cart WHERE user_id = $user_id")->fetch_assoc()['count'] ?? 0;
    echo json_encode(['success' => true, 'cart_count' => $cart_count]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>