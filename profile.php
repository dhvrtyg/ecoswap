<?php include 'header.php'; ?>
<?php
// Retrieve user ID to view (either from GET or fallback to current logged-in user)
$target_user_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0);

if ($target_user_id <= 0) {
    echo "<div class='container py-5'><div class='alert alert-warning text-center fw-semibold py-4' style='border-radius:12px;'>Please specify a user ID or log in to view your profile.</div></div>";
    include 'footer.php';
    exit();
}

// Fetch user profile info
$stmt = $conn->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$target_user = null;
if ($stmt) {
    $stmt->bind_param("i", $target_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $target_user = $result->fetch_assoc();
    $stmt->close();
}

if (!$target_user) {
    echo "<div class='container py-5'><div class='alert alert-danger text-center fw-semibold py-4' style='border-radius:12px;'>User profile not found.</div></div>";
    include 'footer.php';
    exit();
}

// Calculate review statistics (Average rating and total count)
$stmt_stats = $conn->prepare("SELECT AVG(rating), COUNT(id) FROM ratings WHERE reviewee_id = ?");
$avg_rating = 0.0;
$total_reviews = 0;
if ($stmt_stats) {
    $stmt_stats->bind_param("i", $target_user_id);
    $stmt_stats->execute();
    $stmt_stats->bind_result($db_avg, $db_count);
    if ($stmt_stats->fetch()) {
        $avg_rating = $db_avg ? round($db_avg, 1) : 0.0;
        $total_reviews = $db_count ? $db_count : 0;
    }
    $stmt_stats->close();
}

// Calculate completed swaps count
$stmt_swaps = $conn->prepare("SELECT COUNT(id) FROM swaps WHERE status = 'completed' AND (item1_owner_id = ? OR item2_owner_id = ?)");
$completed_swaps = 0;
if ($stmt_swaps) {
    $stmt_swaps->bind_param("ii", $target_user_id, $target_user_id);
    $stmt_swaps->execute();
    $stmt_swaps->bind_result($db_swaps_count);
    if ($stmt_swaps->fetch()) {
        $completed_swaps = $db_swaps_count ? $db_swaps_count : 0;
    }
    $stmt_swaps->close();
}

// Fetch user's listings
$user_listings = [];
$stmt_listings = $conn->prepare("SELECT id, name, description, category, condition, image_url, status FROM items WHERE user_id = ? ORDER BY id DESC");
if ($stmt_listings) {
    $stmt_listings->bind_param("i", $target_user_id);
    $stmt_listings->execute();
    $result_listings = $stmt_listings->get_result();
    while ($row = $result_listings->fetch_assoc()) {
        $user_listings[] = $row;
    }
    $stmt_listings->close();
}

// Fetch reviews left for this user
$reviews = [];
$stmt_rev = $conn->prepare("
    SELECT r.rating, r.comment, r.created_at, u.id as reviewer_id, u.username as reviewer_name 
    FROM ratings r
    JOIN users u ON r.reviewer_id = u.id
    WHERE r.reviewee_id = ?
    ORDER BY r.id DESC
");
if ($stmt_rev) {
    $stmt_rev->bind_param("i", $target_user_id);
    $stmt_rev->execute();
    $result_rev = $stmt_rev->get_result();
    while ($row = $result_rev->fetch_assoc()) {
        $reviews[] = $row;
    }
    $stmt_rev->close();
}
?>

<div class="container py-5">
    <!-- Profile Header Card -->
    <div class="card border-0 shadow-sm mb-5 p-4" style="border-radius: 20px; background: linear-gradient(135deg, #ffffff, #f9fbf9);">
        <div class="row align-items-center text-center text-md-start g-4">
            <!-- Profile Avatar / Initial -->
            <div class="col-12 col-md-auto d-flex justify-content-center">
                <div class="d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" 
                     style="width: 100px; height: 100px; border-radius: 50%; font-size: 2.5rem; background: linear-gradient(135deg, var(--primary-green), var(--accent-green));">
                    <?php echo strtoupper(substr($target_user['username'], 0, 1)); ?>
                </div>
            </div>
            <!-- User Basic Info -->
            <div class="col-12 col-md">
                <h2 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif; color: #2c3e50;">
                    <?php echo htmlspecialchars($target_user['username']); ?>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $target_user_id): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-6 py-1 px-3 ms-2 align-middle">You</span>
                    <?php endif; ?>
                </h2>
                <p class="text-muted mb-2 small"><i class="bi bi-calendar3 me-1"></i> Swapping since <?php echo date('F Y', strtotime($target_user['created_at'])); ?></p>
                
                <!-- Ratings display -->
                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                    <div class="text-warning">
                        <?php 
                        $full_stars = floor($avg_rating);
                        $has_half = ($avg_rating - $full_stars) >= 0.5;
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $full_stars) {
                                echo '<i class="bi bi-star-fill me-1"></i>';
                            } elseif ($i == $full_stars + 1 && $has_half) {
                                echo '<i class="bi bi-star-half me-1"></i>';
                            } else {
                                echo '<i class="bi bi-star me-1"></i>';
                            }
                        }
                        ?>
                    </div>
                    <span class="fw-bold text-dark mt-1"><?php echo $avg_rating; ?></span>
                    <span class="text-muted mt-1 small">(<?php echo $total_reviews; ?> reviews)</span>
                </div>
            </div>
            <!-- Statistics columns -->
            <div class="col-12 col-md-auto d-flex justify-content-center justify-content-md-end gap-3 gap-sm-4">
                <div class="text-center px-4 py-3 bg-white shadow-sm border border-light rounded-4 flex-fill flex-md-grow-0" style="min-width: 120px;">
                    <div class="h3 fw-bold text-success mb-0"><?php echo $completed_swaps; ?></div>
                    <div class="small text-muted text-uppercase fw-semibold" style="font-size:0.75rem; letter-spacing:0.5px;">Swaps Done</div>
                </div>
                <div class="text-center px-4 py-3 bg-white shadow-sm border border-light rounded-4 flex-fill flex-md-grow-0" style="min-width: 120px;">
                    <div class="h3 fw-bold text-success mb-0"><?php echo count(array_filter($user_listings, function($item) { return $item['status'] === 'available'; })); ?></div>
                    <div class="small text-muted text-uppercase fw-semibold" style="font-size:0.75rem; letter-spacing:0.5px;">Available</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation tabs for Listings vs Reviews -->
    <ul class="nav nav-tabs border-0 mb-4 justify-content-center gap-3" id="profileTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 py-2 fw-semibold border-0 shadow-sm" id="listings-tab" data-bs-toggle="tab" data-bs-target="#listings" type="button" role="tab" aria-controls="listings" aria-selected="true" style="transition: all 0.3s ease;">
                <i class="bi bi-grid-3x3-gap me-2"></i> Listings
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 py-2 fw-semibold border-0 shadow-sm" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false" style="transition: all 0.3s ease;">
                <i class="bi bi-chat-left-text me-2"></i> Reviews (<?php echo $total_reviews; ?>)
            </button>
        </li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content" id="profileTabsContent">
        <!-- Listings tab pane -->
        <div class="tab-pane fade show active" id="listings" role="tabpanel" aria-labelledby="listings-tab">
            <div class="row row-cols-2 row-cols-md-3 g-3 g-sm-4">
                <?php if (empty($user_listings)): ?>
                    <div class="col-12 text-center py-5">
                        <div class="text-muted fs-5"><i class="bi bi-folder2-open display-4 d-block mb-3 text-secondary"></i> No listings posted by this user yet.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($user_listings as $item): ?>
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm overflow-hidden" style="border-radius: 16px; transition: transform 0.3s ease;">
                                <!-- Image with Badge -->
                                <div class="position-relative bg-light d-flex align-items-center justify-content-center" style="height: 180px; overflow: hidden;">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-100 h-100" style="object-fit: cover;">
                                    <span class="position-absolute top-3 start-3 badge rounded-pill px-2.5 py-1 fw-bold text-uppercase <?php 
                                        echo $item['status'] === 'available' ? 'bg-success' : ($item['status'] === 'pending' ? 'bg-warning text-dark' : 'bg-secondary'); 
                                    ?>" style="font-size: 0.65rem; letter-spacing: 0.5px; top: 8px; left: 8px; z-index: 10;">
                                        <?php echo $item['status']; ?>
                                    </span>
                                </div>
                                <div class="card-body p-3 p-sm-4 d-flex flex-column">
                                    <!-- Category list -->
                                    <div class="mb-2">
                                        <?php 
                                        $cats = explode(',', $item['category']);
                                        foreach ($cats as $cat): 
                                        ?>
                                            <span class="badge bg-light text-success border border-success-subtle rounded-pill py-0.5 px-2 small me-1" style="font-size:0.65rem;"><?php echo htmlspecialchars(trim($cat)); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    <h6 class="card-title fw-bold text-dark mb-1" style="font-size: 0.95rem;"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <p class="card-text text-muted small flex-grow-1 d-none d-sm-block"><?php echo htmlspecialchars(substr($item['description'], 0, 100)) . (strlen($item['description']) > 100 ? '...' : ''); ?></p>
                                    <div class="pt-2 border-top d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center mt-2 gap-2">
                                        <span class="small text-muted" style="font-size: 0.75rem;"><strong class="text-success">Condition:</strong> <?php echo htmlspecialchars($item['condition']); ?></span>
                                        <a href="items.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-success btn-sm rounded-pill px-2.5 py-1 fw-bold text-nowrap" style="font-size: 0.75rem;">Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reviews tab pane -->
        <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
            <?php if (empty($reviews)): ?>
                <div class="text-center py-5">
                    <div class="text-muted fs-5"><i class="bi bi-chat-square-quote display-4 d-block mb-3 text-secondary"></i> No reviews posted for this user yet.</div>
                </div>
            <?php else: ?>
                <?php
                // Count star ratings
                $star_counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
                foreach ($reviews as $rev) {
                    $r = intval($rev['rating']);
                    if ($r >= 1 && $r <= 5) {
                        $star_counts[$r]++;
                    }
                }
                ?>
                <div class="row g-4">
                    <!-- Rating Breakdown & Histogram Column -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm p-4 sticky-md-top" style="border-radius: 16px; top: 90px; z-index: 10;">
                            <h5 class="fw-bold mb-3 text-dark">Rating Summary</h5>
                            
                            <!-- Average score -->
                            <div class="d-flex align-items-baseline gap-2 mb-1">
                                <span class="display-4 fw-bold text-success"><?php echo $avg_rating; ?></span>
                                <span class="text-muted">/ 5.0</span>
                            </div>
                            
                            <!-- Average stars -->
                            <div class="text-warning mb-3">
                                <?php 
                                $full_stars = floor($avg_rating);
                                $has_half = ($avg_rating - $full_stars) >= 0.5;
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $full_stars) {
                                        echo '<i class="bi bi-star-fill me-1" style="font-size: 1.15rem;"></i>';
                                    } elseif ($i == $full_stars + 1 && $has_half) {
                                        echo '<i class="bi bi-star-half me-1" style="font-size: 1.15rem;"></i>';
                                    } else {
                                        echo '<i class="bi bi-star me-1" style="font-size: 1.15rem;"></i>';
                                    }
                                }
                                ?>
                                <div class="text-muted small mt-1"><?php echo $total_reviews; ?> review<?php echo $total_reviews > 1 ? 's' : ''; ?></div>
                            </div>
                            
                            <!-- Histogram -->
                            <div class="mt-3">
                                <?php for ($i = 5; $i >= 1; $i--): 
                                    $count = $star_counts[$i];
                                    $pct = $total_reviews > 0 ? ($count / $total_reviews) * 100 : 0;
                                ?>
                                    <div class="d-flex align-items-center mb-2" style="font-size: 0.85rem;">
                                        <div style="width: 50px;" class="text-muted fw-semibold"><?php echo $i; ?> star<?php echo $i > 1 ? 's' : ''; ?></div>
                                        <div class="progress flex-grow-1 mx-2" style="height: 8px; border-radius: 4px; background-color: #e9ecef;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $pct; ?>%; border-radius: 4px;" aria-valuenow="<?php echo $pct; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div style="width: 25px;" class="text-end text-muted fw-semibold"><?php echo $count; ?></div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reviews List Column -->
                    <div class="col-md-8">
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($reviews as $rev): ?>
                                <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <!-- Reviewer Name -->
                                            <a href="profile.php?id=<?php echo $rev['reviewer_id']; ?>" class="text-success fw-bold text-decoration-none h6 mb-1 d-block">
                                                <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($rev['reviewer_name']); ?>
                                            </a>
                                            <!-- Review Date -->
                                            <span class="text-muted small" style="font-size: 0.8rem;"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></span>
                                        </div>
                                        
                                        <!-- Review rating stars -->
                                        <div class="text-warning">
                                            <?php 
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $rev['rating']) {
                                                    echo '<i class="bi bi-star-fill ms-0.5"></i>';
                                                } else {
                                                    echo '<i class="bi bi-star ms-0.5"></i>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Comment -->
                                    <p class="mb-0 text-dark" style="line-height: 1.6;">
                                        <?php echo !empty($rev['comment']) ? '"' . htmlspecialchars($rev['comment']) . '"' : '<em class="text-muted">No comment left.</em>'; ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
#profileTabs .nav-link {
    background-color: #f1f3f5;
    color: #495057;
}
#profileTabs .nav-link.active {
    background-color: var(--primary-green) !important;
    color: white !important;
}
</style>

<?php include 'footer.php'; ?>
