<?php
session_start();
include 'include/header.php'; 
include 'include/head.php'; 
include 'session_check.php';
include 'include/navbar.php'; 
include 'includes/db_conn.php';

function generateTaskImage($tasks) {
    $imgWidth = 1080;
    $imgHeight = 800 + (count($tasks) * 60);
    $image = imagecreatetruecolor($imgWidth, $imgHeight);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $blue = imagecolorallocate($image, 0, 102, 204);
    $red = imagecolorallocate($image, 204, 0, 0);
    imagefilledrectangle($image, 0, 0, $imgWidth, $imgHeight, $white);
    $fontPath = __DIR__ . "/Fonts/ARIAL.TTF"; 
    if (!file_exists($fontPath)) {
        die("Error: Font file not found!");
    }
    imagettftext($image, 24, 0, 20, 50, $black, $fontPath, "*** TASK REPORT ***");
    imagettftext($image, 18, 0, 800, 50, $black, $fontPath, "Print Date: " . date('d-m-Y'));

    $y = 100;
    $lineHeight = 40;
    foreach ($tasks as $task) {
        imagettftext($image, 18, 0, 20, $y, $blue, $fontPath, "Job Assign To: " . $task['username']);
        $labels = [
            "Task Date" => $task['task_date'],
            "Task Time" => $task['task_time'],
            "Assigned By" => $task['assign_by'],
            "Task" => $task['task'],
            "Contact Name" => $task['contact_to'],
            "Contact No" => $task['contact_no'],
            "Priority" => $task['priority'],
            "Report To" => $task['report_to'],
            "Shift Task" => $task['shift_task'],
            "Status" => $task['status'],
            "Task Type" => $task['recurrence_type'],
        ];
        foreach ($labels as $key => $value) {
            imagettftext($image, 16, 0, 20, $y + 30, $black, $fontPath, "$key:");
            imagettftext($image, 16, 0, 250, $y + 30, $blue, $fontPath, $value);
            $y += $lineHeight;
        }
        $y += 20;
    }
    imagettftext($image, 14, 0, 20, $imgHeight - 100, $red, $fontPath, "DO THE DIGITAL ~ Go for online payment, try to avoid cheque payment.");
    imagettftext($image, 14, 0, 20, $imgHeight - 60, $black, $fontPath, "\u260E +91 98810 63639 | (02550) 253453");
    imagettftext($image, 14, 0, 20, $imgHeight - 40, $black, $fontPath, "\u2709 bhauraopingle71@gmail.com");
    $filename = "uploads/task_" . date('Y-m-d_H-i-s') . ".png";
    imagepng($image, $filename);
    imagedestroy($image);
    return $filename;
}

function sendTaskImageToWhatsApp($contact, $imagePath) {
    $apiUrl = 'https://message.richsol.com/api/v1/sendmessage';
    $apiKey = 'rtrterte546565df4e8r56wd56a4';
    $postData = [
        'key' => $apiKey,
        'mobileno' => $contact,
        'msg' => 'Tasks Assigned. View details below:',
        'File' => $imagePath,
        'type' => 'Image'
    ];
    $curl = curl_init($apiUrl);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

if (isset($_GET['username'])) {
    $assignedTo = $_GET['username'];
    $query = $conn->query("SELECT * FROM tasks WHERE username='$assignedTo' AND task_date = CURDATE()");
    $tasks = [];
    while ($task = $query->fetch_assoc()) {
        $tasks[] = $task;
    }
    if (!empty($tasks)) {
        $contact = $tasks[0]['contact_no'];
        $imagePath = generateTaskImage($tasks);
        $_SESSION['image_path'] = $imagePath;
        $_SESSION['assignedTo'] = $assignedTo;
    } else {
        $_SESSION['error'] = "No tasks found for this person!";
    }
    
}

if (isset($_POST['send_whatsapp'])) {
    $assignedTo = $_POST['username'];
    $query = $conn->query("SELECT * FROM tasks WHERE username='$assignedTo' AND task_date = CURDATE()");
    $tasks = [];
    while ($task = $query->fetch_assoc()) {
        $tasks[] = $task;
    }
    if (!empty($tasks)) {
        $contact = $tasks[0]['contact_no'];
        $imagePath = generateTaskImage($tasks);
        $imageUrl = "http://localhost/balaji/" . $imagePath;
        $response = sendTaskImageToWhatsApp($contact, $imageUrl);
        $_SESSION['message'] = "API Response: " . $response;
    } else {
        $_SESSION['error'] = "No tasks found for this person!";
    }
    header("Location: send-img.php");
    exit();
}

$todaysDate = date('Y-m-d');
$query = "SELECT * FROM tasks WHERE task_date = ?";
$params = [$todaysDate];
$paramTypes = "s";
if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $query = "SELECT * FROM tasks WHERE task_date BETWEEN ? AND ?";
    $params = [$_GET['start_date'], $_GET['end_date']];
    $paramTypes = "ss";
}
if (!empty($_GET['recurrence_type'])) {
    $query .= " AND recurrence_type = ?";
    $params[] = $_GET['recurrence_type'];
    $paramTypes .= "s";
}
$stmt = $conn->prepare($query);
$stmt->bind_param($paramTypes, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<section class="d-flex pb-5">
    <div class="container data-table p-5">
        <div class="ps-5">
            <h1>Send To-Do Image</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="todo">To-Do List</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Send To-Do Image</li>
                </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
            <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['image_path']) && isset($_SESSION['assignedTo'])): ?>
                <div class="mb-3">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#taskImageModal">Preview Task Image</button>
                    <div class="modal fade" id="taskImageModal" tabindex="-1" aria-labelledby="taskImageModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="taskImageModalLabel">Task Image Preview</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <img src="<?= $_SESSION['image_path']; ?>" alt="Task Image" style="max-width: 100%; height: auto;">
                                </div>
                                <div class="modal-footer">
                                    <form method="POST" action="send-img.php">
                                        <input type="hidden" name="username" value="<?= $_SESSION['assignedTo']; ?>">
                                        <button type="submit" class="btn btn-success" name="send_whatsapp">Send to WhatsApp</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php unset($_SESSION['image_path'], $_SESSION['assignedTo']); endif; ?>

            <form method="GET" action="send-img.php">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <input type="date" class="form-control" name="start_date" value="<?= $_GET['start_date'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" name="end_date" value="<?= $_GET['end_date'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" name="recurrence_type">
                            <option value="">Select Recurrence</option>
                            <option value="Daily" <?= ($_GET['recurrence_type'] ?? '') == 'Daily' ? 'selected' : '' ?>>Daily</option>
                            <option value="Weekly" <?= ($_GET['recurrence_type'] ?? '') == 'Weekly' ? 'selected' : '' ?>>Weekly</option>
                            <option value="Monthly" <?= ($_GET['recurrence_type'] ?? '') == 'Monthly' ? 'selected' : '' ?>>Monthly</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                    </div>
                </div>
            </form>

            <table class="table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Assigned To</th>
                        <th>Contact</th>
                        <th>Priority</th>
                        <th>Shift Task</th>
                        <th>Recurrence</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['task'] ?></td>
                            <td><?= $row['username'] ?></td>
                            <td><?= $row['contact_no'] ?></td>
                            <td><?= $row['priority'] ?></td>
                            <td><?= $row['shift_task'] ?></td>
                            <td><?= $row['recurrence_type'] ?></td>
                            <td><?= $row['status'] ?></td>
                            <td><a href="send-img.php?username=<?= $row['username'] ?>" class="btn btn-success">Send to WhatsApp</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>



<?php include 'include/footer.php'; ?>