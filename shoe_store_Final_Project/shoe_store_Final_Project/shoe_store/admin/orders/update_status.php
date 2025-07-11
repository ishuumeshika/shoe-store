<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isAdmin()) {
    $response['message'] = 'Unauthorized access';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

$order_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

if (!$order_id || !in_array($status, ['accepted', 'cancelled'])) {
    $response['message'] = 'Invalid order ID or status';
    echo json_encode($response);
    exit;
}

$conn->begin_transaction();

try {
    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update order status: " . $stmt->error);
    }
    $stmt->close();

    // If order is accepted, reduce product quantities
    if ($status === 'accepted') {
        // Get order items
        $items_stmt = $conn->prepare("SELECT product_id, product_type, quantity 
                                     FROM order_items 
                                     WHERE order_id = ?");
        $items_stmt->bind_param("i", $order_id);
        $items_stmt->execute();
        $items = $items_stmt->get_result();
        
        while ($item = $items->fetch_assoc()) {
            if ($item['product_type'] === 'new') {
                // Check if sufficient stock exists
                $stock_stmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
                $stock_stmt->bind_param("i", $item['product_id']);
                $stock_stmt->execute();
                $stock_result = $stock_stmt->get_result();
                $current_quantity = $stock_result->fetch_assoc()['quantity'];
                $stock_stmt->close();

                if ($current_quantity < $item['quantity']) {
                    throw new Exception("Insufficient stock for product ID: " . $item['product_id']);
                }

                // Update product quantity
                $update_stmt = $conn->prepare("UPDATE products 
                                              SET quantity = quantity - ? 
                                              WHERE id = ?");
                $update_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
                
                if (!$update_stmt->execute()) {
                    throw new Exception("Failed to update product quantity: " . $update_stmt->error);
                }
                $update_stmt->close();
            }
            // Note: Used shoes are typically single items, so no quantity update needed
        }
        $items_stmt->close();
    }

    $conn->commit();
    $response['success'] = true;
    $response['message'] = 'Order status updated successfully';

} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = $e->getMessage();
    error_log("Order status update error: " . $e->getMessage());
}

echo json_encode($response);
$conn->close();
?>