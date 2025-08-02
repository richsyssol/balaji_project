<?php
// city_add.php
include 'includes/db_conn.php'; // Ensure this connects to DB correctly
session_start();

// CSRF token setup
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$city = '';
$pincode = '';
$errors = [];
$edit_id = null;
$is_edit = false;

// Detect edit
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_id = (int)$_GET['id'];
    $is_edit = true;

    $stmt = $conn->prepare("SELECT * FROM cities WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $city = $row['city'];
        $pincode = $row['pincode'];
    } else {
        die("Entry not found");
    }
}

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = (int)$_GET['id'];

    // Optional: Confirm the ID exists before deleting
    $stmt = $conn->prepare("DELETE FROM cities WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();

    header("Location: city_add.php"); // Redirect after delete
    exit;
}


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token mismatch");
    }

    $city = strtoupper(trim($_POST['city']));
    $pincode = trim($_POST['pincode']);

    if (empty($city)) {
        $errors[] = "City is required";
    }
    if (empty($pincode)) {
        $errors[] = "Pincode is required";
    }

    if (empty($errors)) {
        if (isset($_POST['edit_id']) && $_POST['edit_id'] !== '') {
            // UPDATE
            $update_id = (int)$_POST['edit_id'];
            $stmt = $conn->prepare("UPDATE cities SET city = ?, pincode = ? WHERE id = ?");
            $stmt->bind_param("ssi", $city, $pincode, $update_id);
            $stmt->execute();
            header("Location: city_add.php");
            exit;
        } else {
            // INSERT
            $stmt = $conn->prepare("INSERT INTO cities (city, pincode) VALUES (?, ?)");
            $stmt->bind_param("ss", $city, $pincode);
            $stmt->execute();
            header("Location: city_add.php");
            exit;
        }
    }
}
?>

<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container data-table p-5 ">
        <div class="ps-5">
            <div>
                <h1>CITY</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="client-form">client</a></li>
                <li class="breadcrumb-item active" aria-current="page">CITY</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">

        <h2><?= $is_edit ? 'Edit City' : 'Add City' ?></h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="city_add.php<?= $is_edit ? '?action=edit&id=' . $edit_id : '' ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <?php if ($is_edit): ?>
                <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6 mb-3 field">
                    <label class="form-label">City</label>
                    <input type="text" class="form-control" name="city" value="<?= htmlspecialchars($city) ?>" required>
                </div>
                <div class="col-md-6 mb-3 field">
                    <label class="form-label">Pincode</label>
                    <input type="text" class="form-control" name="pincode" value="<?= htmlspecialchars($pincode) ?>" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><?= $is_edit ? 'Update' : 'Add' ?> City</button>
        </form>

        <hr>

        
        <div class="mb-3 field">
            <label for="searchCity" class="form-label">Search City</label>
            <input type="text" id="searchCity" class="form-control" placeholder="Type city name to filter...">
        </div>

        <h4>All Cities</h4>

        <table class="table table-bordered" id="citiesTable">
            <thead>
                <tr>
                    <th>City</th>
                    <th>Pincode</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = $conn->query("SELECT * FROM cities ORDER BY id ASC");
                while ($r = $res->fetch_assoc()):
                ?>
                <tr>
                    <td><?= htmlspecialchars($r['city']) ?></td>
                    <td><?= htmlspecialchars($r['pincode']) ?></td>
                    <td>
                        <a href="city_add.php?action=edit&id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="city_add.php?action=delete&id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this city?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
</section>


<script>
document.getElementById('searchCity').addEventListener('input', function () {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#citiesTable tbody tr');

    rows.forEach(row => {
        const cityCell = row.querySelector('td:first-child'); // City column
        const cityText = cityCell.textContent.toLowerCase();

        if (cityText.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>