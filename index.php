<?php include 'header.php'; ?>

<div class="hero-section">
    <div class="hero-vector-blob-1"></div>
    <div class="hero-vector-blob-2"></div>
    <div class="hero-content">
        <div class="mb-2" style="font-size: 1.1rem; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; color: var(--accent-green);">Community Bartering Platform</div>
        <h1 class="display-3 mb-4 fw-bold" style="font-family: 'Playfair Display', serif; letter-spacing: -1px; line-height: 1.15; color: var(--primary-green);">Trade What You Have,<br>Get What You Need</h1>
        <p class="fs-5 mb-4 text-muted mx-auto" style="max-width: 580px; font-weight: 400;">The premium campus platform for community-based bartering. Connect, swap, and share on our open source platform.</p>
        
        <form action="items.php" method="GET" class="hero-search-bar">
            <input type="text" name="search" placeholder="Search books, electronics, dorm decor..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit"><i class="bi bi-search"></i></button>
        </form>
    </div>
</div>

<div class="category-section">
    <div class="container">
        <div class="row justify-content-center g-4">
            <div class="col-4 col-md-2">
                <a href="items.php?category=Books" class="text-decoration-none">
                    <div class="cat-circle"><i class="bi bi-book"></i></div>
                    <div class="cat-label">Books</div>
                </a>
            </div>
            <div class="col-4 col-md-2">
                <a href="items.php?category=Electronics" class="text-decoration-none">
                    <div class="cat-circle"><i class="bi bi-laptop"></i></div>
                    <div class="cat-label">Electronics</div>
                </a>
            </div>
            <div class="col-4 col-md-2">
                <a href="items.php?category=Stationery" class="text-decoration-none">
                    <div class="cat-circle"><i class="bi bi-pen"></i></div>
                    <div class="cat-label">Stationery</div>
                </a>
            </div>
            <div class="col-4 col-md-2">
                <a href="items.php?category=Clothing" class="text-decoration-none">
                    <div class="cat-circle"><i class="bi bi-bag"></i></div>
                    <div class="cat-label">Clothing</div>
                </a>
            </div>
            <div class="col-4 col-md-2">
                <a href="items.php?category=Dorm" class="text-decoration-none">
                    <div class="cat-circle"><i class="bi bi-lamp"></i></div>
                    <div class="cat-label">Dorm</div>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="how-it-works-section">
    <div class="container">
        <h2 class="display-5 mb-5 fw-bold" style="font-family: 'Playfair Display'; letter-spacing: -0.5px;">How EcoSwap Works</h2>
        <div class="row text-center g-4">
            
            <div class="col-md-4">
                <a href="add_item.php" class="text-decoration-none">
                    <div class="step-card">
                        <div class="step-icon">
                            <i class="bi bi-camera"></i>
                        </div>
                        <h4 class="mb-3 fw-bold" style="color: var(--primary-green);">1. List Your Item</h4>
                        <p class="text-muted mb-0">Upload details of clothes, books, or dorm gear you no longer need. Quick and simple.</p>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="index.php?search=" class="text-decoration-none">
                    <div class="step-card">
                        <div class="step-icon">
                            <i class="bi bi-search"></i>
                        </div>
                        <h4 class="mb-3 fw-bold" style="color: var(--primary-green);">2. Discover Swaps</h4>
                        <p class="text-muted mb-0">Explore listings from other students on campus. Request a trade in one click.</p>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="dashboard.php" class="text-decoration-none">
                    <div class="step-card">
                        <div class="step-icon">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                        <h4 class="mb-3 fw-bold" style="color: var(--primary-green);">3. Meet & Swap</h4>
                        <p class="text-muted mb-0">Chat securely on the platform to arrange a safe meetup on campus and complete the exchange.</p>
                    </div>
                </a>
            </div>

        </div>
    </div>
</div>

<div class="suggest-section">
    <div class="container">
        <h2 class="display-6 mb-2 fw-bold" style="font-family: 'Playfair Display';">Have something to trade?</h2>
        <p class="mb-3 text-muted">Join hundreds of students saving money and promoting sustainability every semester.</p>
        <a href="add_item.php" class="btn-feature shadow">List an Item Now &rarr;</a>
    </div>
</div>

<div class="container mt-5 mb-5 pt-4">
    <h3 class="text-center mb-5" style="font-family: 'Playfair Display'; color: var(--primary-green); font-weight: 700; letter-spacing: -0.5px;">Recently Listed for Swap</h3>
    <div class="row g-4">
        <?php
        $where_clauses = ["status = 'available'"]; 
        $params = [];
        $types = "";
        
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search_term = '%' . $_GET['search'] . '%';
            $where_clauses[] = "(name LIKE ? OR description LIKE ?)";
            $params[] = $search_term;
            $params[] = $search_term;
            $types .= "ss";
        }
        
        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $where_clauses[] = "category = ?";
            $params[] = $_GET['category'];
            $types .= "s";
        }

        $sql = "SELECT id, name, description, category, `condition`, image_url, user_id FROM items";
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
        $sql .= " ORDER BY created_at DESC LIMIT 6"; 

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $img_src = htmlspecialchars(get_item_image($row["image_url"], $row["category"]));
                echo "<div class='col-md-4'>";
                echo "<div class='card h-100 shadow-sm border-0'>";
                echo "<div class='card-img-top' style='height: 220px; overflow: hidden; padding: 15px; display: flex; align-items: center; justify-content: center; background-color: var(--sage-light);'>";
                echo "<img src='" . $img_src . "' style='height: 100%; max-height: 190px; object-fit: contain; transition: var(--transition-smooth);' class='item-image-vector' alt='Item'>";
                echo "</div>";
                echo "<div class='card-body text-center d-flex flex-column justify-content-between'>";
                echo "<div>";
                echo "<h5 class='card-title mb-1' style='color: var(--primary-green); font-weight: bold;'>" . htmlspecialchars($row["name"]) . "</h5>";
                echo "<p class='card-text text-muted small text-uppercase mb-3' style='letter-spacing: 1px; font-size: 0.75rem; font-weight: 600;'>" . htmlspecialchars($row["category"]) . " &bull; " . htmlspecialchars($row["condition"]) . "</p>";
                echo "</div>";
                echo "</div>"; 
                echo "<div class='card-footer bg-white border-top-0 pb-4 px-4'>";
                
                if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $row['user_id']) {
                    echo "<a href='request_swap.php?item_id=" . $row['id'] . "' class='btn btn-outline-success w-100 rounded-pill'>Request Swap</a>";
                } else if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']) {
                    echo "<button class='btn btn-light w-100 rounded-pill text-muted' disabled>Your Item</button>";
                } else {
                    echo "<a href='login.php' class='btn btn-outline-success w-100 rounded-pill'>Login to Swap</a>";
                }
                echo "</div>"; 
                echo "</div>"; 
                echo "</div>"; 
            }
        } else {
            echo "<div class='col-12 text-center text-muted py-5'><i class='bi bi-inbox fs-1 d-block mb-3'></i>No items found. Be the first to list one!</div>";
        }
        $stmt->close();
        ?>
    </div>
</div>

<?php include 'footer.php'; ?>