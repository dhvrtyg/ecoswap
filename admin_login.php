<?php include 'header.php'; ?>
<?php
// We use a simple, hardcoded check for the project. 
// In a real application, you would check a database flag (e.g., is_admin = 1).
define('ADMIN_USER', 'admin');
define('ADMIN_PASSWORD', 'admin123'); // Use a known, simple password for testing

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === ADMIN_USER && $password === ADMIN_PASSWORD) {
        // Successful admin login
        $_SESSION['is_admin'] = true;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error_message = "Invalid Admin Credentials.";
    }
}
?>

<div class="container mt-5">
    <form action="admin_login.php" method="POST" class="w-50 mx-auto p-4 border rounded shadow-sm bg-danger-subtle">
        <h2 class="mb-4 text-center text-danger">Admin Login</h2>
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="mb-3">
            <label for="username" class="form-label">Username:</label>
            <input type="text" id="username" name="username" class="form-control" required>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        
        <button type="submit" class="btn btn-danger w-100">Login as Admin</button>
    </form>
</div>

<?php include 'footer.php'; ?>