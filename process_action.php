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

if ($swap_id <= 0 || ($action != 'accept' && $action != 'decline')) {
    die("Invalid action or swap ID.");
}

// 1. Fetch swap details and verify user ownership (security check)
// Check if the current user is the owner of the item being requested (item1_owner_id)
$sql_check = "SELECT item1_owner_id, item1_id, item2_id FROM swaps WHERE id = ? AND item1_owner_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $swap_id, $user_id);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows === 0) {
    die("Error: Swap not found or you are not authorized to manage it.");
}

$stmt_check->bind_result($owner_id, $item1_id, $item2_id);
$stmt_check->fetch();
$stmt_check->close();

// 2. Update the swap status
$new_status = ($action == 'accept') ? 'accepted' : 'declined';
$sql_update_swap = "UPDATE swaps SET status = ? WHERE id = ?";
$stmt_update = $conn->prepare($sql_update_swap);
$stmt_update->bind_param("si", $new_status, $swap_id);
$stmt_update->execute();
$stmt_update->close();

// 3. OPTIONAL ADVANCED FEATURE: If accepted, update item status to 'swapped'
if ($new_status == 'accepted') {
    // This marks both items as no longer available for general swap
    $sql_update_items = "UPDATE items SET status = 'swapped' WHERE id IN (?, ?)";
    $stmt_items = $conn->prepare($sql_update_items);
    $stmt_items->bind_param("ii", $item1_id, $item2_id);
    $stmt_items->execute();
    $stmt_items->close();
}

// 4. Redirect back to the dashboard with a status message
header("Location: dashboard.php?action_status=" . $new_status);
exit();
?>