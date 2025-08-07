<?php
// Database connection
include 'includes/db_conn.php';


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate_bill'])) {
        generateBill($conn);
    }
}

// Function to generate bill
function generateBill($conn) {
    $client_id = $conn->real_escape_string($_POST['client_id']);
    $amount = $conn->real_escape_string($_POST['amount']);
    $bill_date = $conn->real_escape_string($_POST['bill_date']);
    
    // Get client details
    $client_query = $conn->query("SELECT * FROM client WHERE id = '$client_id'");
    
    if (!$client_query) {
        echo "<script>alert('Error fetching client: " . $conn->error . "');</script>";
        return;
    }
    
    $client = $client_query->fetch_assoc();
    
    if (!$client) {
        echo "<script>alert('Client not found');</script>";
        return;
    }
    
    // Create bill image (simplified for this example)
    $bill_image = generateBillImage($client, $amount, $bill_date);
    
    // Store bill in database
    $stmt = $conn->prepare("INSERT INTO bills (client_id, bill_date, bill_image, amount) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        echo "<script>alert('Prepare failed: " . $conn->error . "');</script>";
        return;
    }
    
    $null = NULL;
    $stmt->bind_param("isbs", $client_id, $bill_date, $null, $amount);
    $stmt->send_long_data(2, $bill_image);
    
    if ($stmt->execute()) {
        echo "<script>alert('Bill generated successfully');</script>";
    } else {
        echo "<script>alert('Error generating bill: " . $stmt->error . "');</script>";
    }
}

// Function to generate bill image (simplified)
function generateBillImage($client, $amount, $date) {
    $bill_text = "BILL RECEIPT\n";
    $bill_text .= "----------------------------\n";
    $bill_text .= "Date: $date\n";
    $bill_text .= "Client: {$client['client_name']}\n";
    $bill_text .= "Contact: {$client['contact']}\n";
    $bill_text .= "Address: {$client['address']}\n";
    $bill_text .= "Amount: $amount\n";
    $bill_text .= "----------------------------\n";
    $bill_text .= "Thank you for your business!";
    
    return $bill_text;
}

// Function to fetch client suggestions
function getClientSuggestions($conn, $search) {
    $search = $conn->real_escape_string($search);
    $query = $conn->query("SELECT id, client_name FROM client WHERE client_name LIKE '%$search%' LIMIT 5");
    
    if (!$query) {
        return ['error' => $conn->error];
    }
    
    $suggestions = [];
    while ($row = $query->fetch_assoc()) {
        $suggestions[] = $row;
    }
    
    return $suggestions;
}

// AJAX handler for client search
if (isset($_GET['search_clients'])) {
    $search = $_GET['search_term'] ?? '';
    $suggestions = getClientSuggestions($conn, $search);
    echo json_encode($suggestions);
    exit;
}

// Get all bills for display
$bills_query = $conn->query("
    SELECT b.id, b.bill_date, b.amount, c.client_name 
    FROM bills b
    JOIN client c ON b.client_id = c.id
    ORDER BY b.bill_date DESC
");

if (!$bills_query) {
    die("Error fetching bills: " . $conn->error);
}

$bills = [];
while ($row = $bills_query->fetch_assoc()) {
    $bills[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select, button { padding: 8px; width: 100%; max-width: 300px; }
        #client_suggestions { border: 1px solid #ddd; max-height: 200px; overflow-y: auto; display: none; position: absolute; background: white; z-index: 1000; width: 300px; }
        .suggestion-item { padding: 8px; cursor: pointer; }
        .suggestion-item:hover { background-color: #f0f0f0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Billing System</h1>
        
        <h2>Generate New Bill</h2>
        <form method="POST" id="bill_form">
            <div class="form-group">
                <label for="client_search">Search Client:</label>
                <input type="text" id="client_search" placeholder="Type client name...">
                <div id="client_suggestions"></div>
                <input type="hidden" name="client_id" id="client_id">
                <div id="search_error" class="error"></div>
            </div>
            
            <div class="form-group" id="client_details" style="display: none;">
                <h3>Client Details</h3>
                <p><strong>Name:</strong> <span id="client_name"></span></p>
                <p><strong>Contact:</strong> <span id="client_contact"></span></p>
                <p><strong>Address:</strong> <span id="client_address"></span></p>
            </div>
            
            <div class="form-group">
                <label for="amount">Amount:</label>
                <input type="number" name="amount" id="amount" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="bill_date">Bill Date:</label>
                <input type="date" name="bill_date" id="bill_date" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <button type="submit" name="generate_bill">Generate Bill</button>
        </form>
        
        <h2>Generated Bills</h2>
        <?php if (isset($bills) && !empty($bills)): ?>
        <table>
            <thead>
                <tr>
                    <th>Bill ID</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Amount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bills as $bill): ?>
                <tr>
                    <td><?php echo $bill['id']; ?></td>
                    <td><?php echo $bill['bill_date']; ?></td>
                    <td><?php echo $bill['client_name']; ?></td>
                    <td><?php echo number_format($bill['amount'], 2); ?></td>
                    <td><a href="view_bill.php?id=<?php echo $bill['id']; ?>" target="_blank">View Bill</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No bills generated yet.</p>
        <?php endif; ?>
    </div>

    <script>
        // Client search functionality
        document.getElementById('client_search').addEventListener('input', function() {
            const searchTerm = this.value.trim();
            if (searchTerm.length < 2) {
                document.getElementById('client_suggestions').style.display = 'none';
                return;
            }
            
            fetch(`billing_system.php?search_clients=1&search_term=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(data => {
                    const suggestionsDiv = document.getElementById('client_suggestions');
                    suggestionsDiv.innerHTML = '';
                    
                    if (data.error) {
                        document.getElementById('search_error').textContent = data.error;
                        return;
                    }
                    
                    if (data.length > 0) {
                        data.forEach(client => {
                            const div = document.createElement('div');
                            div.className = 'suggestion-item';
                            div.textContent = client.client_name;
                            div.dataset.id = client.id;
                            div.addEventListener('click', function() {
                                selectClient(client.id);
                            });
                            suggestionsDiv.appendChild(div);
                        });
                        suggestionsDiv.style.display = 'block';
                    } else {
                        suggestionsDiv.style.display = 'none';
                    }
                })
                .catch(error => {
                    document.getElementById('search_error').textContent = 'Error fetching suggestions';
                    console.error('Error:', error);
                });
        });
        
        // Select client from suggestions
        function selectClient(clientId) {
            fetch(`client_details.php?id=${clientId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(client => {
                    if (client.error) {
                        document.getElementById('search_error').textContent = client.error;
                        return;
                    }
                    
                    document.getElementById('client_id').value = client.id;
                    document.getElementById('client_search').value = client.client_name;
                    document.getElementById('client_name').textContent = client.client_name;
                    document.getElementById('client_contact').textContent = client.contact;
                    document.getElementById('client_address').textContent = client.address;
                    document.getElementById('client_details').style.display = 'block';
                    document.getElementById('client_suggestions').style.display = 'none';
                    document.getElementById('search_error').textContent = '';
                })
                .catch(error => {
                    document.getElementById('search_error').textContent = 'Error fetching client details';
                    console.error('Error:', error);
                });
        }
        
        // Close suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.id !== 'client_search') {
                document.getElementById('client_suggestions').style.display = 'none';
            }
        });
    </script>
</body>
</html>