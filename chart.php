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

    // Sort departments by count in Asending order (biggest first)
    asort($department_totals);

    // Prepare chart data - sorted by count Asending
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
                    ?>
                </div>

                

                <!-- Graph Section -->
                <div class="row mt-4">
                    <div class="col-md-10 mx-auto">
                        <div class="card">
                            
                            <div class="card-body">
                                <canvas id="entriesChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Entries Table -->
                <div class="row mt-4 mb-5">
                    <div class="col-md-6 mx-auto">
                        <div class="card">
                            <div class="card-body p-0">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Department</th>
                                            <th>Count</th>
                                            <th>Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $counter = 0;
                                        $colors = ['table-primary', 'table-success', 'table-warning', 'table-info', 'table-danger'];
                                        foreach ($department_totals as $dept => $count): 
                                            $percentage = $total_entries_count > 0 ? round(($count / $total_entries_count) * 100, 2) : 0;
                                            $color_class = $colors[$counter % count($colors)];
                                            $counter++;
                                        ?>
                                            <tr class="<?= $color_class ?>">
                                                <td><strong><?= htmlspecialchars($dept) ?></strong></td>
                                                <td><?= $count ?></td>
                                                <td><?= $percentage ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="table-dark">
                                            <td><strong>GRAND TOTAL</strong></td>
                                            <td><strong><?= $total_entries_count ?></strong></td>
                                            <td><strong>100%</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('entriesChart').getContext('2d');
                    
                    // Define colors for the chart
                    const backgroundColors = [
                        'rgba(54, 162, 235, 0.8)',  // Blue
                        'rgba(75, 192, 192, 0.8)',  // Green
                        'rgba(255, 206, 86, 0.8)',  // Yellow
                        'rgba(153, 102, 255, 0.8)', // Purple
                        'rgba(255, 99, 132, 0.8)'   // Red
                    ];
                    
                    const borderColors = [
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 99, 132, 1)'
                    ];

                    const chartData = {
                        labels: <?php echo json_encode($chart_data['labels']); ?>,
                        datasets: [{
                            label: 'Number of Entries',
                            data: <?php echo json_encode($chart_data['data']); ?>,
                            backgroundColor: backgroundColors,
                            borderColor: borderColors,
                            borderWidth: 2,
                            borderRadius: 5,
                            borderSkipped: false,
                        }]
                    };

                    const options = {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { 
                                display: true,
                                position: 'top',
                                labels: {
                                    font: {
                                        size: 14,
                                        weight: 'bold'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleFont: { size: 14 },
                                bodyFont: { size: 14 },
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.raw;
                                    }
                                }
                            },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                formatter: function(value) {
                                    return value;
                                },
                                font: {
                                    weight: 'bold',
                                    size: 12
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { 
                                    display: true, 
                                    text: 'Number of Entries',
                                    font: {
                                        size: 14,
                                        weight: 'bold'
                                    }
                                },
                                ticks: { 
                                    stepSize: 1,
                                    precision: 0,
                                    font: {
                                        size: 12
                                    }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.1)'
                                }
                            },
                            x: {
                                title: { 
                                    display: true,
                                    text: 'Departments',
                                    font: {
                                        size: 14,
                                        weight: 'bold'
                                    }
                                },
                                ticks: {
                                    font: {
                                        size: 12,
                                        weight: 'bold'
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        },
                        animation: {
                            duration: 1000,
                            easing: 'easeOutQuart'
                        }
                    };

                    // Create the chart
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
?>