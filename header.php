<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoSwap</title>
    
    <link href="https://bootswatch.com/5/minty/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-green: #2b4c33;
            --accent-green: #4f772d;
            --sage-light: #f0f5f1;
            --sage-medium: #dce6df;
            --text-dark: #1b2e22;
            --text-muted: #5a7061;
            --bg-page: #fbfcfb;
            --border-color: #e3ebde;
            --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* --- GENERAL TYPOGRAPHY --- */
        body {
            font-family: 'Outfit', sans-serif;
            color: var(--text-dark);
            overflow-x: hidden;
            background-color: var(--bg-page);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        h1, h2, h3, h4, h5, .navbar-brand, .footer-logo {
            font-family: 'Playfair Display', serif;
            color: var(--primary-green);
        }

        /* --- NAV BAR --- */
        .navbar {
            background-color: rgba(251, 252, 251, 0.9) !important;
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
        }

        .navbar-brand {
            font-size: 1.8rem !important;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .nav-link {
            font-weight: 500;
            color: var(--text-muted) !important;
            transition: var(--transition-smooth);
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary-green) !important;
        }

        .btn-outline-success {
            color: var(--primary-green);
            border-color: var(--primary-green);
            font-weight: 500;
            transition: var(--transition-smooth);
        }

        .btn-outline-success:hover {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
            color: white;
            transform: translateY(-1px);
        }

        /* --- HERO SECTION (Geometric Vector) --- */
        .hero-section {
            position: relative;
            height: 80vh;
            width: 100%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: linear-gradient(135deg, #f3f7f4 0%, #e8f0eb 100%);
        }

        /* Abstract geometric grid lines */
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: 
                radial-gradient(var(--border-color) 1.5px, transparent 1.5px),
                radial-gradient(var(--border-color) 1.5px, transparent 1.5px);
            background-size: 40px 40px;
            background-position: 0 0, 20px 20px;
            opacity: 0.6;
            z-index: 0;
        }

        /* Vector shape highlights */
        .hero-vector-blob-1 {
            position: absolute;
            top: -10%; left: -10%;
            width: 50vw; height: 50vw;
            background: radial-gradient(circle, rgba(79, 119, 45, 0.08) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
            filter: blur(40px);
            z-index: 0;
        }

        .hero-vector-blob-2 {
            position: absolute;
            bottom: -10%; right: -10%;
            width: 45vw; height: 45vw;
            background: radial-gradient(circle, rgba(43, 76, 51, 0.08) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
            filter: blur(40px);
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            width: 100%;
            padding: 20px;
        }

        .hero-search-bar {
            background: white;
            padding: 6px 12px;
            border-radius: 99px;
            display: flex;
            align-items: center;
            max-width: 540px;
            margin: 30px auto 0;
            border: 1px solid var(--border-color);
            box-shadow: 0 8px 30px rgba(43, 76, 51, 0.06);
            transition: var(--transition-smooth);
        }

        .hero-search-bar:focus-within {
            border-color: var(--accent-green);
            box-shadow: 0 8px 30px rgba(43, 76, 51, 0.12);
            transform: translateY(-2px);
        }

        .hero-search-bar input {
            border: none;
            flex-grow: 1;
            padding: 12px 16px;
            outline: none;
            font-size: 1rem;
            color: var(--text-dark);
            background: transparent;
            font-family: inherit;
        }

        .hero-search-bar input::placeholder {
            color: var(--text-muted);
            opacity: 0.7;
        }

        .hero-search-bar button {
            background: var(--primary-green);
            border: none;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-smooth);
        }

        .hero-search-bar button:hover {
            background: var(--accent-green);
            transform: scale(1.05);
        }

        /* --- CATEGORY CIRCLES --- */
        .category-section {
            padding: 60px 0;
            text-align: center;
            background-color: white;
            border-bottom: 1px solid var(--border-color);
        }

        .cat-circle {
            width: 72px;
            height: 72px;
            background-color: var(--sage-light);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: var(--primary-green);
            font-size: 1.8rem;
            transition: var(--transition-smooth);
            border: 1px solid var(--border-color);
        }

        .cat-circle:hover {
            transform: translateY(-4px) rotate(3deg);
            background-color: var(--primary-green);
            color: white;
            border-color: var(--primary-green);
            box-shadow: 0 10px 20px rgba(43, 76, 51, 0.1);
        }

        .cat-label {
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            color: var(--text-dark);
            font-weight: 600;
            transition: var(--transition-smooth);
        }

        a:hover .cat-label {
            color: var(--primary-green);
        }

        /* --- HOW IT WORKS (Modern Light Layout) --- */
        .how-it-works-section {
            background-color: var(--bg-page);
            color: var(--text-dark);
            padding: 90px 0;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }

        .step-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 40px 30px;
            height: 100%;
            box-shadow: 0 4px 20px -2px rgba(43, 76, 51, 0.03);
            transition: var(--transition-smooth);
        }

        .step-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 30px -4px rgba(43, 76, 51, 0.08);
            border-color: var(--accent-green);
        }

        .step-icon {
            width: 80px;
            height: 80px;
            background-color: var(--sage-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            color: var(--primary-green);
            font-size: 2.2rem;
            transition: var(--transition-smooth);
        }

        .step-card:hover .step-icon {
            background-color: var(--primary-green);
            color: white;
            transform: scale(1.05);
        }

        /* --- SUGGEST SECTION --- */
        .suggest-section {
            background: linear-gradient(135deg, var(--sage-light) 0%, #e2ece5 100%);
            padding: 70px 0;
            text-align: center;
            color: var(--text-dark);
            border-bottom: 1px solid var(--border-color);
        }

        .btn-feature {
            background-color: var(--primary-green);
            color: white;
            padding: 14px 38px;
            font-weight: 600;
            border: none;
            margin-top: 24px;
            display: inline-block;
            text-decoration: none;
            transition: var(--transition-smooth);
            border-radius: 99px;
            box-shadow: 0 4px 15px rgba(43, 76, 51, 0.15);
        }

        .btn-feature:hover {
            background-color: var(--accent-green);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(43, 76, 51, 0.25);
        }

        /* --- CARD / PRODUCT DESIGN --- */
        .card {
            border-radius: 20px;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid var(--border-color) !important;
            box-shadow: 0 4px 20px -2px rgba(43, 76, 51, 0.04) !important;
            transition: var(--transition-smooth);
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 30px -4px rgba(43, 76, 51, 0.08) !important;
            border-color: var(--accent-green) !important;
        }

        .card-img-top {
            border-bottom: 1px solid var(--border-color);
            background-color: #f7faf8;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* --- FOOTER --- */
        .custom-footer {
            background-color: white;
            padding: 80px 0 40px;
            color: var(--text-muted);
            border-top: 1px solid var(--border-color);
            margin-top: auto;
        }

        .footer-logo {
            font-size: 2.2rem;
            color: var(--primary-green);
            margin-bottom: 15px;
            font-weight: 700;
        }

        .footer-link {
            color: var(--text-muted);
            text-decoration: none;
            display: block;
            margin-bottom: 12px;
            font-size: 0.95rem;
            transition: var(--transition-smooth);
        }

        .footer-link:hover {
            color: var(--primary-green);
            padding-left: 4px;
        }

        /* --- RESPONSIVE ADJUSTMENTS --- */
        @media (max-width: 768px) {
            .hero-section { height: 70vh; }
            .display-3 { font-size: 2.4rem; }
            .cat-circle { width: 64px; height: 64px; font-size: 1.5rem; }
            .step-card { padding: 30px 20px; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light py-3 shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php" style="font-family: 'Playfair Display', serif; font-size: 1.8rem; color: #3a5a40;">
        eco<span style="font-style: italic; color: #7d9d8d;">\</span>wap
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link mx-2" href="items.php">Browse Items</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
          <?php
          $pending_count = 0;
          $sql_pending = "SELECT COUNT(id) FROM swaps WHERE item1_owner_id = ? AND status = 'pending'";
          $stmt_pending = $conn->prepare($sql_pending);
          if ($stmt_pending) {
              $stmt_pending->bind_param("i", $_SESSION['user_id']);
              $stmt_pending->execute();
              $stmt_pending->bind_result($pending_count);
              $stmt_pending->fetch();
              $stmt_pending->close();
          }
          ?>
          <li class="nav-item">
            <a class="nav-link mx-2 position-relative d-inline-block" href="dashboard.php">
              Dashboard
              <?php if ($pending_count > 0): ?>
                <span class="position-absolute top-10 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25em 0.5em; transform: translate(2px, -2px) !important;">
                  <?php echo $pending_count; ?>
                </span>
              <?php endif; ?>
            </a>
          </li>
          <li class="nav-item"><a class="nav-link mx-2" href="add_item.php">List Item</a></li>
          <li class="nav-item"><a class="nav-link mx-2" href="profile.php"><i class="bi bi-person-circle me-1"></i> My Profile</a></li>
          <li class="nav-item"><a class="nav-link mx-2 text-danger text-opacity-75" href="logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link mx-2" href="login.php">Login</a></li>
          <li class="nav-item"><a class="btn btn-outline-success rounded-pill px-4 mx-2" href="register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>