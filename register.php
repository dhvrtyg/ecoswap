<?php include 'header.php'; ?>

<style>
    /* Hide navbar on register */
    nav.navbar { display: none; }
    body { padding: 0; margin: 0; overflow: hidden; background: white; }
    .split-container { display: flex; height: 100vh; width: 100vw; }
    .split-image {
        flex: 1;
        position: relative;
        display: none;
        overflow: hidden;
    }
    @media(min-width: 768px) { .split-image { display: block; } }
    .image-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 50px; text-align: center;
    }
    .split-form {
        flex: 1; display: flex; flex-direction: column; justify-content: center;
        align-items: center; padding: 40px; position: relative; max-width: 600px;
        background-color: #faf9f6;
    }
    .back-btn {
        position: absolute;
        top: 30px; left: 30px;
        text-decoration: none;
        color: var(--primary-green);
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 5px;
    }
</style>

<div class="split-container">
    <div class="split-image">
        <!-- SVG background, animated -->
        <svg viewBox="0 0 100 100" preserveAspectRatio="xMidYMid slice" style="position: absolute; top:0; left:0; width:100%; height:100%; z-index: 1;">
            <defs>
                <linearGradient id="bg-grad-reg" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#f2f5f3" />
                    <stop offset="100%" stop-color="#dae3dc" />
                </linearGradient>
                <style>
                    @keyframes sway {
                        0%, 100% { transform: rotate(-2deg); }
                        50% { transform: rotate(2deg); }
                    }
                    @keyframes growth {
                        0% { transform: scale(0.85); opacity: 0.9; }
                        50% { transform: scale(1.05); opacity: 1; }
                        100% { transform: scale(0.85); opacity: 0.9; }
                    }
                    .swaying-plant {
                        animation: sway 5s ease-in-out infinite;
                        transform-origin: 50px 75px;
                    }
                    .pulsing-sun {
                        animation: growth 8s ease-in-out infinite;
                        transform-origin: 50px 30px;
                    }
                </style>
            </defs>
            <rect width="100" height="100" fill="url(#bg-grad-reg)" />
            <!-- Soft glowing sun/circle -->
            <circle cx="50" cy="30" r="12" fill="#eef5ef" stroke="none" class="pulsing-sun" />
        </svg>
        <div class="image-overlay" style="z-index: 2;">
            <div class="swaying-plant" style="margin-bottom: 2rem; display: flex; justify-content: center;">
                <svg width="140" height="140" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="40" fill="#ffffff" stroke="var(--primary-green)" stroke-width="1.5" />
                    <!-- Stem -->
                    <path d="M50,75 L50,45 C50,40 55,35 60,35" fill="none" stroke="var(--primary-green)" stroke-width="3" stroke-linecap="round"/>
                    <!-- Leaf 1 -->
                    <path d="M50,55 C42,50 42,42 50,45 Z" fill="var(--accent-green)" />
                    <!-- Leaf 2 -->
                    <path d="M60,35 C68,30 68,22 60,25 Z" fill="var(--accent-green)" />
                    <!-- Soil base line -->
                    <path d="M35,75 L65,75" fill="none" stroke="var(--primary-green)" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </div>
            <h1 class="display-3 fw-bold" style="color: var(--primary-green); font-family: 'Playfair Display'; font-weight: 800;">Join EcoSwap.</h1>
            <p class="fs-5" style="color: #4b6652; max-width: 460px;">Connect with your fellow campus students and barter sustainably.</p>
        </div>
    </div>

    <div class="split-form">
        <a href="index.php" class="back-btn"><i class="bi bi-arrow-left"></i> Home</a>
        
        <div class="w-100" style="max-width: 400px;">
            <div class="text-center mb-4">
                <h2 style="font-family: 'Playfair Display'; color: var(--primary-green); font-size: 2.5rem; font-weight: 700;">Create Account</h2>
                <p class="text-muted">Start swapping today</p>
            </div>

            <form action="register_process.php" method="POST">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                    <label for="username">Username</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                    <label for="email">Email address</label>
                </div>
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">Password</label>
                </div>
                
                <button type="submit" class="btn w-100 py-3 rounded-pill fw-bold text-white shadow-sm" style="background-color: var(--primary-green);">Sign Up</button>
            </form>
            
            <div class="text-center mt-4">
                <p class="text-muted">Already have an account? <a href="login.php" style="color: var(--primary-green); font-weight: bold;">Log in</a></p>
            </div>
        </div>
    </div>
</div>