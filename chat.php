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

// Fetch swap details and item details
$sql_swap = "
    SELECT s.item1_id, s.item2_id, s.status, s.item1_owner_id, s.item2_owner_id,
           i1.name AS item1_name, i2.name AS item2_name,
           i1.image_url AS item1_image, i2.image_url AS item2_image,
           i1.category AS item1_category, i2.category AS item2_category
    FROM swaps s
    JOIN items i1 ON s.item1_id = i1.id
    JOIN items i2 ON s.item2_id = i2.id
    WHERE s.id = ?";
$stmt_swap = $conn->prepare($sql_swap);
$stmt_swap->bind_param("i", $swap_id);
$stmt_swap->execute();
$result_swap = $stmt_swap->get_result();
$swap = $result_swap->fetch_assoc();
$stmt_swap->close();

if (!$swap) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Swap not found.</div></div>";
    include 'footer.php';
    exit();
}

$status = $swap['status'];
$item1_name = htmlspecialchars($swap['item1_name']);
$item2_name = htmlspecialchars($swap['item2_name']);
$item1_image = get_item_image($swap['item1_image'], $swap['item1_category']);
$item2_image = get_item_image($swap['item2_image'], $swap['item2_category']);

// Determine which item belongs to whom
$my_item_name = "";
$their_item_name = "";
$my_item_image = "";
$their_item_image = "";

if ($user_id == $swap['item1_owner_id']) {
    $my_item_name = $item1_name;
    $my_item_image = $item1_image;
    $their_item_name = $item2_name;
    $their_item_image = $item2_image;
} else {
    $my_item_name = $item2_name;
    $my_item_image = $item2_image;
    $their_item_name = $item1_name;
    $their_item_image = $item1_image;
}

// Check if user has already rated this swap
$sql_rated = "SELECT id FROM ratings WHERE swap_id = ? AND reviewer_id = ?";
$stmt_rated = $conn->prepare($sql_rated);
$stmt_rated->bind_param("ii", $swap_id, $user_id);
$stmt_rated->execute();
$stmt_rated->store_result();
$has_rated = ($stmt_rated->num_rows > 0);
$stmt_rated->close();
?>

<style>
    /* Premium Chat Layout overrides */
    .msg-bubble {
        max-width: 75%;
        padding: 10px 14px;
        position: relative;
        font-size: 0.95rem;
        line-height: 1.45;
        box-shadow: 0 1px 2px rgba(0,0,0,0.08);
        margin-bottom: 4px;
    }
    
    .msg-sent {
        background-color: var(--bs-primary) !important;
        color: white !important;
        border-radius: 16px 16px 4px 16px;
    }
    
    .msg-received {
        background-color: white !important;
        color: #212529 !important;
        border-radius: 16px 16px 16px 4px;
        border: 1px solid var(--bs-border-color);
    }
    
    .msg-timestamp {
        font-size: 0.7rem;
        opacity: 0.75;
        margin-top: 4px;
        text-align: right;
    }
    
    .msg-sender-name {
        font-size: 0.78rem;
        font-weight: 600;
        margin-bottom: 2px;
        display: block;
        color: var(--bs-success);
    }
    
    .cursor-pointer {
        cursor: pointer;
    }
    
    /* Responsive viewport adjustments */
    @media (max-width: 767.98px) {
        .custom-footer {
            display: none !important;
        }
        
        body {
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }
        
        .container.mt-4 {
            margin-top: 0 !important;
            padding: 0 !important;
            max-width: 100% !important;
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            height: 100%;
        }
        
        .card.shadow-sm {
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            display: flex;
            flex-direction: column;
            flex: 1;
            height: 100%;
            overflow: hidden;
        }
        
        .card-header {
            border-radius: 0 !important;
            padding: 0.75rem 1rem !important;
        }
        
        #chat-history {
            flex: 1;
            height: 0 !important; /* Force browser to compute scroll container height under flexbox */
            padding: 1rem !important;
        }
        
        .card-footer {
            border-radius: 0 !important;
            padding: 0.75rem 1rem !important;
            background-color: #fff !important;
        }
        
        .msg-bubble {
            max-width: 85%;
        }
    }
</style>

<div class="container mt-4">
    <?php if (isset($_GET['error']) && $_GET['error'] === 'profanity'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Inappropriate Language Blocked!</strong> Your message was not sent because it contained offensive or prohibited words. Please keep the conversation polite and friendly.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Chat with <?php echo htmlspecialchars($recipient_username); ?></h5>
            <a href="dashboard.php" class="btn btn-sm btn-light">Back to Dashboard</a>
        </div>
        
        <!-- Toggle header for swap details on mobile viewports -->
        <div class="d-flex d-md-none justify-content-between align-items-center bg-light border-bottom px-3 py-2 cursor-pointer" data-bs-toggle="collapse" data-bs-target="#swapInfoCollapse" style="user-select: none;">
            <span class="fw-semibold text-dark small">
                <i class="bi bi-info-circle me-1 text-primary"></i> Trade Details & Actions
            </span>
            <span class="badge rounded-pill <?php
                if ($status == 'pending') echo 'bg-warning text-dark';
                elseif ($status == 'accepted') echo 'bg-success';
                elseif ($status == 'completed') echo 'bg-info';
                else echo 'bg-danger';
            ?>">
                <?php echo ucfirst($status); ?>
            </span>
        </div>
        
        <!-- Interactive Swap Status & Info Panel -->
        <div class="collapse d-md-block bg-light border-bottom" id="swapInfoCollapse">
            <div class="p-3">
                <div class="row align-items-center">
                    <!-- Swap items preview -->
                    <div class="col-md-7 d-flex align-items-center gap-3 mb-3 mb-md-0">
                        <div class="text-center">
                            <img src="<?php echo $my_item_image; ?>" alt="<?php echo $my_item_name; ?>" class="rounded border shadow-sm" style="width: 60px; height: 60px; object-fit: cover;">
                            <div class="small fw-semibold mt-1 text-muted text-truncate" style="max-width: 80px;">Your Item</div>
                        </div>
                        <div class="fs-4 text-secondary">
                            <i class="bi bi-arrow-left-right text-success"></i>
                        </div>
                        <div class="text-center">
                            <img src="<?php echo $their_item_image; ?>" alt="<?php echo $their_item_name; ?>" class="rounded border shadow-sm" style="width: 60px; height: 60px; object-fit: cover;">
                            <div class="small fw-semibold mt-1 text-muted text-truncate" style="max-width: 80px;">Their Item</div>
                        </div>
                        <div class="ms-2">
                            <h6 class="mb-1 text-dark">Trading <span class="text-primary"><?php echo $my_item_name; ?></span> for <span class="text-primary"><?php echo $their_item_name; ?></span></h6>
                            <span class="badge rounded-pill <?php
                                if ($status == 'pending') echo 'bg-warning text-dark';
                                elseif ($status == 'accepted') echo 'bg-success';
                                elseif ($status == 'completed') echo 'bg-info';
                                else echo 'bg-danger';
                            ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Swap actions -->
                    <div class="col-md-5 text-md-end">
                        <?php if ($status == 'pending'): ?>
                            <?php if ($user_id == $swap['item1_owner_id']): ?>
                                <!-- Current user is the recipient of the swap proposal -->
                                <div class="d-flex gap-2 justify-content-md-end">
                                    <a href="process_action.php?action=accept&swap_id=<?php echo $swap_id; ?>&redirect=chat" class="btn btn-sm btn-success rounded-pill px-3">Approve Trade</a>
                                    <a href="process_action.php?action=decline&swap_id=<?php echo $swap_id; ?>&redirect=chat" class="btn btn-sm btn-danger rounded-pill px-3">Decline</a>
                                </div>
                            <?php else: ?>
                                <!-- Current user is the initiator -->
                                <span class="text-muted small italic"><i class="bi bi-hourglass-split"></i> Waiting for approval</span>
                            <?php endif; ?>
                        <?php elseif ($status == 'accepted'): ?>
                            <div class="d-flex gap-2 justify-content-md-end align-items-center">
                                <span class="text-muted small me-2 d-none d-lg-inline">Trade Approved!</span>
                                <a href="process_action.php?action=complete&swap_id=<?php echo $swap_id; ?>&redirect=chat" class="btn btn-sm btn-primary rounded-pill px-3">Complete Trade</a>
                            </div>
                        <?php elseif ($status == 'completed'): ?>
                            <span class="text-success small fw-bold"><i class="bi bi-check2-all"></i> Swap Finalized!</span>
                        <?php elseif ($status == 'declined'): ?>
                            <span class="text-danger small"><i class="bi bi-x-circle"></i> Swap Declined</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Inline Rating Form (Shown only if Completed & user has not rated yet) -->
                <?php if ($status == 'completed' && !$has_rated): ?>
                    <div class="mt-3 p-3 bg-white border rounded" style="border-left: 4px solid var(--bs-info) !important;">
                        <h6 class="mb-2 text-dark"><i class="bi bi-star-fill text-warning me-1"></i> Rate your trading partner</h6>
                        <form action="submit_rating.php" method="POST">
                            <input type="hidden" name="swap_id" value="<?php echo $swap_id; ?>">
                            <input type="hidden" name="reviewee_id" value="<?php echo $recipient_id; ?>">
                            <input type="hidden" name="redirect" value="chat">
                            
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <label class="small text-muted mb-0">Your Rating:</label>
                                <div class="rating-stars fs-5">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required style="display:none;">
                                        <label for="star<?php echo $i; ?>" class="star-label cursor-pointer text-secondary" style="font-size: 1.3rem; margin-right: 4px;" onclick="highlightStars(<?php echo $i; ?>)"><i class="bi bi-star"></i></label>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <div class="input-group input-group-sm">
                                <input type="text" name="comment" class="form-control" placeholder="Write a short review (e.g. Friendly and quick swap!)..." required>
                                <button class="btn btn-info text-white" type="submit">Submit Review</button>
                            </div>
                        </form>
                    </div>
                    <script>
                        function highlightStars(rating) {
                            for (let i = 1; i <= 5; i++) {
                                const label = document.querySelector('label[for="star' + i + '"]');
                                if (label) {
                                    const icon = label.querySelector('i');
                                    if (i <= rating) {
                                         icon.className = 'bi bi-star-fill text-warning';
                                    } else {
                                         icon.className = 'bi bi-star text-secondary';
                                    }
                                }
                            }
                        }
                    </script>
                <?php elseif ($status == 'completed' && $has_rated): ?>
                    <div class="mt-2 text-muted small text-center bg-white py-1 border rounded">
                        <i class="bi bi-check-circle-fill text-success"></i> You have submitted a review for this swap.
                    </div>
                <?php endif; ?>
            </div>
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
                    $bubble_class = $is_mine ? 'msg-sent' : 'msg-received';
                    $sender_label = $is_mine ? 'You' : htmlspecialchars($message['username']);

                    echo '<div class="d-flex mb-3 ' . $wrapper_class . '">';
                    echo '<div class="msg-bubble ' . $bubble_class . '">';
                    if (!$is_mine) {
                        echo '<span class="msg-sender-name">' . $sender_label . '</span>';
                    }
                    echo htmlspecialchars($message['message_text']);
                    echo '<div class="msg-timestamp">' . date('H:i', strtotime($message['sent_at'])) . '</div>';
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