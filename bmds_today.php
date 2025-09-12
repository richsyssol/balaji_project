<?php 
ob_start();  // Start output buffering
    include 'include/header.php'; 
    include 'include/head.php'; 
    include 'session_check.php';
?>

<?php
// Database connection 
include 'includes/db_conn.php';

// Set default date range (last month to today)
$defaultStartDate = date('Y-m-d', strtotime('-1 month'));
$defaultEndDate = date('Y-m-d');

// Get date range from request or use defaults
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : $defaultStartDate;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : $defaultEndDate;

// Query to fetch active trainings for the selected date range
$sql = "SELECT * FROM bmds_entries 
        WHERE start_date <= ? AND end_date >= ? AND is_deleted = 0 
        ORDER BY start_date ASC, start_time ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $endDate, $startDate);
$stmt->execute();
$result = $stmt->get_result();

// Group data by date and then by start_time
$groupedData = [];
while ($row = $result->fetch_assoc()) {
    $dateKey = date("Y-m-d", strtotime($row['start_date']));
    $timeKey = date("g:i A", strtotime($row['start_time']));
    $groupedData[$dateKey][$timeKey][] = $row;
}

// Close the database connection
$conn->close();
?>

<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container data-table p-5 ">
        <div class="ps-5">
            <div>
                <h1>Training Schedule</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="bmds">BMDS</a></li>
                <li class="breadcrumb-item active" aria-current="page">Training Schedule</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
            <!-- Date Range Filter Form -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <form method="GET" action="" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?php echo htmlspecialchars($startDate); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?php echo htmlspecialchars($endDate); ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="?" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div id="reportSection">   
                <div class="text-center">
                    <h2>TRAINING SCHEDULE FROM <?php echo date('d-m-Y', strtotime($startDate)); ?> TO <?php echo date('d-m-Y', strtotime($endDate)); ?></h2>
                </div>

                <?php
                $totalCount = 0;
                foreach ($groupedData as $dateGroup) {
                    foreach ($dateGroup as $timeGroup) {
                        $totalCount += count($timeGroup);
                    }
                }
                echo "<h3>Total Trainings: " . $totalCount . "</h3>";
                ?>

                <hr>

                <!-- Display the grouped data -->
                <?php if (!empty($groupedData)): ?>
                    <?php foreach ($groupedData as $date => $timeGroups): ?>
                        <div class="date-group mb-4">
                            
                            <?php foreach ($timeGroups as $time => $entries): ?>
                                <div class="time-group mb-3">
                                    <h4 class="ms-3"><?php echo htmlspecialchars($time) . ' / Count: ' . count($entries); ?></h4>
                                    
                                    <table class="table table-bordered">
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
                                                        $original_date = $row['policy_date'];
                                                        $formatted_date = DateTime::createFromFormat('Y-m-d', $original_date)->format('d-m-Y');
                                                        echo htmlspecialchars($formatted_date); 
                                                        ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['client_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['contact']); ?></td>
                                                    <td>
                                                        <?php 
                                                        $start_date = date("d-m-Y", strtotime($row['start_date']));
                                                        $end_date = date("d-m-Y", strtotime($row['end_date']));
                                                        echo htmlspecialchars($start_date) . " To " . htmlspecialchars($end_date); 
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $start_time = date("g:i A", strtotime($row['start_time']));
                                                        $end_time = date("g:i A", strtotime($row['end_time']));
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
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No records found for the selected date range.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>