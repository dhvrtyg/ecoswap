<?php include 'header.php'; ?>
<?php
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the ID of the item the user wants to swap for
$requested_item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
if ($requested_item_id <= 0) {
    die("<div class='container py-5'><div class='alert alert-danger text-center rounded-4 shadow-sm'>Invalid item selected.</div></div>");
}

// Fetch requested item details
$sql_req = "SELECT name, category, `condition`, image_url, description, user_id FROM items WHERE id = ?";
$stmt_req = $conn->prepare($sql_req);
if (!$stmt_req) {
    die("Database error.");
}
$stmt_req->bind_param("i", $requested_item_id);
$stmt_req->execute();
$result_req = $stmt_req->get_result();
$requested_item = $result_req->fetch_assoc();
$stmt_req->close();

if (!$requested_item) {
    echo "<div class='container py-5'><div class='alert alert-danger text-center rounded-4 shadow-sm'>Requested item not found.</div></div>";
    include 'footer.php';
    exit();
}

$owner_id = $requested_item['user_id'];
if ($owner_id === $_SESSION['user_id']) {
    echo "<div class='container py-5'><div class='alert alert-warning text-center rounded-4 shadow-sm fw-bold'>You cannot request a swap for your own item.</div></div>";
    include 'footer.php';
    exit();
}

// Fetch owner's username
$stmt_owner = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt_owner->bind_param("i", $owner_id);
$stmt_owner->execute();
$stmt_owner->bind_result($owner_username);
$stmt_owner->fetch();
$stmt_owner->close();

// Get the user's own items for the dropdown menu (only available items)
$user_id = $_SESSION['user_id'];
$sql_user_items = "SELECT id, name FROM items WHERE user_id = ? AND status = 'available'";
$stmt_user_items = $conn->prepare($sql_user_items);
$stmt_user_items->bind_param("i", $user_id);
$stmt_user_items->execute();
$user_items_result = $stmt_user_items->get_result();

?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="fw-bold mb-4 text-center" style="font-family: 'Playfair Display', serif; color: #2c3e50;">Propose a Swap</h1>
            
            <div class="row align-items-stretch g-4 mb-5">
                <!-- Requested Item Card -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm overflow-hidden h-100 d-flex flex-column" style="border-radius: 16px;">
                        <div class="position-relative bg-light d-flex align-items-center justify-content-center" style="height: 200px; overflow: hidden;">
                            <img src="<?php echo htmlspecialchars(get_item_image($requested_item['image_url'], $requested_item['category'])); ?>" alt="<?php echo htmlspecialchars($requested_item['name']); ?>" class="w-100 h-100" style="object-fit: cover;">
                            <span class="position-absolute top-3 start-3 badge bg-success rounded-pill px-3 py-1 text-uppercase fw-bold" style="font-size:0.7rem; top: 12px; left: 12px; z-index: 10;">Requested</span>
                        </div>
                        <div class="card-body p-4 d-flex flex-column justify-content-between flex-grow-1">
                            <div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-1 px-2.5 small mb-2" style="font-size: 0.75rem;">
                                    <?php echo htmlspecialchars($requested_item['category']); ?>
                                </span>
                                <h5 class="card-title fw-bold text-dark mb-1"><?php echo htmlspecialchars($requested_item['name']); ?></h5>
                                <p class="text-muted small mb-3">Owned by: <strong class="text-success"><?php echo htmlspecialchars($owner_username); ?></strong></p>
                                <p class="card-text text-muted small mb-0"><?php echo htmlspecialchars($requested_item['description']); ?></p>
                            </div>
                            <div class="pt-3 border-top mt-3">
                                <span class="small text-muted"><strong class="text-success">Condition:</strong> <?php echo htmlspecialchars($requested_item['condition']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Arrow Icon -->
                <div class="col-md-2 d-flex align-items-center justify-content-center py-2">
                    <div class="d-none d-md-flex align-items-center justify-content-center bg-success text-white rounded-circle shadow-sm mx-auto" style="width: 50px; height: 50px; min-width: 50px;">
                        <i class="bi bi-arrow-left-right fs-4"></i>
                    </div>
                    <div class="d-md-none d-flex align-items-center justify-content-center bg-success text-white rounded-circle shadow-sm mx-auto my-2" style="width: 50px; height: 50px; min-width: 50px;">
                        <i class="bi bi-arrow-down-up fs-4"></i>
                    </div>
                </div>

                <!-- Swap Selection / Offer Form Column -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm p-4 h-100 bg-white d-flex flex-column justify-content-between" style="border-radius: 16px; border: 1px solid var(--border-color) !important;">
                        <div>
                            <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-gift me-2 text-success"></i>Your Offer</h5>
                            <p class="text-muted small mb-4">Select which of your available listings you would like to offer in exchange for this item.</p>
                            
                            <form action="process_swap.php" method="POST">
                                <input type="hidden" name="requested_item_id" value="<?php echo $requested_item_id; ?>">
                                <input type="hidden" name="owner_id" value="<?php echo $owner_id; ?>">

                                <div class="mb-4">
                                    <label for="offered_item" class="form-label fw-semibold text-muted small text-uppercase">Select your item to offer:</label>
                                    <select name="offered_item_id" id="offered_item" class="form-select bg-light border-0 py-2.5" style="border-radius: 10px;" required>
                                        <?php
                                        if ($user_items_result->num_rows > 0) {
                                            while($item_row = $user_items_result->fetch_assoc()) {
                                                echo "<option value='" . $item_row['id'] . "'>" . htmlspecialchars($item_row['name']) . "</option>";
                                            }
                                        } else {
                                            echo "<option value=''>You have no available items to offer.</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                        </div>

                        <div>
                            <?php if ($user_items_result->num_rows > 0): ?>
                                <button type="submit" class="btn btn-success w-100 rounded-pill py-2.5 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-send-fill"></i>
                                    <span>Send Swap Proposal</span>
                                </button>
                            <?php else: ?>
                                <div class="alert alert-warning border-0 small mb-3 py-3" style="border-radius: 10px;">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i> You must have at least one <strong>available</strong> item to propose a swap.
                                </div>
                                <a href="add_item.php" class="btn btn-success w-100 rounded-pill py-2.5 fw-bold shadow-sm">
                                    <i class="bi bi-plus-lg me-1"></i> List an Item Now
                                </a>
                            <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$stmt_user_items->close();
$conn->close();
include 'footer.php'; 
?>