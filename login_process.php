<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Get the input (we call it 'login_input' because it could be either)
    $login_input = trim($_POST['username']); 
    $password = $_POST['password'];

    // 2. The Smart SQL Query: Check if the input matches username OR email
    $sql = "SELECT id, username, password_hash FROM users WHERE username = ? OR email = ?";
    
    $stmt = $conn->prepare($sql);
    // Bind the same input to both question marks (is it a username? is it an email?)
    $stmt->bind_param("ss", $login_input, $login_input);
    
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $fetched_username, $password_hash);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        // User found! Now verify password
        if (password_verify($password, $password_hash)) {
            // Success!
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $fetched_username;
            header("Location: index.php"); // Redirect to home
            exit();
        } else {
            // Found user, but wrong password
            echo "<script>alert('Incorrect password. Please try again.'); window.location.href='login.php';</script>";
        }
    } else {
        // No user found with that name OR email
        echo "<script>alert('User not found. Please register first.'); window.location.href='register.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>