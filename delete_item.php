<?php
header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(0);

session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please log in first.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get item ID
$item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;

if ($item_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid item ID.']);
    exit();
}

// 1. Fetch item to verify ownership and check image path
$sql = "SELECT id, user_id, image_url FROM items WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $stmt->bind_result($id, $item_owner_id, $image_url);
    
    if (!$stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Item not found.']);
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();

    // Verify ownership
    if ($item_owner_id != $user_id) {
        echo json_encode(['status' => 'error', 'message' => 'You do not have permission to delete this item.']);
        $conn->close();
        exit();
    }

    // 2. Perform deletion
    $sql_delete = "DELETE FROM items WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $item_id);
        if ($stmt_delete->execute()) {
            // Delete actual uploaded file if it's not a placeholder SVG illustration
            if (!empty($image_url) && strpos($image_url, 'placeholder-') === false && file_exists($image_url)) {
                @unlink($image_url);
            }
            
            echo json_encode(['status' => 'success', 'message' => 'Item has been deleted successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error: failed to delete item.']);
        }
        $stmt_delete->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare deletion statement.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare verification query.']);
}

$conn->close();
?>
