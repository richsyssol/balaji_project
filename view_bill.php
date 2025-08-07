<?php
// Database connection
include 'includes/db_conn.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['id'])) {
    die("Bill ID not provided");
}

$bill_id = $conn->real_escape_string($_GET['id']);
$query = $conn->query("
    SELECT b.bill_image, c.client_name 
    FROM bills b
    JOIN client c ON b.client_id = c.id
    WHERE b.id = '$bill_id'
");

// Check if query failed
if ($query === false) {
    die("Query failed: " . $conn->error);
}

if ($query->num_rows === 0) {
    die("Bill not found");
}

$bill = $query->fetch_assoc();

// Output as plain text
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Bill - <?php echo htmlspecialchars($bill['client_name']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        pre { white-space: pre-wrap; background: #f5f5f5; padding: 15px; border-radius: 5px; }
        button { padding: 8px 15px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #45a049; }
    </style>
</head>
<body>
    <h2>Bill for <?php echo htmlspecialchars($bill['client_name']); ?></h2>
    <pre><?php echo htmlspecialchars($bill['bill_image']); ?></pre>
    <button onclick="window.print()">Print Bill</button>
    
    <div style="margin-top: 20px; color: #666;">
        <p>If you see this message, the system is working in text mode.</p>
        <p>In a production environment, you would generate an actual image using PHP's GD library.</p>
    </div>
</body>
</html>