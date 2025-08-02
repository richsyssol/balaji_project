<?php 

    include 'include/header.php'; 
    include 'include/head.php'; 
    include 'session_check.php';
?>



<?php
// Include your database connection
include 'includes/db_conn.php'; // Update this with the correct path to your database connection goal

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $goal = strtoupper($_POST['goal']);
    $goal_id = $_POST['goal_id'];

    // Prepare the SQL query to update the goal for the selected goal_id
    $sql = "UPDATE goal SET goal = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("si", $goal, $goal_id);

        if ($stmt->execute()) {
            echo "<div class='container alert alert-success'>Goal updated successfully.</div>";
        } else {
            echo "<div class='container alert alert-danger'>Error updating goal: " . $stmt->error . "</div>";
        }

        $stmt->close();
    } else {
        echo "<div class='container alert alert-danger'>Error preparing the query: " . $conn->error . "</div>";
    }

    // Close the connection
    $conn->close();
}
?>


<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container data-table p-5 ">
        <div class="ps-5">
            <div>
                <h1>SET GOAL</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">SET GOAL</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
        

        <div class="text-center">
            <h2>Update Goal</h2>
        </div>

        <form method="POST" action="">
            <div class="row">
                <div class="mb-3 field">
                    <label for="goal_id" class="form-label">Select Job:</label>
                    <select id="goal_id" name="goal_id" class="form-control" required>
                        <option value="">Select a Job</option>
                        <?php
                        // Fetch records to populate the dropdown
                        $sql = "SELECT id, job_type FROM goal";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='" . $row['id'] . "'>" . $row['job_type'] . "</option>";
                            }
                        } else {
                            echo "<option value=''>No records available</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3 field">
                    <label for="goal" class="form-label">New goal:</label>
                    <input type="goal" id="goal" name="goal" class="form-control" required>
                </div>
                <button type="submit" class="btn sub-btn1">Update</button>
            </div>
        </form>

        </div>  
    </div>
</section>




<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

<?php //} ?>