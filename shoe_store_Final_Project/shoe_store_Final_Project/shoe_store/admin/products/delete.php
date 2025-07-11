<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Only allow admin
requireAdmin();

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }

    // Validate ID
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }

    // Optional: delete image file from server
    $imageQuery = $conn->query("SELECT image FROM products WHERE id = $id");
    if ($imageQuery && $imageRow = $imageQuery->fetch_assoc()) {
        $imagePath = __DIR__ . '/../../../assets/images/uploads/' . $imageRow['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath); // delete image file
        }
    }

    // Delete from database
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete from database']);
    }

    $stmt->close();
    $conn->close();
}
?>
