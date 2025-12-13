<?php include 'header.php'; ?>

<style>
    nav.navbar { display: none; }
    body { padding: 0; margin: 0; overflow: hidden; background: white; }
    .split-container { display: flex; height: 100vh; width: 100vw; }
    .split-image {
        flex: 1;
        /* Different image for Register page */
        background: url('https://images.unsplash.com/photo-1542601906990-b4d3fb7d5c73?q=80&w=2070&auto=format&fit=crop') center center/cover no-repeat;
        position: relative;
        display: none;
    }
    @media(min-width: 768px) { .split-image { display: block; } }
    .image-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(58, 90, 64, 0.4);
        display: flex; flex-direction: column; justify-content: center; padding: 50px; color: white;
    }
    .split-form {
        flex: 1; display: flex; flex-direction: column; justify-content: center;
        align-items: center; padding: 40px; position: relative; max-width: 600px;
        background-color: #faf9f6;
    }
    .back-btn { position: absolute; top: 30px; left: 30px; text-decoration: none; color: #3a5a40; font-weight: bold; }
</style>

<div class="split-container">
    <div class="split-image">
        <div class="image-overlay">
            <h1 class="display-3 fw-bold">Join EcoSwap.</h1>
            <p class="fs-4">Connect with students and trade sustainable goods.</p>
        </div>
    </div>

    <div class="split-form">
        <a href="index.php" class="back-btn"><i class="bi bi-arrow-left"></i> Home</a>
        
        <div class="w-100" style="max-width: 400px;">
            <div class="text-center mb-4">
                <h2 style="font-family: 'Playfair Display'; color: #3a5a40; font-size: 2.5rem;">Create Account</h2>
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
                
                <button type="submit" class="btn w-100 py-3 rounded-pill fw-bold text-white shadow-sm" style="background-color: #3a5a40;">Sign Up</button>
            </form>
            
            <div class="text-center mt-4">
                <p class="text-muted">Already have an account? <a href="login.php" style="color: #3a5a40; font-weight: bold;">Log in</a></p>
            </div>
        </div>
    </div>
</div>