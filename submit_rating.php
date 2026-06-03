<?php
header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(0);

session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to submit a rating.']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $swap_id = isset($_POST['swap_id']) ? intval($_POST['swap_id']) : 0;
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    if ($swap_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid swap ID.']);
        exit();
    }

    if ($rating < 1 || $rating > 5) {
        echo json_encode(['status' => 'error', 'message' => 'Rating must be between 1 and 5 stars.']);
        exit();
    }

    if (!empty($comment) && contains_bad_words($comment)) {
        echo json_encode(['status' => 'error', 'message' => 'Your comment contains inappropriate or blocked words. Please keep it clean.']);
        exit();
    }

    // 1. Fetch swap details to verify user is a participant
    $sql_swap = "SELECT item1_owner_id, item2_owner_id, status, item1_id, item2_id FROM swaps WHERE id = ?";
    $stmt_swap = $conn->prepare($sql_swap);
    
    if (!$stmt_swap) {
        echo json_encode(['status' => 'error', 'message' => 'Database preparation error.']);
        exit();
    }

    $stmt_swap->bind_param("i", $swap_id);
    $stmt_swap->execute();
    $stmt_swap->bind_result($item1_owner_id, $item2_owner_id, $swap_status, $item1_id, $item2_id);
    
    if (!$stmt_swap->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Swap not found.']);
        $stmt_swap->close();
        exit();
    }
    $stmt_swap->close();

    // Verify user is part of the swap
    if ($user_id != $item1_owner_id && $user_id != $item2_owner_id) {
        echo json_encode(['status' => 'error', 'message' => 'You are not a participant in this swap.']);
        exit();
    }

    // Verify swap has been accepted (it can be 'accepted' or 'completed')
    if ($swap_status !== 'accepted' && $swap_status !== 'completed') {
        echo json_encode(['status' => 'error', 'message' => 'You can only review accepted or completed swaps.']);
        exit();
    }

    // Determine the reviewee (the other party)
    $reviewee_id = ($user_id == $item1_owner_id) ? $item2_owner_id : $item1_owner_id;

    // Check if the current user already left a rating for this swap
    $sql_check_rating = "SELECT id FROM ratings WHERE swap_id = ? AND reviewer_id = ?";
    $stmt_check = $conn->prepare($sql_check_rating);
    $stmt_check->bind_param("ii", $swap_id, $user_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'You have already submitted a rating for this swap.']);
        $stmt_check->close();
        exit();
    }
    $stmt_check->close();

    // Begin database transaction for rating insertion and swap completion
    if (method_exists($conn, 'beginTransaction')) {
        $conn->beginTransaction();
    } else {
        $conn->begin_transaction();
    }

    try {
        // 2. Insert rating record
        $sql_insert = "INSERT INTO ratings (swap_id, reviewer_id, reviewee_id, rating, comment) VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("iiiis", $swap_id, $user_id, $reviewee_id, $rating, $comment);
        $stmt_insert->execute();
        $stmt_insert->close();

        // 3. Update swap status to completed (if not already completed)
        if ($swap_status !== 'completed') {
            $sql_complete = "UPDATE swaps SET status = 'completed' WHERE id = ?";
            $stmt_complete = $conn->prepare($sql_complete);
            $stmt_complete->bind_param("i", $swap_id);
            $stmt_complete->execute();
            $stmt_complete->close();
            
            // Re-ensure items are marked as swapped
            $sql_items = "UPDATE items SET status = 'swapped' WHERE id IN (?, ?)";
            $stmt_items = $conn->prepare($sql_items);
            $stmt_items->bind_param("ii", $item1_id, $item2_id);
            $stmt_items->execute();
            $stmt_items->close();
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Thank you! Your feedback has been submitted.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Failed to save review: ' . $e->getMessage()]);
    }

    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
