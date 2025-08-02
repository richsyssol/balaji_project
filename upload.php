<?php
include 'includes/db_conn.php';
include 'session_check.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
        $csvFile = fopen($_FILES['csv_file']['tmp_name'], 'r');

        // Skip the first row (CSV headers)
        fgetcsv($csvFile);

        // Prepare SQL statement for only the necessary columns
        $stmt = $conn->prepare("
            INSERT INTO client (reg_num ,date, client_name, address, contact, birth_date, age)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        // Check if the prepared statement is valid
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        // Bind parameters
        $stmt->bind_param("ssssssi",$reg_num, $date, $client_name, $address, $contact, $birth_date, $age);

        $count = 1;
        // Read and insert data row by row
        while (($row = fgetcsv($csvFile)) !== false) {
            // Map CSV columns to variables
            $reg_num = $count++;
            $date = date('Y-m-d', strtotime($row[0])); // Convert date format if needed
            $client_name = $row[1];
            $address = $row[2];
            $contact = $row[3];
            $birth_date = date('Y-m-d', strtotime($row[4])); // Convert birth_date format
            $age = (int)$row[5];

            // Execute the statement
            $stmt->execute();
        }

        fclose($csvFile);
        $stmt->close();

        echo "CSV data has been successfully imported.";
    } else {
        echo "Error uploading CSV file.";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload CSV</title>
</head>
<body>
    <h1>Upload CSV File</h1>
    <form action="" method="post" enctype="multipart/form-data">
        <label for="csv_file">Choose CSV File:</label>
        <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
        <button type="submit">Upload and Import</button>
    </form>
</body>
</html>
