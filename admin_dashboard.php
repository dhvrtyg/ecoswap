<?php include 'header.php'; ?>
<?php
// Ensure only admins can access this page
if (!isset($_SESSION['is_admin'])) {
    header("Location: admin_login.php");
    exit();
}
?>

<h2 class="mb-4 text-danger">Admin Dashboard</h2>

<ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="users-tab" data-bs-toggle="tab" href="#users" role="tab">Manage Users</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="items-tab" data-bs-toggle="tab" href="#items" role="tab">Manage Items</a>
  </li>
</ul>

<div class="tab-content">
    
    <div class="tab-pane fade show active" id="users" role="tabpanel">
        <h4 class="mb-3">All Registered Users</h4>
        <table class="table table-bordered">
            <thead>
                <tr><th>ID</th><th>Username</th><th>Email</th><th>Registered On</th></tr>
            </thead>
            <tbody>
                <?php
                $sql_users = "SELECT id, username, email, created_at FROM users ORDER BY id DESC";
                $result_users = $conn->query($sql_users);
                while($row = $result_users->fetch_assoc()):
                ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="tab-pane fade" id="items" role="tabpanel">
        <h4 class="mb-3">All Posted Items</h4>
        <table class="table table-bordered">
            <thead>
                <tr><th>ID</th><th>Name</th><th>Owner ID</th><th>Category</th><th>Status</th><th>Image</th></tr>
            </thead>
            <tbody>
                <?php
                $sql_items = "SELECT id, name, user_id, category, status, image_url FROM items ORDER BY id DESC";
                $result_items = $conn->query($sql_items);
                while($row = $result_items->fetch_assoc()):
                ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td><span class="badge bg-<?php echo ($row['status'] == 'swapped' ? 'warning' : 'success'); ?>"><?php echo ucfirst($row['status']); ?></span></td>
                        <td><a href="<?php echo htmlspecialchars($row['image_url']); ?>" target="_blank">View</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
$conn->close();
include 'footer.php'; 
?>