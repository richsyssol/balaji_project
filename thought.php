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
                <h1>THOUGHT</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">THOUGHT</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
        
        <!-- <div class="float-start p-3">
            <a href="thought-form.php?action=add" class="btn sub-btn1 w-100">ADD THOUGHT</a>
        </div> -->


            <?php
            // Database connection
             include 'includes/db_conn.php';
            
            // Define your search query (if any)
            $search_query = ''; // Modify this based on user input or specific conditions
            $offset = 0; // Initialize offset for pagination (if applicable)
            
            // Prepare SQL query to fetch data from the "thought" table
            $sql = "SELECT * FROM thought LIMIT $offset, 10"; // Adjust LIMIT for pagination as needed
            $result = $conn->query($sql);
            ?>
            
            <table class="table my-5">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">THOUGHT</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result && $result->num_rows > 0) {
                        $serial_number = $offset + 1; // Initialize serial number
                        while ($row = $result->fetch_assoc()) {
                            ?>
                            <tr>
                                <th scope="row"><?php echo $serial_number++; ?></th>
                                <td><?php echo htmlspecialchars($row['thought']); // Ensure you have the correct column name ?></td>
                                <td>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') : ?>
                                        <a href="thought-form.php?action=edit&id=<?php echo $row['id']; ?>" class="text-decoration-none text-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a> 
                                        <!-- &nbsp;/&nbsp; -->
                                    <?php endif; ?>
                                    
                                    <!-- <a href="thought-form.php?action=add_new&id=<?php //echo $row['id']; ?>" class="text-decoration-none text-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Add New">
                                        <i class="fa-solid fa-plus"></i>
                                    </a> -->
                                </td>
                            </tr>
                            <?php 
                        }
                    } else {
                        echo "<tr><td colspan='3'>No records found</td></tr>";
                    }
                    // Close statements and connection
                    $conn->close();
                    ?>
                </tbody>
            </table>
            
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               You want to delete this record?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>


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
        window.location.href = 'lic-form?action=delete&id=<?php echo $row['id']; ?>' + deleteId;
    });
});
</script>



<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

<?php //} ?>