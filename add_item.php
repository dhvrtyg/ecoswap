<?php include 'header.php'; ?>
<?php
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: login.html");
    exit();
}
?>

<h2 class="mb-4">List an Item for Swap</h2>
<form action="add_item_process.php" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="item_name" class="form-label">Item Name:</label>
        <input type="text" id="item_name" name="item_name" class="form-control" required>
    </div>
    
    <div class="mb-3">
        <label for="description" class="form-label">Description:</label>
        <textarea id="description" name="description" class="form-control" rows="4"></textarea>
    </div>
    
    <div class="mb-3">
        <label for="category" class="form-label">Category:</label>
        <input type="text" id="category" name="category" class="form-control">
    </div>
    
    <div class="mb-3">
        <label for="condition" class="form-label">Condition:</label>
        <select id="condition" name="condition" class="form-select">
            <option value="new">New</option>
            <option value="like-new">Like New</option>
            <option value="used">Used</option>
            <option value="worn">Worn</option>
        </select>
    </div>
    
    <div class="mb-3">
        <label for="item_image" class="form-label">Item Image:</label>
        <input type="file" id="item_image" name="item_image" class="form-control" required>
    </div>
    
    <button type="submit" class="btn btn-primary">Add Item</button>
</form>

<?php include 'footer.php'; ?>