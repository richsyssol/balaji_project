<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<?php
include 'session_check.php';
include 'include/header.php'; 
include 'include/head.php';
include 'includes/db_conn.php';

$id = $_GET['id'];

$query = "SELECT * FROM client WHERE id = $id";
$result = $conn->query($query);
$policy = $result->fetch_assoc();

$client_name = $policy['client_name'];
$client_location = $policy['address'];
$contact = $policy['contact'];
$policy_number = $policy['policy_number'];
$policy_date = date('d-m-Y', strtotime($policy['policy_date']));
$start_date = date('d-m-Y', strtotime($policy['start_date']));
$end_date = date('d-m-Y', strtotime($policy['end_date']));
$vehicle = $policy['vehicle'];
$sub_type = $policy['sub_type'];
$amount = $policy['amount'];
$policy_company = $policy['policy_company'];
?>

<style>
  body {
    padding: 30px;
    line-height: 1.6;
  }
  .letterhead {
    border-bottom: 2px solid #000;
    padding-bottom: 10px;
    margin-bottom: 20px;
  }
  .company-name {
    font-size: 24px;
    font-weight: bold;
    text-align: center;
  }
  .company-address {
    text-align: center;
    margin-bottom: 10px;
  }
  .bill-title {
    text-align: center;
    font-weight: bold;
    font-size: 20px;
    margin: 20px 0;
  }
  .bill-info {
    width: 100%;
    margin-bottom: 20px;
  }
  .bill-info td {
    padding: 5px;
  }
  .bill-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
  }
  .bill-table th, .bill-table td {
    border: 1px solid #000;
    padding: 8px;
    text-align: left;
  }
  .bill-table th {
    background-color: #f2f2f2;
  }
  .text-right {
    text-align: right;
  }
  .signature {
    margin-top: 50px;
    width: 300px;
    float: right;
    text-align: center;
  }
  .signature-line {
    border-top: 1px solid #000;
    margin-top: 50px;
    padding-top: 5px;
  }
  .footer {
    margin-top: 30px;
    font-size: 12px;
    text-align: center;
  }
  
  @media print {
    body {
      margin: 0;
      padding: 0;
    }
    .button-container, nav, .breadcrumb, .sub-btn1, .navbar {
      display: none !important;
    }
    .container {
      padding: 0 !important;
      margin: 0 !important;
    }
  }
</style>

<section class="d-flex pb-5">
  <?php include 'include/navbar.php'; ?>
  <div class="container data-table p-5">
    <div class="ps-5">
      <h1>Generate Bill</h1>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="gic">GIC</a></li>
          <li class="breadcrumb-item active" aria-current="page">Bill</li>
        </ol>
      </nav>
    </div>

    <div class="bg-white con-tbl p-5">
      <!-- Action Buttons -->
      <div class="button-container mb-4">
        <button type="button" class="btn sub-btn1" onclick="showPasswordModal()">Print Bill</button>
        <button class="btn sub-btn1" onclick="generatePDF()">Download PDF</button>
      </div>

      <!-- Bill Content -->
      <div id="reportSection">
        <!-- Letterhead -->
        <div class="letterhead">
          <div class="company-name">Your Company Name</div>
          <div class="company-address">
            123 Business Street, City - 400001<br>
            GSTIN: XXXXXXXX | Phone: XXXXXXXXXX<br>
            Email: info@yourcompany.com | Website: www.yourcompany.com
          </div>
        </div>

        <!-- Bill Title -->
        <div class="bill-title">TAX INVOICE</div>

        <!-- Bill Information -->
        <table class="bill-info">
          <tr>
            <td><strong>Bill No:</strong> <?= 'B'.date('Ymd').$id ?></td>
            <td class="text-right"><strong>Date:</strong> <?= date('d-m-Y') ?></td>
          </tr>
          <tr>
            <td colspan="2"><strong>Policy No:</strong> <?= $policy_number ?></td>
          </tr>
        </table>

        <!-- Client and Company Details -->
        <table class="bill-info">
          <tr>
            <td width="50%">
              <strong>Billed To:</strong><br>
              <?= $client_name ?><br>
              <?= $client_location ?><br>
              Contact: <?= $contact ?>
            </td>
            <td width="50%">
              <strong>Insurance Company:</strong><br>
              <?= $policy_company ?><br>
              Policy Type: <?= $sub_type ?><br>
              Vehicle: <?= $vehicle ?>
            </td>
          </tr>
        </table>

        <!-- Bill Items Table -->
        <table class="bill-table">
          <thead>
            <tr>
              <th width="5%">Sr No</th>
              <th width="55%">Description</th>
              <th width="15%">Policy Date</th>
              <th width="10%">From</th>
              <th width="10%">To</th>
              <th width="15%" class="text-right">Amount (₹)</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td>Insurance Premium for <?= $vehicle ?> (<?= $sub_type ?>)</td>
              <td><?= $policy_date ?></td>
              <td><?= $start_date ?></td>
              <td><?= $end_date ?></td>
              <td class="text-right"><?= number_format($amount, 2) ?></td>
            </tr>
            <!-- Add more rows if needed -->
            <tr>
              <td colspan="5" class="text-right"><strong>Sub Total:</strong></td>
              <td class="text-right"><?= number_format($amount, 2) ?></td>
            </tr>
            <tr>
              <td colspan="5" class="text-right"><strong>GST (18%):</strong></td>
              <td class="text-right"><?= number_format($amount * 0.18, 2) ?></td>
            </tr>
            <tr>
              <td colspan="5" class="text-right"><strong>Total Amount:</strong></td>
              <td class="text-right"><?= number_format($amount * 1.18, 2) ?></td>
            </tr>
          </tbody>
        </table>

        <!-- Payment Terms -->
        <div>
          <strong>Payment Terms:</strong> Net 15 days from date of invoice<br>
          <strong>Payment Method:</strong> Cheque/Online Transfer<br>
          <strong>Bank Details:</strong> Your Bank Name, A/C No: XXXXXXXXXX, IFSC: XXXXXXXX
        </div>

        <!-- Signature -->
        <div class="signature">
          For Your Company Name<br><br>
          <div class="signature-line"></div>
          Authorized Signatory
        </div>

        <!-- Footer Note -->
        <div class="footer">
          This is a computer generated invoice. No signature required.<br>
          Thank you for your business!
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Password verification Modal for print screen -->
<div class="modal fade" id="printpasswordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
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
    
    function generatePDF() {
        const element = document.getElementById('reportSection');
        const opt = {
            margin: 10,
            filename: 'Insurance_Bill_<?= $policy_number ?>.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { 
                scale: 2,
                logging: false,
                useCORS: true,
                letterRendering: true
            },
            jsPDF: { 
                unit: 'mm', 
                format: 'a4', 
                orientation: 'portrait' 
            }
        };
        
        // Generate and save the PDF
        html2pdf().set(opt).from(element).save();
    }
</script>