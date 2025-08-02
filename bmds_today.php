<?php 
ob_start();  // Start output buffering
    include 'include/header.php'; 
    include 'include/head.php'; 
    include 'session_check.php';
?>

<?php
// Database connection 
include 'includes/db_conn.php';

// Get today's date
$today = date('Y-m-d');

// Query to fetch active trainings for today
$sql = "SELECT * FROM bmds_entries WHERE start_date <= ? AND end_date >= ? AND is_deleted = 0 ORDER BY start_time ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $today, $today);
$stmt->execute();
$result = $stmt->get_result();

// Group data by start_time
$groupedData = [];
while ($row = $result->fetch_assoc()) {
    $timeKey = date("g:i A", strtotime($row['start_time'])); // Convert start_time to AM/PM format
    $groupedData[$timeKey][] = $row; // Group rows by start_time
}

// Close the database connection
$conn->close();
?>

<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container data-table p-5 ">
        <div class="ps-5">
            <div>
                <h1>Today's Training</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="bmds">BMDS</a></li>
                <li class="breadcrumb-item active" aria-current="page">Today's Training</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
    <div id="reportSection">   
        
        <div class="text-center">
            <!-- <h2>TODAY'S TRAINING</h2> -->
        </div>

        <?php
            $totalCount = 0;
            foreach ($groupedData as $group) {
                $totalCount += count($group);
            }
            echo "<h3>Total Trainings Today: " . $totalCount . "</h3>";

            
            
            
        ?>

        <hr>

        <!-- Display the grouped data -->
        <?php if (!empty($groupedData)): ?>
            <?php foreach ($groupedData as $time => $entries): ?>

                <div class="text-center">
                    <!-- SHOW TIME GROUP -->
                    <h4><?php echo htmlspecialchars($time) . ' / Total Count : ' . count($entries); ?></h4>
                </div>

                <table class="table table-bordered my-3">
                    <thead>
                        <tr>
                            <th scope="col">Reg No.</th>
                            <th scope="col">Date</th>
                            <th scope="col">Client Name</th>
                            <th scope="col">Contact</th>
                            <th scope="col">Start/End Date</th>
                            <th scope="col">Start/End Time</th>
                            <th scope="col">Car Type</th>
                            <th scope="col">KM Ride</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['reg_num']); ?></td>
                                <td>
                                    <?php 
                                    $original_date = $row['policy_date']; // date in YYYY-MM-DD format from database
                                    $formatted_date = DateTime::createFromFormat('Y-m-d', $original_date)->format('d-m-Y'); // format to DD/MM/YYYY
                                    echo htmlspecialchars($formatted_date); 
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['client_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['contact']); ?></td>
                                <td>
                                    <?php 
                                    // Convert start date to dd-mm-yyyy format
                                    $start_date = date("d-m-Y", strtotime($row['start_date']));
                                    // Convert end date to dd-mm-yyyy format
                                    $end_date = date("d-m-Y", strtotime($row['end_date']));
                                    echo htmlspecialchars($start_date) . " To " . htmlspecialchars($end_date); 
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $start_time = date("g:i A", strtotime($row['start_time'])); // Convert to AM/PM
                                    $end_time = date("g:i A", strtotime($row['end_time']));     // Convert to AM/PM
                                    echo htmlspecialchars($start_time) . " To " . htmlspecialchars($end_time); 
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['car_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['ride']); ?></td>
                                <td><?php echo htmlspecialchars($row['form_status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    
                </table>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No records found for today.</p>
        <?php endif; ?>
        
    

        
        
    </div>
</div>
</div>
</section>

<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

<?php //} ?>