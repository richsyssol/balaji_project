<?php
include 'include/header.php'; 
include 'include/head.php'; 
include 'session_check.php';
include 'includes/db_conn.php';

$search = '';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $search = trim($_POST['search'] ?? '');
}
?>

<!-- HTML Content for displaying letters in cards -->
<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container p-5">
        <div class="ps-5">
            <h1>LETTER</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">LETTER</li>
                </ol>
            </nav>
        </div>

        <div class="bg-white con-tbl p-5">

        <!-- Search Form -->
                <form method="POST" class="d-flex field">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search by subject, sender or recipient" value="<?= htmlspecialchars($search) ?>" />
                    <button type="submit" class="btn sub-btn1">Search</button>
                </form>

            <!-- Add Task Button -->
            <div class="d-flex justify-content-between align-items-center my-4">
                <a href="letter_envelope_generator.php" class="btn sub-btn1">Add Letter</a>
            </div>

            <div class="row pt-3">
                <?php
                if (!empty($search)) {
                    $stmt = $conn->prepare("SELECT * FROM letters WHERE subject LIKE ? OR sender_name LIKE ? OR recipient_name LIKE ? ORDER BY id DESC");
                    $likeSearch = "%$search%";
                    $stmt->bind_param("sss", $likeSearch, $likeSearch, $likeSearch);
                    $stmt->execute();
                    $letters = $stmt->get_result();
                } else {
                    $letters = $conn->query("SELECT * FROM letters ORDER BY id DESC");
                }

                if ($letters->num_rows > 0):
                    while ($letter = $letters->fetch_assoc()):
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($letter['subject']) ?></h5>
                            <?php if (!empty($letter['sender_name'])): ?>
                                <p><strong>Sender:</strong> <?= htmlspecialchars($letter['sender_name']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($letter['recipient_name'])): ?>
                                <p><strong>Recipient:</strong> <?= htmlspecialchars($letter['recipient_name']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($letter['message'])): ?>
                                <p><strong>Message:</strong> <?= nl2br(htmlspecialchars($letter['message'])) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <a href="letter_envelope_generator.php?action=edit&id=<?= $letter['id'] ?>" class="btn sub-btn1">Edit</a>
                            <a href="view_letter.php?id=<?= $letter['id'] ?>" class="btn sub-btn1">View Letter</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; else: ?>
                    <p class="text-center">No letters found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'include/footer.php'; ?>
