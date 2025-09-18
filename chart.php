<?php 
include 'session_check.php';
include 'includes/db_conn.php';

// Initialize variables
$total_entries_count = 0;
$department_totals = [
    'BMDS'  => 0,
    'GIC'   => 0,
    'LIC'   => 0,
    'R/JOB' => 0,   // comes from rto_entries
    'MF'    => 0
];
$chart_data = [];
$from_date = $_POST['from_date'] ?? date('Y-m-01');
$to_date   = $_POST['to_date'] ?? date('Y-m-t');

// Always execute the queries to show data by default (current month)
// Handle form submit or initial page load
if (isset($_POST['generate_report']) || true) {
    
    // BMDS
    $res = $conn->query("SELECT COUNT(*) as cnt FROM bmds_entries WHERE DATE(policy_date) BETWEEN '$from_date' AND '$to_date'");
    if ($res && $row = $res->fetch_assoc()) {
        $department_totals['BMDS'] = (int)$row['cnt'];
    }

    // GIC
    $res = $conn->query("SELECT COUNT(*) as cnt FROM gic_entries WHERE DATE(policy_date) BETWEEN '$from_date' AND '$to_date'");
    if ($res && $row = $res->fetch_assoc()) {
        $department_totals['GIC'] = (int)$row['cnt'];
    }

    // LIC
    $res = $conn->query("SELECT COUNT(*) as cnt FROM lic_entries WHERE DATE(policy_date) BETWEEN '$from_date' AND '$to_date'");
    if ($res && $row = $res->fetch_assoc()) {
        $department_totals['LIC'] = (int)$row['cnt'];
    }

    // R/JOB (from rto_entries)
    $res = $conn->query("SELECT COUNT(*) as cnt FROM rto_entries WHERE DATE(policy_date) BETWEEN '$from_date' AND '$to_date'");
    if ($res && $row = $res->fetch_assoc()) {
        $department_totals['R/JOB'] = (int)$row['cnt'];
    }

    // MF
    $res = $conn->query("SELECT COUNT(*) as cnt FROM mf_entries WHERE DATE(policy_date) BETWEEN '$from_date' AND '$to_date'");
    if ($res && $row = $res->fetch_assoc()) {
        $department_totals['MF'] = (int)$row['cnt'];
    }

    // Calculate grand total
    $total_entries_count = array_sum($department_totals);

    // Prepare chart data - don't include grand total as a separate bar
    $chart_data = [
        'labels' => array_keys($department_totals),
        'data'   => array_values($department_totals)
    ];

    $conn->close();
}
?>

<?php
    include 'include/header.php';
    include 'include/head.php'; 
    include 'include/navbar.php'; 
?>

<!-- Add Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 

<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    
    <div class="container data-table p-5">
        <div class="bg-white con-tbl p-5">
            <h1 class="text-center">Total Entries Graph</h1>

            <!-- Form -->
            <form method="POST" class="p-3">
                <div class="row">
                    <!-- Date Range Fields -->
                    <div class="mb-3 date-fields field col-md-2">
                        <label for="from_date" class="form-label">From</label>
                        <input type="date" name="from_date" class="form-control" id="from_date" 
                               value="<?= htmlspecialchars($from_date) ?>">
                    </div>
            
                    <div class="mb-3 date-fields field col-md-2">
                        <label for="to_date" class="form-label">To</label>
                        <input type="date" name="to_date" class="form-control" id="to_date" 
                               value="<?= htmlspecialchars($to_date) ?>">
                    </div>
                </div>
            
                <!-- Buttons -->
                <button type="submit" name="generate_report" class="btn sub-btn1">Search</button>
            </form>

            <div id="reportSection">
            <?php if (!empty($chart_data) && array_sum($chart_data['data']) > 0): ?>

                <div class="my-5">
                    <?php
                        $formatted_start_date = date("d/m/Y", strtotime($from_date));
                        $formatted_end_date = date("d/m/Y", strtotime($to_date));
                        echo "<h4 class='text-center'>Graph From $formatted_start_date TO $formatted_end_date</h4>";
                        echo "<h5 class='text-center'>Total Entries: $total_entries_count</h5>";
                    ?>
                </div>

                <!-- Graph Section -->
                <div class="row mt-4">
                    <div class="col-md-8 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Department-wise Entry Count</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="entriesChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('entriesChart').getContext('2d');
                    const chartData = {
                        labels: <?php echo json_encode($chart_data['labels']); ?>,
                        datasets: [{
                            label: 'Number of Entries',
                            data: <?php echo json_encode($chart_data['data']); ?>,
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.7)', 
                                'rgba(75, 192, 192, 0.7)', 
                                'rgba(255, 206, 86, 0.7)', 
                                'rgba(153, 102, 255, 0.7)', 
                                'rgba(255, 99, 132, 0.7)'
                            ],
                            borderColor: [
                                'rgba(54, 162, 235, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 99, 132, 1)'
                            ],
                            borderWidth: 1
                        }]
                    };

                    const options = {
                        responsive: true,
                        plugins: {
                            legend: { 
                                position: 'top' 
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.raw;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { 
                                    display: true, 
                                    text: 'Number of Entries' 
                                },
                                ticks: { 
                                    stepSize: 1,
                                    precision: 0
                                }
                            },
                            x: {
                                title: { 
                                    display: true, 
                                }
                            }
                        }
                    };

                    new Chart(ctx, { 
                        type: 'bar', 
                        data: chartData, 
                        options: options 
                    });
                });
                </script>

            <?php elseif (isset($_POST['generate_report'])): ?>
                <div class="alert alert-info mt-4">
                    No entries found for the selected date range.
                </div>
            <?php else: ?>
                <div class="alert alert-info mt-4">
                    No entries found for the current month.
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';