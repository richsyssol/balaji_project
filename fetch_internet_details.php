<?php
include 'session_check.php';
include 'includes/db_conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["internet"])) {
    $internet = $conn->real_escape_string($_POST["internet"]);

    $query = "SELECT consumer_number, reference FROM internet_details WHERE internet = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("s", $internet);
        $stmt->execute();
        $stmt->bind_result($consumer_number, $reference);

        if ($stmt->fetch()) {
            // Send response as JSON
            echo json_encode([
                "consumer_number" => $consumer_number,
                "reference" => $reference
            ]);
        } else {
            echo json_encode([
                "consumer_number" => "",
                "reference" => ""
            ]);
        }

        $stmt->close();
    } else {
        echo json_encode([
            "consumer_number" => "",
            "reference" => ""
        ]);
    }

    $conn->close();
}
?>
