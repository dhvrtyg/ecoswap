<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'db_connect.php';

// Ensure the request is a POST request
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Invalid request method.");
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$requested_item_id = isset($_POST['requested_item_id']) ? intval($_POST['requested_item_id']) : 0;
$offered_item_id = isset($_POST['offered_item_id']) ? intval($_POST['offered_item_id']) : 0;

if ($offered_item_id <= 0 || $requested_item_id <= 0) {
    die("Error: Missing item information.");
}

// 1. Get the owner of the requested item (item1_owner_id)
$sql_owner = "SELECT user_id FROM items WHERE id = ?";
$stmt_owner = $conn->prepare($sql_owner);
$stmt_owner->bind_param("i", $requested_item_id);
$stmt_owner->execute();
$stmt_owner->bind_result($owner_id);
$stmt_owner->fetch();
$stmt_owner->close();

if (!$owner_id) {
    die("Requested item not found or you are the owner.");
}

// 2. Check if the offered item belongs to the user (item2_owner_id)
$sql_check_owner = "SELECT user_id FROM items WHERE id = ?";
$stmt_check_owner = $conn->prepare($sql_check_owner);
$stmt_check_owner->bind_param("i", $offered_item_id);
$stmt_check_owner->execute();
$stmt_check_owner->bind_result($offered_item_owner_id);
$stmt_check_owner->fetch();
$stmt_check_owner->close();

if ($offered_item_owner_id != $user_id) {
    die("The offered item does not belong to you.");
}

// 3. Insert the swap request into the swaps table
$sql_insert_swap = "INSERT INTO swaps (item1_id, item2_id, item1_owner_id, item2_owner_id, status) VALUES (?, ?, ?, ?, 'pending')";
$stmt_insert_swap = $conn->prepare($sql_insert_swap);
$stmt_insert_swap->bind_param("iiii", $requested_item_id, $offered_item_id, $owner_id, $user_id);

if ($stmt_insert_swap->execute()) {
    // SUCCESS: Redirect to the dashboard
    header("Location: dashboard.php?status=success");
    exit();
} else {
    echo "Error: " . $stmt_insert_swap->error;
}

$stmt_insert_swap->close();
$conn->close();
?>