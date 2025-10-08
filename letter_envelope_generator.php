<?php
include 'include/header.php'; 
include 'include/head.php'; 
include 'session_check.php';
include 'includes/db_conn.php';

// CSRF Token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables
$sender_name = '';
$sender_address = '';
$recipient_name = '';
$recipient_address = '';
$subject = '';
$message = '';
$referance = '';
$errors = [];
$is_edit = false;
$id = null;

// Check if it's an edit operation
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $is_edit = true;

    $stmt = $conn->prepare("SELECT * FROM letters WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $sender_name = $row['sender_name'];
        $sender_address = $row['sender_address'];
        $recipient_name = $row['recipient_name'];
        $recipient_address = $row['recipient_address'];
        $subject = $row['subject'];
        $message = $row['message'];
        $referance = $row['referance'];
    } else {
        die("Letter not found.");
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    $sender_name = trim($_POST['sender_name']);
    $sender_address = trim($_POST['sender_address']);
    $recipient_name = trim($_POST['recipient_name']);
    $recipient_address = trim($_POST['recipient_address']);
    $subject = trim($_POST['subject']);
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $referance = isset($_POST['referance']) ? trim($_POST['referance']) : '';

    // Validation - Allow all characters including special characters, numbers, and text
    // Minimum 1 character required, allow everything except potentially dangerous characters for your context
    $pattern = "/^[\s\S]{1,}$/"; // Allows any character including newlines, at least 1 character
    
    if (empty($sender_name) || !preg_match($pattern, $sender_name)) $errors[] = "Sender name is required";
    if (empty($sender_address) || !preg_match($pattern, $sender_address)) $errors[] = "Sender address is required";
    if (empty($recipient_name) || !preg_match($pattern, $recipient_name)) $errors[] = "Recipient name is required";
    if (empty($recipient_address) || !preg_match($pattern, $recipient_address)) $errors[] = "Recipient address is required";
    if (empty($subject) || !preg_match($pattern, $subject)) $errors[] = "Subject is required";
    if (empty($message) || !preg_match($pattern, $message)) $errors[] = "Message is required";
    if (empty($referance) || !preg_match($pattern, $referance)) $errors[] = "Reference is required";

    // Process if no errors
    if (empty($errors)) {
        if ($is_edit) {
            // Use prepared statements for update
            $stmt = $conn->prepare("UPDATE letters SET 
                        sender_name = ?, 
                        sender_address = ?, 
                        recipient_name = ?,
                        recipient_address = ?, 
                        subject = ?, 
                        message = ?, 
                        referance = ?
                    WHERE id = ?");
            $stmt->bind_param("sssssssi", 
                $sender_name, $sender_address, $recipient_name, 
                $recipient_address, $subject, $message, 
                $referance, $id
            );
            
            if ($stmt->execute()) {
                header("Location: letter");
                exit();
            } else {
                echo "Error updating record: " . $stmt->error;
            }
            $stmt->close();
        } else {
            // Use prepared statements for insert
            $stmt = $conn->prepare("INSERT INTO letters 
                        (sender_name, sender_address, recipient_name, recipient_address, subject, message, referance) 
                    VALUES 
                        (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", 
                $sender_name, $sender_address, $recipient_name, 
                $recipient_address, $subject, $message, 
                $referance
            );
            
            if ($stmt->execute()) {
                header("Location: letter");
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>


<!-- HTML SECTION -->
<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container p-5">
        <div class="ps-5">
            <h1>LETTER GENRATION</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="letter">LETTER</a></li>
                    <li class="breadcrumb-item active" aria-current="page">LETTER GENRATION</li>
                </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
            <h2 class="text-center">Create Letter & Envelope PDF</h2>

            <form 
                action="letter_envelope_generator.php<?php 
                    if ($is_edit) echo '?action=edit&id=' . $id; 
                    // elseif ($add_client) echo '?action=add_client&id=' . $id; 
                ?>" 
                method="POST" class="p-5 shadow bg-white">

                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?= $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <h5>From : </h5>
                        <div class="mb-3 field">
                            <label class="form-label">Name</label>
                            <input type="text" name="sender_name" class="form-control" value="<?= htmlspecialchars($sender_name); ?>" required>
                        </div>
                        <div class="mb-3 field">
                            <label class="form-label">Address</label>
                            <textarea name="sender_address" class="form-control" rows="3" required><?= htmlspecialchars($sender_address); ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>To :</h5>
                        <div class="mb-3 field">
                            <label class="form-label">Name</label>
                            <input type="text" name="recipient_name" class="form-control" value="<?= htmlspecialchars($recipient_name); ?>" required>
                        </div>
                        <div class="mb-3 field">
                            <label class="form-label">Address</label>
                            <textarea name="recipient_address" class="form-control" rows="3" required><?= htmlspecialchars($recipient_address); ?></textarea>
                        </div>
                    </div>
                </div>

                <h5>Letter Details :</h5>
                <div class="mb-3 field">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($subject); ?>" required>
                </div>
                <div class="mb-3 field">
                    <label class="form-label">Referance</label>
                    <input type="text" name="referance" class="form-control" value="<?= htmlspecialchars($referance); ?>">
                </div>
                <div class="mb-3 field">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-control" rows="4" required><?= htmlspecialchars($message); ?></textarea>
                </div>

                <input type="submit" class="btn sub-btn" value="<?= $is_edit ? 'Update Entry' : 'Add Entry'; ?>">
            </form>

            
        </div>
    </div>
    
</section>
