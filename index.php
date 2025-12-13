<?php include 'header.php'; ?>

<div class="hero-section" style="background: url('https://images.unsplash.com/photo-1518173946687-a4c8892bbd9f?q=80&w=2070&auto=format&fit=crop') no-repeat center center; background-size: cover; position: relative; height: 85vh; display: flex; align-items: center; justify-content: center; overflow: hidden;">
    
    <video autoplay muted loop playsinline class="hero-video" style="position: absolute; top: 50%; left: 50%; min-width: 100%; min-height: 100%; width: auto; height: auto; z-index: 0; transform: translate(-50%, -50%); object-fit: cover;">
        <source src="https://cdn.coverr.co/videos/coverr-walking-in-a-forest-4354/1080p.mp4" type="video/mp4">
    </video>
    
    <div class="video-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(250, 249, 246, 0.65); z-index: 1;"></div>

    <div class="hero-content" style="position: relative; z-index: 2; width: 100%; text-align: center; color: #3a5a40;">
        <div class="mb-3" style="font-family: 'Playfair Display'; font-size: 1.4rem; opacity: 0.9;">eco\wap</div>
        
        <h1 class="display-3 mb-3 fw-bold" style="font-family: 'Playfair Display'; letter-spacing: -1px;">Trade What You Have,<br>Get What You Need</h1>
        <p class="fs-5 mb-5" style="color: #4a4a4a; max-width: 600px; margin: 0 auto;">The premium college community platform for sustainable bartering. Save money and reduce waste today.</p>
        
        <form action="index.php" method="GET" class="hero-search-bar shadow-sm" style="background: white; padding: 8px; border-radius: 5px; display: flex; max-width: 500px; margin: 25px auto; border: 1px solid #c8d6c9;">
            <input type="text" name="search" placeholder="Search for items..." style="border: none; flex-grow: 1; padding: 10px; outline: none;">
            <button type="submit" style="background: transparent; border: none; padding: 0 15px; color: #3a5a40;"><i class="bi bi-search fs-5"></i></button>
        </form>
    </div>
</div>

<div class="category-section">
    <div class="container">
        <div class="row justify-content-center g-4">
            <div class="col-4 col-md-2">
                <a href="index.php?category=Books" class="text-decoration-none">
                    <div class="cat-circle"><i class="bi bi-book"></i></div>
                    <div class="cat-label">Books</div>
                </a>
            </div>
            <div class="col-4 col-md-2">
                <a href="index.php?category=Electronics" class="text-decoration-none">
                    <div class="cat-circle"><i class="bi bi-laptop"></i></div>
                    <div class="cat-label">Electronics</div>
                </a>
            </div>
            <div class="col-4 col-md-2">
                <a href="index.php?category=Stationery" class="text-decoration-none">
                    <div class="cat-circle"><i class="bi bi-pen"></i></div>
                    <div class="cat-label">Stationery</div>
                </a>
            </div>
            <div class="col-4 col-md-2">
                <a href="index.php?category=Clothing" class="text-decoration-none">
                    <div class="cat-circle"><i class="bi bi-bag"></i></div>
                    <div class="cat-label">Clothing</div>
                </a>
            </div>
            <div class="col-4 col-md-2">
                <a href="index.php?category=Dorm" class="text-decoration-none">
                    <div class="cat-circle"><i class="bi bi-lamp"></i></div>
                    <div class="cat-label">Dorm</div>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="how-it-works-section">
    <div class="container">
        <h2 class="display-5 mb-5">How EcoSwap Works</h2>
        <div class="row text-center">
            
            <div class="col-md-4 mb-4 px-4">
                <a href="add_item.php" class="text-decoration-none text-white step-hover">
                    <div class="step-icon">
                        <i class="bi bi-camera"></i>
                    </div>
                    <h4 class="mb-3 mt-3">1. List It</h4>
                    <p class="small text-white-50 px-3">Upload a photo of the item you don't need anymore. Click here to list now.</p>
                </a>
            </div>

            <div class="col-md-4 mb-4 px-4">
                <a href="index.php?search=" class="text-decoration-none text-white step-hover">
                    <div class="step-icon">
                        <i class="bi bi-search"></i>
                    </div>
                    <h4 class="mb-3 mt-3">2. Request</h4>
                    <p class="small text-white-50 px-3">Browse items from other students and send a swap request.</p>
                </a>
            </div>

            <div class="col-md-4 mb-4 px-4">
                <a href="dashboard.php" class="text-decoration-none text-white step-hover">
                    <div class="step-icon">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <h4 class="mb-3 mt-3">3. Swap</h4>
                    <p class="small text-white-50 px-3">Chat to arrange a meetup on campus. Click to view your chats.</p>
                </a>
            </div>

        </div>
    </div>
</div>

<div class="suggest-section">
    <div class="container">
        <h2 class="display-6 mb-3">Have something to trade?</h2>
        <p class="mb-4" style="opacity: 0.9;">Join hundreds of students saving money every semester.</p>
        <a href="add_item.php" class="btn-feature shadow">List an Item Now >></a>
    </div>
</div>

<div class="container mt-5 mb-5 pt-4">
    <h3 class="text-center mb-5" style="font-family: 'Playfair Display'; color: #3a5a40;">Recently Listed for Swap</h3>
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
                echo "<div class='col-md-4'>";
                echo "<div class='card h-100 shadow-sm border-0' style='transition: transform 0.3s; cursor: pointer;'>";
                echo "<img src='" . htmlspecialchars($row["image_url"]) . "' class='card-img-top' style='height: 220px; object-fit: cover;' alt='Item'>";
                echo "<div class='card-body text-center'>";
                echo "<h5 class='card-title mb-1' style='color: #3a5a40; font-weight: bold;'>" . htmlspecialchars($row["name"]) . "</h5>";
                echo "<p class='card-text text-muted small text-uppercase' style='letter-spacing: 1px; font-size: 0.75rem;'>" . htmlspecialchars($row["category"]) . " • " . htmlspecialchars($row["condition"]) . "</p>";
                echo "</div>"; 
                echo "<div class='card-footer bg-white border-top-0 pb-4 px-4'>";
                
                if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $row['user_id']) {
                    echo "<a href='request_swap.php?item_id=" . $row['id'] . "' class='btn btn-outline-success w-100 rounded-pill' style='color: #3a5a40; border-color: #3a5a40;'>Request Swap</a>";
                } else if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']) {
                    echo "<button class='btn btn-light w-100 rounded-pill text-muted' disabled>Your Item</button>";
                } else {
                    echo "<a href='login.php' class='btn btn-outline-success w-100 rounded-pill' style='color: #3a5a40; border-color: #3a5a40;'>Login to Swap</a>";
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