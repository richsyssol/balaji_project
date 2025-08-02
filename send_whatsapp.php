<?php 
    include 'include/header.php'; 
    include 'include/head.php'; 
    include 'session_check.php';
?>

<?php
// Database connection settings
include 'includes/db_conn.php';

// Function to fetch client data
function fetchClientData($startDate = null, $endDate = null, $address = '') 
{
    global $conn;

    function convertToDBDate($date)
    {
        $dateObj = DateTime::createFromFormat('Y-m-d', $date);
        return $dateObj ? $dateObj->format('Y-m-d') : false;
    }

    $startDateFormatted = $startDate ? convertToDBDate($startDate) : null;
    $endDateFormatted = $endDate ? convertToDBDate($endDate) : null;

    if (($startDate && !$startDateFormatted) || ($endDate && !$endDateFormatted)) {
        return false; // invalid date
    }

    $query = "SELECT client_name, contact, policy_date, address
              FROM client 
              WHERE is_deleted = 0";

    if ($startDateFormatted && $endDateFormatted) {
        $query .= " AND DATE(policy_date) BETWEEN '$startDateFormatted' AND '$endDateFormatted'";
    } elseif ($startDateFormatted) {
        $query .= " AND DATE(policy_date) = '$startDateFormatted'";
    }

    if (!empty($address)) {
        $query .= " AND address LIKE '%" . mysqli_real_escape_string($conn, $address) . "%'";
    }
    
    $query .= " ORDER BY policy_date DESC";

    return mysqli_query($conn, $query);
}

// WhatsApp API functions
function sendWhatsAppTemplate($contact, $client_name, $templateName)
{
    $apiUrl = 'https://partners.pinbot.ai/v1/messages';
    $apiKey = 'fbae4610-2023-11f0-ad4f-92672d2d0c2d';
    $phoneNumberId = '919422246469';

    $data = [
        "messaging_product" => "whatsapp",
        "recipient_type" => "individual",
        "to" => $contact,
        "type" => "template",
        "template" => [
            "name" => $templateName,
            "language" => ["code" => "en"],
            
        ]
    ];

    return sendCurlRequest($apiUrl, $data, $apiKey, $phoneNumberId);
}

function sendWhatsAppMedia($contact, $mediaUrl, $templateName)
{
    $apiUrl = 'https://partners.pinbot.ai/v1/messages';
    $apiKey = 'fbae4610-2023-11f0-ad4f-92672d2d0c2d';
    $phoneNumberId = '919422246469';

    $data = [
        "messaging_product" => "whatsapp",
        "recipient_type" => "individual",
        "to" => $contact,
        "type" => "template",
        "template" => [
            "language" => ["code" => "en"],
            "name" => $templateName, // You can set a default template name here
            "components" => [
                [
                    "type" => "header",
                    "parameters" => [
                        [
                            "type" => "image",
                            "image" => [
                                "link" => $mediaUrl
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    return sendCurlRequest($apiUrl, $data, $apiKey, $phoneNumberId);
}
function sendCurlRequest($url, $data, $apiKey, $wanumber)
{
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'apikey:' . $apiKey,
            'wanumber:' . $wanumber
        ]
    ]);
    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);
    
    return $error ? ["error" => $error] : json_decode($response, true);
}

// Handle form submission
$clients = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        // Handle search form
        $startDate = $_POST['start_date'] ?? date('Y-m-d');
        $endDate = $_POST['end_date'] ?? date('Y-m-d');
        
        $result = fetchClientData($startDate, $endDate);
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $clients[] = $row;
            }
        } else {
            $_SESSION['message'] = "No clients found for the selected date range.";
        }
    }
    elseif (isset($_POST['action']) && $_POST['action'] === 'send_whatsapp') {
        // Handle WhatsApp sending
        $selectedClients = $_POST['selected_clients'] ?? [];
        $clientNames = $_POST['client_names'] ?? [];
        $updatedContacts = $_POST['updated_contacts'] ?? [];
        $customContact = $_POST['custom_contact'] ?? '';
        $messageType = $_POST['message_type'] ?? 'template';
        $templateName = $_POST['template_name'] ?? '';
        $mediaUrl = $_POST['media_url'] ?? '';

        $responses = [];
        
        if (!empty($customContact)) {
            $response = ($messageType === 'media')
    ? sendWhatsAppMedia($contact, $mediaUrl, $templateName)
    : sendWhatsAppTemplate($contact, $name, $templateName);
            
            $responses[] = "Custom Contact ($customContact): " . json_encode($response);
        }

        foreach ($selectedClients as $originalContact) {
            $contact = $updatedContacts[$originalContact] ?? $originalContact;
            $name = $clientNames[$originalContact] ?? 'Unknown';
            
            $response = ($messageType === 'media')
    ? sendWhatsAppMedia($contact, $mediaUrl, $templateName)
    : sendWhatsAppTemplate($contact, $name, $templateName);
            
            $responses[] = "$name ($contact): " . json_encode($response);
            
            // Rate limiting - 1 second delay between messages
            sleep(1);
        }

        $_SESSION['message'] = implode("<br>", $responses);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    elseif (isset($_POST['generate_csv'])) {
        // Handle CSV export
        $admin_password = $_POST['admin_password'] ?? '';
        
        $sql = "SELECT password FROM file WHERE file_type = 'CSV' LIMIT 1";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($admin_password, $row['password'])) {
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename=anniversary_clients.csv');
                
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Client Name', 'Contact', 'Policy Date', 'Address']);
                
                $result = fetchClientData($_POST['start_date'] ?? null, $_POST['end_date'] ?? null);
                while ($row = mysqli_fetch_assoc($result)) {
                    fputcsv($output, [
                        $row['client_name'],
                        $row['contact'],
                        $row['policy_date'],
                        $row['address']
                    ]);
                }
                exit();
            } else {
                $_SESSION['message'] = "Incorrect password!";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    }
    elseif (isset($_POST['generate_pdf'])) {
        // Handle PDF export
        $admin_password = $_POST['admin_password'] ?? '';
        
        $sql = "SELECT password FROM file WHERE file_type = 'PDF' LIMIT 1";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($admin_password, $row['password'])) {
                require('fpdf/fpdf.php');
                
                $pdf = new FPDF('L', 'mm', 'A4');
                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 16);
                $pdf->Cell(0, 10, 'Anniversary Clients Report', 0, 1, 'C');
                
                // Column headers
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(60, 10, 'Client Name', 1);
                $pdf->Cell(50, 10, 'Contact', 1);
                $pdf->Cell(40, 10, 'Policy Date', 1);
                $pdf->Cell(60, 10, 'Address', 1);
                $pdf->Ln();
                
                // Data rows
                $pdf->SetFont('Arial', '', 10);
                $result = fetchClientData($_POST['start_date'] ?? null, $_POST['end_date'] ?? null);
                while ($row = mysqli_fetch_assoc($result)) {
                    $pdf->Cell(60, 10, $row['client_name'], 1);
                    $pdf->Cell(50, 10, $row['contact'], 1);
                    $pdf->Cell(40, 10, date('d/m/Y', strtotime($row['policy_date'])), 1);
                    $pdf->Cell(60, 10, $row['address'], 1);
                    $pdf->Ln();
                }
                
                $pdf->Output('D', 'anniversary_clients.pdf');
                exit();
            } else {
                $_SESSION['message'] = "Incorrect password!";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    }
}

// Default: Fetch today's clients if no search performed
if (empty($clients) && !isset($_POST['submit'])) {
    $result = fetchClientData(date('Y-m-d'), date('Y-m-d'));
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $clients[] = $row;
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
                <h1>Client Data</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="client">Client</a></li>
                <li class="breadcrumb-item active" aria-current="page">Client Data</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
            <form method="POST">
                <div class="row">
                    <div class="col-md-2 field">
                        <label for="start_date">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" 
                               value="<?= isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-2 field">
                        <label for="end_date">End Date:</label>
                        <input type="date" id="end_date" name="end_date" 
                               value="<?= isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="submit" class="sub-btn1 mt-4 p-1">Search</button>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn sub-btn1 mt-4" data-bs-toggle="modal" data-bs-target="#csvModal">
                            EXCEL
                        </button>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn sub-btn1 mt-4" data-bs-toggle="modal" data-bs-target="#pdfModal">
                            PDF
                        </button>
                    </div>
                </div>
            </form>

            <?php if (isset($_SESSION['message']) && !empty($_SESSION['message'])): ?>
                <div class="alert alert-info mt-3">
                    <?php echo htmlspecialchars($_SESSION['message']); ?>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <div class="pt-5">
                <?php if (!empty($clients)) : ?>
             

                    <div class="mb-3">
                        <h5>Summary:</h5>
                        <p>Total Entries: <?php echo count($clients); ?></p>
                    </div>

                    <form method="POST" id="anniversaryForm">
                        <input type="hidden" name="action" value="send_whatsapp">
                        
                        <div class="row mb-3">
                            <div class="col-md-4 field">
                                <label for="message_type">Message Type:</label>
                                <select name="message_type" id="message_type" class="form-control" required>
                                    <option value="template">Template Message</option>
                                    <option value="media">Media Message</option>
                                </select>
                            </div>
                            <div class="col-md-4 field" id="template_name_group">
                                <label for="template_name">Template Name:</label>
                                <input type="text" name="template_name" class="form-control" placeholder="e.g., demo_msg" required>
                            </div>
                            <div class="col-md-4 field d-none" id="media_details_group">
                                <label for="media_url">Media URL:</label>
                                <input type="url" name="media_url" class="form-control" placeholder="https://example.com/image.jpg">
                            </div>
                        </div>

                        <button type="button" id="sendBtn" class="btn btn-primary mb-3">Send WhatsApp Messages</button>
                        <button type="button" id="selectAllBtn" class="btn btn-secondary mb-3">Select/Deselect All</button>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th width="5%">Select</th>
                                        <th width="15%">Policy Date</th>
                                        <th width="25%">Client Name</th>
                                        <th width="25%">Contact (Double-click to edit)</th>
                                        <th width="30%">Address</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clients as $client): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="selected_clients[]" value="<?php echo htmlspecialchars($client['contact']); ?>" class="client-checkbox">
                                                <input type="hidden" name="client_names[<?php echo htmlspecialchars($client['contact']); ?>]" value="<?php echo htmlspecialchars($client['client_name']); ?>">
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($client['policy_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($client['client_name']); ?></td>
                                            <td ondblclick="makeEditable(this, '<?php echo htmlspecialchars($client['contact']); ?>')">
                                                <span class="contact-display"><?php echo htmlspecialchars($client['contact']); ?></span>
                                                <input type="hidden" name="updated_contacts[<?php echo htmlspecialchars($client['contact']); ?>]" value="<?php echo htmlspecialchars($client['contact']); ?>" class="contact-input">
                                            </td>
                                            <td><?php echo htmlspecialchars($client['address']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <div id="loadingSpinner" class="text-center my-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Sending messages, please wait...</p>
                    </div>

                    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Confirm Message Sending</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to send messages to the selected clients?</p>
                                    <p id="selectedCount">0 clients selected</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary" id="confirmSend">Send Messages</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">No clients found for the selected criteria.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- CSV Export Modal -->
<div class="modal fade" id="csvModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export to CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="start_date" value="<?= isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : date('Y-m-d') ?>">
                    <input type="hidden" name="end_date" value="<?= isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : date('Y-m-d') ?>">
                    <div class="mb-3">
                        <label for="csvPassword" class="form-label">Admin Password</label>
                        <input type="password" class="form-control" id="csvPassword" name="admin_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="generate_csv" class="btn btn-primary">Export</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- PDF Export Modal -->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export to PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="start_date" value="<?= isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : date('Y-m-d') ?>">
                    <input type="hidden" name="end_date" value="<?= isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : date('Y-m-d') ?>">
                    <div class="mb-3">
                        <label for="pdfPassword" class="form-label">Admin Password</label>
                        <input type="password" class="form-control" id="pdfPassword" name="admin_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="generate_pdf" class="btn btn-primary">Export</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle between template and media fields
document.getElementById('message_type').addEventListener('change', function() {
    if (this.value === 'template') {
        document.getElementById('template_name_group').classList.remove('d-none');
        document.getElementById('media_details_group').classList.add('d-none');
        document.querySelector('[name="template_name"]').required = true;
        document.querySelector('[name="media_url"]').required = false;
    } else {
        document.getElementById('media_details_group').classList.remove('d-none');
        document.querySelector('[name="template_name"]').required = false;
        document.querySelector('[name="media_url"]').required = true;
    }
});

// Select/Deselect all clients
document.getElementById('selectAllBtn').addEventListener('click', function() {
    const checkboxes = document.querySelectorAll('.client-checkbox');
    const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });
});

// Update selected count when checkboxes change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('client-checkbox')) {
        updateSelectedCount();
    }
});

function updateSelectedCount() {
    const selected = document.querySelectorAll('.client-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = `${selected} clients selected`;
}

// Make contact field editable
function makeEditable(td, originalValue) {
    const span = td.querySelector('.contact-display');
    const input = td.querySelector('.contact-input');
    const currentValue = span.textContent;
    
    const inputField = document.createElement('input');
    inputField.type = 'text';
    inputField.className = 'form-control form-control-sm';
    inputField.value = currentValue;
    
    td.innerHTML = '';
    td.appendChild(inputField);
    inputField.focus();
    
    function saveValue() {
        const newValue = inputField.value.trim() || originalValue;
        span.textContent = newValue;
        input.value = newValue;
        
        td.innerHTML = '';
        td.appendChild(span);
        td.appendChild(input);
    }
    
    inputField.addEventListener('blur', saveValue);
    inputField.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            saveValue();
        }
    });
}

// Handle message sending
document.getElementById('sendBtn').addEventListener('click', function() {
    const selectedCount = document.querySelectorAll('.client-checkbox:checked').length;
    const customContact = document.getElementById('custom_contact').value;
    
    if (selectedCount === 0 && !customContact) {
        alert('Please select at least one client or enter a custom contact number');
        return;
    }
    
    updateSelectedCount();
    const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    modal.show();
});

document.getElementById('confirmSend').addEventListener('click', function() {
    document.getElementById('loadingSpinner').style.display = 'block';
    document.getElementById('anniversaryForm').submit();
});

// Close modals after export
const exportModals = ['csvModal', 'pdfModal'];
exportModals.forEach(modalId => {
    const modal = document.getElementById(modalId);
    modal.addEventListener('hidden.bs.modal', function() {
        window.location.reload();
    });
});
</script>

<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>