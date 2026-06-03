<?php include 'header.php'; ?>

<style>
    /* Hide the main navbar on login/register for a cleaner look */
    nav.navbar { display: none; }
    
    /* Reset body padding/margin */
    body { padding: 0; margin: 0; overflow: hidden; background: white; }
    
    .split-container {
        display: flex;
        height: 100vh;
        width: 100vw;
    }
    
    /* LEFT SIDE: The Image/Vector */
    .split-image {
        flex: 1; /* Takes up 50-60% of width */
        position: relative;
        display: none; /* Hidden on mobile */
        overflow: hidden;
    }
    
    @media(min-width: 768px) {
        .split-image { display: block; }
    }

    .image-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 50px;
        text-align: center;
    }

    /* RIGHT SIDE: The Form */
    .split-form {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 40px;
        position: relative;
        max-width: 600px; /* Stop it from getting too wide */
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
                <linearGradient id="bg-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#e8ede9" />
                    <stop offset="100%" stop-color="#d3ded5" />
                </linearGradient>
                <style>
                    @keyframes spinSlow {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                    @keyframes floatSlow {
                        0%, 100% { transform: translateY(0px); }
                        50% { transform: translateY(-8px); }
                    }
                    .rotating-gear {
                        animation: spinSlow 40s linear infinite;
                        transform-origin: 50px 50px;
                    }
                    .floating-icon {
                        animation: floatSlow 6s ease-in-out infinite;
                    }
                </style>
            </defs>
            <rect width="100" height="100" fill="url(#bg-grad)" />
            <circle cx="50" cy="50" r="45" fill="none" stroke="#2b4c33" stroke-width="0.2" stroke-dasharray="1 3" class="rotating-gear" />
            <circle cx="50" cy="50" r="35" fill="none" stroke="#4f772d" stroke-width="0.15" stroke-dasharray="2 4" class="rotating-gear" style="animation-direction: reverse; animation-duration: 25s;" />
        </svg>
        <div class="image-overlay" style="z-index: 2;">
            <div class="floating-icon" style="margin-bottom: 2rem; display: flex; justify-content: center;">
                <svg width="140" height="140" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="40" fill="#ffffff" stroke="var(--primary-green)" stroke-width="1.5" />
                    <!-- Swap Arrow 1 -->
                    <path d="M35,45 C35,38 45,35 55,35 L60,35" fill="none" stroke="var(--accent-green)" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M57,31 L63,35 L57,39" fill="none" stroke="var(--accent-green)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <!-- Swap Arrow 2 -->
                    <path d="M65,55 C65,62 55,65 45,65 L40,65" fill="none" stroke="var(--primary-green)" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M43,69 L37,65 L43,61" fill="none" stroke="var(--primary-green)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <!-- Small green leaf -->
                    <path d="M50,44 C46,49 48,54 50,54 C52,54 54,49 50,44 Z" fill="var(--accent-green)" />
                </svg>
            </div>
            <h1 class="display-3 fw-bold" style="color: var(--primary-green); font-family: 'Playfair Display'; font-weight: 800;">Welcome Back.</h1>
            <p class="fs-5" style="color: #4b6652; max-width: 460px;">Continue your journey towards a zero-waste campus lifestyle.</p>
        </div>
    </div>

    <div class="split-form">
        <a href="index.php" class="back-btn"><i class="bi bi-arrow-left"></i> Home</a>
        
        <div class="w-100" style="max-width: 400px;">
            <div class="text-center mb-4">
                <h2 style="font-family: 'Playfair Display'; color: var(--primary-green); font-size: 2.5rem; font-weight: 700;">Log in</h2>
                <p class="text-muted">Enter your username to continue</p>
            </div>

            <form action="login_process.php" method="POST">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                    <label for="username">Username</label>
                </div>
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">Password</label>
                </div>
                
                <button type="submit" class="btn w-100 py-3 rounded-pill fw-bold text-white shadow-sm" style="background-color: var(--primary-green);">Log In</button>
            </form>
            
            <div class="text-center mt-4">
                <p class="text-muted">Don't have an account? <a href="register.php" style="color: var(--primary-green); font-weight: bold;">Sign up</a></p>
            </div>
        </div>
    </div>
</div>