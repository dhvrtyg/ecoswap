<?php include 'header.php'; ?>
<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($item_id <= 0) {
    echo "<div class='container py-5'><div class='alert alert-danger'>Invalid item ID.</div></div>";
    include 'footer.php';
    exit();
}

// Fetch item details
$item = null;
$sql = "SELECT * FROM items WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();
}

if (!$item) {
    echo "<div class='container py-5'><div class='alert alert-danger'>Item not found.</div></div>";
    include 'footer.php';
    exit();
}

// Check ownership
if ($item['user_id'] != $user_id) {
    echo "<div class='container py-5'><div class='alert alert-danger'>You do not have permission to edit this item.</div></div>";
    include 'footer.php';
    exit();
}

// Check status - editing is allowed only if status is available
if ($item['status'] !== 'available') {
    echo "<div class='container py-5'><div class='alert alert-warning'>Only items with status 'available' can be edited. This item is currently " . htmlspecialchars($item['status']) . ".</div></div>";
    include 'footer.php';
    exit();
}

// Parse categories
$selected_categories = array_map('trim', explode(',', $item['category'] ?? ''));
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php" class="text-success text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-success text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Item</li>
                </ol>
            </nav>

            <!-- Card container -->
            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 16px;">
                <div class="card-header border-0 py-4 px-4 px-md-5 text-white d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, var(--primary-green), var(--accent-green));">
                    <div>
                        <h3 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif; letter-spacing: -0.5px;">Edit Listing</h3>
                        <p class="mb-0 small text-white-50">Modify your item details, update categories, or replace the photo.</p>
                    </div>
                    <div style="font-size: 2.2rem; opacity: 0.85;"><i class="bi bi-pencil-square"></i></div>
                </div>

                <div class="card-body p-4 p-md-5">
                    <!-- Success & Error Alert Messages container -->
                    <div id="alertContainer"></div>

                    <form id="editItemForm" enctype="multipart/form-data">
                        <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">

                        <!-- Item Name -->
                        <div class="mb-4">
                            <label for="item_name" class="form-label small fw-bold text-uppercase" style="letter-spacing: 0.5px; color: var(--primary-green);">Item Name</label>
                            <input type="text" id="item_name" name="item_name" class="form-control bg-light border-0 py-3" placeholder="e.g. Mechanical Keyboard, Calculus Textbook" style="border-radius: 8px;" value="<?php echo htmlspecialchars($item['name']); ?>" required>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label small fw-bold text-uppercase" style="letter-spacing: 0.5px; color: var(--primary-green);">Description</label>
                            <textarea id="description" name="description" class="form-control bg-light border-0 py-3" rows="4" placeholder="Describe the item condition, size, compatibility or specifications..." style="border-radius: 8px;" required><?php echo htmlspecialchars($item['description']); ?></textarea>
                        </div>

                        <!-- Category Checkboxes -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-uppercase d-block mb-2" style="letter-spacing: 0.5px; color: var(--primary-green);">Category (Select one or more)</label>
                            <div class="bg-light p-3 border-0" style="border-radius: 8px;">
                                <div class="row g-2">
                                    <div class="col-6 col-sm-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="categories[]" value="Books" id="catBooks" <?php echo in_array('Books', $selected_categories) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="catBooks"><i class="bi bi-book me-1"></i> Books</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="categories[]" value="Electronics" id="catElectronics" <?php echo in_array('Electronics', $selected_categories) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="catElectronics"><i class="bi bi-laptop me-1"></i> Electronics</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="categories[]" value="Stationery" id="catStationery" <?php echo in_array('Stationery', $selected_categories) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="catStationery"><i class="bi bi-pen me-1"></i> Stationery</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="categories[]" value="Clothing" id="catClothing" <?php echo in_array('Clothing', $selected_categories) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="catClothing"><i class="bi bi-bag me-1"></i> Clothing</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="categories[]" value="Dorm" id="catDorm" <?php echo in_array('Dorm', $selected_categories) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="catDorm"><i class="bi bi-lamp me-1"></i> Dorm</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Condition and Optional Image Upload -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="condition" class="form-label small fw-bold text-uppercase" style="letter-spacing: 0.5px; color: var(--primary-green);">Condition</label>
                                <select id="condition" name="condition" class="form-select bg-light border-0 py-3" style="border-radius: 8px;" required>
                                    <option value="New" <?php echo $item['condition'] === 'New' ? 'selected' : ''; ?>>New</option>
                                    <option value="Like New" <?php echo $item['condition'] === 'Like New' ? 'selected' : ''; ?>>Like New</option>
                                    <option value="Good" <?php echo $item['condition'] === 'Good' ? 'selected' : ''; ?>>Good</option>
                                    <option value="Fair" <?php echo $item['condition'] === 'Fair' ? 'selected' : ''; ?>>Fair</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="item_image" class="form-label small fw-bold text-uppercase" style="letter-spacing: 0.5px; color: var(--primary-green);">Update Image (Optional)</label>
                                <input type="file" id="item_image" name="item_image" class="form-control bg-light border-0 py-3" style="border-radius: 8px;" accept="image/*">
                                <div class="form-text text-muted mt-1 small">Leave blank to keep the current image.</div>
                            </div>
                        </div>

                        <!-- Current Image Preview -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-uppercase d-block" style="letter-spacing: 0.5px; color: var(--primary-green);">Current Photo</label>
                            <div class="d-flex align-items-center gap-3 bg-light p-3 rounded-3" style="max-width: 320px;">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="Current Image" class="rounded" style="width: 80px; height: 80px; object-fit: cover; border: 1px solid #ddd;">
                                <div>
                                    <div class="fw-semibold text-truncate" style="max-width: 180px;"><?php echo htmlspecialchars(basename($item['image_url'])); ?></div>
                                    <span class="badge bg-secondary mt-1">Existing</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid mt-4">
                            <button type="submit" id="submitBtn" class="btn btn-success rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-save"></i>
                                <span>Save Changes</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editItemForm');
    const submitBtn = document.getElementById('submitBtn');
    const alertContainer = document.getElementById('alertContainer');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Basic frontend validation for category selection
        const checkedCategories = form.querySelectorAll('input[name="categories[]"]:checked');
        if (checkedCategories.length === 0) {
            showAlert('danger', '<i class="bi bi-exclamation-triangle-fill me-2"></i> Please select at least one category.');
            return;
        }

        // Client-side image size check (2MB)
        const fileInput = document.getElementById('item_image');
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            if (file.size > 2000000) {
                showAlert('danger', '<i class="bi bi-exclamation-triangle-fill me-2"></i> The selected image exceeds the 2MB size limit.');
                return;
            }
        }

        // Client-side bad words check
        const badWords = ['badword', 'abuse', 'spam', 'scam', 'offensive', 'trash', 'curseword', 'inappropriate', 'fraud', 'sh*t', 'f*ck', 'b*tch', 'asshole', 'bastard', 'crap', 'dick', 'piss'];
        const nameVal = document.getElementById('item_name').value.toLowerCase();
        const descVal = document.getElementById('description').value.toLowerCase();
        for (const word of badWords) {
            if (nameVal.includes(word)) {
                showAlert('danger', '<i class="bi bi-exclamation-triangle-fill me-2"></i> The item name contains inappropriate or prohibited words.');
                return;
            }
            if (descVal.includes(word)) {
                showAlert('danger', '<i class="bi bi-exclamation-triangle-fill me-2"></i> The description contains inappropriate or prohibited words.');
                return;
            }
        }

        // Disable button and show loading state
        submitBtn.disabled = true;
        const originalBtnHTML = submitBtn.innerHTML;
        submitBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <span>Saving changes...</span>
        `;

        // Clear alerts
        alertContainer.innerHTML = '';

        const formData = new FormData(form);

        fetch('edit_item_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Server error: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHTML;

            if (data.status === 'success') {
                showAlert('success', `<i class="bi bi-check-circle-fill me-2"></i> <strong>Success!</strong> ${data.message}`);
                window.scrollTo({ top: 0, behavior: 'smooth' });
                // Briefly delay redirection to dashboard to allow the user to view success response
                setTimeout(function() {
                    window.location.href = 'dashboard.php';
                }, 1500);
            } else {
                showAlert('danger', `<i class="bi bi-exclamation-octagon-fill me-2"></i> <strong>Error:</strong> ${data.message}`);
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHTML;
            showAlert('danger', `<i class="bi bi-exclamation-octagon-fill me-2"></i> <strong>Network error:</strong> Unable to save details. ${err.message}`);
        });
    });

    function showAlert(type, htmlContent) {
        alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show border-0 py-3" role="alert" style="border-radius: 12px; font-weight: 500;">
                ${htmlContent}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }
});
</script>

<?php include 'footer.php'; ?>
