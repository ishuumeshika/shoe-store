<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Check authentication
if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Cancel order (for users)
if($action == 'cancel' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = (int)$_POST['id'];
    $user_id = $_SESSION['user_id'];
    
    // Verify order belongs to user and is cancellable
    $order = $conn->query("SELECT id, order_status FROM orders WHERE id = $order_id AND user_id = $user_id")->fetch_assoc();
    
    if(!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    if($order['order_status'] != 'processing') {
        echo json_encode(['success' => false, 'message' => 'Order cannot be cancelled at this stage']);
        exit;
    }
    
    // Update order status
    $conn->query("UPDATE orders SET order_status = 'cancelled' WHERE id = $order_id");
    
    // Restore product quantities
    $items = $conn->query("SELECT product_id, product_type, quantity FROM order_items WHERE order_id = $order_id");
    
    while($item = $items->fetch_assoc()) {
        if($item['product_type'] == 'new') {
            $conn->query("UPDATE products SET quantity = quantity + {$item['quantity']} WHERE id = {$item['product_id']}");
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
    exit;
}

// Update order status (for admin)
if($action == 'update_status' && $_SERVER['REQUEST_METHOD'] == 'POST' && isAdmin()) {
    $order_id = (int)$_POST['id'];
    $status = trim($_POST['status']);
    $tracking_number = isset($_POST['tracking_number']) ? trim($_POST['tracking_number']) : null;
    $shipping_carrier = isset($_POST['shipping_carrier']) ? trim($_POST['shipping_carrier']) : null;
    
    // Validate status
    $valid_statuses = ['processing', 'shipped', 'delivered', 'cancelled'];
    if(!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }
    
    // Verify order exists
    $order = $conn->query("SELECT id, order_status FROM orders WHERE id = $order_id")->fetch_assoc();
    
    if(!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    // Prepare update query
    $query = "UPDATE orders SET order_status = ?";
    $params = [$status];
    $types = 's';
    
    if($status == 'shipped' && $tracking_number && $shipping_carrier) {
        $query .= ", tracking_number = ?, shipping_carrier = ?";
        $params[] = $tracking_number;
        $params[] = $shipping_carrier;
        $types .= 'ss';
    }
    
    $query .= " WHERE id = ?";
    $params[] = $order_id;
    $types .= 'i';
    
    // Update order
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    // If cancelling, restore product quantities
    if($status == 'cancelled' && $order['order_status'] == 'processing') {
        $items = $conn->query("SELECT product_id, product_type, quantity FROM order_items WHERE order_id = $order_id");
        
        while($item = $items->fetch_assoc()) {
            if($item['product_type'] == 'new') {
                $conn->query("UPDATE products SET quantity = quantity + {$item['quantity']} WHERE id = {$item['product_id']}");
            }
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>