<?php include 'header.php'; ?>
<?php
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$swap_id = isset($_GET['swap_id']) ? intval($_GET['swap_id']) : 0;

if ($swap_id <= 0) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Invalid swap selected.</div></div>";
    include 'footer.php';
    exit();
}

// 1. Verify user is part of this swap (Security Check)
$sql_check = "SELECT item1_owner_id, item2_owner_id FROM swaps WHERE id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $swap_id);
$stmt_check->execute();
$stmt_check->bind_result($owner1, $owner2);
if (!$stmt_check->fetch()) {
    $stmt_check->close();
    echo "<div class='container mt-5'><div class='alert alert-danger'>Swap not found.</div></div>";
    include 'footer.php';
    exit();
}
$stmt_check->close();

if ($user_id != $owner1 && $user_id != $owner2) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Access Denied.</div></div>";
    include 'footer.php';
    exit();
}

// Determine recipient
$recipient_id = ($user_id == $owner1) ? $owner2 : $owner1;

// Get recipient username for the header
$sql_recipient = "SELECT username FROM users WHERE id = ?";
$stmt_recipient = $conn->prepare($sql_recipient);
$stmt_recipient->bind_param("i", $recipient_id);
$stmt_recipient->execute();
$stmt_recipient->bind_result($recipient_username);
$stmt_recipient->fetch();
$stmt_recipient->close();
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Chat with <?php echo htmlspecialchars($recipient_username); ?></h5>
            <a href="dashboard.php" class="btn btn-sm btn-light">Back to Dashboard</a>
        </div>
        
        <div class="card-body overflow-auto" id="chat-history" style="height: 400px; background-color: #f8f9fa;">
            <?php
            $sql_messages = "
                SELECT m.message_text, m.sent_at, m.sender_id, u.username 
                FROM messages m
                JOIN users u ON m.sender_id = u.id
                WHERE m.swap_id = ? 
                ORDER BY m.sent_at ASC";
            
            $stmt_messages = $conn->prepare($sql_messages);
            $stmt_messages->bind_param("i", $swap_id);
            $stmt_messages->execute();
            $result_messages = $stmt_messages->get_result();

            if ($result_messages->num_rows > 0) {
                while($message = $result_messages->fetch_assoc()) {
                    // Check ownership by ID, not Name (Much safer!)
                    $is_mine = ($message['sender_id'] == $user_id);
                    
                    $wrapper_class = $is_mine ? 'justify-content-end' : 'justify-content-start';
                    $bubble_class = $is_mine ? 'bg-primary text-white' : 'bg-white border text-dark';
                    $sender_label = $is_mine ? 'You' : htmlspecialchars($message['username']);

                    echo '<div class="d-flex mb-3 ' . $wrapper_class . '">';
                    echo '<div class="p-3 rounded shadow-sm ' . $bubble_class . '" style="max-width: 75%;">';
                    echo '<strong>' . $sender_label . ':</strong> ' . htmlspecialchars($message['message_text']);
                    echo '<div class="text-end mt-1" style="font-size: 0.75rem; opacity: 0.8;">' . date('H:i', strtotime($message['sent_at'])) . '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p class="text-center text-muted mt-5">No messages yet. Start the negotiation!</p>';
            }
            $stmt_messages->close();
            ?>
        </div>

        <div class="card-footer">
            <form action="send_message.php" method="POST" class="d-flex gap-2">
                <input type="hidden" name="swap_id" value="<?php echo $swap_id; ?>">
                <input type="hidden" name="recipient_id" value="<?php echo $recipient_id; ?>">
                <input type="text" name="message_text" class="form-control" placeholder="Type your message..." required autocomplete="off">
                <button class="btn btn-primary" type="submit">Send</button>
            </form>
        </div>
    </div>
</div>

<script>
    // This makes the chat scroll to the bottom automatically on load
    var chatBox = document.getElementById("chat-history");
    chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php include 'footer.php'; ?>