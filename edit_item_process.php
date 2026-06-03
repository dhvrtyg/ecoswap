<?php
header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(0);

session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to update an item.']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $item_name = isset($_POST['item_name']) ? trim($_POST['item_name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    $condition = isset($_POST['condition']) ? trim($_POST['condition']) : '';

    if ($item_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid item ID.']);
        exit();
    }

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

    $category_str = implode(',', $categories);

    // Fetch existing item details to verify ownership, status, and get the old image URL
    $sql_verify = "SELECT user_id, image_url, status FROM items WHERE id = ?";
    $stmt_verify = $conn->prepare($sql_verify);
    
    if (!$stmt_verify) {
        echo json_encode(['status' => 'error', 'message' => 'Database error preparing verification.']);
        exit();
    }

    $stmt_verify->bind_param("i", $item_id);
    $stmt_verify->execute();
    $stmt_verify->bind_result($db_owner_id, $db_image_url, $db_status);
    
    if (!$stmt_verify->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Item not found.']);
        $stmt_verify->close();
        exit();
    }
    $stmt_verify->close();

    // Verify ownership
    if ($db_owner_id != $user_id) {
        echo json_encode(['status' => 'error', 'message' => 'You do not have permission to update this item.']);
        exit();
    }

    // Verify item is available
    if ($db_status !== 'available') {
        echo json_encode(['status' => 'error', 'message' => 'Only items with status "available" can be modified.']);
        exit();
    }

    $image_url = $db_image_url;
    $new_image_uploaded = false;

    // Check if a new image was uploaded
    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] != UPLOAD_ERR_NO_FILE) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES["item_image"]["name"], PATHINFO_EXTENSION));
        $file_name = uniqid() . '-' . time() . '.' . $file_extension;
        $target_file = $target_dir . $file_name;

        // Check if image file is an actual image
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
            $new_image_uploaded = true;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload and save new image file.']);
            exit();
        }
    }

    // Prepare update query
    $sql_update = "UPDATE items SET name = ?, description = ?, category = ?, `condition` = ?, image_url = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    
    if ($stmt_update) {
        $stmt_update->bind_param("sssssi", $item_name, $description, $category_str, $condition, $image_url, $item_id);
        
        if ($stmt_update->execute()) {
            // If update succeeded and a new image was uploaded, delete the old custom image
            if ($new_image_uploaded && !empty($db_image_url)) {
                if (strpos($db_image_url, 'placeholder-') === false && file_exists($db_image_url)) {
                    @unlink($db_image_url);
                }
            }
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Item details updated successfully!'
            ]);
        } else {
            // Delete new file if database update failed
            if ($new_image_uploaded && file_exists($image_url)) {
                @unlink($image_url);
            }
            echo json_encode([
                'status' => 'error',
                'message' => 'Database execution failed: ' . $stmt_update->error
            ]);
        }
        $stmt_update->close();
    } else {
        // Delete new file if update prep failed
        if ($new_image_uploaded && file_exists($image_url)) {
            @unlink($image_url);
        }
        echo json_encode([
            'status' => 'error',
            'message' => 'Database update preparation failed: ' . $conn->error
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
