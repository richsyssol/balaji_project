<?php
include 'include/header.php'; 
include 'include/head.php'; 
include 'session_check.php';
include 'includes/db_conn.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Fetch the letter details
    $result = $conn->query("SELECT * FROM letters WHERE id=$id");
    if ($result->num_rows > 0) {
        $letter = $result->fetch_assoc();
    } else {
        die("Letter not found.");
    }
}
?>

<!-- Formal Structure of Letter -->
<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container p-5">
        <div class="ps-5">
            <h1>LETTER FORMAT</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="letter">LETTER</a></li>
                    <li class="breadcrumb-item active" aria-current="page">LETTER FORMAT</li>
                </ol>
            </nav>
        </div>

        <div class="bg-white con-tbl p-5">
            <div class="p-5">

                <div id="reportSection">

                    <p>
                        <strong>Date : </strong> <?= date('d-m-Y') ?>
                    </p>
                
                    <strong>From,</strong> <br>
                    <?= htmlspecialchars($letter['sender_name']) ?>, <br> 
                    <?= htmlspecialchars($letter['sender_address']) ?> </p> <br>

                    <p><strong>To,</strong> <br>
                    <?= htmlspecialchars($letter['recipient_name']) ?>, <br>

                    <?= htmlspecialchars($letter['recipient_address']) ?></p>

                    <p><strong>Subject:</strong> <?= htmlspecialchars($letter['subject']) ?></p>

                    <?php if (!empty($letter['referance'])): ?>
                        <p><strong>Referance:</strong> <?= htmlspecialchars($letter['referance']) ?></p>
                    <?php endif; ?>

                    <p><?= nl2br(htmlspecialchars($letter['message'])) ?></p>


                    <p>
                        Yours Sincerely, <br>
                        <div class="border w-25 p-4"></div> <br>
                        (<?= htmlspecialchars($letter['sender_name']) ?>)
                    </p>
                </div>

            </div>

            <!-- Trigger Button -->
            <div class="col-md-1">
                <button type="button" class="btn sub-btn1 mt-4" onclick="showPasswordModal()">Print</button>
            </div>
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

<?php include 'include/footer.php'; ?>
