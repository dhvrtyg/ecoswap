<?php include 'header.php'; ?>

<?php
// Pagination and limits
$limit = 9;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Filter fields
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Handle both string and array input for categories
$categories = [];
if (isset($_GET['category'])) {
    if (is_array($_GET['category'])) {
        $categories = $_GET['category'];
    } elseif (trim($_GET['category']) !== '' && trim($_GET['category']) !== 'All') {
        $categories = [trim($_GET['category'])];
    }
}

$condition = isset($_GET['condition']) ? trim($_GET['condition']) : '';
$status_filter = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : 'available';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';

// Build SQL filters
$where_clauses = [];
$params = [];
$types = "";

if ($search !== '') {
    $search_param = '%' . $search . '%';
    $where_clauses[] = "(name LIKE ? OR description LIKE ?)";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($categories)) {
    $cat_clauses = [];
    foreach ($categories as $cat) {
        $cat = trim($cat);
        if ($cat !== '' && $cat !== 'All') {
            $cat_clauses[] = "category LIKE ?";
            $params[] = '%' . $cat . '%';
            $types .= "s";
        }
    }
    if (!empty($cat_clauses)) {
        $where_clauses[] = "(" . implode(" OR ", $cat_clauses) . ")";
    }
}

if ($condition !== '' && $condition !== 'All') {
    $where_clauses[] = "`condition` = ?";
    $params[] = $condition;
    $types .= "s";
}

if ($status_filter === 'available') {
    $where_clauses[] = "status = 'available'";
} elseif ($status_filter === 'swapped') {
    $where_clauses[] = "status = 'swapped'";
}

$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

// Sorting logic
$order_sql = " ORDER BY created_at DESC";
if ($sort === 'oldest') {
    $order_sql = " ORDER BY created_at ASC";
} elseif ($sort === 'alpha_asc') {
    $order_sql = " ORDER BY name ASC";
} elseif ($sort === 'alpha_desc') {
    $order_sql = " ORDER BY name DESC";
}

// 1. Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM items" . $where_sql;
$stmt_count = $conn->prepare($count_sql);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$res_count = $stmt_count->get_result();
$row_count = $res_count->fetch_assoc();
$total_items = $row_count['total'];
$stmt_count->close();

$total_pages = ceil($total_items / $limit);
if ($total_pages < 1) $total_pages = 1;
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $limit;

// 2. Fetch page items
$items_sql = "SELECT id, name, description, category, `condition`, image_url, user_id, status FROM items" . $where_sql . $order_sql . " LIMIT ? OFFSET ?";
$stmt_items = $conn->prepare($items_sql);

// Append limit/offset params to bind statement
$types_items = $types . "ii";
$params_items = array_merge($params, [$limit, $offset]);

$stmt_items->bind_param($types_items, ...$params_items);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
?>

<div class="container mt-5 mb-5">
    <div class="row mb-4 align-items-end">
        <div class="col-md-8">
            <h2 class="display-6 fw-bold mb-1" style="font-family: 'Playfair Display'; color: var(--primary-green);">Explore Swaps</h2>
            <p class="text-muted mb-0">Discover and trade books, clothes, electronics, and dorm decorations around campus.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                <i class="bi bi-tag-fill text-success me-1"></i> <?php echo $total_items; ?> Items Found
            </span>
        </div>
    </div>

<style>
    @media (max-width: 767.98px) {
        .item-card {
            border-radius: 16px !important;
        }
        .item-card .card-img-top {
            height: 130px !important;
            padding: 10px !important;
        }
        .item-card .card-img-top img {
            max-height: 110px !important;
        }
        .item-card .card-body {
            padding: 12px !important;
        }
        .item-card .card-footer {
            padding: 0 12px 12px 12px !important;
        }
        .item-card .card-title {
            font-size: 0.95rem !important;
            margin-bottom: 4px !important;
        }
        .item-card .card-text {
            font-size: 0.75rem !important;
            margin-bottom: 8px !important;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 36px;
        }
        .item-card .card-footer .btn {
            font-size: 0.8rem !important;
            padding: 6px 12px !important;
        }
        .item-card .badge.position-absolute {
            font-size: 0.6rem !important;
            padding: 4px 8px !important;
            top: 10px !important;
            left: 10px !important;
        }
        .item-card .badge.position-absolute[style*="right"] {
            right: 10px !important;
            left: auto !important;
        }
    }
</style>

    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <!-- Mobile Filter Toggler Button -->
            <div class="d-lg-none mb-3">
                <button class="btn btn-outline-success w-100 rounded-pill py-2.5 fw-semibold d-flex align-items-center justify-content-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#mobileFiltersCollapse" aria-expanded="false" aria-controls="mobileFiltersCollapse">
                    <i class="bi bi-sliders2-vertical"></i> Show Filters & Sort
                </button>
            </div>
            
            <div class="collapse d-lg-block" id="mobileFiltersCollapse">
                <div class="card shadow-sm border-0 p-4 sticky-lg-top" style="top: 100px; border-radius: 16px; background-color: white;">
                    <h5 class="fw-bold mb-4" style="color: var(--primary-green); font-family: 'Playfair Display'; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">Filters</h5>
                    
                    <form action="items.php" method="GET">
                        <!-- Search query -->
                        <div class="mb-3">
                            <label for="searchQuery" class="form-label small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Search keyword</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light border-0 py-2" id="searchQuery" name="search" placeholder="Type here..." value="<?php echo htmlspecialchars($search); ?>" style="border-radius: 8px 0 0 8px;">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                            </div>
                        </div>

                        <!-- Category selection (Checkboxes) -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Category</label>
                            <div class="bg-light p-3 border-0" style="border-radius: 8px;">
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" id="catAll" name="category_all" value="1" <?php echo empty($categories) ? 'checked' : ''; ?> onchange="if(this.checked){ document.querySelectorAll('.cat-check').forEach(c => c.checked = false); }">
                                    <label class="form-check-label small" for="catAll">All Categories</label>
                                </div>
                                <div class="form-check mb-1">
                                    <input class="form-check-input cat-check" type="checkbox" name="category[]" value="Books" id="catBooks" <?php echo in_array('Books', $categories) ? 'checked' : ''; ?> onchange="document.getElementById('catAll').checked = false;">
                                    <label class="form-check-label small" for="catBooks">Books</label>
                                </div>
                                <div class="form-check mb-1">
                                    <input class="form-check-input cat-check" type="checkbox" name="category[]" value="Electronics" id="catElectronics" <?php echo in_array('Electronics', $categories) ? 'checked' : ''; ?> onchange="document.getElementById('catAll').checked = false;">
                                    <label class="form-check-label small" for="catElectronics">Electronics</label>
                                </div>
                                <div class="form-check mb-1">
                                    <input class="form-check-input cat-check" type="checkbox" name="category[]" value="Stationery" id="catStationery" <?php echo in_array('Stationery', $categories) ? 'checked' : ''; ?> onchange="document.getElementById('catAll').checked = false;">
                                    <label class="form-check-label small" for="catStationery">Stationery</label>
                                </div>
                                <div class="form-check mb-1">
                                    <input class="form-check-input cat-check" type="checkbox" name="category[]" value="Clothing" id="catClothing" <?php echo in_array('Clothing', $categories) ? 'checked' : ''; ?> onchange="document.getElementById('catAll').checked = false;">
                                    <label class="form-check-label small" for="catClothing">Clothing</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input cat-check" type="checkbox" name="category[]" value="Dorm" id="catDorm" <?php echo in_array('Dorm', $categories) ? 'checked' : ''; ?> onchange="document.getElementById('catAll').checked = false;">
                                    <label class="form-check-label small" for="catDorm">Dorm</label>
                                </div>
                            </div>
                        </div>

                        <!-- Condition selection -->
                        <div class="mb-3">
                            <label for="filterCondition" class="form-label small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Condition</label>
                            <select class="form-select bg-light border-0 py-2" id="filterCondition" name="condition" style="border-radius: 8px;">
                                <option value="All" <?php echo $condition === 'All' || $condition === '' ? 'selected' : ''; ?>>All Conditions</option>
                                <option value="New" <?php echo $condition === 'New' ? 'selected' : ''; ?>>New</option>
                                <option value="Like New" <?php echo $condition === 'Like New' ? 'selected' : ''; ?>>Like New</option>
                                <option value="Good" <?php echo $condition === 'Good' ? 'selected' : ''; ?>>Good</option>
                                <option value="Fair" <?php echo $condition === 'Fair' ? 'selected' : ''; ?>>Fair</option>
                            </select>
                        </div>

                        <!-- Sort -->
                        <div class="mb-3">
                            <label for="filterSort" class="form-label small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Sort by</label>
                            <select class="form-select bg-light border-0 py-2" id="filterSort" name="sort" style="border-radius: 8px;">
                                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest Listed</option>
                                <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest Listed</option>
                                <option value="alpha_asc" <?php echo $sort === 'alpha_asc' ? 'selected' : ''; ?>>Alphabetical (A-Z)</option>
                                <option value="alpha_desc" <?php echo $sort === 'alpha_desc' ? 'selected' : ''; ?>>Alphabetical (Z-A)</option>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="mb-4">
                            <label for="filterStatus" class="form-label small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Status</label>
                            <select class="form-select bg-light border-0 py-2" id="filterStatus" name="status_filter" style="border-radius: 8px;">
                                <option value="available" <?php echo $status_filter === 'available' ? 'selected' : ''; ?>>Available</option>
                                <option value="swapped" <?php echo $status_filter === 'swapped' ? 'selected' : ''; ?>>Swapped</option>
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Items</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-outline-success w-100 rounded-pill py-2 fw-semibold">Apply Filters</button>
                        <a href="items.php" class="btn btn-link w-100 text-center text-muted small mt-2 text-decoration-none">Reset All</a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Catalog Results -->
        <div class="col-lg-9">
            <?php if ($result_items->num_rows > 0): ?>
                <div class="row g-3 g-md-4">
                    <?php while($row = $result_items->fetch_assoc()): ?>
                        <?php $img_src = htmlspecialchars(get_item_image($row["image_url"], $row["category"])); ?>
                        <div class="col-6 col-md-6 col-xl-4">
                            <div class="card item-card h-100 shadow-sm border-0 position-relative overflow-hidden">
                                
                                <!-- Category Badge floating on card -->
                                <span class="badge position-absolute bg-white text-dark shadow-sm px-3 py-2 rounded-pill font-monospace" style="top: 15px; left: 15px; z-index: 5; font-size: 0.7rem; font-weight: 700; border: 1px solid var(--border-color);">
                                    <?php echo htmlspecialchars($row["category"]); ?>
                                </span>

                                <?php if ($row["status"] == 'swapped'): ?>
                                    <span class="badge position-absolute bg-secondary text-white shadow-sm px-3 py-2 rounded-pill" style="top: 15px; right: 15px; z-index: 5; font-size: 0.7rem; font-weight: 700;">
                                        Swapped
                                    </span>
                                <?php endif; ?>

                                <!-- Animated Vector Image Display -->
                                <div class="card-img-top" style="height: 200px; overflow: hidden; padding: 20px; display: flex; align-items: center; justify-content: center; background-color: var(--sage-light); border-bottom: 1px solid var(--border-color);">
                                    <img src="<?php echo $img_src; ?>" style="height: 100%; max-height: 160px; object-fit: contain; transition: var(--transition-smooth);" class="item-image-vector" alt="Item Image">
                                </div>

                                <div class="card-body d-flex flex-column justify-content-between p-4">
                                    <div>
                                        <h5 class="card-title fw-bold mb-2" style="color: var(--primary-green); font-family: 'Playfair Display';"><?php echo htmlspecialchars($row["name"]); ?></h5>
                                        <p class="card-text text-muted small mb-3"><?php echo htmlspecialchars(substr($row["description"], 0, 100)) . (strlen($row["description"]) > 100 ? '...' : ''); ?></p>
                                        <div class="d-flex align-items-center gap-2 mb-3">
                                            <span class="badge bg-light text-muted border rounded-pill px-2 py-1" style="font-size: 0.7rem;">Condition: <?php echo htmlspecialchars($row["condition"]); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer bg-white border-0 pb-4 px-4">
                                    <?php if ($row["status"] == 'swapped'): ?>
                                        <button class="btn btn-light w-100 rounded-pill text-muted" disabled>Item Swapped</button>
                                    <?php elseif (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $row['user_id']): ?>
                                        <a href="request_swap.php?item_id=<?php echo $row['id']; ?>" class="btn btn-outline-success w-100 rounded-pill">Request Swap</a>
                                    <?php elseif (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']): ?>
                                        <button class="btn btn-light w-100 rounded-pill text-muted" disabled>Your Item</button>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-outline-success w-100 rounded-pill">Login to Swap</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination navigation -->
                <?php if ($total_pages > 1): ?>
                    <nav class="mt-5 d-flex justify-content-center">
                        <ul class="pagination pagination-md shadow-sm rounded-pill" style="overflow: hidden;">
                            <!-- Previous page -->
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link border-0 text-success bg-white px-3" href="items.php?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>

                            <!-- Page numbers -->
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link border-0 px-3 <?php echo $page == $i ? 'bg-success text-white' : 'text-success bg-white'; ?>" href="items.php?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next page -->
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link border-0 text-success bg-white px-3" href="items.php?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <!-- Clean Animated Empty State -->
                <div class="text-center py-5 bg-white border border-light-subtle shadow-sm" style="border-radius: 20px;">
                    <div style="margin: 0 auto 1.5rem; display: flex; justify-content: center;">
                        <svg width="120" height="120" viewBox="0 0 100 100">
                            <defs>
                                <linearGradient id="empty-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#f2f5f3" />
                                    <stop offset="100%" stop-color="#e2ece4" />
                                </linearGradient>
                                <style>
                                    @keyframes pulseMagnifier {
                                        0%, 100% { transform: translate(0px, 0px) scale(1); }
                                        50% { transform: translate(-3px, -3px) scale(1.05); }
                                    }
                                    .magnifier-lens {
                                        animation: pulseMagnifier 4s ease-in-out infinite;
                                        transform-origin: 40px 40px;
                                    }
                                </style>
                            </defs>
                            <circle cx="50" cy="50" r="45" fill="url(#empty-grad)" />
                            
                            <!-- Small leaves on the side -->
                            <path d="M70,40 C65,45 68,50 70,50 C72,50 75,45 70,40 Z" fill="var(--accent-green)" opacity="0.6"/>
                            <path d="M25,60 C20,65 23,70 25,70 C27,70 30,65 25,60 Z" fill="var(--primary-green)" opacity="0.6"/>
                            
                            <!-- Search Glass -->
                            <g class="magnifier-lens">
                                <circle cx="45" cy="45" r="16" fill="none" stroke="var(--primary-green)" stroke-width="3" />
                                <line x1="56" y1="56" x2="72" y2="72" stroke="var(--primary-green)" stroke-width="4.5" stroke-linecap="round" />
                                <!-- Lens reflection -->
                                <path d="M37,37 A12,12 0 0,1 47,33" fill="none" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"/>
                            </g>
                        </svg>
                    </div>
                    <h4 class="fw-bold" style="color: var(--primary-green); font-family: 'Playfair Display';">No Swaps Found</h4>
                    <p class="text-muted mx-auto" style="max-width: 400px; font-size: 0.95rem;">We couldn't find any items matching your criteria. Try adjusting your keywords or clearing filters.</p>
                    <a href="items.php" class="btn btn-outline-success rounded-pill px-4 py-2 mt-3">Reset Search</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$stmt_items->close();
$conn->close();
?>

<?php include 'footer.php'; ?>
