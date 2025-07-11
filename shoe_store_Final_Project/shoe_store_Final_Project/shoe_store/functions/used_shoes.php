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

// Delete listing (for users)
if($action == 'delete' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $listing_id = (int)$_POST['id'];
    $user_id = $_SESSION['user_id'];
    
    // Verify listing belongs to user
    $listing = $conn->query("SELECT id, image FROM used_shoes WHERE id = $listing_id AND user_id = $user_id")->fetch_assoc();
    
    if(!$listing) {
        echo json_encode(['success' => false, 'message' => 'Listing not found or unauthorized']);
        exit;
    }
    
    // Delete image file
    $image_path = "../assets/images/used_shoes/{$listing['image']}";
    if(file_exists($image_path)) {
        unlink($image_path);
    }
    
    // Delete from database
    $conn->query("DELETE FROM used_shoes WHERE id = $listing_id");
    
    echo json_encode(['success' => true, 'message' => 'Listing deleted successfully']);
    exit;
}

// Update status (for admin)
if($action == 'update_status' && $_SERVER['REQUEST_METHOD'] == 'POST' && isAdmin()) {
    $listing_id = (int)$_POST['id'];
    $status = trim($_POST['status']);
    
    // Validate status
    $valid_statuses = ['approved', 'pending', 'rejected'];
    if(!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }
    
    // Update status
    $stmt = $conn->prepare("UPDATE used_shoes SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $listing_id);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Listing status updated successfully']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>