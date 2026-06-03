<?php include 'header.php'; ?>
<?php
// Check if user is logged in, otherwise redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = 'User'; 

// Fetch user's details for the Welcome message
$sql_user = "SELECT username FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$stmt_user->bind_result($fetched_username);
if ($stmt_user->fetch()) {
    $username = $fetched_username;
    $_SESSION['username'] = $fetched_username; 
}
$stmt_user->close();
?>

<div class="container py-5">
    <div id="alertPlaceholder"></div>

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-5">
        <div>
            <h1 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif; color: #2c3e50;">Hello, <?php echo htmlspecialchars($username); ?>!</h1>
            <p class="text-muted mb-0">Manage your listings, handle incoming swap proposals, and view swap histories.</p>
        </div>
        <a href="add_item.php" class="btn btn-success rounded-pill px-4 py-2.5 fw-bold shadow-sm d-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i>
            <span>List New Item</span>
        </a>
    </div>

    <!-- Custom styled tabs -->
    <ul class="nav nav-pills mb-4 gap-2 scroll-x-nowrap" id="dashboardTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active rounded-pill px-4 py-2 fw-semibold shadow-sm" id="items-tab" data-bs-toggle="tab" data-bs-target="#items" type="button" role="tab" aria-selected="true">
            <i class="bi bi-tags-fill me-2"></i>Your Items
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-4 py-2 fw-semibold shadow-sm" id="incoming-tab" data-bs-toggle="tab" data-bs-target="#incoming" type="button" role="tab" aria-selected="false">
            <i class="bi bi-arrow-down-left-circle-fill me-2"></i>Incoming Requests
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-4 py-2 fw-semibold shadow-sm" id="outgoing-tab" data-bs-toggle="tab" data-bs-target="#outgoing" type="button" role="tab" aria-selected="false">
            <i class="bi bi-arrow-up-right-circle-fill me-2"></i>Your Requests
        </button>
      </li>
    </ul>

    <div class="tab-content bg-white p-4 p-md-5 rounded-4 shadow-sm border border-light" id="dashboardTabContent" style="min-height: 350px;">
        
        <!-- Tab 1: User's Items -->
        <div class="tab-pane fade show active" id="items" role="tabpanel" aria-labelledby="items-tab">
            <?php
            $sql_items = "SELECT id, name, category, `condition`, image_url, status FROM items WHERE user_id = ? ORDER BY created_at DESC";
            $stmt_items = $conn->prepare($sql_items);
            $stmt_items->bind_param("i", $user_id);
            $stmt_items->execute();
            $result_items = $stmt_items->get_result();

            if ($result_items->num_rows > 0) {
                echo '<div class="row g-4" id="listingsGrid">';
                while($row = $result_items->fetch_assoc()) {
                    $item_id = $row['id'];
                    echo "<div class='col-lg-4 col-md-6 col-12 listing-card-wrapper' id='item-card-{$item_id}' style='transition: all 0.5s ease;'>";
                    echo "<div class='card h-100 border-0 shadow-sm overflow-hidden position-relative' style='border-radius: 16px;'>";
                    
                    // Display image
                    $img_src = htmlspecialchars(get_item_image($row["image_url"], $row["category"]));
                    echo "<div class='position-relative bg-light d-flex align-items-center justify-content-center' style='height: 200px; overflow: hidden;'>";
                    echo "<img src='" . $img_src . "' style='width: 100%; height: 100%; object-fit: cover;' alt='Item Image'>";
                    
                    // Status Badge
                    $badge_class = ($row['status'] == 'available') ? 'bg-success' : (($row['status'] == 'pending') ? 'bg-warning text-dark' : 'bg-secondary');
                    echo "<span class='position-absolute top-3 start-3 badge rounded-pill px-3 py-1 text-uppercase fw-bold' style='font-size: 0.7rem; letter-spacing: 0.5px; top: 12px; left: 12px; z-index: 10;'>{$row['status']}</span>";
                    echo "</div>";

                    echo "<div class='card-body p-4 d-flex flex-column justify-content-between'>";
                    echo "<div>";
                    // Category Badge
                    $cats = explode(',', $row['category']);
                    echo "<div class='mb-2'>";
                    foreach ($cats as $c) {
                        echo "<span class='badge bg-success-subtle text-success border border-success-subtle rounded-pill py-1 px-2.5 small me-1' style='font-size:0.75rem;'>" . htmlspecialchars(trim($c)) . "</span>";
                    }
                    echo "</div>";
                    
                    echo "<h5 class='card-title fw-bold text-dark mb-2'>" . htmlspecialchars($row["name"]) . "</h5>";
                    echo "<p class='card-text small text-muted'><strong class='text-success'>Condition:</strong> " . htmlspecialchars($row["condition"]) . "</p>";
                    echo "</div>";

                    // Actions
                    echo "<div class='d-flex gap-2 pt-3 border-top mt-3'>";
                    if ($row['status'] == 'available') {
                        echo "<a href='edit_item.php?id={$item_id}' class='btn btn-outline-success btn-sm rounded-pill px-3 fw-bold flex-grow-1'><i class='bi bi-pencil-square me-1'></i> Edit</a>";
                        echo "<button onclick='confirmDelete({$item_id})' class='btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold flex-grow-1'><i class='bi bi-trash3 me-1'></i> Delete</button>";
                    } else {
                        echo "<span class='text-muted small text-center w-100 py-1.5 bg-light rounded-pill'><i class='bi bi-lock-fill me-1'></i> Locked (In Transaction)</span>";
                    }
                    echo "</div>";

                    echo "</div>"; // card-body
                    echo "</div>"; // card
                    echo "</div>"; // col
                }
                echo '</div>';
            } else {
                echo "<div class='text-center py-5'><i class='bi bi-folder2-open display-4 d-block mb-3 text-secondary'></i><p class='text-muted mb-0'>You have not posted any items yet.</p></div>";
            }
            $stmt_items->close();
            ?>
        </div>

        <!-- Tab 2: Incoming Requests -->
        <div class="tab-pane fade" id="incoming" role="tabpanel" aria-labelledby="incoming-tab">
            <?php
            $sql_incoming = "
                SELECT s.id AS swap_id, i1.name AS requested_item, i2.name AS offered_item, s.status, u.id AS requester_id, u.username AS requester
                FROM swaps s
                JOIN items i1 ON s.item1_id = i1.id 
                JOIN items i2 ON s.item2_id = i2.id 
                JOIN users u ON s.item2_owner_id = u.id 
                WHERE s.item1_owner_id = ? 
                ORDER BY s.created_at DESC";
            $stmt_incoming = $conn->prepare($sql_incoming);
            $stmt_incoming->bind_param("i", $user_id);
            $stmt_incoming->execute();
            $result_incoming = $stmt_incoming->get_result();

            if ($result_incoming->num_rows > 0) {
                echo '<div class="d-flex flex-column gap-3">';
                while($row = $result_incoming->fetch_assoc()) {
                    $swap_id = $row['swap_id'];
                    $status = $row['status'];
                    
                    // Check if current user has reviewed this swap
                    $stmt_rated = $conn->prepare("SELECT rating FROM ratings WHERE swap_id = ? AND reviewer_id = ?");
                    $has_rated = false;
                    $rated_stars = 0;
                    if ($stmt_rated) {
                        $stmt_rated->bind_param("ii", $swap_id, $user_id);
                        $stmt_rated->execute();
                        $stmt_rated->bind_result($db_r);
                        if ($stmt_rated->fetch()) {
                            $has_rated = true;
                            $rated_stars = $db_r;
                        }
                        $stmt_rated->close();
                    }

                    echo "<div class='card border-light shadow-sm p-4' style='border-radius:14px;' id='incoming-swap-{$swap_id}'>";
                    echo "<div class='row align-items-center g-3'>";
                    echo "<div class='col-12 col-md'>";
                    echo "<div class='mb-1'><a href='profile.php?id={$row['requester_id']}' class='text-success fw-bold text-decoration-none'><i class='bi bi-person-circle me-1'></i> " . htmlspecialchars($row['requester']) . "</a> offered:</div>";
                    echo "<div class='h6 fw-bold text-dark mb-0'><em>" . htmlspecialchars($row['offered_item']) . "</em> &harr; for your: <em>" . htmlspecialchars($row['requested_item']) . "</em></div>";
                    echo "</div>";
                    
                    echo "<div class='col-12 col-md-auto d-flex flex-column flex-md-row gap-2 justify-content-end align-items-stretch align-items-md-center'>";
                    if ($status == 'pending') {
                        echo "<a href='chat.php?swap_id={$swap_id}' class='btn btn-outline-success btn-sm rounded-pill px-3 fw-bold'><i class='bi bi-chat-dots me-1'></i> Chat</a>";
                        echo "<a href='process_action.php?action=accept&swap_id={$swap_id}' class='btn btn-success btn-sm rounded-pill px-3 fw-bold'><i class='bi bi-check-lg me-1'></i> Accept</a>";
                        echo "<a href='process_action.php?action=decline&swap_id={$swap_id}' class='btn btn-danger btn-sm rounded-pill px-3 fw-bold'><i class='bi bi-x-lg me-1'></i> Decline</a>";
                    } elseif ($status == 'accepted') {
                        echo "<span class='badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-bold text-uppercase me-2'><i class='bi bi-check-circle-fill me-1'></i> Accepted</span>";
                        echo "<a href='chat.php?swap_id={$swap_id}' class='btn btn-success btn-sm rounded-pill px-3 fw-bold me-2'><i class='bi bi-chat-left-text me-1'></i> Chat</a>";
                        echo "<button onclick='openRatingModal({$swap_id}, \"{$row['requester']}\")' class='btn btn-outline-success btn-sm rounded-pill px-3 fw-bold'><i class='bi bi-star me-1'></i> Complete & Rate</button>";
                    } elseif ($status == 'completed') {
                        echo "<span class='badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1.5 rounded-pill fw-bold text-uppercase me-2'><i class='bi bi-check2-all me-1'></i> Completed</span>";
                        echo "<a href='chat.php?swap_id={$swap_id}' class='btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold me-2'><i class='bi bi-chat-left-text me-1'></i> Chat</a>";
                        if ($has_rated) {
                            echo "<span class='small text-muted'><i class='bi bi-star-fill text-warning me-1'></i> You rated: <strong>{$rated_stars}/5</strong></span>";
                        } else {
                            echo "<button onclick='openRatingModal({$swap_id}, \"{$row['requester']}\")' class='btn btn-outline-success btn-sm rounded-pill px-3 fw-bold'><i class='bi bi-star me-1'></i> Rate User</button>";
                        }
                    } else {
                        echo "<span class='badge bg-light text-muted border px-3 py-1.5 rounded-pill fw-bold text-uppercase'><i class='bi bi-x-circle me-1'></i> Declined</span>";
                    }
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
                }
                echo '</div>';
            } else {
                echo "<div class='text-center py-5'><i class='bi bi-inbox display-4 d-block mb-3 text-secondary'></i><p class='text-muted mb-0'>No incoming swap requests at the moment.</p></div>";
            }
            $stmt_incoming->close();
            ?>
        </div>

        <!-- Tab 3: Outgoing Requests -->
        <div class="tab-pane fade" id="outgoing" role="tabpanel" aria-labelledby="outgoing-tab">
            <?php
            $sql_outgoing = "
                SELECT s.id AS swap_id, i1.name AS wanted_item, i2.name AS my_item, s.status, u.id AS owner_id, u.username AS owner
                FROM swaps s
                JOIN items i1 ON s.item1_id = i1.id
                JOIN items i2 ON s.item2_id = i2.id
                JOIN users u ON s.item1_owner_id = u.id
                WHERE s.item2_owner_id = ?
                ORDER BY s.created_at DESC";
            $stmt_outgoing = $conn->prepare($sql_outgoing);
            $stmt_outgoing->bind_param("i", $user_id);
            $stmt_outgoing->execute();
            $result_outgoing = $stmt_outgoing->get_result();

            if ($result_outgoing->num_rows > 0) {
                echo '<div class="d-flex flex-column gap-3">';
                while($row = $result_outgoing->fetch_assoc()) {
                    $swap_id = $row['swap_id'];
                    $status = $row['status'];

                    // Check if current user has reviewed this swap
                    $stmt_rated = $conn->prepare("SELECT rating FROM ratings WHERE swap_id = ? AND reviewer_id = ?");
                    $has_rated = false;
                    $rated_stars = 0;
                    if ($stmt_rated) {
                        $stmt_rated->bind_param("ii", $swap_id, $user_id);
                        $stmt_rated->execute();
                        $stmt_rated->bind_result($db_r);
                        if ($stmt_rated->fetch()) {
                            $has_rated = true;
                            $rated_stars = $db_r;
                        }
                        $stmt_rated->close();
                    }

                    echo "<div class='card border-light shadow-sm p-4' style='border-radius:14px;' id='outgoing-swap-{$swap_id}'>";
                    echo "<div class='row align-items-center g-3'>";
                    echo "<div class='col-12 col-md'>";
                    echo "<div class='mb-1'>You offered your: <em>" . htmlspecialchars($row['my_item']) . "</em> for:</div>";
                    echo "<div class='h6 fw-bold text-dark mb-0'><em>" . htmlspecialchars($row['wanted_item']) . "</em> owned by <a href='profile.php?id={$row['owner_id']}' class='text-success fw-bold text-decoration-none'><i class='bi bi-person-circle me-1'></i> " . htmlspecialchars($row['owner']) . "</a></div>";
                    echo "</div>";

                    echo "<div class='col-12 col-md-auto d-flex flex-column flex-md-row gap-2 justify-content-end align-items-stretch align-items-md-center'>";
                    if ($status == 'accepted') {
                        echo "<span class='badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-bold text-uppercase me-2'><i class='bi bi-check-circle-fill me-1'></i> Accepted</span>";
                        echo "<a href='chat.php?swap_id={$swap_id}' class='btn btn-success btn-sm rounded-pill px-3 fw-bold me-2'><i class='bi bi-chat-left-text me-1'></i> Chat</a>";
                        echo "<button onclick='openRatingModal({$swap_id}, \"{$row['owner']}\")' class='btn btn-outline-success btn-sm rounded-pill px-3 fw-bold'><i class='bi bi-star me-1'></i> Complete & Rate</button>";
                    } elseif ($status == 'completed') {
                        echo "<span class='badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1.5 rounded-pill fw-bold text-uppercase me-2'><i class='bi bi-check2-all me-1'></i> Completed</span>";
                        echo "<a href='chat.php?swap_id={$swap_id}' class='btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold me-2'><i class='bi bi-chat-left-text me-1'></i> Chat</a>";
                        if ($has_rated) {
                            echo "<span class='small text-muted'><i class='bi bi-star-fill text-warning me-1'></i> You rated: <strong>{$rated_stars}/5</strong></span>";
                        } else {
                            echo "<button onclick='openRatingModal({$swap_id}, \"{$row['owner']}\")' class='btn btn-outline-success btn-sm rounded-pill px-3 fw-bold'><i class='bi bi-star me-1'></i> Rate User</button>";
                        }
                    } elseif ($status == 'declined') {
                        echo "<span class='badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fw-bold text-uppercase'><i class='bi bi-x-circle me-1'></i> Declined</span>";
                    } else {
                        echo "<span class='badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1.5 rounded-pill fw-bold text-uppercase me-2'><i class='bi bi-clock me-1'></i> Pending</span>";
                        echo "<a href='chat.php?swap_id={$swap_id}' class='btn btn-sm btn-outline-success rounded-pill px-3 fw-bold'><i class='bi bi-chat-dots me-1'></i> Negotiate</a>";
                    }
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
                }
                echo '</div>';
            } else {
                echo "<div class='text-center py-5'><i class='bi bi-arrow-up-right-circle display-4 d-block mb-3 text-secondary'></i><p class='text-muted mb-0'>You have not made any swap requests yet.</p></div>";
            }
            $stmt_outgoing->close();
            ?>
        </div>

    </div>
</div>

<!-- Complete & Rate Overlay Modal -->
<div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
      <div class="modal-header border-0 text-white py-3" style="background: linear-gradient(135deg, var(--primary-green), var(--accent-green));">
        <h5 class="modal-title fw-bold" id="ratingModalLabel"><i class="bi bi-star-fill me-2"></i>Review Your Swap</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="ratingForm">
            <input type="hidden" name="swap_id" id="modalSwapId" value="">
            
            <p class="text-muted small mb-4">You are rating your swap experience with <strong class="text-success" id="modalRevieweeName">User</strong>. Completing this review marks the swap transaction as completed.</p>
            
            <!-- Dynamic Star Selector -->
            <div class="mb-4 text-center">
                <label class="form-label d-block fw-bold text-uppercase small text-muted mb-2">Rating</label>
                <div class="d-flex justify-content-center gap-2 star-rating-container" style="font-size: 2.2rem;">
                    <i class="bi bi-star text-secondary star-selector" data-value="1" style="cursor: pointer; transition: color 0.2s ease;"></i>
                    <i class="bi bi-star text-secondary star-selector" data-value="2" style="cursor: pointer; transition: color 0.2s ease;"></i>
                    <i class="bi bi-star text-secondary star-selector" data-value="3" style="cursor: pointer; transition: color 0.2s ease;"></i>
                    <i class="bi bi-star text-secondary star-selector" data-value="4" style="cursor: pointer; transition: color 0.2s ease;"></i>
                    <i class="bi bi-star text-secondary star-selector" data-value="5" style="cursor: pointer; transition: color 0.2s ease;"></i>
                </div>
                <input type="hidden" name="rating" id="modalRatingValue" value="0">
                <div class="form-text text-danger d-none mt-1 small" id="ratingError"><i class="bi bi-exclamation-triangle"></i> Please select a star rating.</div>
            </div>

            <!-- Comment feedback -->
            <div class="mb-3">
                <label for="modalComment" class="form-label fw-bold text-uppercase small text-muted">Feedback Comment (Optional)</label>
                <textarea id="modalComment" name="comment" class="form-control bg-light border-0 py-2.5" rows="3" placeholder="Share your experience (e.g. fast communication, item matched description)..." style="border-radius: 10px;"></textarea>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" id="modalSubmitBtn" class="btn btn-success rounded-pill py-2.5 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-shield-check"></i>
                    <span>Submit Review</span>
                </button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ------------------------------------
    // Star Rating Widget Logic
    // ------------------------------------
    const stars = document.querySelectorAll('.star-selector');
    const ratingInput = document.getElementById('modalRatingValue');
    const ratingError = document.getElementById('ratingError');

    stars.forEach(star => {
        // Highlight stars on hover
        star.addEventListener('mouseover', function() {
            const value = parseInt(this.getAttribute('data-value'));
            highlightStars(value);
        });

        // Restore stars when mouse leaves container
        star.parentElement.addEventListener('mouseleave', function() {
            const currentSelected = parseInt(ratingInput.value);
            highlightStars(currentSelected);
        });

        // Set selected value on click
        star.addEventListener('click', function() {
            const value = parseInt(this.getAttribute('data-value'));
            ratingInput.value = value;
            highlightStars(value);
            ratingError.classList.add('d-none');
        });
    });

    function highlightStars(count) {
        stars.forEach((s, idx) => {
            if (idx < count) {
                s.classList.remove('bi-star', 'text-secondary');
                s.classList.add('bi-star-fill', 'text-warning');
            } else {
                s.classList.remove('bi-star-fill', 'text-warning');
                s.classList.add('bi-star', 'text-secondary');
            }
        });
    }

    // ------------------------------------
    // Ratings Modal Submit Process
    // ------------------------------------
    const ratingForm = document.getElementById('ratingForm');
    const ratingModal = new bootstrap.Modal(document.getElementById('ratingModal'));
    const modalSubmitBtn = document.getElementById('modalSubmitBtn');

    ratingForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const ratingVal = parseInt(ratingInput.value);
        if (ratingVal < 1 || ratingVal > 5) {
            ratingError.classList.remove('d-none');
            return;
        }

        // Disable button
        modalSubmitBtn.disabled = true;
        const originalBtnHTML = modalSubmitBtn.innerHTML;
        modalSubmitBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <span>Submitting review...</span>
        `;

        const formData = new FormData(ratingForm);

        fetch('submit_rating.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network error: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            modalSubmitBtn.disabled = false;
            modalSubmitBtn.innerHTML = originalBtnHTML;

            if (data.status === 'success') {
                ratingModal.hide();
                showGlobalAlert('success', `<i class="bi bi-check-circle-fill me-2"></i> ${data.message}`);
                // Refresh dashboard to display completed stats and rating scores
                setTimeout(() => {
                    window.location.reload();
                }, 1200);
            } else {
                alert('Error submitting rating: ' + data.message);
            }
        })
        .catch(err => {
            modalSubmitBtn.disabled = false;
            modalSubmitBtn.innerHTML = originalBtnHTML;
            alert('Failed to submit rating due to error: ' + err.message);
        });
    });
});

// Open ratings modal
function openRatingModal(swapId, peerUsername) {
    document.getElementById('modalSwapId').value = swapId;
    document.getElementById('modalRevieweeName').textContent = peerUsername;
    document.getElementById('modalRatingValue').value = 0;
    
    // Clear star selections
    const stars = document.querySelectorAll('.star-selector');
    stars.forEach(s => {
        s.classList.remove('bi-star-fill', 'text-warning');
        s.classList.add('bi-star', 'text-secondary');
    });
    
    document.getElementById('ratingError').classList.add('d-none');
    document.getElementById('modalComment').value = '';

    const modal = new bootstrap.Modal(document.getElementById('ratingModal'));
    modal.show();
}

// ------------------------------------
// AJAX Deletion Process
// ------------------------------------
function confirmDelete(itemId) {
    if (confirm('Are you absolutely sure you want to delete this listing? This action is permanent.')) {
        const alertPlaceholder = document.getElementById('alertPlaceholder');
        const formData = new FormData();
        formData.append('item_id', itemId);

        fetch('delete_item.php', {
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
            if (data.status === 'success') {
                showGlobalAlert('success', `<i class="bi bi-check-circle-fill me-2"></i> ${data.message}`);
                
                // Animate fade-out and delete listing card
                const card = document.getElementById(`item-card-${itemId}`);
                if (card) {
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        card.remove();
                        // If no listings remain, show placeholder message
                        const grid = document.getElementById('listingsGrid');
                        if (grid && grid.children.length === 0) {
                            grid.parentElement.innerHTML = "<div class='text-center py-5'><i class='bi bi-folder2-open display-4 d-block mb-3 text-secondary'></i><p class='text-muted mb-0'>You have not posted any items yet.</p></div>";
                        }
                    }, 500);
                }
            } else {
                showGlobalAlert('danger', `<i class="bi bi-exclamation-octagon-fill me-2"></i> <strong>Error:</strong> ${data.message}`);
            }
        })
        .catch(err => {
            showGlobalAlert('danger', `<i class="bi bi-exclamation-octagon-fill me-2"></i> <strong>Error:</strong> Failed to execute deletion. ${err.message}`);
        });
    }
}

function showGlobalAlert(type, htmlContent) {
    const alertPlaceholder = document.getElementById('alertPlaceholder');
    alertPlaceholder.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show border-0 py-3 mb-4 shadow-sm" role="alert" style="border-radius: 12px; font-weight: 500;">
            ${htmlContent}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<style>
/* Custom pill overrides */
#dashboardTabs .nav-link {
    background-color: #f1f3f5;
    color: #495057;
    transition: all 0.3s ease;
}
#dashboardTabs .nav-link.active {
    background-color: var(--primary-green) !important;
    color: white !important;
}
</style>

<?php 
$conn->close();
include 'footer.php'; 
?>