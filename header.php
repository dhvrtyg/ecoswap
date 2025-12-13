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
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    
    <style>
        /* --- GENERAL TYPOGRAPHY --- */
        body {
            font-family: 'Lato', sans-serif;
            color: #4a4a4a;
            overflow-x: hidden;
            background-color: #faf9f6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        h1, h2, h3, h4, h5, .navbar-brand, .footer-logo {
            font-family: 'Playfair Display', serif;
        }

        /* --- HERO SECTION --- */
        .hero-section {
            position: relative;
            height: 85vh;
            width: 100%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #3a5a40;
            /* FALLBACK IMAGE: This shows if the video fails */
            background: url('https://images.unsplash.com/photo-1542601906990-b4d3fb7d5c73?q=80&w=2070&auto=format&fit=crop') center/cover no-repeat;
        }

        /* Video Styling */
        .hero-video {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            z-index: 0;
            transform: translate(-50%, -50%);
            object-fit: cover;
        }

        /* Overlay */
        .video-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(250, 249, 246, 0.65); /* Cream tint overlay */
            z-index: 1;
        }

        /* Content */
        .hero-content {
            position: relative;
            z-index: 2; /* Ensures text is on top */
            width: 100%;
            padding: 20px;
        }

        .hero-search-bar {
            background: white;
            padding: 8px;
            border-radius: 5px;
            display: flex;
            max-width: 500px;
            margin: 25px auto;
            border: 1px solid #c8d6c9;
            box-shadow: 0 4px 15px rgba(58, 90, 64, 0.1);
        }

        .hero-search-bar input {
            border: none;
            flex-grow: 1;
            padding: 10px;
            outline: none;
            font-size: 0.95rem;
        }

        .hero-search-bar button {
            background: transparent;
            border: none;
            padding: 0 15px;
            color: #3a5a40;
        }

        /* --- CATEGORY CIRCLES --- */
        .category-section {
            padding: 50px 0;
            text-align: center;
            background-color: white;
            border-bottom: 1px solid #f0f0f0;
        }

        .cat-circle {
            width: 80px;
            height: 80px;
            background-color: #7d9d8d;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 2rem;
            transition: transform 0.3s ease, background-color 0.3s;
        }

        .cat-circle:hover {
            transform: translateY(-5px);
            background-color: #3a5a40;
        }

        .cat-label {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1.5px;
            color: #3a5a40;
            font-weight: 700;
        }

        /* --- HOW IT WORKS --- */
        .how-it-works-section {
            background-color: #3a5a40;
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .step-icon {
            width: 90px;
            height: 90px;
            background-color: #dce775;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            color: #3a5a40;
            font-size: 2.2rem;
            transition: transform 0.3s;
            cursor: pointer;
        }

        .step-hover:hover .step-icon {
            transform: scale(1.1);
            background-color: white;
        }

        /* --- SUGGEST SECTION --- */
        .suggest-section {
            background-color: #b8b8aa;
            padding: 60px 0;
            text-align: center;
            color: white;
        }

        .btn-feature {
            background-color: #dce775;
            color: #3a5a40;
            padding: 12px 35px;
            font-weight: bold;
            border: none;
            margin-top: 20px;
            display: inline-block;
            text-decoration: none;
            transition: all 0.3s;
            border-radius: 4px;
        }

        .btn-feature:hover {
            background-color: #f0f4c3;
            color: #3a5a40;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        /* --- FOOTER --- */
        .custom-footer {
            background-color: #faf9f6;
            padding: 70px 0 30px;
            color: #555;
            border-top: 1px solid #eaeaea;
            margin-top: auto;
        }

        .footer-logo {
            font-size: 2.2rem;
            color: #3a5a40;
            margin-bottom: 15px;
        }

        .footer-link {
            color: #555;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .footer-link:hover {
            color: #3a5a40;
            text-decoration: underline;
        }

        /* --- MOBILE --- */
        @media (max-width: 768px) {
            .hero-section { height: 60vh; }
            .hero-video { display: none; }
            .display-3 { font-size: 2.2rem; }
            .cat-circle { width: 60px; height: 60px; font-size: 1.5rem; }
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
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link mx-2" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link mx-2" href="add_item.php">List Item</a></li>
          <li class="nav-item"><a class="nav-link mx-2 text-danger" href="logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link mx-2" href="login.php">Login</a></li>
          <li class="nav-item"><a class="btn btn-outline-success rounded-pill px-4 mx-2" href="register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>