<?php
include 'session_check.php';
include 'includes/db_conn.php';

if (isset($_GET['query'])) {
    $query = $conn->real_escape_string($_GET['query']);
    $sql = "SELECT city FROM cities WHERE city LIKE '$query%' ORDER BY city ASC LIMIT 10";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='suggestion'>" . htmlspecialchars($row['city']) . "</div>";
        }
    } else {
        echo "<div class='suggestion'>No results found</div>";
    }
}
?>
