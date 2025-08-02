
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<?php
include 'session_check.php';
include 'include/header.php'; 
include 'include/head.php';
include 'includes/db_conn.php';

$id = $_GET['id'];

$query = "SELECT * FROM gic_entries WHERE id = $id";
$result = $conn->query($query);
$policy = $result->fetch_assoc();

$client_name = $policy['client_name'];
$client_location = $policy['address'];
$contact = $policy['contact'];
$policy_number = $policy['policy_number'];
$end_date = $policy['end_date'];
$mv_number = $policy['mv_number'];
$vehicle = $policy['vehicle'];
$sub_type = $policy['sub_type'];



?>

<style>
  body {
    padding: 30px;
    line-height: 1.6;
  }
  .heading {
    text-align: center;
    font-weight: bold;
    font-size: 18px;
  }
  .right {
    text-align: right;
  }
  .bold {
    font-weight: bold;
  }
  .table, .table th, .table td {
    border: 1px solid black;
    border-collapse: collapse;
  }
  .table th, .table td {
    padding: 5px 10px;
  }
  .checkboxes {
    width: 100%;
    margin-top: 10px;
  }
  .checkboxes td {
    padding: 3px 10px;
  }
  .table {
    width: 97% !important;
  }
  .table td {
    height: 50px;
    vertical-align: top;
  }
  .table input {
    width: 100%;
    border: none;
    outline: none;
    background: transparent;
  }
  input{
    width: 100px;
    border: none;
    outline: none;
    background: transparent;
  }
  .thanks {
    float: right;
    width: 298px;
  }
  .date {
    float: right;
    width: 140px;
  }
  .footer {
    margin-top: 30px;
    font-weight: bold;
    background-color: black;
    color: white;
    padding: 10px;
    width: 300px;
  }
  .button {
    text-align: right;
    margin-bottom: 20px;
  }

  .checkbox{
    width: 15px;
  }

  .sign{
    border: 1px solid;
    height: 48px;
    width: 225px;
  }

  .letterhead{
    border: 1px solid;
    /* height: 60px; */
    width: 97%;
  }


  @page {
  size: A4;
  margin: 120px 30px 100px 30px; /* top right bottom left */
}

@media print {
  body {
    margin: 0;
    padding: 0;
  }

  .footer {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    background-color: black !important;
    color: white !important;
  }

  .button,
  .btn,
  .button-container,
  nav,
  .breadcrumb,
  .sub-btn1,
  .navbar {
    display: none !important;
  }

  .letterhead {
    border: none !important; /* Don't show border when printing */
  }

  .container,
  .con-tbl,
  #reportSection {
    padding: 0 !important;
    margin: 0 !important;
  }

  html, body {
    width: 210mm;
    height: 297mm;
  }
}

  
</style>

<section class="d-flex pb-5">
  <?php include 'include/navbar.php'; ?>
  <div class="container data-table p-5">
    <div class="ps-5">
      <h1>GIC Expiry Letter</h1>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="gic">GIC</a></li>
          <li class="breadcrumb-item active" aria-current="page">Letter</li>
        </ol>
      </nav>
    </div>

    

    <div class="bg-white con-tbl p-5" >

     <!-- Trigger Button -->
        <div>
            <button type="button" class="btn sub-btn1" onclick="showPasswordModal()">Print Letter</button>
            <button class="btn sub-btn1" onclick="generatePDF()">Download PDF</button>
        </div>

        


        <div id="reportSection" class="mt-5">

          <div class="letterhead"></div>

          <div class="heading">* आपली सुरक्षितता...आपल्या हाती ! *</div>
            <div class="date">दि. <span class="bold"><?= date('d-m-Y') ?></span></div>

            <p>प्रति <br>
              <span class="bold"> मा . <?= $client_name ?>,</span><br>
              <span><?= $client_location ?>,</span><br>
              <span><?= $contact ?>,</span><br>
              यांचे सेवेशी सादर,
            </p>

            <p>
              <span class="bold">विषय :</span> विमा पॉलिसीचे नूतनीकरण करणेबाबत,
            </p>

            <p>
              <span class="bold">संदर्भ :</span> <?= $mv_number ?> (<?= $vehicle ?>) / <?= $sub_type ?> <br>
              <span class="bold">Policy Number :</span> <?= $policy_number ?>
            </p>

            <p>
              महोदय / महोदया,<br><br>
              वरील विषयान्वये आपणांस विनंतीपूर्वक कळवू इच्छितो की आपल्या वरील पॉलिसीची मुदत दि. <?= date('d-m-Y', strtotime($end_date)) ?> रोजी संपत आहे तरी विमा पॉलिसीची जोखीम अखंडितपणे चालू राहण्यासाठी वरील पॉलिसीचे नूतनीकरण मुदतीचे आत करावे ही नम्र विनंती.
            </p>

            <p>
              वरील पॉलिसी नूतनीकरण करणेकामी कृपया रु. <input type="text" placeholder="______________"> चा चेक
            </p>

            <table class="checkboxes">
              <tr>
                <td><input type="checkbox" class="checkbox"> युनायटेड इंडिया इंश्युरन्स कं. लि.</td>
                <td><input type="checkbox" class="checkbox"> बजाज अलियान्झ जनरल इंश्युरन्स कं. लि.</td>
              </tr>
              <tr>
                <td><input type="checkbox" class="checkbox"> आयसीआयसीआय लोम्बार्ड जनरल इंश्युरन्स कं. लि.</td>
                <td><input type="checkbox" class="checkbox"> एचडीएफसी एर्गो हेअल्थ इंश्युरन्स लि. </td>
              </tr>
            </table>

            <p>
            च्या नावे द्यावा.
            </p>

            <br>

            <p>
              टीप : सोबत <input type="checkbox" class="checkbox" checked> मार्क केलेल्या कागदपत्रांची स्वाक्षरी केलेल्या झेरॉक्स प्रति द्याव्यात ही नम्र विनंती
            </p>

            <table class="checkboxes">
              <tr>
                <td><input type="checkbox" class="checkbox"> आर.सी. बुक व पॉलिसी (असल्यास)</td>
                <td><input type="checkbox" class="checkbox"> पॅन कार्ड</td>
                <td><input type="checkbox" class="checkbox"> जीएसटी सर्टिफिकेट</td>
                <td><input type="checkbox" class="checkbox"> आधार कार्ड</td>
              </tr>
              <tr>
                <td><input type="checkbox" class="checkbox"> शॉप ऍक्ट , नोंदणी दाखला</td>
                <td><input type="checkbox" class="checkbox"> ड्रायव्हिंग लायसन्स</td>
                <td><input type="checkbox" class="checkbox"> आयकर विवरण पत्र</td>
                <td><input type="checkbox" class="checkbox"> कॅन्सल चेक</td>
              </tr>
            </table>

            <br>

            <table class="table">
              <thead>
                <tr>
                  <th>Sr No.</th>
                  <th>Full Name</th>
                  <th>DOB</th>
                  <th>Gender</th>
                  <th>Monthly Salary</th>
                  <th>Designation</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>1.</td>
                  <td><input type="text"></td>
                  <td><input type="text"></td>
                  <td><input type="text"></td>
                  <td><input type="text"></td>
                  <td><input type="text"></td>
                </tr>
                <tr>
                  <td colspan="6" class="text-center">EXPECTING ABOVE LIST IN ENGLISH AND IN EXCEL FORMAT ONLY</td>
                </tr>
              </tbody>
            </table>

            <div class="thanks">
              आपल्या अनमोल सहकार्याबद्दल धन्यवाद !<br>
              विमा क्षेत्रातील आपला विश्वसनीय सल्लागार <br>
              <span class="fw-bold">भाऊराव यशवंत पिंगळे</span> <br>
              <div class="sign"></div>
            </div>

            <footer class="footer">
              विमा पॉलिसीज , गरज काळाची <br>
              घेईल काळजी आपली व प्रियजनांची
            </footer>
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
    
     function generatePDF() {
            const element = document.getElementById('reportSection');
            const opt = {
                margin: 10,
                filename: 'GIC_Expiry_Letter_<?= $policy_number ?>.pdf',
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
    
    // function generatePDF() {
    //     const element = document.getElementById('reportSection');
    //     const opt = {
    //         margin: 10,
    //         filename: 'Insurance_Renewal_Letter.pdf',
    //         image: { type: 'jpeg', quality: 0.98 },
    //         html2canvas: { scale: 1 },
    //         jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    //     };
        
    //     html2pdf().set(opt).from(element).save();
    // }
        

//  const clientContact = "<?php echo $contact; ?>";
//   function generateAndSendPDF() {
//     const element = document.getElementById('reportSection');
//     html2pdf().from(element).outputPdf('blob').then((pdfBlob) => {
//         const formData = new FormData();
//         formData.append('pdf', pdfBlob, 'Insurance_Renewal_Letter.pdf');
//         formData.append('client_contact', clientContact); // Use the contact from DB

//         fetch('send_letter_whatsapp.php', {
//             method: 'POST',
//             body: formData
//         })
//         .then(res => res.text())
//         .then(res => console.log(res))
//         .catch(err => console.error(err));
//     });
// }
</script>

