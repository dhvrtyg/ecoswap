<?php
session_start();
include 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$swap_id = isset($_GET['swap_id']) ? intval($_GET['swap_id']) : 0;

if ($swap_id <= 0 || ($action != 'accept' && $action != 'decline' && $action != 'complete')) {
    die("Invalid action or swap ID.");
}

// 1. Fetch swap details
$sql_check = "SELECT item1_owner_id, item2_owner_id, item1_id, item2_id, status FROM swaps WHERE id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $swap_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$swap = $result_check->fetch_assoc();
$stmt_check->close();

if (!$swap) {
    die("Error: Swap not found.");
}

$item1_owner_id = $swap['item1_owner_id'];
$item2_owner_id = $swap['item2_owner_id'];
$item1_id = $swap['item1_id'];
$item2_id = $swap['item2_id'];
$current_status = $swap['status'];

// Check authorization
if ($action == 'accept' || $action == 'decline') {
    if ($user_id != $item1_owner_id) {
        die("Error: Only the recipient of the swap offer can accept or decline it.");
    }
    if ($current_status != 'pending') {
        die("Error: Swap is not pending.");
    }
} elseif ($action == 'complete') {
    if ($user_id != $item1_owner_id && $user_id != $item2_owner_id) {
        die("Error: You are not authorized to complete this swap.");
    }
    if ($current_status != 'accepted') {
        die("Error: Swap is not accepted yet.");
    }
}

$new_status = $current_status;

// 2. Perform database updates
if ($action == 'accept') {
    $new_status = 'accepted';
    
    // Update swap status
    $sql_update = "UPDATE swaps SET status = 'accepted' WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("i", $swap_id);
    $stmt->execute();
    $stmt->close();
    
    // Mark both items as swapped
    $sql_items = "UPDATE items SET status = 'swapped' WHERE id IN (?, ?)";
    $stmt = $conn->prepare($sql_items);
    $stmt->bind_param("ii", $item1_id, $item2_id);
    $stmt->execute();
    $stmt->close();
    
    // Swap ownership (transfer item ownership)
    // Item 1 owner gets Item 2; Item 2 owner gets Item 1.
    $sql_own1 = "UPDATE items SET user_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql_own1);
    $stmt->bind_param("ii", $item2_owner_id, $item1_id);
    $stmt->execute();
    $stmt->close();
    
    $sql_own2 = "UPDATE items SET user_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql_own2);
    $stmt->bind_param("ii", $item1_owner_id, $item2_id);
    $stmt->execute();
    $stmt->close();
    
} elseif ($action == 'decline') {
    $new_status = 'declined';
    $sql_update = "UPDATE swaps SET status = 'declined' WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("i", $swap_id);
    $stmt->execute();
    $stmt->close();
    
} elseif ($action == 'complete') {
    $new_status = 'completed';
    $sql_update = "UPDATE swaps SET status = 'completed' WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("i", $swap_id);
    $stmt->execute();
    $stmt->close();
}

$redirect = isset($_GET['redirect']) && $_GET['redirect'] === 'chat' ? "chat.php?swap_id=" . $swap_id : "dashboard.php?action_status=" . $new_status;
header("Location: " . $redirect);
exit();
?>