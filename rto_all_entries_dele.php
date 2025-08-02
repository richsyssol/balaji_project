<?php
include 'includes/db_conn.php';
include 'session_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = $_POST['selected_ids'] ?? [];
    $password = $_POST['password'] ?? '';

    if (empty($ids)) {
        echo "<script>alert('No records selected.'); window.location.href='rto.php';</script>";
        exit;
    }

    if (empty($password)) {
        echo "<script>alert('Password required.'); window.location.href='rto.php';</script>";
        exit;
    }

    // Verify admin password
    $stmt = $conn->prepare("SELECT password FROM user WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Sanitize IDs and delete
            $ids = array_map('intval', $ids);
            $idList = implode(',', $ids);

            $query = "UPDATE rto_entries SET is_deleted = 1, deleted_at = NOW() WHERE id IN ($idList)";
            if ($conn->query($query)) {
                echo "<script>alert('Selected records deleted successfully.'); window.location.href='rto.php';</script>";
            } else {
                echo "<script>alert('Database error.'); window.location.href='rto.php';</script>";
            }
        } else {
            echo "<script>alert('Incorrect password.'); window.location.href='rto.php';</script>";
        }
    } else {
        echo "<script>alert('Admin user not found.'); window.location.href='rto.php';</script>";
    }
}
?>
