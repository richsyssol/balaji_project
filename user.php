<?php 
// session_start(); // Start the session

// // Check if user is logged in
// if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true ) {
//     // Redirect to login page if not logged in
//     header("Location: login.php"); // Adjust path if needed
//     exit(); // Ensure no further code is executed
// }
// else{
    include 'include/header.php'; 
    include 'include/head.php'; 
    include 'session_check.php';
?>


<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container p-5 ">
        <div class="ps-5">
            <div>
                <h1>USER</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">USER</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
        
        <div class="float-start p-3">
            <a href="user-form.php?action=add" class="btn sub-btn1 w-100">ADD NEW</a>
        </div>

        <!-- Single Search Form -->
        <form method="POST" class="p-3">
            <div class="row">
                <div class="col-md-8 field">
                    <input type="text" name="search_query" class="form-control" placeholder="Search by Username" />
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn sub-btn1">Search</button>
                </div>
            </div>
        </form>

        <?php 
            include 'includes/db_conn.php';
        
            // Initialize search variable
            $search_query = isset($_POST['search_query']) ? trim($_POST['search_query']) : '';
        
            // Only proceed if there is a search query
            if (!empty($search_query)) {
                // Get the current page number from query string, default to 1
                $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $items_per_page = 5; // Set the number of entries per page
                $offset = ($current_page - 1) * $items_per_page;
        
                // Prepare SQL query to search by username and exclude 'admin' role
                $sql = "SELECT * FROM `user` WHERE username LIKE ? AND role = 'user' LIMIT ?, ?";
                $count_sql = "SELECT COUNT(*) as total FROM `user` WHERE username LIKE ? AND role = 'user'";
        
                // Prepare and execute count statement to get total records
                $count_stmt = $conn->prepare($count_sql);
                $search_query_with_wildcard = "%$search_query%";
                $count_stmt->bind_param("s", $search_query_with_wildcard);
                $count_stmt->execute();
                $count_result = $count_stmt->get_result();
                $total_records = $count_result->fetch_assoc()['total'];
                $count_stmt->close();
        
                // Calculate total pages
                $total_pages = ceil($total_records / $items_per_page);
        
                // Prepare and execute the main search query
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sii", $search_query_with_wildcard, $offset, $items_per_page);
                $stmt->execute();
                $result = $stmt->get_result();
            }
        ?>



            <!-- Display the Table Only If a Search Has Been Performed -->
            <?php if (!empty($search_query)) : ?>
                <!-- Display Table -->
                <table class="table my-5">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Username</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($result) > 0) {
                            $serial_number = $offset + 1; // Initialize serial number
                            while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <th scope="row"><?php echo $serial_number++; ?></th>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td>
                                        <!-- Add user-specific actions here -->
                                        <a href="user-form.php?edit=<?php echo $row['id']; ?>&edit_username=<?php echo urlencode($row['username']); ?>" class="text-decoration-none text-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                             <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        
                                        &nbsp;&nbsp;/&nbsp;&nbsp;
                                        
                                        <!-- Trigger link for the password modal -->
                                        <a class="text-decoration-none text-dark" data-bs-toggle="modal" data-bs-target="#passwordModal" data-item-id="<?php echo $row['id']; ?>">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php }
                        } else {
                            echo "<tr><td colspan='4'>No records found</td></tr>";
                        }
                        $stmt->close();
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="my-5 text-center">Please enter a search query to see results.</p>
            <?php endif; ?>





            
            <!-- Pagination -->
            <?php if (!empty($search_query) && $total_pages > 1): ?>
            <nav aria-label="Page navigation example float-end mt-5">
              <ul class="pagination neumorphic-pagination">
                <li class="page-item <?= $current_page == 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= max(1, $current_page - 1) ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $current_page == $i ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $current_page == $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= min($total_pages, $current_page + 1) ?>">Next</a>
                </li>
              </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Password Verification Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="passwordModalLabel">Password Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="verificationForm" action="user-delete.php" method="POST">
                    <input type="hidden" name="itemId" id="itemId"> <!-- Hidden input to hold the item ID -->
                    <div class="mb-3">
                        <label for="passwordInput" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" id="passwordInput" placeholder="********" required>
                    </div>
                    <div id="passwordError" class="text-danger" style="display: none;">Incorrect password. Please try again.</div>
                    <button type="submit" class="btn btn-primary">Verify</button>
                </form>
            </div>
        </div>
    </div>
</div>



<!--script for delete id-->
<script>

document.getElementById('passwordModal').addEventListener('show.bs.modal', function (event) {
    // Get the anchor element that triggered the modal
    var triggerElement = event.relatedTarget;
    
    // Extract the item ID from the data attribute
    var itemId = triggerElement.getAttribute('data-item-id');
    
    // Find the hidden input field in the modal and set its value
    var modalItemIdInput = document.getElementById('itemId');
    modalItemIdInput.value = itemId;
});
</script>



<script>
    
    // when hover on icon show tooltip

    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>


<!--script for delete-->
<script>
$(document).ready(function(){
    var deleteId;

    // When delete button is clicked
    $('.deleteBtn').on('click', function(){
        deleteId = $(this).data('id');
        $('#deleteModal').modal('show');
    });

    // When confirm delete button in the modal is clicked
    $('#confirmDelete').on('click', function(){
        window.location.href = 'bmds-form?action=delete&id=<?php echo $row['id']; ?>' + deleteId;
    });
});
</script>



<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

<?php //} ?>