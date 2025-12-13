<footer class="custom-footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4 text-center text-md-start">
                <div class="footer-logo">eco<span style="font-style: italic;">\</span>wap</div>
                <p class="small mt-2">A student-led initiative to reduce waste and save money through bartering.</p>
            </div>

            <div class="col-6 col-md-4 mb-4">
                <h6 class="fw-bold mb-3">Quick Links</h6>
                <a href="index.php" class="footer-link">Home</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="footer-link">My Dashboard</a>
                    <a href="add_item.php" class="footer-link">List an Item</a>
                    <a href="logout.php" class="footer-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="footer-link">Login</a>
                    <a href="register.php" class="footer-link">Register</a>
                <?php endif; ?>
            </div>

            <div class="col-6 col-md-4 mb-4">
                <h6 class="fw-bold mb-3">Project Info</h6>
                <p class="small text-muted">
                    Built for College Project<br>
                    <strong>Subject:</strong> Web Development<br>
                    <strong>Status:</strong> Active Beta
                </p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>