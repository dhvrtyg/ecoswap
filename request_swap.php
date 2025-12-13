<?php include 'header.php'; ?>
<?php
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Get the ID of the item the user wants to swap for
$requested_item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
if ($requested_item_id <= 0) {
    die("Invalid item selected.");
}

// Get the owner's ID for the requested item
$sql = "SELECT user_id FROM items WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $requested_item_id);
$stmt->execute();
$stmt->bind_result($owner_id);
$stmt->fetch();
$stmt->close();

if ($owner_id === $_SESSION['user_id']) {
    die("You cannot request a swap for your own item.");
}

// Get the user's own items for the dropdown menu
$user_id = $_SESSION['user_id'];
$sql_user_items = "SELECT id, name FROM items WHERE user_id = ?";
$stmt_user_items = $conn->prepare($sql_user_items);
$stmt_user_items->bind_param("i", $user_id);
$stmt_user_items->execute();
$user_items_result = $stmt_user_items->get_result();

?>

<h2 class="mb-4">Request a Swap</h2>
<p>You are requesting a swap for the item with ID: **<?php echo $requested_item_id; ?>**</p>
<form action="process_swap.php" method="POST">
    <input type="hidden" name="requested_item_id" value="<?php echo $requested_item_id; ?>">
    <input type="hidden" name="owner_id" value="<?php echo $owner_id; ?>">

    <div class="mb-3">
        <label for="offered_item" class="form-label">Select your item to offer:</label>
        <select name="offered_item_id" id="offered_item" class="form-select" required>
            <?php
            if ($user_items_result->num_rows > 0) {
                while($item_row = $user_items_result->fetch_assoc()) {
                    echo "<option value='" . $item_row['id'] . "'>" . htmlspecialchars($item_row['name']) . "</option>";
                }
            } else {
                echo "<option value=''>You have no items to offer.</option>";
            }
            ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Send Swap Request</button>
</form>

<?php 
$stmt_user_items->close();
$conn->close();
include 'footer.php'; 
?>