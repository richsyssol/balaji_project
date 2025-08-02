<?php 
    include 'include/header.php'; 
    include 'include/head.php'; 
    include 'session_check.php';
?>

<?php
// Database connection settings
include 'includes/db_conn.php';

// Fetch expiry entries function
function fetchexpiries($startDate = null, $endDate = null, $bmds_type = '') {
    global $conn;

    function convertToDBDate($date) {
        $dateObj = DateTime::createFromFormat('d-m-Y', $date);
        return $dateObj ? $dateObj->format('Y-m-d') : false;
    }

    $startDateFormatted = $startDate ? convertToDBDate($startDate) : null;
    $endDateFormatted = $endDate ? convertToDBDate($endDate) : null;

    if (($startDate && !$startDateFormatted) || ($endDate && !$endDateFormatted)) {
        return false; // invalid date
    }

    $query = "SELECT client_name, contact, test_date, bmds_type, llr_type, mdl_type, city, sr_num,class, car_type,llr_class  FROM bmds_entries";
    
    $conditions = ["is_deleted = 0"];

    if ($startDateFormatted && $endDateFormatted) {
        $conditions[] = "test_date BETWEEN '$startDateFormatted' AND '$endDateFormatted'";
    } elseif ($startDateFormatted) {
        $conditions[] = "test_date = '$startDateFormatted'";
    }

    if (!empty($bmds_type)) {
        $conditions[] = "bmds_type = '" . mysqli_real_escape_string($conn, $bmds_type) . "'";
    }

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    $query .= " ORDER BY sr_num ASC";

    return mysqli_query($conn, $query);
}

// select Message function for sms
function chooseMessage($bmds_type, $testDate, $llrtype, $dltype, $srnumbers, $city) {
    $formattedDate = '';
    if (!empty($testDate)) {
        $dateObj = DateTime::createFromFormat('Y-m-d', $testDate);
        $formattedDate = $dateObj ? $dateObj->format('d-m-Y') : $testDate;
    }

    if ($bmds_type === 'LLR') {
        // Different messages for different LLR types
        if ($llrtype === 'FRESH') {
            return "Dear Sir/Ma'am, Reminder for your Driving Licence Test (L) $formattedDate $city. Your sr number is $srnumbers. Please be present and carry all necessary documents as required. Best regards, Bhaurao Pingle Balaji Motor Driving School. M:9960581819 / 9881063639";
        } 
        elseif ($llrtype === 'EXEMPTED') {
            return "Dear Sir/Ma'am, Reminder for your Licence Test $formattedDate $city . Your sr number is $srnumbers. Please be present and carry all necessary documents as required. Best regards, Bhaurao Pingle Balaji Motors Driving School. M:9960581819 / 9881063639";
        }
        else {
            // Default LLR message if type not recognized
            return "No message for sending";
        }
    } 
    elseif ($bmds_type === 'DL') {
        // Single message for all DL types (FRESH, ENDST, REVALID)
        return "Dear Sir/Ma'am, Reminder for your Driving Licence Test $formattedDate $city. Your sr number is $srnumbers. Please be present and carry all necessary documents as required. Best regards, Bhaurao Pingle Balaji Motor Driving School. M:9960581819 / 9881063639";
    } 
    else {
        return "No message for sending";
    }
}



// SMS sending function
function sendExpiryMsg($contact, $client_name, $testDate, $bmds_type, $llrtype, $dltype, $srnumbers, $city) {
    $username = 'Balajimotor@999'; // Replace with your actual SMSJust username
    $password = 'Balajimotor@999'; // Replace with your actual SMSJust password
    $senderId = 'BMDSCH'; // Sender ID as per your SMSJust settings

    $message = chooseMessage($bmds_type, $testDate, $llrtype, $dltype, $srnumbers, $city);
    $url = "https://www.smsjust.com/sms/user/urlsms.php?username=$username&pass=$password&senderid=$senderId&dest_mobileno=$contact&msgtype=TXT&message=" . urlencode($message) . "&response=Y";

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

//select message function for whatsapp
function chooseMessageforwhatsapp($bmds_type, $testDate, $llrtype, $dltype, $srnumbers, $city) {
    $formattedDate = '';
    if (!empty($testDate)) {
        $dateObj = DateTime::createFromFormat('Y-m-d', $testDate);
        $formattedDate = $dateObj ? $dateObj->format('d-m-Y') : $testDate;
    }

    if ($bmds_type == 'LLR') {
        if ($llrtype == 'FRESH') {
            return [
                'template_name' => 'fresh_bmds',
                'parameters' => [$formattedDate, $city, $srnumbers]
            ];
        }
        elseif ($llrtype == 'EXEMPTED') {
            return [
                'template_name' => 'exempted_bmds',
                'parameters' => [$formattedDate, $city, $srnumbers]
            ];
        }
    }
    elseif ($bmds_type == 'DL') {
        return [
            'template_name' => 'dl_bmds',
            'parameters' => [$formattedDate, $city, $srnumbers]
        ];
    }
    return false;
}

// WhatsApp sending function
function sendWhatsAppExpiryMsg($contact, $client_name, $testDate, $bmds_type, $llrtype, $dltype, $srnumbers, $city) {
    if (empty($contact) || empty($testDate) || empty($bmds_type)) {
        return "Error: Contact number, expiry date and policy type are required";
    }

    $formattedDate = $testDate;
    $dateObj = DateTime::createFromFormat('Y-m-d', $testDate);
    if ($dateObj) {
        $formattedDate = $dateObj->format('d-m-Y');
    }

  
    $templateData = chooseMessageforwhatsapp($bmds_type, $testDate, $llrtype, $dltype, $srnumbers, $city);
    if (!$templateData) {
        return "Error: Invalid template data";
    }

     // Validate and format contact number
    if (empty($contact)) {
        return "Error: Contact number is required";
    }

    // Ensure number starts with 91 (India country code)
    $contact = preg_replace('/^(\+91|91)/', '', $contact); // Remove +91 or 91 if already present
    $contact = '91' . $contact; // Prepend 91

    $apiUrl = 'https://partners.pinbot.ai/v1/messages';
    $apiKey = 'fbae4610-2023-11f0-ad4f-92672d2d0c2d';
    $phoneNumberId = '919422246469';

    $whatsappParams = array_map(function ($param) {
        return ["type" => "text", "text" => $param];
    }, $templateData['parameters']);

    $data = [
        "messaging_product" => "whatsapp",
        "recipient_type" => "individual",
        "to" => $contact,
        "type" => "template",
        "template" => [
            "language" => ["code" => "en"],
            "name" => $templateData['template_name'],
            "components" => [[
                "type" => "body",
                "parameters" => $whatsappParams
            ]]
        ]
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'apikey:' . $apiKey,
            'wanumber:' . $phoneNumberId
        ]
    ]);

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
$motorCount = 0;
$nonMotorCount = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $startDate = $_POST['start_date'] ?? null;
    $endDate = $_POST['end_date'] ?? null;

    if (isset($_POST['submit'])) {

            // Default message template
    $message = "FOR LLR POLICY :- FRESH : Dear Sir/Ma'am, Reminder for your Driving Licence Test (L) (Date) (test place). Your sr number is (sr num). Please be present and carry all necessary documents as required. Best regards, Bhaurao Pingle Balaji Motor Driving School. M:9960581819 / 9881063639\n\nEXEMPTED : Dear Sir/Ma'am, Reminder for your Licence Test (Date) (test place) . Your sr number is (sr num). Please be present and carry all necessary documents as required. Best regards, Bhaurao Pingle Balaji Motors Driving School. M:9960581819 / 9881063639 \n\nFOR DL POLICY :- Dear Sir/Ma'am, Reminder for your Driving Licence Test (Date) (test place). Your sr number is (sr num). Please be present and carry all necessary documents as required. Best regards, Bhaurao Pingle Balaji Motor Driving School. M:9960581819 / 9881063639";

        $startDate = $_POST['start_date'] ?? null;
        $endDate = $_POST['end_date'] ?? null;
        $bmds_type = $_POST['policy'] ?? '';
    
        // Convert dates to d-m-Y for consistency
        $startDate = $startDate ? date('d-m-Y', strtotime($startDate)) : null;
        $endDate = $endDate ? date('d-m-Y', strtotime($endDate)) : null;
    
        $result = fetchexpiries($startDate, $endDate, $bmds_type);
    
        if ($result === false) {
            $message = "Invalid date format.";
        } elseif (mysqli_num_rows($result) > 0) {
            $expiries = [];
            $motorCount = $nonMotorCount = 0;

            while ($row = mysqli_fetch_assoc($result)) {
                $expiries[] = $row;
                if ($row['bmds_type'] === 'LLR') {
                    $motorCount++;
                } elseif ($row['bmds_type'] === 'DL') {
                    $nonMotorCount++;
                }
            }
        } else {
            $message = "No expiries found for the selected date range.";
        }
    }

    // Handle message sending
    if ($action === 'send_message' || $action === 'send_whatsapp') {
        $selectedClients = $_POST['selected_clients'] ?? [];
        $clientNames = $_POST['client_names'] ?? [];
        $testDates = $_POST['test_dates'] ?? [];
        $srnumbers = $_POST['srnumbers'] ?? [];
        $cities = $_POST['cities'] ?? [];
        $policytypes = $_POST['policy_types'] ?? [];
        $updatedContacts = $_POST['updated_contacts'] ?? [];
        $llrTypes = $_POST['llr_types'] ?? [];
        // $mdlTypes = $_POST['mdl_types'] ?? [];

        $currentDate = new DateTime();
        $currentDateStr = $currentDate->format('Y-m-d'); 
        $message = "";

        if (!empty($selectedClients) || !empty($customContact) || !empty($customPolicy)) {
            // Process custom contact if provided
            if (!empty($customContact) || !empty($customPolicy)) {
                $clientName = "Custom Client";
                $testDate = $currentDateStr;
                $bmds_type = $customPolicy ?? 'Unknown';
                $srnum = 'N/A';
                $city = 'Unknown';

                if ($action === 'send_message') { 
                    $response = sendExpiryMsg($customContact, $clientName, $testDate, $bmds_type, '', '', $srnum, $city);
                    $message .= "SMS sent to $clientName ($customContact) ($customPolicy): $response";
                } elseif ($action === 'send_whatsapp') {
                    $response = sendWhatsAppExpiryMsg($customContact, $clientName, $testDate, $bmds_type, '', '',$srnum, $city);
                    $message .= "WhatsApp Message sent to $clientName ($customContact): $response";
                }
            }

            // Process selected clients
            foreach ($selectedClients as $originalContact) {
                $newContact = $updatedContacts[$originalContact] ?? $originalContact;
                $clientName = $clientNames[$originalContact] ?? 'Unknown';
                $testDate = $testDates[$originalContact] ?? null;
                $bmds_type = $policytypes[$originalContact] ?? 'Unknown';
                $llrtype = $llrTypes[$originalContact] ?? 'Unknown';
                $srnum = $srnumbers[$originalContact] ?? 'Unknown';
                $city = $cities[$originalContact] ?? 'Unknown';
                $subtype = ($type === 'LLR') ? $llrTypes[$originalContact] ?? '' : $llrTypes[$originalContact] ?? '';

                if (!empty($testDate)) {
                    $testDateTime = DateTime::createFromFormat('Y-m-d', $testDate);
                    if ($testDateTime !== false) {
                        $testDatestr = $testDateTime->format('Y-m-d');
                        if ($testDatestr >= $currentDateStr) {
                            if ($action === 'send_message') {
                                $response = sendExpiryMsg($newContact, $clientName, $testDate, $bmds_type, $subtype, '', $srnum, $city);
                                $message .= "SMS sent to $clientName ($newContact) for expiry on $testDate: $response";
                            } elseif ($action === 'send_whatsapp') {
                                $response = sendWhatsAppExpiryMsg($newContact, $clientName, $testDate, $bmds_type, $subtype, '', $srnum, $city);
                                $message .= "WhatsApp Message sent to $clientName ($newContact) for expiry on $testDate: $response";
                            }
                        } else {
                            $message .= "No message sent to $clientName ($newContact): Expiry date ($testDate) is in the past.";
                        }
                    } else {
                        $message .= "Invalid expiry date format for $clientName ($newContact).";
                    }
                } else {
                    $message .= "No expiry date found for $clientName ($newContact).";
                }
            }
        } else {
            $message = "Please select clients to send messages.";
        }

        $_SESSION['message'] = $message;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
} else {
    // Default message template
    $message = "FOR LLR POLICY :- FRESH : Dear Sir/Ma'am, Reminder for your Driving Licence Test (L) (Date) (test place). Your sr number is (sr num). Please be present and carry all necessary documents as required. Best regards, Bhaurao Pingle Balaji Motor Driving School. M:9960581819 / 9881063639\n\nEXEMPTED : Dear Sir/Ma'am, Reminder for your Licence Test (Date) (test place) . Your sr number is (sr num). Please be present and carry all necessary documents as required. Best regards, Bhaurao Pingle Balaji Motors Driving School. M:9960581819 / 9881063639 \n\nFOR DL POLICY :- Dear Sir/Ma'am, Reminder for your Driving Licence Test (Date) (test place). Your sr number is (sr num). Please be present and carry all necessary documents as required. Best regards, Bhaurao Pingle Balaji Motor Driving School. M:9960581819 / 9881063639";

    // Fetch today's entries by default
    $result = fetchexpiries();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $expiries[] = $row;
            if ($row['bmds_type'] === 'LLR') {
                $motorCount++;
            } elseif ($row['bmds_type'] === 'DL') {
                $nonMotorCount++;
            }
        }
    }
}
?>

<!-- HTML Section -->
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
                <li class="breadcrumb-item"><a href="bmds">BMDS</a></li>
                <li class="breadcrumb-item active" aria-current="page">Send Message</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
            <form method="POST">
                <div class="row">
                    <?php
                    $start_date = $_POST['start_date'] ?? date('Y-m-d');
                    $end_date = $_POST['end_date'] ?? date('Y-m-d');
                    $policy = $_POST['policy'] ?? '';
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
                            <option value="LLR" <?= ($policy === 'LLR') ? 'selected' : '' ?>>LLR</option>
                            <option value="DL" <?= ($policy === 'DL') ? 'selected' : '' ?>>DL</option>
                        </select>
                    </div>
                    <div class="col-md-2 mt-4">
                        <button type="submit" name="submit" class="btn sub-btn1">Search</button>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn sub-btn1 mt-4" onclick="copyContacts()">Copy Contacts</button>
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
            <div>
                <textarea id="message" name="message" rows="5" cols="100" required readonly><?php echo htmlspecialchars($message); ?></textarea>
            </div>

            <div class="pt-5">
                <?php if (!empty($expiries)) : ?>
                    <div>
                        <h3>Summary :</h3>
                        <table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 600px;">
                            <thead style="background-color: #f2f2f2;">
                                <tr>
                                    <th>Policy Type</th>
                                    <th>Total Entries</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>LLR</td>
                                    <td><?php echo htmlspecialchars($motorCount); ?></td>
                                </tr>
                                <tr>
                                    <td>DL</td>
                                    <td><?php echo htmlspecialchars($nonMotorCount); ?></td>
                                </tr>
                                <tr style="font-weight: bold; background-color: #f9f9f9;">
                                    <td>Total</td>
                                    <td><?php echo htmlspecialchars($motorCount + $nonMotorCount); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <form method="POST" id="birthdayForm">
                        <input type="hidden" name="action" id="action_input">
                        <button type="button" class="sub-btn1 msg-btn mt-4 p-1" id="sendMessageBtn">Send Messages</button>
                        <button type="button" class="sub-btn1 msg-btn mt-4 p-1" id="sendWhatsAppBtn">Send WhatsApp</button>

                        <table class="table table-bordered my-5">
                            <thead>
                                <tr>
                                    <th>Select <input type="checkbox" id="select-all" /> </th>
                                    <th>Sr Number</th>
                                    <th>Client Name</th>
                                    <th>City</th>
                                    <th>Contact</th>
                                    <th>No Of Class</th>
                                    <th>Class of vehicle</th>
                                    <th>Type</th>
                                    <th>Sub Type</th>
                                    <th>Test Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($expiries)): ?>
                                    <!-- LLR Section -->
                                    <tr>
                                        <td colspan="8">
                                            <input type="checkbox" id="select-all-motor"> LLR Policies
                                        </td>
                                    </tr>
                                    <?php 
                                    $motorFound = false;
                                    foreach ($expiries as $client): 
                                        if ($client['bmds_type'] === 'LLR'):
                                            $motorFound = true;
                                    ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_clients[]" value="<?php echo $client['contact']; ?>" class="select-client select-client-motor">
                                            <input type="hidden" name="client_names[<?php echo $client['contact']; ?>]" value="<?php echo $client['client_name']; ?>">
                                            <input type="hidden" name="test_dates[<?php echo $client['contact']; ?>]" value="<?php echo $client['test_date']; ?>">
                                            <input type="hidden" name="cities[<?php echo $client['contact']; ?>]" value="<?php echo $client['city']; ?>">
                                            <input type="hidden" name="srnumbers[<?php echo $client['contact']; ?>]" value="<?php echo $client['sr_num']; ?>">
                                            <input type="hidden" name="policy_types[<?php echo $client['contact']; ?>]" value="<?php echo $client['bmds_type']; ?>">
                                            <input type="hidden" name="llr_types[<?php echo $client['contact']; ?>]" value="<?php echo $client['llr_type']; ?>">
                                        </td>
                                        <td><?php echo $client['sr_num']; ?></td>
                                        <td><?php echo $client['client_name']; ?></td>
                                        <td><?php echo $client['city']; ?></td>
                                        <td ondblclick="makeEditable(this, '<?php echo $client['contact']; ?>')">
                                            <span class="contact-display"><?php echo $client['contact']; ?></span>
                                            <input type="hidden" name="updated_contacts[<?php echo $client['contact']; ?>]" value="<?php echo $client['contact']; ?>" class="contact-input">
                                        </td>
                                        <td><?php echo $client['class']; ?></td>
                                        <td>
                                            <?php echo $client['llr_class']; ?> <br>
                                            <?php echo $client['car_type']; ?>
                                        </td>
                                        <td><?php echo $client['bmds_type']; ?></td>
                                        <td><?php echo $client['llr_type']; ?></td>
                                        <td>
                                            <?php 
                                            echo (!empty($client['test_date']) && $client['test_date'] !== '0000-00-00') 
                                                ? date('d/m/Y', strtotime($client['test_date'])) 
                                                : '00-00-0000'; 
                                            ?>
                                        </td>

                                    </tr>
                                    <?php endif; endforeach; ?>
                                    <?php if (!$motorFound): ?>
                                        <tr>
                                            <td colspan="8">No LLR policies found.</td>
                                        </tr>
                                    <?php endif; ?>
                                    
                                    <!-- DL Section -->
                                    <tr>
                                        <td colspan="8">
                                            <input type="checkbox" id="select-all-nonmotor"> DL Policies 
                                        </td>
                                    </tr>
                                    <?php 
                                    $nonMotorFound = false;
                                    foreach ($expiries as $client): 
                                        if ($client['bmds_type'] === 'DL'):
                                            $nonMotorFound = true;
                                    ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_clients[]" value="<?php echo $client['contact']; ?>" class="select-client select-client-nonmotor">
                                            <input type="hidden" name="client_names[<?php echo $client['contact']; ?>]" value="<?php echo $client['client_name']; ?>">
                                            <input type="hidden" name="test_dates[<?php echo $client['contact']; ?>]" value="<?php echo $client['test_date']; ?>">
                                            <input type="hidden" name="cities[<?php echo $client['contact']; ?>]" value="<?php echo $client['city']; ?>">
                                            <input type="hidden" name="srnumbers[<?php echo $client['contact']; ?>]" value="<?php echo $client['sr_num']; ?>">
                                            <input type="hidden" name="policy_types[<?php echo $client['contact']; ?>]" value="<?php echo $client['bmds_type']; ?>">
                                        </td>
                                        <td><?php echo $client['sr_num']; ?></td>
                                        <td><?php echo $client['client_name']; ?></td>
                                        <td><?php echo $client['city']; ?></td>
                                        <td ondblclick="makeEditable(this, '<?php echo $client['contact']; ?>')">
                                            <span class="contact-display"><?php echo $client['contact']; ?></span>
                                            <input type="hidden" name="updated_contacts[<?php echo $client['contact']; ?>]" value="<?php echo $client['contact']; ?>" class="contact-input">
                                        </td>
                                        <td><?php echo $client['class']; ?></td>
                                        <td>
                                            <?php echo $client['llr_class']; ?> <br>
                                            <?php echo $client['car_type']; ?>
                                        </td>
                                        <td><?php echo $client['bmds_type']; ?></td>
                                        <td><?php echo $client['mdl_type']; ?></td>
                                        <td>
                                            <?php 
                                            echo (!empty($client['test_date']) && $client['test_date'] !== '0000-00-00') 
                                                ? date('d/m/Y', strtotime($client['test_date'])) 
                                                : '00-00-0000'; 
                                            ?>
                                        </td>

                                    </tr>
                                    <?php endif; endforeach; ?>
                                    <?php if (!$nonMotorFound): ?>
                                        <tr>
                                            <td colspan="8">No DL policies found.</td>
                                        </tr>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8"><?php echo $message ?: "No expiries found."; ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </form>

                    <!-- Loading Spinner -->
                    <div id="loadingSpinner" style="display: none;">
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
    </div>
</section>



<script>

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



// Script For Send Messages
document.addEventListener('DOMContentLoaded', function() {
    const sendMessageBtn = document.getElementById('sendMessageBtn');
    const sendWhatsAppBtn = document.getElementById('sendWhatsAppBtn');
    const actionInput = document.getElementById('action_input');
    const birthdayForm = document.getElementById('birthdayForm');
    const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    const confirmActionBtn = document.getElementById('confirmActionBtn');
    const loadingSpinner = document.getElementById('loadingSpinner');

    let currentAction = '';

    sendMessageBtn.addEventListener('click', function() {
        currentAction = 'send_message';
        confirmationModal.show();
    });

    sendWhatsAppBtn.addEventListener('click', function() {
        currentAction = 'send_whatsapp';
        confirmationModal.show();
    });

    confirmActionBtn.addEventListener('click', function() {
        actionInput.value = currentAction;
        confirmationModal.hide();
        loadingSpinner.style.display = 'block';
        birthdayForm.submit();
    });

    // Select All checkbox for the whole table
    document.getElementById('select-all').addEventListener('change', function() {
        const isChecked = this.checked;
        document.querySelectorAll('.select-client').forEach(function(checkbox) {
            checkbox.checked = isChecked;
        });
    });

    // Select All checkbox for Motor section
    document.getElementById('select-all-motor').addEventListener('change', function() {
        const isChecked = this.checked;
        document.querySelectorAll('.select-client-motor').forEach(function(checkbox) {
            checkbox.checked = isChecked;
        });
    });

    // Select All checkbox for Nonmotor section
    document.getElementById('select-all-nonmotor').addEventListener('change', function() {
        const isChecked = this.checked;
        document.querySelectorAll('.select-client-nonmotor').forEach(function(checkbox) {
            checkbox.checked = isChecked;
        });
    });

    // Ensure individual checkboxes toggle Select All appropriately
    document.querySelectorAll('.select-client').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const allChecked = document.querySelectorAll('.select-client').length ===
                                document.querySelectorAll('.select-client:checked').length;
            document.getElementById('select-all').checked = allChecked;
        });
    });

    
});


// Edit contact for send temporary messages
    function makeEditable(td, originalValue) {
        let span = td.querySelector(".contact-display");
        let input = td.querySelector(".contact-input");

        let newInput = document.createElement("input");
        newInput.type = "text";
        newInput.value = span.innerText;
        newInput.className = "form-control";
        
        newInput.onblur = function() { 
            if (this.value.trim() === "") {
                this.value = originalValue;
            }
            span.innerText = this.value;
            input.value = this.value;
            td.innerHTML = "";
            td.appendChild(span);
            td.appendChild(input);
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