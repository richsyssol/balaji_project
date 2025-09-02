<?php
include 'include/head.php'; 
    include 'session_check.php';
// Database connection
include 'includes/db_conn.php';

// Get all bills for display
$bills_query = $conn->query("
    SELECT b.id, b.bill_date, b.amount, c.client_name 
    FROM bills b
    JOIN client c ON b.client_id = c.id
    ORDER BY b.bill_date DESC
");

if (!$bills_query) {
    die("Error fetching bills: " . $conn->error);
}

$bills = [];
while ($row = $bills_query->fetch_assoc()) {
    $bills[] = $row;
}

// Check for success message
$success = isset($_GET['success']) ? $_GET['success'] : '';
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
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <a href="billing_system.php" class="btn btn-light"><i class="bi bi-plus-circle"></i> Create New Bill</a>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>
                        
                        <div class="table-responsive">
                            <table id="billsTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Bill ID</th>
                                        <th>Date</th>
                                        <th>Client</th>
                                        <th>Amount (₹)</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bills as $bill): ?>
                                    <tr>
                                        <td><?php echo $bill['id']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($bill['bill_date'])); ?></td>
                                        <td><?php echo $bill['client_name']; ?></td>
                                        <td>₹<?php echo number_format($bill['amount'], 2); ?></td>
                                        <td>
                                            <a href="view_bill.php?id=<?php echo $bill['id']; ?>" target="_blank" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i> View</a>
                                            <!-- <a href="view_bill.php?id=<?php echo $bill['id']; ?>&print=1" target="_blank" class="btn btn-sm btn-success"><i class="bi bi-printer"></i> Print</a> -->
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
                                    </div>
                                    </section>

   
    
    <script>
        $(document).ready(function() {
            $('#billsTable').DataTable({
                responsive: true,
                order: [[0, 'desc']],
                language: {
                    search: "Search bills:",
                    lengthMenu: "Show _MENU_ entries per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ bills",
                    infoEmpty: "Showing 0 to 0 of 0 bills",
                    infoFiltered: "(filtered from _MAX_ total bills)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        });
    </script>
