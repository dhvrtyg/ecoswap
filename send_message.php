<?php
session_start();
include 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender_id = $_SESSION['user_id'];
    $swap_id = intval($_POST['swap_id']);
    $recipient_id = intval($_POST['recipient_id']);
    $message_text = trim($_POST['message_text']);

    if (empty($message_text)) {
        header("Location: chat.php?swap_id=" . $swap_id . "&error=empty");
        exit();
    }

    if (contains_bad_words($message_text)) {
        header("Location: chat.php?swap_id=" . $swap_id . "&error=profanity");
        exit();
    }

    // Insert message into the database
    $sql = "INSERT INTO messages (swap_id, sender_id, receiver_id, message_text) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $swap_id, $sender_id, $recipient_id, $message_text);

    if ($stmt->execute()) {
        // Success: Redirect back to the chat page
        header("Location: chat.php?swap_id=" . $swap_id);
        exit();
    } else {
        // Error handling
        die("Error sending message: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
}
?>