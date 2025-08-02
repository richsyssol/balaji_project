
// navbar collapse button js
document.getElementById('toggleNavbar').addEventListener('click', function() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar.classList.contains('navbar-collapsed')) {
        sidebar.classList.remove('navbar-collapsed');
        sidebar.classList.add('navbar-expanded');
    } else {
        sidebar.classList.remove('navbar-expanded');
        sidebar.classList.add('navbar-collapsed');
    }
});


 
//  cards counter numbers auto increase
 $(document).ready(function() {

        $('.counter').each(function () {
    $(this).prop('Counter',0).animate({
        Counter: $(this).text()
    }, {
        duration: 4000,
        easing: 'swing',
        step: function (now) {
            $(this).text(Math.ceil(now));
        }
    });
});
 
});  


// when select cheque open for fill details
function toggleChequeFields() {
    const paymentMode = document.getElementById('paymentMode').value;
    const chequeDetails = document.getElementById('chequeDetails');

    // Show cheque fields only when "Cheque" is selected
    if (paymentMode === 'Cheque') {
        chequeDetails.style.display = 'block';
    } else {
        chequeDetails.style.display = 'none';
    }
}



// Function to toggle fields based on selected payment mode
function toggleFields() {
    const paymentMode = document.getElementById('paymentMode').value;
    const chequeDetails = document.getElementById('chequeDetails');
    const recoveryDetails = document.getElementById('recoveryDetails');

    // Hide all fields initially
    chequeDetails.style.display = 'none';
    recoveryDetails.style.display = 'none';

    // Show the appropriate fields based on the selected payment mode
    if (paymentMode === 'Cheque') {
        chequeDetails.style.display = 'block';
    } else if (paymentMode === 'Recovery') {
        recoveryDetails.style.display = 'block';
    }
}



 // when select paid open for fill details
function toggleRemarkFields() {
    const paymentMode = document.getElementById('invoiceStatus').value;
    const chequeDetails = document.getElementById('invoiceRemark');

    // Show cheque fields only when "Cheque" is selected
    if (paymentMode === 'Paid') {
        chequeDetails.style.display = 'block';
    } else {
        chequeDetails.style.display = 'none';
    }
}

 


 // Function to toggle dropdowns based on Select Type value
    function toggleTypeDropdown() {
        const paymentMode = document.getElementById('paymentMode').value;
        const mfOptions = document.getElementById('mfOptions');
        const insuranceOptions = document.getElementById('insuranceOptions');

        // Hide both options initially
        mfOptions.style.display = 'none';
        insuranceOptions.style.display = 'none';

        // Show appropriate options based on the selected type
        if (paymentMode === 'MF') {
            mfOptions.style.display = 'block';
        } else if (paymentMode === 'INSURANCE') {
            insuranceOptions.style.display = 'block';
        }
    }
 