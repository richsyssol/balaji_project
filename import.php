<?php
include 'session_check.php';
include 'includes/db_conn.php';

// Check if the form was submitted and file was uploaded
if (isset($_POST['submit']) && isset($_FILES['csvFile']) && $_FILES['csvFile']['error'] == 0) {
    // Get the uploaded file
    $csvFile = $_FILES['csvFile']['tmp_name'];

    // Open the CSV file
    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        // Skip the header row (if any)
        fgetcsv($handle);

        // Fetch the last reg_num from the database to increment it
        $lastRegNumResult = $conn->query("SELECT `reg_num` FROM `demo` ORDER BY `reg_num` DESC LIMIT 1");
        $lastRegNumRow = $lastRegNumResult->fetch_assoc();
        $lastRegNum = $lastRegNumRow ? $lastRegNumRow['reg_num'] : 0;

        // Loop through the rows in the CSV file
        while (($row = fgetcsv($handle)) !== FALSE) {
            // Assuming the CSV columns are in this order:
            // date, time, client_name, contact, client_type, contact_alt, email, address
            $originalDate = $row[0]; // date
            $formattedDate = date("Y-m-d", strtotime($originalDate)); // Convert to MySQL date format

            $time = $row[1]; // time
            $clientName = $row[2]; // client_name
            $contact = $row[3]; // contact
            $clientType = $row[4]; // client_type
            $contactAlt = $row[5]; // contact_alt
            $email = $row[6]; // email
            $address = $row[7]; // address

            // Handle other columns (for example, reg_num, birth_date, gst_no, etc.)
            $regNum = ++$lastRegNum; // Increment the reg_num
            $birthDate = NULL; // Assuming no birth_date in the CSV, set it as NULL
            $gstNo = NULL; // Assuming no gst_no in the CSV, set it as NULL
            $panNo = NULL; // Assuming no pan_no in the CSV, set it as NULL
            $aadharNo = NULL; // Assuming no aadhar_no in the CSV, set it as NULL
            $inquiry = NULL; // Assuming no inquiry in the CSV, set it as NULL
            $reference = NULL; // Assuming no reference in the CSV, set it as NULL
            $creationOn = date("Y-m-d H:i:s"); // Current timestamp for creation_on
            $isDeleted = 0; // Default value for is_deleted
            $deletedAt = NULL; // Default value for deleted_at

            // Prepare the SQL statement for inserting the data
            $sql = "INSERT INTO demo (`reg_num`, `date`, `time`, `client_name`, `client_type`, `address`, `contact`, `contact_alt`, `email`, `birth_date`, `gst_no`, `pan_no`, `aadhar_no`, `inquiry`, `reference`, `creation_on`, `is_deleted`, `deleted_at`) 
                    VALUES ('$regNum', '$formattedDate', '$time', '$clientName', '$clientType', '$address', '$contact', '$contactAlt', '$email', '$birthDate', '$gstNo', '$panNo', '$aadharNo', '$inquiry', '$reference', '$creationOn', '$isDeleted', '$deletedAt')";

            if ($conn->query($sql) !== TRUE) {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }

        // Close the file after processing
        fclose($handle);

        // Provide a success message
        echo "Data successfully imported from CSV!";
    } else {
        echo "Error opening the CSV file.";
    }
} else {
    echo "Please upload a valid CSV file.";
}

// Close the database connection
$conn->close();
?>




<h2>Upload CSV File to Import Data</h2>

    <!-- Form for uploading CSV -->
    <form action="import.php" method="post" enctype="multipart/form-data">
        <label for="csvFile">Choose CSV File:</label>
        <input type="file" name="csvFile" id="csvFile" accept=".csv" required>
        <br><br>
        <button type="submit" name="submit" value="Upload">Upload & Import</button>
    </form>
