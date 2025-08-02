<?php 

    include 'include/header.php'; 
    include 'include/head.php'; 
    include 'session_check.php';

?>



<?php
// Database connection settings
include 'includes/db_conn.php';


// Function to fetch birthday data
function fetchBirthdays($startDate = null, $endDate = null)
    {
        global $conn;
        $query = "";

        // If no date range is provided, fetch today's birthdays
        if (!$startDate && !$endDate) {
            $query = "SELECT client_name, contact, birth_date, address FROM client WHERE 
                    MONTH(birth_date) = MONTH(CURRENT_DATE) AND 
                    DAY(birth_date) = DAY(CURRENT_DATE)";
        } else {
            $startDateFormatted = date('m-d', strtotime($startDate));
            $endDateFormatted = date('m-d', strtotime($endDate));

        

            // Handle date range
            if ($startDateFormatted <= $endDateFormatted) {
                $query = "SELECT client_name, contact, birth_date, address FROM client WHERE 
                        DATE_FORMAT(birth_date, '%m-%d') BETWEEN '$startDateFormatted' AND '$endDateFormatted'
                        ORDER BY DATE_FORMAT(birth_date, '%m-%d') ASC";
            } else {
                $query = "SELECT client_name, contact, birth_date, address FROM client WHERE 
                        (DATE_FORMAT(birth_date, '%m-%d') BETWEEN '$startDateFormatted' AND '12-31')
                        OR 
                        (DATE_FORMAT(birth_date, '%m-%d') BETWEEN '01-01' AND '$endDateFormatted')
                        ORDER BY DATE_FORMAT(birth_date, '%m-%d') ASC";
            }
        }

        return mysqli_query($conn, $query);
    }
 
   
// Function to send SMS
function sendBirthdayWishes($contact, $client_name, $birthDate)
{
    $username = 'Balajimotor@999'; 
    $password = 'Balajimotor@999'; 
    $senderId = 'BMDSCH';

    // Determine if today is the day after birthday (belated)
    $isBelated = false;

    if ($birthDate && $birthDate !== '0000-00-00') {
        $today = new DateTime();
        $birth = new DateTime($birthDate);
        $birth->setDate($today->format('Y'), $birth->format('m'), $birth->format('d')); // Set to this year
    
        // Calculate the difference in days
        $interval = $today->diff($birth)->days;
    
        // Check if birthday was in the last 5 days (and not today or future)
        if ($birth < $today && $interval <= 5) {
            $isBelated = true;
        }
    }

    // Marathi message
    if ($isBelated) {
        $message = "आपणास वाढदिवसाच्या हार्दिक शुभेच्छा..! आपल्या उज्वल भविष्यासाठी आजच SIP ची सुरुवात करा, SIP सुरू करण्यासाठी संपर्क 9277656565 उज्ज्वल / भाऊराव पिंगळे बालाजी मोटर ड्रायव्हिंग स्कूल ,पिंपळगाव बसवंत. 9881063639 #MF investment Subject to Market Risk.";
    } else {
        $message = "आपणास वाढदिवसाच्या हार्दिक शुभेच्छा..! आपल्या उज्वल भविष्यासाठी आजच SIP ची सुरुवात करा, SIP सुरू करण्यासाठी संपर्क 9277656565 उज्ज्वल / भाऊराव पिंगळे बालाजी मोटर ड्रायव्हिंग स्कूल ,पिंपळगाव बसवंत. 9881063639 #MF investment Subject to Market Risk.";
    }

    $message = urlencode($message);

    $url = "https://www.smsjust.com/sms/user/urlsms.php?username=$username&pass=$password&senderid=$senderId&dest_mobileno=$contact&msgtype=UNI&message=$message&response=Y";

    // Use cURL to send the request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return "cURL Error: $error";
    }

    curl_close($ch);
    return $response;
}


// Function to send WhatsApp messages By Updated API
function sendWhatsAppMessage($contact, $client_name) {
    // API Configuration
    $apiUrl = 'https://partners.pinbot.ai/v1/messages';
    $apiKey = 'fbae4610-2023-11f0-ad4f-92672d2d0c2d'; // Your Pinbot API Key
    $phoneNumberId = '919422246469'; // e.g., "919594515799"

    // JSON Payload (as per API docs)
    $data = [
        "messaging_product" => "whatsapp",
        "recipient_type" => "individual",
        "to" => $contact, // e.g., "919594515799"
        "type" => "template",
        "template" => [
            "language" => ["code" => "mr"], // Marathi
            "name" => "birthdaymessage", // Your approved template name
            "components" => [
                [
                    "type" => "body",
                    "parameters" => [
                        ["type" => "text", "text" => $client_name] // Dynamic name
                    ]
                ]
            ]
        ]
    ];

    // cURL Setup
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data), // Encode data as JSON
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json', // Must be JSON
            'apikey:' . $apiKey,
            'wanumber:' . $phoneNumberId
        ]
    ]);

    // Execute & Handle Response
    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        $error = curl_error($curl);
        curl_close($curl);
        return "Error: $error";
    }
    curl_close($curl);
    return $response;
}


// Handle form submission
$birthdays = [];
$message = "";

// Function to split the array into chunks of a given size
function chunkArray($array, $chunkSize) {
    return array_chunk($array, $chunkSize);
}


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $startDate = $_POST['start_date'] ?? null;
    $endDate = $_POST['end_date'] ?? null;

    if (isset($_POST['submit'])) {
        $message = "आपणास वाढदिवसाच्या हार्दिक शुभेच्छा..! आपल्या उज्वल भविष्यासाठी आजच SIP ची सुरुवात करा, SIP सुरू करण्यासाठी संपर्क 9277656565 उज्ज्वल / भाऊराव पिंगळे बालाजी मोटर ड्रायव्हिंग स्कूल ,पिंपळगाव बसवंत. 9881063639 #MF investment Subject to Market Risk.";

        $result = fetchBirthdays($startDate, $endDate);
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $birthdays[] = $row;
            }
        } else {
            $message = "No birthdays found for the selected date range.";
        }
    }

    if ($action === 'send_message' || $action === 'send_whatsapp') {
        $selectedClients = $_POST['selected_clients'] ?? [];
        $clientNames = $_POST['client_names'] ?? [];
        $updatedContacts = $_POST['updated_contacts'] ?? [];
        $customContact = $_POST['custom_contact'] ?? '';
        $birthDates = $_POST['birth_dates'] ?? [];

        $message = "";

        // Custom contact logic
        if (!empty($customContact)) {
            $clientName = "Custom Client";
            $birthDate = date('Y-m-d'); // default today
            $response = ($action === 'send_message') 
                ? sendBirthdayWishes($customContact, $clientName, $birthDate) 
                : sendWhatsAppMessage($customContact, $clientName);

            $message .= "Message sent to $clientName ($customContact): $response<br>";
        }

        // Process selected clients in batches
        if (!empty($selectedClients)) {
            $chunks = chunkArray($selectedClients, 50);

            foreach ($chunks as $chunk) {
                foreach ($chunk as $originalContact) {
                    $newContact = $updatedContacts[$originalContact] ?? $originalContact;
                    $clientName = $clientNames[$originalContact] ?? 'Unknown';
                    $birthDate = $birthDates[$originalContact] ?? date('Y-m-d');

                    $response = ($action === 'send_message') 
                        ? sendBirthdayWishes($newContact, $clientName, $birthDate) 
                        : sendWhatsAppMessage($newContact, $clientName);

                    $message .= "Message sent to $clientName ($newContact): $response<br>";
                }
            }
        }

        if (empty($message)) {
            $message = "No clients selected.";
        }

        $_SESSION['message'] = $message;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
else {

    // show message on screen
    $message = "आपणास वाढदिवसाच्या हार्दिक शुभेच्छा..! आपल्या उज्वल भविष्यासाठी आजच SIP ची सुरुवात करा, SIP सुरू करण्यासाठी संपर्क 9277656565 उज्ज्वल / भाऊराव पिंगळे बालाजी मोटर ड्रायव्हिंग स्कूल ,पिंपळगाव बसवंत. 9881063639 #MF investment Subject to Market Risk.";

    // Default: Fetch today's birthdays
    $result = fetchBirthdays();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $birthdays[] = $row;
        }
    }
}

if (isset($_POST['generate_csv'])) {
    $admin_password = $_POST['admin_password'] ?? '';

    // Fetch stored hashed password
    $sql = "SELECT password FROM file WHERE file_type = 'CSV' LIMIT 1"; 
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['password'];

        if (password_verify($admin_password, $hashed_password)) {
            ob_end_clean(); // Clear output buffer
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=today_birthdays.csv');

            $output = fopen('php://output', 'w');
            fputcsv($output, ['Reg Num', 'Client Name', 'Contact', 'DOB', 'Age', 'Address']);

            // **Fetch only today's birthdays**
            $sql = "SELECT * FROM client WHERE MONTH(birth_date) = MONTH(CURDATE()) AND DAY(birth_date) = DAY(CURDATE())";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                fputcsv($output, [
                    $row['reg_num'],
                    $row['client_name'],
                    $row['contact'],
                    date('d/m/Y', strtotime($row['birth_date'])),
                    $row['age'],
                    $row['address']
                ]);
            }

            fclose($output);
            exit();
        } else {
            echo "<script>alert('Incorrect password!'); window.history.back();</script>";
            exit();
        }
    } else {
        echo "<script>alert('Admin password not set! Contact support.'); window.history.back();</script>";
        exit();
    }
}







     // If user clicks on 'Generate PDF'
    if (isset($_POST['generate_pdf'])) {
        $admin_password = $_POST['admin_password'] ?? '';
    
        $sql = "SELECT password FROM file WHERE file_type = 'PDF' LIMIT 1"; 
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $hashed_password = $row['password'];
    
            if (password_verify($admin_password, $hashed_password)) {
                ob_end_clean(); // Clear output buffer
    
                require('fpdf/fpdf.php');
                $pdf = new FPDF('L', 'mm', 'A3');
                $pdf->SetMargins(10, 10, 10);
                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 16);
                $pdf->Cell(0, 10, 'Today\'s Birthday Report', 0, 1, 'C');
    
                // Column Headers
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(20, 10, 'Reg Num', 1);
                $pdf->Cell(100, 10, 'Client Name', 1);
                $pdf->Cell(70, 10, 'Contact', 1);
                $pdf->Cell(30, 10, 'DOB', 1);
                $pdf->Cell(30, 10, 'Age', 1);
                $pdf->Cell(50, 10, 'Address', 1);
                $pdf->Ln();
    
                // **Fetch only today's birthdays**
                $sql = "SELECT * FROM client WHERE MONTH(birth_date) = MONTH(CURDATE()) AND DAY(birth_date) = DAY(CURDATE())";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->get_result();
    
                $pdf->SetFont('Arial', '', 10);
                while ($item = $result->fetch_assoc()) {
                    $pdf->Cell(20, 10, $item['reg_num'], 1);
                    $pdf->Cell(100, 10, $item['client_name'], 1);
                    $pdf->Cell(70, 10, $item['contact'], 1);
                    $pdf->Cell(30, 10, date('d/m/Y', strtotime($item['birth_date'])), 1);
                    $pdf->Cell(30, 10, $item['age'], 1);
                    $pdf->Cell(50, 10, $item['address'], 1);
                    $pdf->Ln();
                }
    
                if ($result->num_rows == 0) {
                    $pdf->Cell(0, 10, 'No birthdays today.', 1, 1, 'C');
                }
    
                $pdf->Output('D', 'today_birthdays.pdf');
                exit();
            } else {
                echo "<script>alert('Incorrect password!'); window.history.back();</script>";
                exit();
            }
        } else {
            echo "<script>alert('Admin password not set! Contact support.'); window.history.back();</script>";
            exit();
        }
    }
    
    

mysqli_close($conn);
?>


<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container data-table p-5 ">
        <div class="ps-5">
            <div>
                <h1>Birthday</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="client">Client</a></li>
                <li class="breadcrumb-item active" aria-current="page">Birthday</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">


        <form method="POST">
            <div class="row">
                <div class="col-md-2 field">
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" value="<?= isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d'); ?>">
                </div>

                <div class="col-md-2 field">
                    <label for="end_date">End Date:</label>
                    <input type="date" id="end_date" name="end_date" value="<?= isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d'); ?>">
                </div>

                <div class="col-md-2">
                    <button type="submit" name="submit" class="sub-btn1 mt-4 p-1">Search</button>
                </div>
            

                <div class="col-md-1">
                    <button type="button" class="btn sub-btn1 mt-4" data-bs-toggle="modal" data-bs-target="#passwordModal">
                        EXCEL
                    </button>
                </div>

                <div class="col-md-1">
                    <button type="button" class="btn sub-btn1 mt-4" data-bs-toggle="modal" data-bs-target="#passwordModal1">
                    PDF
                    </button>
                </div>
            </div>
                        
        </form>

        <!-- Display message if available -->
        <?php if (isset($_SESSION['message']) && !empty($_SESSION['message'])): ?>
            <div class="alert alert-info">
                <?php echo $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

    <div class="pt-5">
    
    <!-- Birthday Datails -->

        <?php if (!empty($birthdays)) : ?>

        
            

            <h4>Send Birthday Wishes For Today:</h4>

             <!-- show message on screen -->
            <div>
                <textarea id="message" name="message" rows="4" cols="60" required readonly><?php echo htmlspecialchars($message); ?></textarea>
            </div>


            <div class="mt-3">
                <h3>Summary : </h3>
                <?php $totalEntries = count($birthdays); ?>
                <p>Total Entries: <?php echo $totalEntries; ?></p>
            </div>


            <!-- show data -->
            <form method="POST" id="birthdayForm">

               
                <!-- Buttons to trigger different actions -->
                <button type="button" class="sub-btn1 msg-btn mt-4 p-1" id="sendMessageBtn">Send Messages</button>
                <button type="button" class="sub-btn1 msg-btn mt-4 p-1" id="sendWhatsAppBtn">Send WhatsApp</button>

                <!-- Hidden inputs to determine the action -->
                <input type="hidden" name="action" id="action_input">

                <!-- Input field for sending messages to a custom number -->
                <div class="form-group pt-3 w-25">
                    <label for="custom_contact">Enter a Contact Number For Testing:</label>
                    <input type="text" name="custom_contact" id="custom_contact" class="form-control" placeholder="Enter phone number">
                </div>


                <table class="table table-bordered my-5">
                    <thead>
                        <tr>
                            <th>Select <input type="checkbox" id="select-all" /> </th>
                            <th>Client Name</th>
                            <th>Contact</th>
                            <th>Birth Date</th>
                            <th>Age</th>
                            <th>Address</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($birthdays)): ?>
                            <?php foreach ($birthdays as $client): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_clients[]" value="<?php echo $client['contact']; ?>" class="select-client">
                                        <input type="hidden" name="client_names[<?php echo $client['contact']; ?>]" value="<?php echo $client['client_name']; ?>">
                                        <input type="hidden" name="birth_dates[<?php echo $client['contact']; ?>]" value="<?php echo $client['birth_date']; ?>">
                                    </td>
                                    <td><?php echo $client['client_name']; ?></td>


                                    <td ondblclick="makeEditable(this, '<?php echo $client['contact']; ?>')">
                                        <span class="contact-display"><?php echo $client['contact']; ?></span>
                                        <input type="hidden" name="updated_contacts[<?php echo $client['contact']; ?>]" value="<?php echo $client['contact']; ?>" class="contact-input">
                                    </td>


                                    <!-- Birth Date -->
                                    <td>
                                        <?php 
                                        if (!empty($client['birth_date']) && $client['birth_date'] != '0000-00-00') {
                                            echo date('d/m/Y', strtotime($client['birth_date']));
                                        } else {
                                            echo "--";
                                        }
                                        ?>
                                    </td>
                                    
                                    <!-- Age Calculation -->
                                    <td>
                                        <?php 
                                        if (!empty($client['birth_date']) && $client['birth_date'] != '0000-00-00') {
                                            $dob = new DateTime($client['birth_date']);
                                            $currentDate = new DateTime();
                                            $age = $currentDate->diff($dob)->y;
                                            echo $age;
                                        } else {
                                            echo "--";
                                        }
                                        ?>
                                    </td>

                                    <td><?php echo $client['address']; ?></td>
                                    
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6"><?php echo $message ?: "No birthdays found."; ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                
            </form>

            <!-- Loading Spinner -->
<div id="loadingSpinner">
    <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p>Sending messages, please wait...</p>
</div>

<!-- Modal for Confirmation -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to proceed with this action?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmActionBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>



        <?php else : ?>
            <p>No birthdays to display.</p>
        <?php endif; ?>
    </div>
    </div>
</section>

<!-- Excel Download Modal for Entering Password -->
<div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <!-- <h5 class="modal-title" id="passwordModalLabel">Enter Password For EXCEL Download</h5> -->
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="csvDownloadForm" method="post">
        <div class="modal-body">
          <input type="password" name="admin_password" class="form-control" placeholder="Enter Admin Password" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" name="generate_csv" value="generate_csv" class="btn btn-primary" id="downloadButton">Download</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- PDF Download Modal for Entering Password -->
<div class="modal fade" id="passwordModal1" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <!-- <h5 class="modal-title" id="passwordModalLabel">Enter Password For PDF Download</h5> -->
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="csvDownloadForm" method="post">
        <div class="modal-body">
          <input type="password" name="admin_password" class="form-control" placeholder="Enter Admin Password" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" name="generate_pdf" value="generate_pdf" class="btn btn-primary" id="downloadpdf">Download</button>
        </div>
      </form>
    </div>
  </div>
</div>



<script>
    // Script For Send Messages
    const sendMessageBtn = document.getElementById('sendMessageBtn');
    const sendWhatsAppBtn = document.getElementById('sendWhatsAppBtn');
    const actionInput = document.getElementById('action_input');
    const birthdayForm = document.getElementById('birthdayForm');
    const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    const confirmActionBtn = document.getElementById('confirmActionBtn');

    let currentAction = '';

    sendMessageBtn.addEventListener('click', function () {
        currentAction = 'send_message';
        confirmationModal.show();
    });

    sendWhatsAppBtn.addEventListener('click', function () {
        currentAction = 'send_whatsapp';
        confirmationModal.show();
    });

    confirmActionBtn.addEventListener('click', function () {
        actionInput.value = currentAction;

        // Hide the modal
        confirmationModal.hide();

        // Show loading spinner
        document.getElementById('loadingSpinner').style.display = 'block';

        // Submit the form
        birthdayForm.submit();
    });

    // Select All Checkbox functionality
    document.getElementById('select-all').addEventListener('change', function () {
        const isChecked = this.checked;
        document.querySelectorAll('.select-client').forEach(function (checkbox) {
            checkbox.checked = isChecked;
        });
    });

    // Ensure individual checkboxes toggle Select All appropriately
    document.querySelectorAll('.select-client').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const allChecked = document.querySelectorAll('.select-client').length === 
                                document.querySelectorAll('.select-client:checked').length;
            document.getElementById('select-all').checked = allChecked;
        });
    });


    // Excel download Close modal and refresh page when "Download" button is clicked
 document.getElementById('downloadButton').addEventListener('click', function () {
    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('passwordModal'));
    modal.hide();

    // Refresh the page after closing the modal
    setTimeout(function() {
      window.location.reload();  // This refreshes the page
    }, 500); // Delay to ensure modal closes before page reload
  });

 // Pdf download Close modal and refresh page when "Download" button is clicked
 document.getElementById('downloadpdf').addEventListener('click', function () {
    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('passwordModal1'));
    modal.hide();

    // Refresh the page after closing the modal
    setTimeout(function() {
      window.location.reload();  // This refreshes the page
    }, 500); // Delay to ensure modal closes before page reload
});



// Edit contact for send temprory messages
    function makeEditable(td, originalValue) {
    let span = td.querySelector(".contact-display");
    let input = td.querySelector(".contact-input");

    let newInput = document.createElement("input");
    newInput.type = "text";
    newInput.value = span.innerText;
    newInput.className = "form-control";
    
    newInput.onblur = function() { 
        if (this.value.trim() === "") {
            this.value = originalValue; // Restore original if empty
        }
        span.innerText = this.value;
        input.value = this.value;
        td.innerHTML = "";
        td.appendChild(span);
        td.appendChild(input);
    };

    newInput.onkeypress = function(event) {
        if (event.key === "Enter") {
            this.blur(); // Save on Enter key
        }
    };

    td.innerHTML = "";
    td.appendChild(newInput);
    newInput.focus();
}


</script>


    <?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

<?php //} ?>