<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to add an item.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $item_name = $_POST['item_name'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $condition = $_POST['condition'];

    // Handle image upload
    $target_dir = "uploads/";
    $file_name = uniqid() . '-' . basename($_FILES["item_image"]["name"]);
    $target_file = $target_dir . $file_name;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image
    $check = getimagesize($_FILES["item_image"]["tmp_name"]);
    if($check === false) {
        die("File is not an image.");
    }

    // Check file size (e.g., limit to 5MB)
    if ($_FILES["item_image"]["size"] > 5000000) {
        die("Sorry, your file is too large.");
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        die("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
    }

    // Move the uploaded file to the 'uploads' directory
    if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
        // File uploaded successfully, now insert item into the database
        $image_url = $target_file;

        // Prepare and bind SQL statement
        $sql = "INSERT INTO items (name, description, category, `condition`, image_url, user_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $item_name, $description, $category, $condition, $image_url, $user_id);

        if ($stmt->execute()) {
            echo "Item added successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Sorry, there was an error uploading your file.";
    }

    $conn->close();
}
?>