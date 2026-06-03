<?php
header('Content-Type: application/json');

ini_set('display_errors', 0); // Hide errors from output to prevent malformed JSON
error_reporting(0);

session_start();
include 'db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'You must be logged in to list an item.'
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $item_name = isset($_POST['item_name']) ? trim($_POST['item_name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    $condition = isset($_POST['condition']) ? trim($_POST['condition']) : '';

    if (empty($item_name)) {
        echo json_encode(['status' => 'error', 'message' => 'Item name is required.']);
        exit();
    }

    if (contains_bad_words($item_name)) {
        echo json_encode(['status' => 'error', 'message' => 'The item name contains inappropriate or prohibited words.']);
        exit();
    }

    if (contains_bad_words($description)) {
        echo json_encode(['status' => 'error', 'message' => 'The description contains inappropriate or prohibited words.']);
        exit();
    }

    if (empty($categories)) {
        echo json_encode(['status' => 'error', 'message' => 'At least one category must be selected.']);
        exit();
    }

    // Join categories list as comma-separated string
    $category_str = implode(',', $categories);

    // Validate image file presence
    if (!isset($_FILES['item_image']) || $_FILES['item_image']['error'] == UPLOAD_ERR_NO_FILE) {
        echo json_encode(['status' => 'error', 'message' => 'Item image is required.']);
        exit();
    }

    // Handle image upload
    $target_dir = "uploads/";
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($_FILES["item_image"]["name"], PATHINFO_EXTENSION));
    $file_name = uniqid() . '-' . time() . '.' . $file_extension;
    $target_file = $target_dir . $file_name;

    // Check if image file is a actual image
    $check = getimagesize($_FILES["item_image"]["tmp_name"]);
    if ($check === false) {
        echo json_encode(['status' => 'error', 'message' => 'The uploaded file is not a valid image.']);
        exit();
    }

    // Check file size (limit to 2MB)
    if ($_FILES["item_image"]["size"] > 2000000) {
        echo json_encode(['status' => 'error', 'message' => 'The uploaded file size exceeds the 2MB limit.']);
        exit();
    }

    // Allow certain file formats
    $allowed_types = ["jpg", "png", "jpeg", "gif", "svg", "webp"];
    if (!in_array($file_extension, $allowed_types)) {
        echo json_encode(['status' => 'error', 'message' => 'Only JPG, JPEG, PNG, GIF, SVG, and WEBP files are allowed.']);
        exit();
    }

    // Move the uploaded file to the 'uploads' directory
    if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
        $image_url = $target_file;

        // Prepare and bind SQL statement
        $sql = "INSERT INTO items (name, description, category, `condition`, image_url, user_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("sssssi", $item_name, $description, $category_str, $condition, $image_url, $user_id);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Item has been listed successfully!'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Database execution failed: ' . $stmt->error
                ]);
            }
            $stmt->close();
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Database preparation failed: ' . $conn->error
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to move uploaded file. Check directory permissions.'
        ]);
    }

    $conn->close();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}
?>