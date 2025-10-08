<?php
include 'include/head.php'; 
include 'session_check.php';

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
    $bill_date = $conn->real_escape_string($_POST['bill_date']);
    $tax_rate = $conn->real_escape_string($_POST['tax_rate']);
    
    // Process items - FIXED: Get item_price from form
    $item_price = isset($_POST['item_price']) ? floatval($_POST['item_price']) : 0;
    $item_description = $conn->real_escape_string($_POST['item_description']);
    
    // Calculate subtotal
    $subtotal = $item_price;
    
    // Calculate tax and total
    $tax = $subtotal * ($tax_rate / 100);
    $total = $subtotal + $tax;
    
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
    
    // Create items array for bill data
    $items = [[
        'description' => $item_description,
        'unit_price' => $item_price,
        'amount' => $item_price
    ]];
    
    // Create bill data
    $bill_data = generateBillData($client, $items, $subtotal, $tax, $tax_rate, $total, $bill_date);
    
    // Store bill in database
    $stmt = $conn->prepare("INSERT INTO bills (client_id, bill_date, bill_data, amount) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        echo "<script>alert('Prepare failed: " . $conn->error . "');</script>";
        return;
    }
    
    $null = NULL;
    $stmt->bind_param("isbd", $client_id, $bill_date, $null, $total);
    $stmt->send_long_data(2, $bill_data);
    
    if ($stmt->execute()) {
        // Redirect to bills list page after successful creation
        header("Location: bills_list.php?success=Bill generated successfully");
        exit();
    } else {
        echo "<script>alert('Error generating bill: " . $stmt->error . "');</script>";
    }
}

// Function to generate bill data - FIXED: Corrected parameters
function generateBillData($client, $items, $subtotal, $tax, $tax_rate, $total, $bill_date) {
    $bill_data = [
        'items' => $items,
        'subtotal' => $subtotal,
        'tax' => $tax,
        'tax_rate' => $tax_rate,
        'total' => $total,
        'bill_date' => $bill_date,
        'client' => [
            'name' => $client['client_name'],
            'contact' => $client['contact'],
            'address' => $client['address'],
            'email' => $client['email'] ?? ''
        ]
    ];
    
    return json_encode($bill_data);
}
?>

<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container data-table p-5 ">
        <div class="ps-5">
            <div>
                <h1>BILLS</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">BILLS</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">

    <style>
    .suggestion-item { 
        padding: 10px; 
        cursor: pointer; 
        border-bottom: 1px solid #eee;
        transition: background-color 0.2s;
    }
    .suggestion-item:hover { 
        background-color: #f8f9fa; 
    }
    .suggestion-item .small {
        font-size: 12px;
    }
    #client_suggestions { 
        border: 1px solid #ddd; 
        max-height: 250px; 
        overflow-y: auto; 
        display: none; 
        position: absolute; 
        background: white; 
        z-index: 1000; 
        width: 100%; 
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        border-radius: 4px;
    }
    .bill-summary {
        background-color: #f9f9f9;
        padding: 15px;
        border-radius: 4px;
        margin: 20px 0;
    }
    .bill-summary .total {
        font-weight: bold;
        font-size: 18px;
        border-top: 2px solid #0d6efd;
        padding-top: 10px;
        margin-top: 10px;
    }
    .item-row {
        margin-bottom: 10px;
    }
</style>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="card-title mb-0"><i class="bi bi-receipt"></i> Generate New Bill</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="bill_form">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="client_search" class="form-label">Search Client</label>
            <input type="text" class="form-control" id="client_search" placeholder="Type client name...">
            <div id="client_suggestions" class="mt-1"></div>
            <input type="hidden" name="client_id" id="client_id" required>
            <div id="search_error" class="text-danger small mt-1"></div>
        </div>
    </div>

    <div class="mb-3" id="client_details" style="display: none;">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Client Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Name:</strong> <span id="client_name"></span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Primary Contact:</strong> <span id="client_contact"></span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Alternate Contact:</strong> <span id="client_contact_alt"></span></p>
                    </div>
                    <div class="col-md-12">
                        <p><strong>Address:</strong> <span id="client_address"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <h5 class="mb-3">Bill Items</h5>
        <div id="bill_items">
            <div class="item-row row g-2 mb-2">
                <div class="col-md-8">
                    <input type="text" class="form-control" name="item_description" placeholder="Description" required>
                </div>
                <div class="col-md-3">
                    <input type="number" class="form-control" name="item_price" id="item_price" placeholder="Amount" step="0.01" min="0" required>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-3 mb-3">
            <label for="tax_rate" class="form-label">Tax Rate (%)</label>
            <input type="number" class="form-control" name="tax_rate" id="tax_rate" step="0.01" value="0" min="0">
        </div>
        <div class="col-md-6 mb-3">
            <label for="bill_date" class="form-label">Bill Date</label>
            <input type="date" class="form-control" name="bill_date" id="bill_date" required value="<?php echo date('Y-m-d'); ?>">
        </div>
    </div>
        
    <div class="bill-summary">
        <h5 class="mb-3">Bill Summary</h5>
        <div class="row">
            <div class="col-md-6">
                <p>Subtotal: ₹<span id="subtotal">0.00</span></p>
                <p>Tax (<span id="tax_rate_display">0</span>%): ₹<span id="tax_amount">0.00</span></p>
                <p class="total fw-bold">Total: ₹<span id="total_amount">0.00</span></p>
            </div>
        </div>
    </div>
    
    <div class="d-flex gap-2">
        <button type="submit" name="generate_bill" class="btn btn-success"><i class="bi bi-check-circle"></i> Generate Bill</button>
        <a href="bills_list.php" class="btn btn-primary"><i class="bi bi-list-ul"></i> View Bills</a>
    </div>
</form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </section>

    <script>
        // Client search functionality
        document.getElementById('client_search').addEventListener('input', function() {
            const searchTerm = this.value.trim();
            if (searchTerm.length < 2) {
                document.getElementById('client_suggestions').style.display = 'none';
                return;
            }
            
            fetch(`ajax_handlers.php?search_clients=1&search_term=${encodeURIComponent(searchTerm)}`)
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
                            div.className = 'suggestion-item p-2 border-bottom';
                            div.style.cursor = 'pointer';
                            
                            div.innerHTML = `
                                <div><strong>${client.client_name}</strong></div>
                                <div class="small text-muted">
                                    ${client.contact ? client.contact : ''}
                                    ${client.contact_alt ? ' / ' + client.contact_alt : ''}
                                </div>
                            `;
                            
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
            fetch(`ajax_handlers.php?client_details=1&client_id=${clientId}`)
                .then(response => response.json())
                .then(client => {
                    if (client.error) {
                        document.getElementById('search_error').textContent = client.error;
                        return;
                    }
                    
                    document.getElementById('client_id').value = client.id;
                    document.getElementById('client_search').value = client.client_name;
                    document.getElementById('client_name').textContent = client.client_name;
                    document.getElementById('client_contact').textContent = client.contact || 'N/A';
                    document.getElementById('client_contact_alt').textContent = client.contact_alt || 'N/A';
                    document.getElementById('client_address').textContent = client.address || 'N/A';
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

        // Calculate bill totals
        function calculateBill() {
            let subtotal = 0;
            
            // Calculate subtotal from item price
            const itemPrice = parseFloat(document.getElementById('item_price').value) || 0;
            subtotal = itemPrice;
            
            // Calculate tax 
            const taxRate = parseFloat(document.getElementById('tax_rate').value) || 0;
            const taxAmount = subtotal * (taxRate / 100);
            const total = subtotal + taxAmount;
            
            // Update display
            document.getElementById('subtotal').textContent = subtotal.toFixed(2);
            document.getElementById('tax_amount').textContent = taxAmount.toFixed(2);
            document.getElementById('tax_rate_display').textContent = taxRate;
            document.getElementById('total_amount').textContent = total.toFixed(2);
        }

        // Add event listeners for real-time calculation
        document.getElementById('tax_rate').addEventListener('input', calculateBill);
        document.getElementById('item_price').addEventListener('input', calculateBill);

        // Form validation before submission
        document.getElementById('bill_form').addEventListener('submit', function(e) {
            const clientId = document.getElementById('client_id').value;
            if (!clientId) {
                e.preventDefault();
                alert('Please select a client');
                return false;
            }
            
            const itemPrice = parseFloat(document.getElementById('item_price').value) || 0;
            if (itemPrice <= 0) {
                e.preventDefault();
                alert('Please enter a valid item amount');
                return false;
            }
        });

        // Initialize calculation on page load
        document.addEventListener('DOMContentLoaded', function() {
            calculateBill();
        });
    </script>