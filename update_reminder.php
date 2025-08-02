<?php
include 'includes/db_conn.php';

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

if (!$id || !in_array($action, ['snooze', 'dismiss'])) {
    die(json_encode(["error" => "Invalid request"]));
}

if ($action == 'snooze') {
    $datetime = $_GET['datetime'] ?? '';

    if (!$datetime) {
        die(json_encode(["error" => "No datetime provided"]));
    }

    // Convert input to proper format
    $new_time = date('Y-m-d H:i:s', strtotime($datetime));

    $sql = "UPDATE expenses_reminders SET snooze_until = ?, reminder_status = 'snoozed' WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die(json_encode(["error" => $conn->error]));
    }

    $stmt->bind_param("si", $new_time, $id);
} else {
    $sql = "UPDATE expenses_reminders SET reminder_status = 'dismissed' WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die(json_encode(["error" => $conn->error]));
    }

    $stmt->bind_param("i", $id);
}

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Database error"]);
}
?>
