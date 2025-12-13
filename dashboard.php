<?php include 'header.php'; ?>
<?php
// Check if user is logged in, otherwise redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = 'User'; 

// Fetch user's details for the Welcome message
$sql_user = "SELECT username FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$stmt_user->bind_result($fetched_username);
if ($stmt_user->fetch()) {
    $username = $fetched_username;
    // Update session just in case
    $_SESSION['username'] = $fetched_username; 
}
$stmt_user->close();
?>

<div class="container mt-4">

    <?php 
    if (isset($_GET['status']) && $_GET['status'] == 'success') {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Action completed successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
    ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        <a href="add_item.php" class="btn btn-primary">+ List New Item</a>
    </div>

    <ul class="nav nav-tabs mb-4" id="dashboardTabs" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" id="items-tab" data-bs-toggle="tab" href="#items" role="tab">Your Items</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" id="incoming-tab" data-bs-toggle="tab" href="#incoming" role="tab">Incoming Requests</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" id="outgoing-tab" data-bs-toggle="tab" href="#outgoing" role="tab">Your Requests</a>
      </li>
    </ul>

    <div class="tab-content" id="dashboardTabContent">
        
        <div class="tab-pane fade show active" id="items" role="tabpanel">
            <?php
            $sql_items = "SELECT id, name, category, `condition`, image_url, status FROM items WHERE user_id = ? ORDER BY created_at DESC";
            $stmt_items = $conn->prepare($sql_items);
            $stmt_items->bind_param("i", $user_id);
            $stmt_items->execute();
            $result_items = $stmt_items->get_result();

            if ($result_items->num_rows > 0) {
                echo '<div class="row">';
                while($row = $result_items->fetch_assoc()) {
                    echo "<div class='col-md-3 mb-4'>";
                    echo "<div class='card h-100 shadow-sm'>"; // Added shadow-sm for look
                    echo "<img src='" . htmlspecialchars($row["image_url"]) . "' class='card-img-top' style='height: 150px; object-fit: cover;' alt='Item Image'>";
                    echo "<div class='card-body'>";
                    echo "<h6 class='card-title'>" . htmlspecialchars($row["name"]) . "</h6>";
                    
                    // Status Badge Logic
                    $badge_class = ($row['status'] == 'available') ? 'bg-success' : 'bg-secondary';
                    echo "<span class='badge {$badge_class}'>" . ucfirst($row["status"]) . "</span>";
                    
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
                }
                echo '</div>';
            } else {
                echo "<div class='alert alert-light text-center'>You have not posted any items yet.</div>";
            }
            $stmt_items->close();
            ?>
        </div>

        <div class="tab-pane fade" id="incoming" role="tabpanel">
            <?php
            $sql_incoming = "
                SELECT s.id AS swap_id, i1.name AS requested_item, i2.name AS offered_item, s.status, u.username AS requester
                FROM swaps s
                JOIN items i1 ON s.item1_id = i1.id 
                JOIN items i2 ON s.item2_id = i2.id 
                JOIN users u ON s.item2_owner_id = u.id 
                WHERE s.item1_owner_id = ? 
                ORDER BY s.created_at DESC";
            $stmt_incoming = $conn->prepare($sql_incoming);
            $stmt_incoming->bind_param("i", $user_id);
            $stmt_incoming->execute();
            $result_incoming = $stmt_incoming->get_result();

            if ($result_incoming->num_rows > 0) {
                echo '<div class="list-group">';
                while($row = $result_incoming->fetch_assoc()) {
                    echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
                    echo '<div>';
                    echo '<strong>' . htmlspecialchars($row['requester']) . '</strong> offered <em>' . htmlspecialchars($row['offered_item']) . '</em> for your <em>' . htmlspecialchars($row['requested_item']) . '</em>';
                    echo '</div>';
                    
                    echo '<div>';
                    if ($row['status'] == 'pending') {
                        // Pending? Show Accept/Decline
                        echo '<a href="chat.php?swap_id=' . $row['swap_id'] . '" class="btn btn-sm btn-outline-primary me-2">Negotiate</a>';
                        echo '<a href="process_action.php?action=accept&swap_id=' . $row['swap_id'] . '" class="btn btn-sm btn-success me-2">Accept</a>';
                        echo '<a href="process_action.php?action=decline&swap_id=' . $row['swap_id'] . '" class="btn btn-sm btn-danger">Decline</a>';
                    } elseif ($row['status'] == 'accepted') {
                        // Accepted? Show Chat
                        echo '<span class="badge bg-success me-2">Accepted</span>';
                        echo '<a href="chat.php?swap_id=' . $row['swap_id'] . '" class="btn btn-sm btn-primary">Chat</a>';
                    } else {
                        echo '<span class="badge bg-secondary">Declined</span>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo "<div class='alert alert-light text-center'>No incoming requests.</div>";
            }
            $stmt_incoming->close();
            ?>
        </div>

        <div class="tab-pane fade" id="outgoing" role="tabpanel">
            <?php
            $sql_outgoing = "
                SELECT s.id AS swap_id, i1.name AS wanted_item, i2.name AS my_item, s.status, u.username AS owner
                FROM swaps s
                JOIN items i1 ON s.item1_id = i1.id
                JOIN items i2 ON s.item2_id = i2.id
                JOIN users u ON s.item1_owner_id = u.id
                WHERE s.item2_owner_id = ?
                ORDER BY s.created_at DESC";
            $stmt_outgoing = $conn->prepare($sql_outgoing);
            $stmt_outgoing->bind_param("i", $user_id);
            $stmt_outgoing->execute();
            $result_outgoing = $stmt_outgoing->get_result();

            if ($result_outgoing->num_rows > 0) {
                echo '<div class="list-group">';
                while($row = $result_outgoing->fetch_assoc()) {
                    echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
                    echo '<div>';
                    echo 'You offered <em>' . htmlspecialchars($row['my_item']) . '</em> for <strong>' . htmlspecialchars($row['wanted_item']) . '</strong> (Owner: ' . htmlspecialchars($row['owner']) . ')';
                    echo '</div>';

                    echo '<div>';
                    if ($row['status'] == 'accepted') {
                        echo '<span class="badge bg-success me-2">Accepted</span>';
                        echo '<a href="chat.php?swap_id=' . $row['swap_id'] . '" class="btn btn-sm btn-primary">Chat</a>';
                    } elseif ($row['status'] == 'declined') {
                        echo '<span class="badge bg-danger">Declined</span>';
                    } else {
                        echo '<span class="badge bg-warning text-dark me-2">Pending</span>';
                        echo '<a href="chat.php?swap_id=' . $row['swap_id'] . '" class="btn btn-sm btn-outline-secondary">Message Owner</a>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo "<div class='alert alert-light text-center'>You haven't made any requests yet.</div>";
            }
            $stmt_outgoing->close();
            ?>
        </div>
    </div>
</div>

<?php 
// Close connection explicitly at the end
$conn->close();
include 'footer.php'; 
?>