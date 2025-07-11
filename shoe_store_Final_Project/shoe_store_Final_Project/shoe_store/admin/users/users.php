<?php
require_once '../../includes/config.php';

if(!isset($_GET['action'])) {
    die(json_encode(['success' => false, 'message' => 'Invalid request']));
}

$action = $_GET['action'];

switch($action) {
    case 'delete':
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = (int)$_POST['id'];
            
            // Prevent deleting yourself
            if($id == $_SESSION['user_id']) {
                die(json_encode(['success' => false, 'message' => 'You cannot delete yourself']));
            }
            
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
            }
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}