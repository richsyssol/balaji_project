
<?php 

    include 'include/header.php';
    include 'include/head.php';
    include 'total_count.php';
    include 'session_check.php';

?>


<div class="d-flex">
    <?php include 'include/navbar.php'; ?>

    <div class="container py-5">
        <div class="text-light"> 
            <div class="thought-box">
                <div class="p-5">
                    <?php
                        echo "<h3>$thought</h3>";
                    ?>
                    <!-- <h3>IT'S MY DAY</h3> -->
                    <h3>TODAY'S REPORT</h3>
                    <h3>DATE: <?php echo date('d/m/Y', strtotime($today)); ?></h3>
                </div>
                
            
                
            </div>
            
        </div>

        <div class="text-center mt-5 p-3" style="background-color: #1da5e9;">
            <!-- Date Range Form -->
            <form method="GET" action="" style="display: flex; justify-content: center; align-items: center; gap: 15px;">

                <?php
                // Get the current date
                $currentDate = date('Y-m-d');

                // Get the first day of the current month
                $firstDayOfMonth = date('Y-m-01');

                // Get the last day of the current month
                $lastDayOfMonth = date('Y-m-t');

                // Retain values from GET request or use defaults
                $start_date = $_GET['start_date'] ?? $firstDayOfMonth;
                $end_date = $_GET['end_date'] ?? $lastDayOfMonth;
                ?>

                <div class="field">
                    <label for="start_date" class="text-light">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" 
                        value="<?= htmlspecialchars($start_date); ?>" required>
                </div>
                <div class="field">
                    <label for="end_date" class="text-light">End Date:</label>
                    <input type="date" id="end_date" name="end_date" 
                        value="<?= htmlspecialchars($end_date); ?>" required>
                </div>
                
                <div>
                    <button type="submit">Show Totals</button>
                </div>

            </form>

        </div>


        <div class=" py-5 justify-content-center">
            
        
        <div class="d-flex flex-row flex-wrap gap-3 mb-4">
            

            <!-- GIC Section -->
                <div class="card shadow count">
                    <div class="card-body">
                        <h4 class="card-title text-center">GIC</h4>
                        <p class="card-text">NOP: <?php echo $gic_entries_today; ?></p>
                        <p class="card-text">PREMIUM: <?php echo number_format($gic_total_amount_today); ?></p>
                        <p class="card-text">TILL TODAY : <?php echo $gic_entries_month_to_date; ?> / <?php echo number_format($gic_total_amount_month_to_date); ?></p>
                        <p class="card-text">TILL DATE : <?php echo $gic_entries_range; ?> / <?php echo number_format($gic_total_amount_range); ?></p>
                        <p class="card-text">LAST MONTH : <?php echo $gic_entries_last_month; ?> / <?php echo number_format($gic_total_amount_last_month); ?></p>
                        <h5>GOAL : <?php echo $goals['GIC']; ?></h5>

                    </div>
                </div>

            

            <!-- BMDS Section -->
                <div class="card shadow count">
                    <div class="card-body">
                        <h4 class="card-title text-center">BMDS</h4>
                        <p class="card-text">JOB: <?php echo $bmds_entries_today; ?></p>
                        <p class="card-text">LLR : <?php echo $class_llr_today; ?></p>
                        <p class="card-text">DL : <?php echo $class_dl_today; ?></p>
                        <p class="card-text">ADM : <?php echo $class_adm_today; ?></p>
                        <p class="card-text">PREMIUM: <?php echo number_format($bmds_total_amount_today); ?></p>
                        <p><strong>TILL TODAY</strong></p>
                        <p class="card-text">TILL TODAY : <?php echo $bmds_entries_month_to_date; ?> / <?php echo number_format($bmds_total_amount_month_to_date); ?></p>
                        <p><strong>TILL DATE</strong></p>
                        <p class="card-text">TILL DATE : <?php echo $bmds_entries_range; ?> / <?php echo number_format($bmds_total_amount_range); ?></p>

                        <p class="card-text">LLR : <?php echo $class_totals['LLR']['entries']; ?> / <?php echo number_format($class_totals['LLR']['amount']); ?></p>

                        <p class="card-text">DL : <?php echo $class_totals['DL']['entries']; ?> / <?php echo number_format($class_totals['DL']['amount']); ?></p>
                        
                        <p class="card-text">ADM : <?php echo $class_totals['ADM']['entries']; ?>  / <?php echo number_format($class_totals['ADM']['amount']); ?></p>
                        
                        <p class="card-text">LAST MONTH : <?php echo $bmds_entries_last_month; ?> / <?php echo number_format($bmds_total_amount_last_month); ?></p>
                        <h5>GOAL : <?php echo $goals['BMDS']; ?></h5>
                    </div>
                </div>

            <!-- MF Section -->
                <div class="card shadow count">
                    <div class="card-body">
                        <h4 class="card-title text-center">MF</h4>
                        <p class="card-text">JOB: <?php echo $mf_entries_today; ?></p>
                        <p class="card-text">PREMIUM: <?php echo number_format($mf_total_amount_today); ?></p>
                        <p class="card-text">TILL TODAY : <?php echo $lic_entries_month_to_date; ?> / <?php echo number_format($lic_total_amount_month_to_date); ?></p>
                        <p class="card-text">TILL DATE : <?php echo $mf_entries_range; ?> / <?php echo number_format($mf_total_amount_range); ?></p>
                        <p class="card-text">LAST MONTH : <?php echo $mf_entries_month_to_date; ?> / <?php echo number_format($mf_total_amount_month_to_date); ?></p>
                        <h5>GOAL : <?php echo $goals['MF']; ?></h5>
                    </div>
                </div>

            <!-- RTO Section -->
                <div class="card shadow count">
                    <div class="card-body">
                        <h4 class="card-title text-center">R/JOB</h4>
                        <p class="card-text">JOB: <?php echo $rto_entries_today; ?></p>
                        <p class="card-text">NT : <?php echo $rto_nt_today; ?></p>
                        <p class="card-text">TR : <?php echo $rto_tr_today; ?></p>
                        <p class="card-text">DL : <?php echo $rto_dl_today; ?></p>
                        <p><strong>TILL DATE</strong></p>
                        <p class="card-text">TILL DATE : <?php echo $rto_entries_range; ?> / <?php echo number_format($rto_total_amount_range); ?></p>
                        <!-- total count and total cash in hand amount -->
                        <p class="card-text">NT : <?php echo $rto_totals['NT']['entries']; ?> / <?php echo number_format($rto_totals['NT']['amount']); ?></p>
                        <p class="card-text">TR : <?php echo $rto_totals['TR']['entries']; ?> / <?php echo number_format($rto_totals['TR']['amount']); ?></p>
                        <p class="card-text">DL : <?php echo $rto_totals['DL']['entries']; ?>  / <?php echo number_format($rto_totals['DL']['amount']); ?></p>
                        <h5>GOAL : <?php echo $goals['RTO']; ?></h5>
                    </div>
                </div>

            <!-- LIC Section -->
                <div class="card shadow count">
                    <div class="card-body">
                        <h4 class="card-title text-center">LIC</h4>
                        <p class="card-text">SERVICING JOB: <?php echo $lic_entries_today; ?></p>
                        <p class="card-text">NEW PREMIUM : <?php echo $new_business_entries_today; ?> / <?php echo number_format($new_business_total_today); ?></p>
                        <p class="card-text">RENEWAL PREMIUM : <?php echo $renewal_business_entries_today; ?> / <?php echo number_format($renewal_business_total_today); ?></p>
                        <!-- <p class="card-text">PREMIUM: <?php //echo $lic_total_amount_today; ?></p> -->
                        <p><strong>TILL TODAY</strong></p>
                        <p class="card-text">NEW PREMIUM : <?php echo $new_business_entries; ?> / <?php echo number_format($new_business_total_amount); ?></p>
                        <p class="card-text">RENEWAL PREMIUM : <?php echo $renewal_business_entries; ?> / <?php echo number_format($renewal_business_total_amount); ?></p>
                        <p class="card-text">TILL DATE : <?php echo $lic_entries_range; ?> / <?php echo number_format($lic_total_amount_range); ?></p>
                        <p><strong>LAST MONTH</strong></p>
                        <p class="card-text">NEW PREMIUM : <?php echo $new_business_entries_last_month; ?> / <?php echo number_format($new_business_total_last_month); ?></p>
                        <p class="card-text">RENEWAL PREMIUM : <?php echo $renewal_business_entries_last_month; ?> / <?php echo number_format($renewal_business_total_last_month); ?></p>
                        <h5>GOAL : <?php echo $goals['LIC']; ?></h5>
                    </div>
                </div>

        </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card shadow">
                        <div class="card-body text-center">
                            <h4 class="card-title">EXPENSE</h4>
                            <p class="card-text">TODAY : <?php echo $expense_entries_today; ?> / <?php echo number_format($expense_total_amount_today); ?></p>
                            <p class="card-text">TILL TODAY : <?php echo $expense_entries_month_to_date; ?> / <?php echo number_format($expense_total_amount_month_to_date); ?></p>
                            <p class="card-text">TILL DATE : <?php echo $expense_entries_range; ?> / <?php echo number_format($expense_total_amount_range); ?></p>
                            <p class="card-text">LAST MONTH : <?php echo $expense_entries_last_month; ?> / <?php echo number_format($expense_total_amount_last_month); ?></p>
                            
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card shadow">
                        <div class="card-body text-center">
                            <h4 class="card-title">MY DREAM CLIENT</h4>
                            <h5><?php echo $goals['CLIENT']; ?></h5>
                            <!-- <p class="card-text">PREMIUM: <?php //echo $rto_total_amount_today; ?></p> -->
                            <p class="card-text">Total Client : <?php echo $total_client; ?></p>
                            <p class="card-text">Today's Total Client : <?php echo $todays_total_client; ?></p>
                            
                            
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>


  
  <script>
  // Array of daily thoughts
//   const thoughts = [
//     "Believe you can and you're halfway there.",
//     "The only limit to our realization of tomorrow is our doubts of today.",
//     "Do something today that your future self will thank you for.",
//     "With the new day comes new strength and new thoughts.",
//     "Success is not final, failure is not fatal: It is the courage to continue that counts.",
//     "Your limitation—it's only your imagination.",
//     "Great things never come from comfort zones."
//   ];

//   // Function to get the current day of the year
//   function getDayOfYear() {
//     const now = new Date();
//     const start = new Date(now.getFullYear(), 0, 0);
//     const diff = now - start;
//     const oneDay = 1000 * 60 * 60 * 24;
//     return Math.floor(diff / oneDay);
//   }

//   // Function to display the current day's thought
//   function showDailyThought() {
//     const dayOfYear = getDayOfYear();
//     const thoughtIndex = dayOfYear % thoughts.length;
//     document.getElementById("dailyThought").textContent = thoughts[thoughtIndex];
//   }

//   // Call the function when the page loads
//   showDailyThought();
</script>


<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

<?php //} ?>