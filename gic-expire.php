<?php 
    include 'include/header.php'; 
    include 'include/head.php'; 
    include 'session_check.php';
?>



<?php
// Database connection settings
include 'includes/db_conn.php';


// Fetch expiry entries function
function fetchexpiries($startDate = null, $endDate = null, $policyType = '', $policycompany = '', $policyvehicle = '', $subtype = '',$nonmotorsubtype = '') 
{
    global $conn;

    function convertToDBDate($date)
    {
        $dateObj = DateTime::createFromFormat('d-m-Y', $date);
        return $dateObj ? $dateObj->format('Y-m-d') : false;
    }

    $startDateFormatted = $startDate ? convertToDBDate($startDate) : null;
    $endDateFormatted = $endDate ? convertToDBDate($endDate) : null;

    if (($startDate && !$startDateFormatted) || ($endDate && !$endDateFormatted)) {
        return false; // invalid date
    }

    $query = "SELECT reg_num, client_name, contact, end_date, policy_type, mv_number,policy_company,policy_number,vehicle_type,vehicle, nonmotor_type_select, address , amount,sub_type,nonmotor_subtype_select
              FROM gic_entries 
              WHERE is_deleted = 0";

    if ($startDateFormatted && $endDateFormatted) {
        $query .= " AND end_date BETWEEN '$startDateFormatted' AND '$endDateFormatted'";
    } elseif ($startDateFormatted) {
        $query .= " AND end_date = '$startDateFormatted'";
    }

    if (!empty($policyType)) {
        $query .= " AND policy_type = '" . mysqli_real_escape_string($conn, $policyType) . "'";
    }
    if (!empty($policycompany)) {
        $query .= " AND policy_company = '" . mysqli_real_escape_string($conn, $policycompany) . "'";
    }

    if (!empty($policyvehicle)) {
        $query .= " AND vehicle_type = '" . mysqli_real_escape_string($conn, $policyvehicle) . "'";
    }

    if (!empty($subtype)) {
        $query .= " AND sub_type = '" . mysqli_real_escape_string($conn, $subtype) . "'";
    }

    if (!empty($nonmotorsubtype)) {
        $query .= " AND nonmotor_subtype_select = '" . mysqli_real_escape_string($conn, $nonmotorsubtype) . "'";
    }

    $query .= " ORDER BY end_date ASC";

    return mysqli_query($conn, $query);
}


// For Text msg - Function to choose the message based on policy type
function chooseMessage($policyType, $expiryDate, $mvnumber, $nonpolicytype) {
    // Convert to dd-mm-yyyy format
    $formattedDate = '';
    if (!empty($expiryDate)) {
        $dateObj = DateTime::createFromFormat('Y-m-d', $expiryDate); // assuming your expiryDate is in Y-m-d format
        if ($dateObj) {
            $formattedDate = $dateObj->format('d-m-Y');
        } else {
            $formattedDate = $expiryDate; // fallback if format fails
        }
    }

    if ($policyType == 'Motor') {
        return "Dear Sir/Mam, Your Motor Policy $mvnumber is expiring on $formattedDate. Please contact Balaji Motor Driving School. M: 9881712967 / 9881063639";
    } elseif ($policyType == 'NonMotor') {
        return "Dear Sir/Mam, Your $nonpolicytype Policy is expiring on $formattedDate. Please contact Balaji Motor Driving School. M: 9881712967 / 9881063639";
    } else {
        return "No message for sending";
    }
}


// For whatsapp - Function to choose the message based on policy type
function chooseMessageforwhatsapp($policyType, $expiryDate, $mvnumber, $nonpolicytype) {
    // Convert to dd-mm-yyyy format
    $formattedDate = '';
    if (!empty($expiryDate)) {
        $dateObj = DateTime::createFromFormat('Y-m-d', $expiryDate);
        if ($dateObj) {
            $formattedDate = $dateObj->format('d-m-Y');
        } else {
            $formattedDate = $expiryDate; // fallback if format fails
        }
    }

    if ($policyType == 'Motor') {
        return [
            'template_name' => 'motorexpirymsg', // Your approved template name for motor policies
            'parameters' => [
                $mvnumber,
                $formattedDate
            ]
        ];
    } elseif ($policyType == 'NonMotor') {
        return [
            'template_name' => 'nonmotorexpirymsg', // Your approved template name for non-motor policies
            'parameters' => [
                $nonpolicytype,
                $formattedDate
            ]
        ];
    } else {
        return false;
    }
}




// Function to send SMS
function sendExpiryMsg($contact, $client_name, $expiryDate,$policyType,$mvnumber,$nonpolicytype)
{
    // Replace with your actual username, password, sender ID from SMSJust
    $username = 'Balajimotor@999'; // Replace with your actual SMSJust username
    $password = 'Balajimotor@999'; // Replace with your actual SMSJust password
    $senderId = 'BMDSCH'; // Sender ID as per your SMSJust settings
    
    // Get the message based on policy type
    $message = chooseMessage($policyType, $expiryDate,$mvnumber,$nonpolicytype);

    // Construct the URL with the actual parameters
    $url = "https://www.smsjust.com/sms/user/urlsms.php?username=$username&pass=$password&senderid=$senderId&dest_mobileno=$contact&msgtype=TXT&message=" . urlencode($message) . "&response=Y";

    // Execute the cURL request asynchronously using exec()
    $command = "curl -s '$url' > /dev/null 2>/dev/null &";
    exec($command);

    // Initialize cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120); // Set timeout to avoid script hanging

    // Execute cURL request and get response
    $response = curl_exec($ch);
    
    // Check if the cURL request was successful
    if ($response === false) {
        // Handle error
        $error = curl_error($ch);
        curl_close($ch);
        return "cURL Error: $error";
    }

    // Close the cURL session
    curl_close($ch);

    // Return the API response for logging or debugging
    return $response;
}



// Function to send WhatsApp messages
function sendWhatsAppExpiryMsg($contact, $client_name, $expiryDate, $policyType,$mvnumber = '',$nonpolicytype = '') {
    
    // Validate required inputs
    if (empty($contact) || empty($expiryDate) || empty($policyType)) {
        return "Error: Contact number, expiry date and policy type are required";
    }
    
    // Format expiry date to dd-mm-yyyy if needed
    $formattedDate = $expiryDate;
    $dateObj = DateTime::createFromFormat('Y-m-d', $expiryDate);
    if ($dateObj) {
        $formattedDate = $dateObj->format('d-m-Y');
    }

    // Determine template and parameters based on policy type
    if ($policyType == 'Motor') {
        if (empty($mvnumber)) {
            return "Error: Vehicle number is required for Motor policy";
        }
        $templateName = 'motorexpirymsg';
        $parameters = [
            $mvnumber,          // Will replace {{1}} (vehicle number)
            $formattedDate      // Will replace {{2}} (expiry date)
        ];
    } elseif ($policyType == 'NonMotor') {
        if (empty($nonpolicytype)) {
            return "Error: Policy type is required for Non-Motor policy";
        }
        $templateName = 'nonmotorexpirymsg';
        $parameters = [
            $nonpolicytype,     // Will replace {{1}} (policy type)
            $formattedDate      // Will replace {{2}} (expiry date)
        ];
    } else {
        return "Error: Invalid policy type. Use 'Motor' or 'NonMotor'";
    }
    
    // API Configuration
    $apiUrl = 'https://partners.pinbot.ai/v1/messages';
    $apiKey = 'fbae4610-2023-11f0-ad4f-92672d2d0c2d'; // Your Pinbot API Key
    $phoneNumberId = '919422246469'; // e.g., "919594515799"


    // Prepare parameters for the template
    // Prepare WhatsApp parameters
    $whatsappParams = array_map(function($param) {
        return ["type" => "text", "text" => $param];
    }, $parameters);
    
    // JSON Payload (as per API docs)
    $data = [
        "messaging_product" => "whatsapp",
        "recipient_type" => "individual",
        "to" => $contact, // e.g., "919594515799"
        "type" => "template",
        "template" => [
            "language" => ["code" => "en"], // English
            "name" => $templateName, // Your approved template name
            "components" => [
                [
                    "type" => "body",
                    "parameters" => $whatsappParams
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
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    
    if (curl_errno($curl)) {
        $error = curl_error($curl);
        curl_close($curl);
        return "Error: $error";
    }
    
    curl_close($curl);
    return $response;
}





// Handle form submission
$expiries = [];
$message = "";

// Function to split array into chunks of 50
function chunkArray($array, $chunkSize) {
    return array_chunk($array, $chunkSize);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = "FOR MOTOR POLICY :- Dear Sir/Mam,Your Motor Policy (mv_number) is expiring on (expiryDate).Please contact Balaji Motor Driving School.M: 9881712967 / 9881063639
    
FOR NON-MOTOR POLICY :- Dear Sir/Mam, Your (nonmotor_type) Policy is expiring on (expiryDate). Please contact Balaji Motor Driving School. M: 9881712967 / 9881063639";
    $action = $_POST['action'] ?? '';
    $startDate = $_POST['start_date'] ?? null;
    $endDate = $_POST['end_date'] ?? null;

    if (isset($_POST['submit'])) {
        $startDate = $_POST['start_date'] ?? null;
        $endDate = $_POST['end_date'] ?? null;
        $policyType = $_POST['policy'] ?? '';
        $policycompany = $_POST['policy_company'] ?? '';
        $policyvehicle = $_POST['vehicle_type'] ?? '';
        $subtype = $_POST['sub_type'] ?? '';
        $nonmotorsubtype = $_POST['nonmotor_subtype_select'] ?? '';
    
        // Convert dates to d-m-Y for consistency
        $startDate = $startDate ? date('d-m-Y', strtotime($startDate)) : null;
        $endDate = $endDate ? date('d-m-Y', strtotime($endDate)) : null;
    
        $result = fetchexpiries($startDate, $endDate, $policyType, $policycompany, $policyvehicle, $subtype, $nonmotorsubtype);
    
        if ($result === false) {
            $message = "Invalid date format.";
        } elseif (mysqli_num_rows($result) > 0) {
            $expiries = [];
            $motorCount = $nonMotorCount = $motorTotalAmount = $nonMotorTotalAmount = 0;
    
            while ($row = mysqli_fetch_assoc($result)) {
                $expiries[] = $row;
    
                $amount = floatval($row['amount']);
                if ($row['policy_type'] === 'Motor') {
                    $motorCount++;
                    $motorTotalAmount += $amount;
                } elseif ($row['policy_type'] === 'NonMotor') {
                    $nonMotorCount++;
                    $nonMotorTotalAmount += $amount;
                }
            }
    
            $totalCount = count($expiries);
            $totalAmount = $motorTotalAmount + $nonMotorTotalAmount;
        } else {
                $message = "FOR MOTOR POLICY :- Dear Sir/Mam,Your Motor Policy (mv_number) is expiring on (expiryDate).Please contact Balaji Motor Driving School.M: 9881712967 / 9881063639
    
FOR NON-MOTOR POLICY :- Dear Sir/Mam, Your (nonmotor_type) Policy is expiring on (expiryDate). Please contact Balaji Motor Driving School. M: 9881712967 / 9881063639";

        }
    }

    // Main code to process the selected clients and send messages
    if ($action === 'send_message' || $action === 'send_whatsapp') {
        $selectedClients = $_POST['selected_clients'] ?? [];
        $clientNames = $_POST['client_names'] ?? [];
        $expiryDates = $_POST['expiry_dates'] ?? [];
        $mvnums = $_POST['mvnums'] ?? [];
        $nontypes = $_POST['nontypes'] ?? [];
        $policyTypes = $_POST['policyTypes'] ?? [];
        $updatedContacts = $_POST['updated_contacts'] ?? []; // Capturing updated contacts
        $customContact = $_POST['custom_contact'] ?? ''; 
        $customPolicy = $_POST['costom_policy'] ?? '';

        $currentDate = new DateTime();
        $currentDateStr = $currentDate->format('Y-m-d'); 
        $message = "";

        if (!empty($selectedClients) || !empty($customContact) || !empty($customPolicy)) {
            // Send message to custom contact
            if (!empty($customContact) || !empty($customPolicy)) {
                $clientName = "Custom Client";
                $expiryDate = null;
                $policyType = $customPolicy ?? 'Unknown';

                if ($action === 'send_message') {
                    $response = sendExpiryMsg($customContact, $clientName, $expiryDate, $policyType, 'Unknown', 'Unknown');
                    $message .= "SMS sent to $clientName ($customContact) ($customPolicy): $response<br>";
                } elseif ($action === 'send_whatsapp') {
                    $response = sendWhatsAppExpiryMsg($customContact, $clientName, $expiryDate, $policyType, 'Unknown', 'Unknown');
                    $message .= "WhatsApp Message sent to $clientName ($customContact): $response<br>";
                }
            }

            // Chunk the selected clients into batches of 50
            $chunks = chunkArray($selectedClients, 50);

            foreach ($chunks as $chunk) {
                foreach ($chunk as $originalContact) {
                    $newContact = $updatedContacts[$originalContact] ?? $originalContact; // Use updated contact if available
                    $clientName = $clientNames[$originalContact] ?? 'Unknown';
                    $expiryDate = $expiryDates[$originalContact] ?? null;
                    $policyType = $policyTypes[$originalContact] ?? 'Unknown';
                    $mvnumber = $mvnums[$originalContact] ?? 'Unknown';
                    $nonpolicytype = $nontypes[$originalContact] ?? 'Unknown';

                    if (!empty($expiryDate)) { // Ensure expiry date exists
                        $expiryDateTime = DateTime::createFromFormat('Y-m-d', $expiryDate);

                        if ($expiryDateTime !== false) { // Check if conversion worked
                            $expiryDateStr = $expiryDateTime->format('Y-m-d');

                            if ($expiryDateStr >= $currentDateStr) { // Compare dates
                                if ($action === 'send_message') {
                                    $response = sendExpiryMsg($newContact, $clientName, $expiryDate, $policyType, $mvnumber, $nonpolicytype);
                                    $message .= "SMS sent to $clientName ($newContact) for expiry on $expiryDate: $response<br>";
                                } elseif ($action === 'send_whatsapp') {
                                    $response = sendWhatsAppExpiryMsg($newContact, $clientName, $expiryDate, $policyType, $mvnumber, $nonpolicytype);
                                    $message .= "WhatsApp Message sent to $clientName ($newContact) for expiry on $expiryDate: $response<br>";
                                }
                            } else {
                                $message .= "No message sent to $clientName ($newContact): Expiry date ($expiryDate) is in the past.<br>";
                            }
                        } else {
                            $message .= "Invalid expiry date format for $clientName ($newContact).<br>";
                        }
                    } else {
                        $message .= "No expiry date found for $clientName ($newContact).<br>";
                    }
                }
            }

            if (empty(trim($message))) {
                $message = "No messages sent. Ensure the selected clients have expiry dates matching the selected criteria.";
            }
        } else {
            $message = "Please select clients to send messages.";
        }

        $_SESSION['message'] = $message;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
else {

    // show message on screen
    $message = "FOR MOTOR POLICY :- Dear Sir/Mam,Your Motor Policy (mv_number) is expiring on (expiryDate).Please contact Balaji Motor Driving School.M: 9881712967 / 9881063639
    
FOR NON-MOTOR POLICY :- Dear Sir/Mam, Your (nonmotor_type) Policy is expiring on (expiryDate). Please contact Balaji Motor Driving School. M: 9881712967 / 9881063639";

    // Default: Fetch today's birthdays
    $result = fetchexpiries();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $birthdays[] = $row;
        }
    }
}



?>


<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container data-table p-5 ">
        <div class="ps-5">
            <div>
                <h1>Send Message</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="gic">GIC</a></li>
                <li class="breadcrumb-item active" aria-current="page">Send Message</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">


        <form method="POST">
            <div class="row">
                <?php
                
                    // Fetch unique company names from the database
                    $companyQuery = "SELECT DISTINCT policy_company FROM gic_entries WHERE policy_company IS NOT NULL AND policy_company != '' ORDER BY policy_company";
                    $companyResult = $conn->query($companyQuery);

                    $vehicleQuery = "SELECT DISTINCT vehicle_type FROM gic_entries WHERE vehicle_type IS NOT NULL AND vehicle_type != '' ORDER BY vehicle_type";
                    $vehicleResult = $conn->query($vehicleQuery);

                    $nonmotorsubtypeQuery = "SELECT DISTINCT nonmotor_subtype_select FROM gic_entries WHERE nonmotor_subtype_select IS NOT NULL AND nonmotor_subtype_select != '' ORDER BY nonmotor_subtype_select";
                    $nonmotorsubtypeResult = $conn->query($nonmotorsubtypeQuery);

                    $start_date = $_POST['start_date'] ?? date('Y-m-d');
                    $end_date = $_POST['end_date'] ?? date('Y-m-d');
                    $policy = $_POST['policy'] ?? '';
                    $sub_type = $_POST['sub_type'] ?? '';
                    $selectedCompany = $_POST['policy_company'] ?? '';
                    $selectedvehicle = $_POST['vehicle_type'] ?? '';
                    $selectednonmotorsubtype = $_POST['nonmotor_subtype_select'] ?? '';

                ?>

                <div class="col-md-2 field">
                    <label for="start_date" class="form-label">Start Date :</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>" />
                </div>

                <div class="col-md-2 field">
                    <label for="end_date" class="form-label">End Date :</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>" />
                </div>

                <div class="col-md-2 field">
                    <label for="policy" class="form-label">Policy Type :</label>
                    <select name="policy" id="policy" class="form-control">
                        <option value="">All</option>
                        <option value="Motor" <?= ($policy === 'Motor') ? 'selected' : '' ?>>Motor</option>
                        <option value="NonMotor" <?= ($policy === 'NonMotor') ? 'selected' : '' ?>>NonMotor</option>
                    </select>
                </div>

                <div class="col-md-2 field" id="motor_sub_type" style="display: <?= ($policy == 'Motor') ? 'block' : 'none'; ?>;">
                    <label for="policy" class="form-label">M Sub Type :</label>
                    <select name="sub_type" id="sub_type" class="form-control">
                        <option value="">All</option>
                        <option value="A" <?= ($sub_type === 'A') ? 'selected' : '' ?>>A</option>
                        <option value="B" <?= ($sub_type === 'B') ? 'selected' : '' ?>>B</option>
                        <option value="SAOD" <?= ($sub_type === 'SAOD') ? 'selected' : '' ?>>SAOD</option>
                        <option value="ENDST" <?= ($sub_type === 'ENDST') ? 'selected' : '' ?>>ENDST</option>
                    </select>
                </div>

                <div class="col-md-2 field" id="nonmotor_sub_type" style="display: <?= ($policy == 'NonMotor') ? 'block' : 'none'; ?>;">
                    <label for="nonmotor_subtype_select" class="form-label">NM Sub Type :</label>
                    <select name="nonmotor_subtype_select" class="form-control">
                        <option value="">All</option>
                        <?php while ($row = $nonmotorsubtypeResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['nonmotor_subtype_select']) ?>" <?= ($selectednonmotorsubtype === $row['nonmotor_subtype_select']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['nonmotor_subtype_select']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-2 field">
                    <label for="policy_company" class="form-label">Policy Company :</label>
                    <select name="policy_company" class="form-control">
                        <option value="">All</option>
                        <?php while ($row = $companyResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['policy_company']) ?>" <?= ($selectedCompany === $row['policy_company']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['policy_company']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-2 field">
                    <label for="vehicle_type" class="form-label">Type Of Vehicle :</label>
                    <select name="vehicle_type" class="form-control">
                        <option value="">All</option>
                        <?php while ($row = $vehicleResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['vehicle_type']) ?>" <?= ($selectedvehicle === $row['vehicle_type']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['vehicle_type']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-2 mt-4">
                    <button type="submit" name="submit" class="btn sub-btn1">Search</button>
                </div>

                <div class="col-md-2">
                    <!-- Copy All Contacts Button -->
                    <button type="button" class="btn sub-btn1 mt-4" onclick="copyContacts()">Copy Contacts</button>
                </div>

                <!-- Trigger Button -->
                    <div class="col-md-1">
                        <button type="button" class="btn sub-btn1 mt-4" onclick="showPasswordModal()">Print</button>
                    </div>
            
            </div>
        </form>

        <!-- Display message if available -->
            <div class="pt-5">
                <?php if (isset($_SESSION['message']) && !empty($_SESSION['message'])): ?>
                    <div class="alert alert-info">
                        <?php echo $_SESSION['message']; ?>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
            </div>

            <h4>Send Expiry Reminder Message:</h4>

                <!-- show message on screen -->
                <div>
                    <textarea id="message" name="message" rows="5" cols="100" required readonly><?php echo htmlspecialchars($message); ?></textarea>
                </div>


    <div class="pt-5">
    




    
    <!-- Birthday Datails -->

        <?php if (!empty($expiries)) : ?>

            

            <!-- show data -->
            <form method="POST" id="birthdayForm">

           

            <!-- Buttons to trigger different actions -->
            <button type="button" class="sub-btn1 msg-btn mt-4 p-1" id="sendMessageBtn">Send Messages</button>
                <button type="button" class="sub-btn1 msg-btn mt-4 p-1" id="sendWhatsAppBtn">Send WhatsApp</button>

                <!-- Hidden inputs to determine the action -->
                <input type="hidden" name="action" id="action_input">
                
                <div class="row">
                    <!-- Input field for sending messages to a custom number -->
                    <div class="form-group pt-3 w-25">
                        <label for="custom_contact">Enter a Contact Number For Testing:</label>
                        <input type="text" name="custom_contact" id="custom_contact" class="form-control" placeholder="Enter phone number">
                    </div>

                    <div class="form-group pt-3 w-25"> 
                        <label for="custom_contact">Select Policy Type For Testing:</label>
                        <select class="form-select" name="costom_policy" aria-label="Default select example">
                            <option selected>Select Policy Type</option>
                            <option value="Motor">Motor</option>
                            <option value="NonMotor">NonMotor</option>
                        </select>
                    </div>
                </div>
                
                <div id="reportSection">
                    
                    <div class="mt-2">
                        <h3>Summary :</h3>
                        <table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 600px;">
                            <thead style="background-color: #f2f2f2;">
                                <tr>
                                    <th>Policy Type</th>
                                    <th>Total Entries</th>
                                    <th>Total Premium (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Motor</td>
                                    <td><?php echo htmlspecialchars($motorCount); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($motorTotalAmount, 2)); ?></td>
                                </tr>
                                <tr>
                                    <td>Non-Motor</td>
                                    <td><?php echo htmlspecialchars($nonMotorCount); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($nonMotorTotalAmount, 2)); ?></td>
                                </tr>
                                <tr style="font-weight: bold; background-color: #f9f9f9;">
                                    <td>Total</td>
                                    <td><?php echo htmlspecialchars($motorCount + $nonMotorCount); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($motorTotalAmount + $nonMotorTotalAmount, 2)); ?></td>
                                </tr>
                            </tbody>
                        </table>

                    </div>

                    <table class="table table-bordered my-5">
                        <thead>
                            <tr>
                                <th class="action-col">Select <input type="checkbox" id="select-all" /> </th>
                                <th>Sr No</th>
                                <th>Reg No</th>
                                <th>Client Name</th>
                                <th>Contact</th>
                                <th>Policy Type</th>
                                <th>Sub Type</th>
                                <th>MV Number/NonMotor Type</th>
                                <th>Insurance Company</th>
                                <th>Premium</th>
                                <th>Expiry Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($expiries)): ?>

                            <?php
                                $srNo = 1;
                                $motorHeaderShown = false;
                                $nonMotorHeaderShown = false;
                            ?>
            
                            <!-- Motor Section -->
                            <tr>
                                <td colspan="8">
                                    <input type="checkbox" id="select-all-motor">  Motor Policies
                                </td>
                            </tr>
                            
                            <?php 
                            $motorFound = false;
                            foreach ($expiries as $client): 
                                if ($client['policy_type'] === 'Motor'):
                                    $motorFound = true;
                                    
                            ?>
                            <tr>
                            <td class="action-col">
                                <input type="checkbox" name="selected_clients[]" value="<?php echo $client['contact']; ?>" class="select-client select-client-motor">
                                <input type="hidden" name="client_names[<?php echo $client['contact']; ?>]" value="<?php echo $client['client_name']; ?>">
                                <input type="hidden" name="expiry_dates[<?php echo $client['contact']; ?>]" value="<?php echo $client['end_date']; ?>">
                                <input type="hidden" name="policyTypes[<?php echo $client['contact']; ?>]" value="<?php echo $client['policy_type']; ?>">
                                <input type="hidden" name="mvnums[<?php echo $client['contact']; ?>]" value="<?php echo $client['mv_number']; ?>">
                            </td>
                            <td><?php echo $srNo++; ?></td>
                            <td><?php echo $client['reg_num']; ?></td>
                            <td>
                                <?php echo $client['client_name']; ?> <br>
                                <?php echo $client['address']; ?>
                            </td>
                            <td ondblclick="makeEditable(this, '<?php echo $client['contact']; ?>')">
                                <span class="contact-display"><?php echo $client['contact']; ?></span>
                                <div id="hiddenInputsContainer"></div>

                            </td>

                            <td><?php echo $client['policy_type']; ?></td>
                            <td><?php echo $client['sub_type']; ?></td>
                            <td>
                                <?php echo $client['mv_number']; ?> <br>
                                <?php echo $client['vehicle']; ?> 
                            </td>
                            <td>
                                <?php echo $client['policy_company']; ?> <br>
                                <?php echo $client['policy_number']; ?>
                            </td>
                            <td><?php echo $client['amount']; ?></td>
                            <td>
                                <?= (!empty($client['end_date']) && $client['end_date'] !== '0000-00-00') 
                                    ? date('d/m/Y', strtotime($client['end_date'])) 
                                    : '00-00-0000' ?>
                            </td>

                
                            </tr>
                            <?php endif; endforeach; ?>
                            <?php if (!$motorFound): ?>
                                <tr>
                                    <td colspan="7">No Motor policies found.</td>
                                </tr>
                            <?php endif; ?>
                            
                            <!-- Nonmotor Section -->
                            <tr>
                                <td colspan="8">
                                    <input type="checkbox" id="select-all-nonmotor"> NonMotor Policies 
                                </td>
                            </tr>

                            <?php 
                            $nonMotorFound = false;
                            foreach ($expiries as $client): 
                                if ($client['policy_type'] === 'NonMotor'):
                                    $nonMotorFound = true;
                                
                            ?>
                            <tr>
                            <td class="action-col">
                                <input type="checkbox" name="selected_clients[]" value="<?php echo $client['contact']; ?>" class="select-client select-client-nonmotor">
                                <input type="hidden" name="client_names[<?php echo $client['contact']; ?>]" value="<?php echo $client['client_name']; ?>">
                                <input type="hidden" name="expiry_dates[<?php echo $client['contact']; ?>]" value="<?php echo $client['end_date']; ?>">
                                <input type="hidden" name="policyTypes[<?php echo $client['contact']; ?>]" value="<?php echo $client['policy_type']; ?>">
                                <input type="hidden" name="nontypes[<?php echo $client['contact']; ?>]" value="<?php echo $client['nonmotor_type_select']; ?>">
                            </td>
                            <td><?php echo $srNo++; ?></td>
                            <td><?php echo $client['reg_num']; ?></td>
                            <td>
                                <?php echo $client['client_name']; ?> <br>
                                <?php echo $client['address']; ?>
                            </td>
                            <td ondblclick="makeEditable(this, '<?php echo $client['contact']; ?>')">
                                <span class="contact-display"><?php echo $client['contact']; ?></span>
                                <div id="hiddenInputsContainer"></div>

                            </td>
                            <td><?php echo $client['policy_type']; ?></td>
                            <td><?php echo $client['nonmotor_subtype_select']; ?></td>
                            <td><?php echo $client['nonmotor_type_select']; ?></td>
                            <td>
                                <?php echo $client['policy_company']; ?> <br>
                                <?php echo $client['policy_number']; ?>
                            </td>
                            <td><?php echo $client['amount']; ?></td>
                            <td>
                                <?= (!empty($client['end_date']) && $client['end_date'] !== '0000-00-00') 
                                    ? date('d/m/Y', strtotime($client['end_date'])) 
                                    : '00-00-0000' ?>
                            </td>

                            </tr>
                            <?php endif; endforeach; ?>
                            <?php if (!$nonMotorFound): ?>
                                <tr>
                                    <td colspan="7">No Nonmotor policies found.</td>
                                </tr>
                            <?php endif; ?>

                            

                            <?php else: ?>
                                <tr>
                                    <td colspan="7"><?php echo $message ?: "No expiries found."; ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>


                    </table>
                </div>
                
                
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
            <p>No expiries to display.</p>
        <?php endif; ?>
    </div>
    </div>
</section>



<!-- Password verification Modal for print screen -->
<div class="modal fade" id="printpasswordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <!-- <h5 class="modal-title" id="passwordModalLabel">Enter Password For Print</h5> -->
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="password" id="passwordInput" class="form-control" placeholder="Enter password" />
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="validatePassword()">Submit</button>
      </div>
    </div>
  </div>
</div>


<!-- script for print password verify -->
<script>
    // Show the password modal when the button is clicked
    function showPasswordModal() {
        $('#printpasswordModal').modal('show');
    }

    // Validate the password entered
    async function validatePassword() {
        const userPassword = document.getElementById('passwordInput').value;

        if (!userPassword) {
            alert("Password is required.");
            return;
        }

        // Validate the entered password with the backend
        const validationResult = await validatePasswordOnServer(userPassword);

        if (validationResult.success) {
            // Password is correct, proceed with print
            window.print();
            $('#printpasswordModal').modal('hide'); // Close the modal
        } else {
            // Show error message if the password is incorrect
            alert(validationResult.error || "Incorrect password!");
        }
    }

    // Function to send password to server for validation
    async function validatePasswordOnServer(userPassword) {
        try {
            const response = await fetch('print_pass.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `password=${encodeURIComponent(userPassword)}`
            });
            const data = await response.json();
            return data;
        } catch (error) {
            console.error("Error validating password:", error);
            return { success: false, error: "Error validating password" };
        }
    }
</script>


<script>

// script for selection of motor & nonmotor subtype dropdown
    
    document.addEventListener("DOMContentLoaded", function () {
    const policySelect = document.getElementById("policy");
    const motorDiv = document.getElementById("motor_sub_type");
    const nonMotorDiv = document.getElementById("nonmotor_sub_type");

    function toggleSubType() {
        const selectedValue = policySelect.value;

        if (selectedValue === "Motor") {
            motorDiv.style.display = "block";
            nonMotorDiv.style.display = "none";
        } else if (selectedValue === "NonMotor") {
            motorDiv.style.display = "none";
            nonMotorDiv.style.display = "block";
        } else {
            motorDiv.style.display = "none";
            nonMotorDiv.style.display = "none";
        }
    }

    policySelect.addEventListener("change", toggleSubType);
});


// Script for copy contacts    

// script for copy contacts to clipboard
function copyContacts() {
    const contactMap = new Map();
    const invalidContacts = [];
    const validContacts = [];
    let duplicateCount = 0;

    document.querySelectorAll('tbody tr').forEach(row => {
        const contact = row.querySelector('td:nth-child(5)')?.innerText.trim();
        if (contact && contact !== '--') {
            const cleaned = contact.replace(/\D/g, '');
            if (/^\d{10,12}$/.test(cleaned)) {
                if (contactMap.has(cleaned)) {
                    contactMap.set(cleaned, contactMap.get(cleaned) + 1);
                    duplicateCount++;
                } else {
                    contactMap.set(cleaned, 1);
                    validContacts.push(cleaned);
                }
            } else {
                invalidContacts.push(contact);
            }
        }
    });

    let message = `📋 Copy Results:\n\n`;
    message += `✅ Valid contacts prepared: ${validContacts.length}\n`;
    message += `❌ Invalid contacts: ${invalidContacts.length}\n`;
    message += `♻️ Duplicate contacts found: ${duplicateCount}\n\n`;

    if (invalidContacts.length > 0) {
        message += `--- Invalid Numbers (not 10-12 digits) ---\n`;
        message += invalidContacts.slice(0, 5).join('\n');
        if (invalidContacts.length > 5) message += `\n...and ${invalidContacts.length - 5} more`;
        message += `\n\n`;
    }

    if (validContacts.length > 0) {
        // Fallback method: create hidden textarea
        const textarea = document.createElement('textarea');
        textarea.value = validContacts.join('\n');
        document.body.appendChild(textarea);
        textarea.select();
        try {
            const success = document.execCommand('copy');
            if (success) {
                message += `📋 ${validContacts.length} contacts copied to clipboard!`;
            } else {
                message += `⚠️ Could not copy to clipboard automatically.\nPlease copy manually:\n\n${textarea.value}`;
            }
        } catch (err) {
            message += `⚠️ Copy failed: ${err}\nPlease copy manually:\n\n${textarea.value}`;
        }
        document.body.removeChild(textarea);
        alert(message);
    } else {
        alert(message + `\n\n⚠️ No valid contacts to copy!`);
    }
}

</script>



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


    // Select All checkbox for the whole table
document.getElementById('select-all').addEventListener('change', function () {
    const isChecked = this.checked;
    // Select all checkboxes (both Motor and Nonmotor)
    document.querySelectorAll('.select-client').forEach(function (checkbox) {
        checkbox.checked = isChecked;
    });
});

// Select All checkbox for Motor section
document.getElementById('select-all-motor').addEventListener('change', function () {
    const isChecked = this.checked;
    // Select all checkboxes in Motor section
    document.querySelectorAll('.select-client-motor').forEach(function (checkbox) {
        checkbox.checked = isChecked;
    });
});

// Select All checkbox for Nonmotor section
document.getElementById('select-all-nonmotor').addEventListener('change', function () {
    const isChecked = this.checked;
    // Select all checkboxes in Nonmotor section
    document.querySelectorAll('.select-client-nonmotor').forEach(function (checkbox) {
        checkbox.checked = isChecked;
    });
});

// Ensure individual checkboxes toggle Select All appropriately for the whole table
document.querySelectorAll('.select-client').forEach(function (checkbox) {
    checkbox.addEventListener('change', function () {
        const allChecked = document.querySelectorAll('.select-client').length ===
                            document.querySelectorAll('.select-client:checked').length;
        document.getElementById('select-all').checked = allChecked;
    });
});

// Ensure individual checkboxes toggle Select All for Motor section
document.querySelectorAll('.select-client-motor').forEach(function (checkbox) {
    checkbox.addEventListener('change', function () {
        const allCheckedMotor = document.querySelectorAll('.select-client-motor').length ===
                                 document.querySelectorAll('.select-client-motor:checked').length;
        document.getElementById('select-all-motor').checked = allCheckedMotor;
    });
});

// Ensure individual checkboxes toggle Select All for Nonmotor section
document.querySelectorAll('.select-client-nonmotor').forEach(function (checkbox) {
    checkbox.addEventListener('change', function () {
        const allCheckedNonmotor = document.querySelectorAll('.select-client-nonmotor').length ===
                                    document.querySelectorAll('.select-client-nonmotor:checked').length;
        document.getElementById('select-all-nonmotor').checked = allCheckedNonmotor;
    });
});

// Edit contact for send temprory messages
function makeEditable(td, originalValue) {
    let span = td.querySelector(".contact-display");

    let newInput = document.createElement("input");
    newInput.type = "text";
    newInput.value = span.innerText.trim();
    newInput.className = "form-control";

    newInput.onblur = function() {
        let updatedValue = this.value.trim();
        if (updatedValue === "") {
            updatedValue = originalValue;
        }
        span.innerText = updatedValue;

        // Hidden inputs container
        let container = document.getElementById("hiddenInputsContainer");

        // Create or update hidden input
        let inputName = "updated_contacts[" + originalValue + "]";
        let existingHidden = container.querySelector("input[name='" + inputName + "']");

        if (existingHidden) {
            existingHidden.value = updatedValue;
        } else {
            let hiddenInput = document.createElement("input");
            hiddenInput.type = "hidden";
            hiddenInput.name = inputName;
            hiddenInput.value = updatedValue;
            container.appendChild(hiddenInput);
        }

        // Restore td
        td.innerHTML = "";
        td.appendChild(span);
    };

    newInput.onkeypress = function(event) {
        if (event.key === "Enter") {
            this.blur();
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