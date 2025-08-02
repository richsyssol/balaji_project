<?php 
    include 'include/header.php'; 
    include 'include/head.php'; 
    include 'session_check.php';
?>



<?php
// Database connection settings
include 'includes/db_conn.php';


// Function to fetch birthday data
function fetchReminders($startDate = null, $endDate = null){
    global $conn;
    $query = "";

    // If no date range is provided, fetch today's anniversaries
    if (!$startDate && !$endDate) {
        $query = "SELECT client_name, contact, day, address FROM mf_entries 
                    WHERE is_deleted = 0 AND DAY(day) = DAY(CURRENT_DATE)";
    } else {
        $startDay = date('d', strtotime($startDate));
        $endDay = date('d', strtotime($endDate));

        // Handle date range
        if ($startDay <= $endDay) {
            $query = "SELECT client_name, contact, day, address FROM mf_entries 
                        WHERE is_deleted = 0 AND DAY(day) BETWEEN $startDay AND $endDay
                        ORDER BY DAY(day) ASC";
        } else {
            $query = "SELECT client_name, contact, day, address FROM mf_entries 
                        WHERE is_deleted = 0 AND (DAY(day) BETWEEN $startDay AND 31)
                        OR (DAY(day) BETWEEN 1 AND $endDay)
                        ORDER BY DAY(day) ASC";
        }
    }

    return mysqli_query($conn, $query);
}



// Function to send SMS
function sendReminders($contact, $client_name,$expiryDate)
{


    // Extract day (dd) from the given expiry date
    $day = date('d', strtotime($expiryDate));

    // Replace with your actual username, password, sender ID from SMSJust
    $username = 'Balajimotor@999'; // Replace with your actual SMSJust username
    $password = 'Balajimotor@999'; // Replace with your actual SMSJust password
    $senderId = 'BMDSCH'; // Sender ID as per your SMSJust settings
    $message = "Dear Sir/Ma'am, This is a reminder that your Mutual Fund Payment is due on $day. Please contact Balaji Motor Driving School. M: 9881712967 / 9881063639";
    
    // Execute the cURL request asynchronously using exec()
    $command = "curl -s '$url' > /dev/null 2>/dev/null &";
    exec($command);

    // Construct the URL with the actual parameters
    $url = "https://www.smsjust.com/sms/user/urlsms.php?username=$username&pass=$password&senderid=$senderId&dest_mobileno=$contact&msgtype=TXT&message=" . urlencode($message) . "&response=Y";

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

// Function to send WhatsApp messages By Updated API
function sendWhatsAppMessage($contact, $client_name, $expiryDate) {
    // API Configuration
    $apiUrl = 'https://partners.pinbot.ai/v1/messages';
    $apiKey = 'fbae4610-2023-11f0-ad4f-92672d2d0c2d'; // Your Pinbot API Key
    $phoneNumberId = '919422246469'; // e.g., "919594515799"
    
    // Validate inputs
    if (empty($contact) || empty($expiryDate)) {
        return "Error: Contact number or expiry date cannot be empty";
    }

    // JSON Payload (as per API docs)
    $data = [
        "messaging_product" => "whatsapp",
        "recipient_type" => "individual",
        "to" => $contact, // e.g., "919594515799"
        "type" => "template",
        "template" => [
            "language" => ["code" => "en"], // English
            "name" => "mfremindermsg", // Your approved template name
            "components" => [
                [
                    "type" => "body",
                    "parameters" => [
                        ["type" => "text", "text" => $expiryDate] // Dynamic name
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
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if (curl_errno($curl)) {
        $error = curl_error($curl);
        curl_close($curl);
        return "CURL Error: $error";
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

        // Show message on screen
        $message = "Dear Sir/Ma'am, This is a reminder that your Mutual Fund Payment is due on (Day). Please contact Balaji Motor Driving School. M: 9881712967 / 9881063639";
        
        // Fetch anniversaries based on the date range
        $result = fetchReminders($startDate, $endDate);
        $birthdays = [];

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $birthdays[] = $row;
            }
        } else {
            $message = "No anniversaries found for the selected date range.";
        }
    }

    if ($action === 'send_message' || $action === 'send_whatsapp') {
        $selectedClients = $_POST['selected_clients'] ?? [];
        $clientNames = $_POST['client_names'] ?? [];
        $expiryDates = $_POST['expiry_dates'] ?? [];
        $updatedContacts = $_POST['updated_contacts'] ?? []; // Capturing updated contacts
        $interval = isset($_POST['interval']) ? (int)$_POST['interval'] : null;
        $customContact = $_POST['custom_contact'] ?? '';  // Get custom contact number
    
        if ((!empty($selectedClients) && $interval !== null) || !empty($customContact)) {
            $currentDay = (int)date('d'); // Current day of the month
            $message = "";
    
            // Handle the custom contact number if entered
            if (!empty($customContact)) {
                $clientName = "Custom Client"; // Use a generic name for custom clients
                if ($action === 'send_message') {
                    $response = sendReminders($customContact, $clientName, null);
                    $message .= "SMS sent to $clientName ($customContact): $response<br>";
                } elseif ($action === 'send_whatsapp') {
                    $response = sendWhatsAppMessage($customContact, $clientName, null);
                    $message .= "WhatsApp Message sent to $clientName ($customContact): $response<br>";
                }
            }
    
            // Split selected clients into batches of 50
            $chunkedClients = array_chunk($selectedClients, 50);
    
            // Loop through each batch of 50 clients
            foreach ($chunkedClients as $clientGroup) {
                foreach ($clientGroup as $originalContact) {
                    $newContact = $updatedContacts[$originalContact] ?? $originalContact; // Use edited number if available
                    $clientName = $clientNames[$originalContact] ?? 'Unknown';
                    $expiryDate = $expiryDates[$originalContact] ?? null;
    
                    if ($expiryDate) {
                        $expiryDay = (int)date('d', strtotime($expiryDate)); // Extract the day of the month from expiry date
                        $dayDiff = $expiryDay - $currentDay;
    
                        // Adjust for days spanning between months
                        if ($dayDiff < 0) {
                            $dayDiff += 31; // Assuming a 31-day month for simplicity
                        }
    
                        if (($interval === 0 && $expiryDay === $currentDay) || 
                            ($dayDiff === $interval)) {
    
                            if ($action === 'send_message') {
                                $response = sendReminders($newContact, $clientName, $expiryDate);
                                $message .= "SMS sent to $clientName ($newContact): $response<br>";
                            } elseif ($action === 'send_whatsapp') {
                                $response = sendWhatsAppMessage($newContact, $clientName, $expiryDate);
                                $message .= "WhatsApp Message sent to $clientName ($newContact): $response<br>";
                            }
                        }
                    }
                }
            }
    
            if (empty($message)) {
                $message = "No messages sent. Ensure the selected clients have expiry dates matching the selected interval.";
            }
        } else {
            $message = "Please select clients and an interval to send messages.";
        }
    
        $_SESSION['message'] = $message;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
else {
    
    // Show message on screen
    $message = "Dear Sir/Ma'am, This is a reminder that your Mutual Fund Payment is due on (Day). Please contact Balaji Motor Driving School. M: 9881712967 / 9881063639";

    // Default: Fetch today's birthdays
    $result = fetchReminders();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $birthdays[] = $row;
        }
    }
}

mysqli_close($conn);

?>


<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container data-table p-5 ">
        <div class="ps-5">
            <div>
                <h1>SEND REMINDER</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="mf">MF</a></li>
                <li class="breadcrumb-item active" aria-current="page">SEND REMINDER</li>
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
                    <!-- Copy All Contacts Button -->
                    <button type="button" class="btn sub-btn1 mt-4" onclick="copyContacts()">Copy Contacts</button>
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

        
           

            <!-- show data -->
            <form method="POST" id="birthdayForm">
                <h4>Send Message For Reminder:</h4>

                

                 <!-- show message on screen -->
                 <div>
                    <textarea id="message" name="message" rows="4" cols="60" required readonly><?php echo htmlspecialchars($message); ?></textarea>
                </div>

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
                            <th>Pay Day</th>
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
                                        <input type="hidden" name="expiry_dates[<?php echo $client['contact']; ?>]" value="<?php echo $client['day']; ?>">
                                    </td>
                                    <td><?php echo $client['client_name']; ?></td>
                                    <td ondblclick="makeEditable(this, '<?php echo $client['contact']; ?>')">
                                        <span class="contact-display"><?php echo $client['contact']; ?></span>
                                        <input type="hidden" name="updated_contacts[<?php echo $client['contact']; ?>]" value="<?php echo $client['contact']; ?>" class="contact-input">
                                    </td>
                                    <td><?php echo date('d', strtotime($client['day'])); ?></td>
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


                <div class="col-md-3 field">
                    <label for="interval">Select Interval:</label>
                    <select id="interval" name="interval" class="form-select">
                        <option value="">--Select Interval--</option>
                        <option value="5">5 Days Before</option>
                        <option value="2">2 Days Before</option>
                        <option value="0">On Expiry Date</option>
                    </select>
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
            <p>No Remiders to display.</p>
        <?php endif; ?>
    </div>
    </div>
</section>

<!-- Password Prompt Modal for copy contact-->
<div class="modal fade" id="copycontact" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="passwordForm">
        <div class="modal-header">
          <h5 class="modal-title" id="passwordModalLabel">Enter Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="password" id="copyPassword" class="form-control" placeholder="Enter password" required>
          <div id="passwordError" class="text-danger mt-2" style="display:none;">Invalid password!</div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Submit</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>

// Script for copy contacts    

// script for copy contacts to clipboard
function copyContacts() {
    const contactMap = new Map();
    const invalidContacts = [];
    const validContacts = [];
    let duplicateCount = 0;

    document.querySelectorAll('tbody tr').forEach(row => {
        const contact = row.querySelector('td:nth-child(3)')?.innerText.trim();
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