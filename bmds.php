<?php 

ob_start();  // Start output buffering
    include 'include/header.php'; 
    include 'include/head.php'; 
    include 'session_check.php';
?>


<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container data-table p-5 ">
        <div class="ps-5">
            <div>
                <h1>BMDS</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">BMDS</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
        
         <!-- Single Search Form -->

         

        <form method="GET" class="p-3">
            <?php
            include 'includes/db_conn.php';

            // Fetch Class of Vehicles dynamically
            $llr_class_sql = "SELECT DISTINCT llr_class FROM `bmds_entries` WHERE is_deleted = 0 ORDER BY llr_class ASC";
            $llr_class_result = $conn->query($llr_class_sql);
            $llr_class_options = [];
            while ($row = $llr_class_result->fetch_assoc()) {
                $llr_class_options[] = $row['llr_class'];
            }

            // Fetch Cities dynamically
            $city_sql = "SELECT DISTINCT city FROM `bmds_entries` WHERE is_deleted = 0 ORDER BY city ASC";
            $city_result = $conn->query($city_sql);
            $city_options = [];
            while ($row = $city_result->fetch_assoc()) {
                $city_options[] = $row['city'];
            }

            // Fetch all search values from GET
            $search_query = $_GET['search_query'] ?? '';
            $selected_llr_class = $_GET['llr_class'] ?? '';
            $bmds_type = $_GET['bmds_type'] ?? '';
            $selected_city = $_GET['city'] ?? '';
            $status = $_GET['status'] ?? '';
            $date_type = $_GET['date_type'] ?? '';
            $sort = $_GET['sort'] ?? '';
            $start_date = $_GET['start_date'] ?? '';
            $end_date = $_GET['end_date'] ?? date('Y-m-d');
            $items_per_page = $_GET['items_per_page'] ?? 10;
            $current_page = $_GET['page'] ?? 1;

            // Sorting logic
            $sortColumn = '';
            $order = '';
            if ($sort === 'sr_num_asc') {
                $sortColumn = 'sr_num';
                $order = 'ASC';
            } elseif ($sort === 'sr_num_desc') {
                $sortColumn = 'sr_num';
                $order = 'DESC';
            } elseif ($sort === 'reg_num_desc') {
                $sortColumn = 'reg_num';
                $order = 'DESC';
            } elseif ($sort === 'reg_num_asc') {
                $sortColumn = 'reg_num';
                $order = 'ASC';
            } elseif ($sort === 'date_desc') {
                $sortColumn = 'policy_date';
                $order = 'DESC';
            } elseif ($sort === 'date_asc') {
                $sortColumn = 'policy_date';
                $order = 'ASC';
            }
            ?>

            <div class="row">
                <!-- SEARCH FIELD -->
                <div class="col-md-4 field">
                    <label class="form-label">Search :</label>
                    <input type="text" name="search_query" class="form-control" value="<?= htmlspecialchars($search_query) ?>" placeholder="Search by Date, Name, Mobile and Car Type" />
                </div>

                <!-- CLASS OF VEHICLE -->
                <div class="col-md-2 field">
                    <label class="form-label">Class of Vehicle:</label>
                    <select name="llr_class" class="form-control">
                        <option value="">All</option>
                        <?php foreach ($llr_class_options as $option): ?>
                            <option value="<?= htmlspecialchars($option) ?>" <?= ($selected_llr_class === $option) ? 'selected' : '' ?>><?= htmlspecialchars($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- TYPE -->
                <div class="col-md-2 field">
                    <label class="form-label">Type :</label>
                    <select name="bmds_type" class="form-control">
                        <option value="">All</option>
                        <option value="LLR" <?= ($bmds_type === 'LLR') ? 'selected' : '' ?>>LLR</option>
                        <option value="DL" <?= ($bmds_type === 'DL') ? 'selected' : '' ?>>DL</option>
                        <option value="ADM" <?= ($bmds_type === 'ADM') ? 'selected' : '' ?>>ADM</option>
                    </select>
                </div>

                <!-- STATUS -->
                <div class="col-md-1 field">
                    <label class="form-label">Status :</label>
                    <select name="status" class="form-control">
                        <option value="">All</option>
                        <option value="Pending" <?= ($status === 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Complete" <?= ($status === 'Complete') ? 'selected' : '' ?>>Complete</option>
                    </select>
                </div>

                <!-- SORT -->
                <div class="col-md-1 field">
                    <label class="form-label">Sort By:</label>
                    <select name="sort" class="form-control">
                        <option value="">Select Sort</option>
                        <option value="sr_num_asc" <?= ($sort === 'sr_num_asc') ? 'selected' : '' ?>>Sr Num (ASC)</option>
                        <option value="sr_num_desc" <?= ($sort === 'sr_num_desc') ? 'selected' : '' ?>>Sr Num (DESC)</option>
                        <option value="reg_num_desc" <?= ($sort === 'reg_num_desc') ? 'selected' : '' ?>>Reg Num (DESC)</option>
                        <option value="reg_num_asc" <?= ($sort === 'reg_num_asc') ? 'selected' : '' ?>>Reg Num (ASC)</option>
                        <option value="date_desc" <?= ($sort === 'date_desc') ? 'selected' : '' ?>>Date (DESC)</option>
                        <option value="date_asc" <?= ($sort === 'date_asc') ? 'selected' : '' ?>>Date (ASC)</option>
                    </select>
                </div>

                <!-- CITY -->
                <div class="col-md-2 field">
                    <label class="form-label">City :</label>
                    <select name="city" class="form-control">
                        <option value="">All</option>
                        <?php foreach ($city_options as $option): ?>
                            <option value="<?= htmlspecialchars($option) ?>" <?= ($selected_city === $option) ? 'selected' : '' ?>><?= htmlspecialchars($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- DATE TYPE -->
                <div class="col-md-2 field">
                    <label class="form-label">Search By:</label>
                    <select name="date_type" class="form-select">
                        <option value="policy_date" <?= ($date_type == 'policy_date') ? 'selected' : '' ?>>Invert Date</option>
                        <option value="test_date" <?= ($date_type == 'test_date') ? 'selected' : '' ?>>Test Date</option>
                    </select>
                </div>

                <!-- START DATE -->
                <div class="col-md-2 field">
                    <label class="form-label">Start Date :</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>" />
                </div>

                <!-- END DATE -->
                <div class="col-md-2 field">
                    <label class="form-label">End Date :</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>" />
                </div>

                <!-- BUTTONS -->
                <div class="col-md-1">
                    <button type="submit" name="generate_report" class="btn sub-btn1 mt-4">Search</button>
                </div>

                <div class="col-md-1">
                    <a href="bmds" class="btn sub-btn1 mt-4">Reset</a>
                </div>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') : ?>
                    <div class="row justify-content-center align-items-center">
                        <div class="col-md-1">
                            <button type="button" class="btn sub-btn1 mt-4" data-bs-toggle="modal" data-bs-target="#passwordModal1">EXCEL</button>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn sub-btn1 mt-4" data-bs-toggle="modal" data-bs-target="#passwordModal2">PDF</button>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn sub-btn1 mt-4" onclick="showPasswordModal()">Print</button>
                        </div>
                        <div class="col-md-2">
                            <a href="bmds-finance" class="btn sub-btn1 mt-4">Finance</a>
                        </div>
                        <div class="col-md-2">
                            <a href="bmds_today" class="btn sub-btn1 mt-4">Today Training</a>
                        </div>
                        <div class="col-md-2">
                            <a href="bmds-reminder" class="btn sub-btn1 mt-4">Reminder Msg</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </form>
        
    <?php 
        
        include 'includes/db_conn.php';
        $report = [];

        // Initialize search variables
        $search_query = isset($_POST['search_query']) ? trim($_POST['search_query']) : (isset($_GET['search_query']) ? trim($_GET['search_query']) : '');
        $status = isset($_POST['status']) ? trim($_POST['status']) : (isset($_GET['status']) ? trim($_GET['status']) : '');
        $date_type = isset($_POST['date_type']) ? $_POST['date_type'] : (isset($_GET['date_type']) ? $_GET['date_type'] : 'policy_date');
        $start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : (isset($_GET['start_date']) ? trim($_GET['start_date']) : '');
        $end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : (isset($_GET['end_date']) ? trim($_GET['end_date']) : '');
        $selected_llr_class  = isset($_POST['llr_class']) ? trim($_POST['llr_class']) : (isset($_GET['llr_class']) ? trim($_GET['llr_class']) : '');
        $selected_city  = isset($_POST['city']) ? trim($_POST['city']) : (isset($_GET['city']) ? trim($_GET['city']) : '');
        $bmds_type  = isset($_POST['bmds_type']) ? trim($_POST['bmds_type']) : (isset($_GET['bmds_type']) ? trim($_GET['bmds_type']) : '');

        // Validate allowed date types for security
        $allowed_date_types = ['policy_date', 'test_date'];
        if (!in_array($date_type, $allowed_date_types)) {
            $date_type = 'policy_date';
        }

        // Total counters
        $total_records = 0;
        $total_amount = 0;
        $total_recov_amount = 0;
        $total_bal_amount = 0;
        $total_llr = 0;
        $llr_amount =0;
        $llr_recov_amount =0;
        $llr_bal_amount =0;
        $llr_fresh_count_class1 = 0;
        $llr_fresh_count_class2 = 0;
        $llr_fresh_count_class3 = 0;
        $llr_fresh_amount_class1 = 0;
        $llr_fresh_amount_class2 = 0;
        $llr_fresh_amount_class3 = 0;
        $llr_fresh_recov_amount_class1 = 0;
        $llr_fresh_recov_amount_class2 = 0;
        $llr_fresh_recov_amount_class3 = 0;
        $llr_fresh_bal_amount_class1 = 0;
        $llr_fresh_bal_amount_class2 = 0;
        $llr_fresh_bal_amount_class3 = 0;
        $llr_exempted_count_class1 = 0;
        $llr_exempted_count_class2 = 0;
        $llr_exempted_count_class3 = 0;
        $llr_exempted_amount_class1 = 0;
        $llr_exempted_amount_class2 = 0;
        $llr_exempted_amount_class3 = 0;
        $llr_exempted_recov_amount_class1 = 0;
        $llr_exempted_recov_amount_class2 = 0;
        $llr_exempted_recov_amount_class3 = 0;
        $llr_exempted_bal_amount_class1 = 0;
        $llr_exempted_bal_amount_class2 = 0;
        $llr_exempted_bal_amount_class3 = 0;
        $total_dl = 0;
        $dl_amount =0;
        $dl_recov_amount =0;
        $dl_bal_amount =0;
        $dl_fresh_count_class1 = 0;
        $dl_fresh_count_class2 = 0;
        $dl_fresh_count_class3 = 0;
        $dl_fresh_amount_class1 = 0;
        $dl_fresh_amount_class2 = 0;
        $dl_fresh_amount_class3 = 0;
        $dl_fresh_recov_amount_class1 = 0;
        $dl_fresh_recov_amount_class2 = 0;
        $dl_fresh_recov_amount_class3 = 0;
        $dl_fresh_bal_amount_class1 = 0;
        $dl_fresh_bal_amount_class2 = 0;
        $dl_fresh_bal_amount_class3 = 0;
        $dl_endst_count_class1 = 0;
        $dl_endst_count_class2 = 0;
        $dl_endst_count_class3 = 0;
        $dl_endst_amount_class1 = 0;
        $dl_endst_amount_class2 = 0;
        $dl_endst_amount_class3 = 0;
        $dl_endst_recov_amount_class1 = 0;
        $dl_endst_recov_amount_class2 = 0;
        $dl_endst_recov_amount_class3 = 0;
        $dl_endst_bal_amount_class1 = 0;
        $dl_endst_bal_amount_class2 = 0;
        $dl_endst_bal_amount_class3 = 0;
        $dl_revalid_count_class1 = 0;
        $dl_revalid_count_class2 = 0;
        $dl_revalid_count_class3 = 0;
        $dl_revalid_amount_class1 = 0;
        $dl_revalid_amount_class2 = 0;
        $dl_revalid_amount_class3 = 0;
        $dl_revalid_recov_amount_class1 = 0;
        $dl_revalid_recov_amount_class2 = 0;
        $dl_revalid_recov_amount_class3 = 0;
        $dl_revalid_bal_amount_class1 = 0;
        $dl_revalid_bal_amount_class2 = 0;
        $dl_revalid_bal_amount_class3 = 0;
        $total_adm = 0;
        $adm_amount =0;
        $adm_recov_amount =0;
        $adm_bal_amount =0;


        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $items_per_page = isset($_GET['items_per_page']) && $_GET['items_per_page'] === 'all' ? 'all' : 10;

        $sortColumn = 'sr_num';
        $order = 'ASC';

        if (isset($_POST['sort'])) {
            $sortOption = $_POST['sort'];
            if ($sortOption === 'reg_num_asc') {
                $sortColumn = 'reg_num';
                $order = 'ASC';
            } elseif ($sortOption === 'reg_num_desc') {
                $sortColumn = 'reg_num';
                $order = 'DESC';
            }
            elseif ($sortOption === 'sr_num_asc') {
    $sortColumn = 'CAST(sr_num AS UNSIGNED)';  // Cast to number for proper numeric sorting
    $order = 'ASC';
} elseif ($sortOption === 'sr_num_desc') {
    $sortColumn = 'CAST(sr_num AS UNSIGNED)';  // Cast to number for proper numeric sorting
    $order = 'DESC';
} elseif ($sortOption === 'date_asc') {
                $sortColumn = 'policy_date';
                $order = 'ASC';
            } elseif ($sortOption === 'date_desc') {
                $sortColumn = 'policy_date';
                $order = 'DESC';
            }
        }

        // If any filter is applied
        if (!empty($search_query) || !empty($status) || !empty($start_date) || !empty($end_date) || !empty($selected_llr_class) || !empty($selected_city) || !empty($bmds_type)) {

            // Convert dates to Y-m-d
            if (!empty($start_date)) {
                $start_date = date('Y-m-d', strtotime($start_date));
            }
            if (!empty($end_date)) {
                $end_date = date('Y-m-d', strtotime($end_date));
            }

            $sql = "SELECT * FROM `bmds_entries` WHERE is_deleted = 0";
            $params = [];
            $param_types = '';

            if (!empty($search_query)) {
                $sql .= " AND (client_name LIKE ? OR contact LIKE ? OR car_type LIKE ?)";
                $search_param = "%$search_query%";
                $params[] = $search_param;
                $params[] = $search_param;
                $params[] = $search_param;
                $param_types .= 'sss';
            }

            if (!empty($status) && ($status === 'Pending' || $status === 'Complete')) {
                $sql .= " AND form_status = ?";
                $params[] = $status;
                $param_types .= 's';
            }

            if (!empty($start_date) && !empty($end_date)) {
                $sql .= " AND $date_type BETWEEN ? AND ?";
                $params[] = $start_date;
                $params[] = $end_date;
                $param_types .= 'ss';
            } elseif (!empty($start_date)) {
                $sql .= " AND $date_type >= ?";
                $params[] = $start_date;
                $param_types .= 's';
            } elseif (!empty($end_date)) {
                $sql .= " AND $date_type <= ?";
                $params[] = $end_date;
                $param_types .= 's';
            }

            if (!empty($selected_llr_class)) {
                $sql .= " AND llr_class LIKE ?";
                $llr_class_param = "%$selected_llr_class%";
                $params[] = $llr_class_param;
                $param_types .= 's';
            }

            if (!empty($selected_city)) {
                $sql .= " AND city LIKE ?";
                $city_param = "%$selected_city%";
                $params[] = $city_param;
                $param_types .= 's';
            }

            if (!empty($bmds_type)) {
                if ($bmds_type === 'LLR' || $bmds_type === 'DL' || $bmds_type === 'ADM') {
                    $sql .= " AND bmds_type = ?";
                    $params[] = $bmds_type;
                    $param_types .= 's';
                }
            }

            $offset = 0;
            
            if ($items_per_page === 'all') {
                $sql .= " ORDER BY $sortColumn $order";
            } else {
                $offset = ($current_page - 1) * $items_per_page;
                $sql .= " ORDER BY $sortColumn $order LIMIT ?, ?";
                $params[] = $offset;
                $params[] = $items_per_page;
                $param_types .= 'ii';
            }

            $stmt = $conn->prepare($sql);
            if ($param_types) {
                $stmt->bind_param($param_types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();


            
            // Count Query 
            $count_sql = "SELECT 
                            COUNT(*) as total, 
                            SUM(amount) as total_amount, 
                            SUM(recov_amount) as total_recov_amount,
                            SUM(bal_amount ) as total_bal_amount,
                            SUM(CASE WHEN bmds_type = 'LLR' THEN 1 ELSE 0 END) as total_llr,
                            SUM(CASE WHEN bmds_type = 'LLR' THEN amount ELSE 0 END) as llr_amount,
                            SUM(CASE WHEN bmds_type = 'LLR' THEN recov_amount ELSE 0 END) as llr_recov_amount,
                            SUM(CASE WHEN bmds_type = 'LLR' THEN bal_amount ELSE 0 END) as llr_bal_amount,
                            SUM(CASE WHEN class='1' AND llr_type = 'FRESH' THEN 1 ELSE 0 END) as llr_fresh_count_class1,
                            SUM(CASE WHEN class='2' AND llr_type = 'FRESH' THEN 1 ELSE 0 END) as llr_fresh_count_class2,
                            SUM(CASE WHEN class='3' AND llr_type = 'FRESH' THEN 1 ELSE 0 END) as llr_fresh_count_class3,
                            SUM(CASE WHEN class='1' AND llr_type = 'FRESH' THEN amount ELSE 0 END) as llr_fresh_amount_class1,
                            SUM(CASE WHEN class='2' AND llr_type = 'FRESH' THEN amount ELSE 0 END) as llr_fresh_amount_class2,
                            SUM(CASE WHEN class='3' AND llr_type = 'FRESH' THEN amount ELSE 0 END) as llr_fresh_amount_class3,
                            SUM(CASE WHEN class='1' AND llr_type = 'FRESH' THEN recov_amount ELSE 0 END) as llr_fresh_recov_amount_class1,
                            SUM(CASE WHEN class='2' AND llr_type = 'FRESH' THEN recov_amount ELSE 0 END) as llr_fresh_recov_amount_class2,
                            SUM(CASE WHEN class='3' AND llr_type = 'FRESH' THEN recov_amount ELSE 0 END) as llr_fresh_recov_amount_class3,
                            SUM(CASE WHEN class='1' AND llr_type = 'FRESH' THEN bal_amount ELSE 0 END) as llr_fresh_bal_amount_class1,
                            SUM(CASE WHEN class='2' AND llr_type = 'FRESH' THEN bal_amount ELSE 0 END) as llr_fresh_bal_amount_class2,
                            SUM(CASE WHEN class='3' AND llr_type = 'FRESH' THEN bal_amount ELSE 0 END) as llr_fresh_bal_amount_class3,
                            SUM(CASE WHEN class='1' AND llr_type = 'EXEMPTED' THEN 1 ELSE 0 END) as llr_exempted_count_class1,
                            SUM(CASE WHEN class='2' AND llr_type = 'EXEMPTED' THEN 1 ELSE 0 END) as llr_exempted_count_class2,
                            SUM(CASE WHEN class='3' AND llr_type = 'EXEMPTED' THEN 1 ELSE 0 END) as llr_exempted_count_class3,
                            SUM(CASE WHEN class='1' AND llr_type = 'EXEMPTED' THEN amount ELSE 0 END) as llr_exempted_amount_class1,
                            SUM(CASE WHEN class='2' AND llr_type = 'EXEMPTED' THEN amount ELSE 0 END) as llr_exempted_amount_class2,
                            SUM(CASE WHEN class='3' AND llr_type = 'EXEMPTED' THEN amount ELSE 0 END) as llr_exempted_amount_class3,
                            SUM(CASE WHEN class='1' AND llr_type = 'EXEMPTED' THEN recov_amount ELSE 0 END) as llr_exempted_recov_amount_class1,
                            SUM(CASE WHEN class='2' AND llr_type = 'EXEMPTED' THEN recov_amount ELSE 0 END) as llr_exempted_recov_amount_class2,
                            SUM(CASE WHEN class='3' AND llr_type = 'EXEMPTED' THEN recov_amount ELSE 0 END) as llr_exempted_recov_amount_class3,
                            SUM(CASE WHEN class='1' AND llr_type = 'EXEMPTED' THEN bal_amount ELSE 0 END) as llr_exempted_bal_amount_class1,
                            SUM(CASE WHEN class='2' AND llr_type = 'EXEMPTED' THEN bal_amount ELSE 0 END) as llr_exempted_bal_amount_class2,
                            SUM(CASE WHEN class='3' AND llr_type = 'EXEMPTED' THEN bal_amount ELSE 0 END) as llr_exempted_bal_amount_class3,
                            SUM(CASE WHEN bmds_type = 'DL' THEN 1 ELSE 0 END) as total_dl,
                            SUM(CASE WHEN bmds_type = 'DL' THEN amount ELSE 0 END) as dl_amount,
                            SUM(CASE WHEN bmds_type = 'DL' THEN recov_amount ELSE 0 END) as dl_recov_amount,
                            SUM(CASE WHEN bmds_type = 'DL' THEN bal_amount ELSE 0 END) as dl_bal_amount,
                            SUM(CASE WHEN class='1' AND mdl_type = 'FRESH' THEN 1 ELSE 0 END) as dl_fresh_count_class1,
                            SUM(CASE WHEN class='2' AND mdl_type = 'FRESH' THEN 1 ELSE 0 END) as dl_fresh_count_class2,
                            SUM(CASE WHEN class='3' AND mdl_type = 'FRESH' THEN 1 ELSE 0 END) as dl_fresh_count_class3,
                            SUM(CASE WHEN class='1' AND mdl_type = 'FRESH' THEN amount ELSE 0 END) as dl_fresh_amount_class1,
                            SUM(CASE WHEN class='2' AND mdl_type = 'FRESH' THEN amount ELSE 0 END) as dl_fresh_amount_class2,
                            SUM(CASE WHEN class='3' AND mdl_type = 'FRESH' THEN amount ELSE 0 END) as dl_fresh_amount_class3,
                            SUM(CASE WHEN class='1' AND mdl_type = 'FRESH' THEN recov_amount ELSE 0 END) as dl_fresh_recov_amount_class1,
                            SUM(CASE WHEN class='2' AND mdl_type = 'FRESH' THEN recov_amount ELSE 0 END) as dl_fresh_recov_amount_class2,
                            SUM(CASE WHEN class='3' AND mdl_type = 'FRESH' THEN recov_amount ELSE 0 END) as dl_fresh_recov_amount_class3,
                            SUM(CASE WHEN class='1' AND mdl_type = 'FRESH' THEN bal_amount ELSE 0 END) as dl_fresh_bal_amount_class1,
                            SUM(CASE WHEN class='2' AND mdl_type = 'FRESH' THEN bal_amount ELSE 0 END) as dl_fresh_bal_amount_class2,
                            SUM(CASE WHEN class='3' AND mdl_type = 'FRESH' THEN bal_amount ELSE 0 END) as dl_fresh_bal_amount_class3,
                            SUM(CASE WHEN class='1' AND mdl_type = 'ENDST' THEN 1 ELSE 0 END) as dl_endst_count_class1,
                            SUM(CASE WHEN class='2' AND mdl_type = 'ENDST' THEN 1 ELSE 0 END) as dl_endst_count_class2,
                            SUM(CASE WHEN class='3' AND mdl_type = 'ENDST' THEN 1 ELSE 0 END) as dl_endst_count_class3,
                            SUM(CASE WHEN class='1' AND mdl_type = 'ENDST' THEN amount ELSE 0 END) as dl_endst_amount_class1,
                            SUM(CASE WHEN class='2' AND mdl_type = 'ENDST' THEN amount ELSE 0 END) as dl_endst_amount_class2,
                            SUM(CASE WHEN class='3' AND mdl_type = 'ENDST' THEN amount ELSE 0 END) as dl_endst_amount_class3,
                            SUM(CASE WHEN class='1' AND mdl_type = 'ENDST' THEN recov_amount ELSE 0 END) as dl_endst_recov_amount_class1,
                            SUM(CASE WHEN class='2' AND mdl_type = 'ENDST' THEN recov_amount ELSE 0 END) as dl_endst_recov_amount_class2,
                            SUM(CASE WHEN class='3' AND mdl_type = 'ENDST' THEN recov_amount ELSE 0 END) as dl_endst_recov_amount_class3,
                            SUM(CASE WHEN class='1' AND mdl_type = 'ENDST' THEN bal_amount ELSE 0 END) as dl_endst_bal_amount_class1,
                            SUM(CASE WHEN class='2' AND mdl_type = 'ENDST' THEN bal_amount ELSE 0 END) as dl_endst_bal_amount_class2,
                            SUM(CASE WHEN class='3' AND mdl_type = 'ENDST' THEN bal_amount ELSE 0 END) as dl_endst_bal_amount_class3,
                            SUM(CASE WHEN class='1' AND mdl_type = 'REVALID' THEN 1 ELSE 0 END) as dl_revalid_count_class1,
                            SUM(CASE WHEN class='2' AND mdl_type = 'REVALID' THEN 1 ELSE 0 END) as dl_revalid_count_class2,
                            SUM(CASE WHEN class='3' AND mdl_type = 'REVALID' THEN 1 ELSE 0 END) as dl_revalid_count_class3,
                            SUM(CASE WHEN class='1' AND mdl_type = 'REVALID' THEN amount ELSE 0 END) as dl_revalid_amount_class1,
                            SUM(CASE WHEN class='2' AND mdl_type = 'REVALID' THEN amount ELSE 0 END) as dl_revalid_amount_class2,
                            SUM(CASE WHEN class='3' AND mdl_type = 'REVALID' THEN amount ELSE 0 END) as dl_revalid_amount_class3,
                            SUM(CASE WHEN class='1' AND mdl_type = 'REVALID' THEN recov_amount ELSE 0 END) as dl_revalid_recov_amount_class1,
                            SUM(CASE WHEN class='2' AND mdl_type = 'REVALID' THEN recov_amount ELSE 0 END) as dl_revalid_recov_amount_class2,
                            SUM(CASE WHEN class='3' AND mdl_type = 'REVALID' THEN recov_amount ELSE 0 END) as dl_revalid_recov_amount_class3,
                            SUM(CASE WHEN class='1' AND mdl_type = 'REVALID' THEN bal_amount ELSE 0 END) as dl_revalid_bal_amount_class1,
                            SUM(CASE WHEN class='2' AND mdl_type = 'REVALID' THEN bal_amount ELSE 0 END) as dl_revalid_bal_amount_class2,
                            SUM(CASE WHEN class='3' AND mdl_type = 'REVALID' THEN bal_amount ELSE 0 END) as dl_revalid_bal_amount_class3,
                            SUM(CASE WHEN bmds_type = 'ADM' THEN 1 ELSE 0 END) as total_adm,
                            SUM(CASE WHEN bmds_type = 'ADM' THEN amount ELSE 0 END) as adm_amount,
                            SUM(CASE WHEN bmds_type = 'ADM' THEN recov_amount ELSE 0 END) as adm_recov_amount,
                            SUM(CASE WHEN bmds_type = 'ADM' THEN bal_amount ELSE 0 END) as adm_bal_amount
                        FROM `bmds_entries` 
                        WHERE is_deleted = 0";
            $count_params = [];
            $count_param_types = '';

            if (!empty($search_query)) {
                $count_sql .= " AND (client_name LIKE ? OR contact LIKE ? OR car_type LIKE ?)";
                $count_params[] = $search_param;
                $count_params[] = $search_param;
                $count_params[] = $search_param;
                $count_param_types .= 'sss';
            }

            if (!empty($status) && ($status === 'Pending' || $status === 'Complete')) {
                $count_sql .= " AND form_status = ?";
                $count_params[] = $status;
                $count_param_types .= 's';
            }

            if (!empty($start_date) && !empty($end_date)) {
                $count_sql .= " AND $date_type BETWEEN ? AND ?";
                $count_params[] = $start_date;
                $count_params[] = $end_date;
                $count_param_types .= 'ss';
            } elseif (!empty($start_date)) {
                $count_sql .= " AND $date_type >= ?";
                $count_params[] = $start_date;
                $count_param_types .= 's';
            } elseif (!empty($end_date)) {
                $count_sql .= " AND $date_type <= ?";
                $count_params[] = $end_date;
                $count_param_types .= 's';
            }

            if (!empty($selected_llr_class)) {
                $count_sql .= " AND llr_class LIKE ?";
                $count_params[] = "%$selected_llr_class%";
                $count_param_types .= 's';
            }

            if (!empty($selected_city)) {
                $count_sql .= " AND city LIKE ?";
                $count_params[] = "%$selected_city%";
                $count_param_types .= 's';
            }

            if (!empty($bmds_type)) {
                if ($bmds_type === 'LLR' || $bmds_type === 'DL' || $bmds_type === 'ADM') {
                    $count_sql .= " AND bmds_type = ?";
                    $count_params[] = $bmds_type;
                    $count_param_types .= 's';
                }
            }

            $count_stmt = $conn->prepare($count_sql);
            if ($count_param_types && !empty($count_params)) {
                $count_stmt->bind_param($count_param_types, ...$count_params);
            }
            
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $count_data = $count_result->fetch_assoc();

          

            $total_records = $count_data['total'];
            $total_amount = $count_data['total_amount'] ?? 0;
            $total_recov_amount = $count_data['total_recov_amount'] ?? 0;
            $total_bal_amount = $count_data['total_bal_amount'] ?? 0;
            $total_llr = $count_data['total_llr'] ?? 0;
            $llr_amount = $count_data['llr_amount'] ?? 0;
            $llr_recov_amount = $count_data['llr_recov_amount'] ?? 0;
            $llr_bal_amount = $count_data['llr_bal_amount'] ?? 0;
            $llr_fresh_count_class1 = $count_data['llr_fresh_count_class1'] ?? 0;
            $llr_fresh_count_class2 = $count_data['llr_fresh_count_class2'] ?? 0;
            $llr_fresh_count_class3 = $count_data['llr_fresh_count_class3'] ?? 0;
            $llr_fresh_amount_class1 = $count_data['llr_fresh_amount_class1'] ?? 0;
            $llr_fresh_amount_class2 = $count_data['llr_fresh_amount_class2'] ?? 0;
            $llr_fresh_amount_class3 = $count_data['llr_fresh_amount_class3'] ?? 0;
            $llr_fresh_recov_amount_class1 = $count_data['llr_fresh_recov_amount_class1'] ?? 0;
            $llr_fresh_recov_amount_class2 = $count_data['llr_fresh_recov_amount_class2'] ?? 0;
            $llr_fresh_recov_amount_class3 = $count_data['llr_fresh_recov_amount_class3'] ?? 0;
            $llr_fresh_bal_amount_class1 = $count_data['llr_fresh_bal_amount_class1'] ?? 0;
            $llr_fresh_bal_amount_class2 = $count_data['llr_fresh_bal_amount_class2'] ?? 0;
            $llr_fresh_bal_amount_class3 = $count_data['llr_fresh_bal_amount_class3'] ?? 0;
            $llr_exempted_count_class1 = $count_data['llr_exempted_count_class1'] ?? 0;
            $llr_exempted_count_class2 = $count_data['llr_exempted_count_class2'] ?? 0;
            $llr_exempted_count_class3 = $count_data['llr_exempted_count_class3'] ?? 0;
            $llr_exempted_amount_class1 = $count_data['llr_exempted_amount_class1'] ?? 0;
            $llr_exempted_amount_class2 = $count_data['llr_exempted_amount_class2'] ?? 0;
            $llr_exempted_amount_class3 = $count_data['llr_exempted_amount_class3'] ?? 0;
            $llr_exempted_recov_amount_class1 = $count_data['llr_exempted_recov_amount_class1'] ?? 0;
            $llr_exempted_recov_amount_class2 = $count_data['llr_exempted_recov_amount_class2'] ?? 0;
            $llr_exempted_recov_amount_class3 = $count_data['llr_exempted_recov_amount_class3'] ?? 0;
            $llr_exempted_bal_amount_class1 = $count_data['llr_exempted_bal_amount_class1'] ?? 0;
            $llr_exempted_bal_amount_class2 = $count_data['llr_exempted_bal_amount_class2'] ?? 0;
            $llr_exempted_bal_amount_class3 = $count_data['llr_exempted_bal_amount_class3'] ?? 0;
            $total_dl = $count_data['total_dl'] ?? 0;
            $dl_amount = $count_data['dl_amount'] ?? 0;
            $dl_recov_amount = $count_data['dl_recov_amount'] ?? 0;
            $dl_bal_amount = $count_data['dl_bal_amount'] ?? 0;
            $dl_fresh_count_class1 = $count_data['dl_fresh_count_class1'] ?? 0;
            $dl_fresh_count_class2 = $count_data['dl_fresh_count_class2'] ?? 0;
            $dl_fresh_count_class3 = $count_data['dl_fresh_count_class3'] ?? 0;
            $dl_fresh_amount_class1 = $count_data['dl_fresh_amount_class1'] ?? 0;
            $dl_fresh_amount_class2 = $count_data['dl_fresh_amount_class2'] ?? 0;
            $dl_fresh_amount_class3 = $count_data['dl_fresh_amount_class3'] ?? 0;
            $dl_fresh_recov_amount_class1 = $count_data['dl_fresh_recov_amount_class1'] ?? 0;
            $dl_fresh_recov_amount_class2 = $count_data['dl_fresh_recov_amount_class2'] ?? 0;
            $dl_fresh_recov_amount_class3 = $count_data['dl_fresh_recov_amount_class3'] ?? 0;
            $dl_fresh_bal_amount_class1 = $count_data['dl_fresh_bal_amount_class1'] ?? 0;
            $dl_fresh_bal_amount_class2 = $count_data['dl_fresh_bal_amount_class2'] ?? 0;
            $dl_fresh_bal_amount_class3 = $count_data['dl_fresh_bal_amount_class3'] ?? 0;
            $dl_endst_count_class1 = $count_data['dl_endst_count_class1'] ?? 0;
            $dl_endst_count_class2 = $count_data['dl_endst_count_class2'] ?? 0;
            $dl_endst_count_class3 = $count_data['dl_endst_count_class3'] ?? 0;
            $dl_endst_amount_class1 = $count_data['dl_endst_amount_class1'] ?? 0;
            $dl_endst_amount_class2 = $count_data['dl_endst_amount_class2'] ?? 0;
            $dl_endst_amount_class3 = $count_data['dl_endst_amount_class3'] ?? 0;
            $dl_endst_recov_amount_class1 = $count_data['dl_endst_recov_amount_class1'] ?? 0;
            $dl_endst_recov_amount_class2 = $count_data['dl_endst_recov_amount_class2'] ?? 0;
            $dl_endst_recov_amount_class3 = $count_data['dl_endst_recov_amount_class3'] ?? 0;
            $dl_endst_bal_amount_class1 = $count_data['dl_endst_bal_amount_class1'] ?? 0;
            $dl_endst_bal_amount_class2 = $count_data['dl_endst_bal_amount_class2'] ?? 0;
            $dl_endst_bal_amount_class3 = $count_data['dl_endst_bal_amount_class3'] ?? 0;
            $dl_revalid_count_class1 = $count_data['dl_revalid_count_class1'] ?? 0;
            $dl_revalid_count_class2 = $count_data['dl_revalid_count_class2'] ?? 0;
            $dl_revalid_count_class3 = $count_data['dl_revalid_count_class3'] ?? 0;
            $dl_revalid_amount_class1 = $count_data['dl_revalid_amount_class1'] ?? 0;
            $dl_revalid_amount_class2 = $count_data['dl_revalid_amount_class2'] ?? 0;
            $dl_revalid_amount_class3 = $count_data['dl_revalid_amount_class3'] ?? 0;
            $dl_revalid_recov_amount_class1 = $count_data['dl_revalid_recov_amount_class1'] ?? 0;
            $dl_revalid_recov_amount_class2 = $count_data['dl_revalid_recov_amount_class2'] ?? 0;
            $dl_revalid_recov_amount_class3 = $count_data['dl_revalid_recov_amount_class3'] ?? 0;
            $dl_revalid_bal_amount_class1 = $count_data['dl_revalid_bal_amount_class1'] ?? 0;
            $dl_revalid_bal_amount_class2 = $count_data['dl_revalid_bal_amount_class2'] ?? 0;
            $dl_revalid_bal_amount_class3 = $count_data['dl_revalid_bal_amount_class3'] ?? 0;
            $total_adm = $count_data['total_adm'] ?? 0;
            $adm_amount = $count_data['adm_amount'] ?? 0;
            $adm_recov_amount = $count_data['adm_recov_amount'] ?? 0;
            $adm_bal_amount = $count_data['adm_bal_amount'] ?? 0;

            
            $total_pages = $items_per_page === 'all' ? 1 : ceil($total_records / $items_per_page);

            // Grouped Data
            $groupedData = [];
            while ($row = $result->fetch_assoc()) {
                $bmds_type = $row['bmds_type'];
                $groupedData[$bmds_type][] = $row;
            }

        } else {
            $result = null;
        }                               


            
        if (isset($_POST['generate_csv'])) {

            // Get the submitted password
            $admin_password = $_POST['admin_password'] ?? '';
        
            // Fetch the stored hashed password from the database
            $sql = "SELECT password FROM file WHERE file_type = 'CSV' LIMIT 1"; 
            $stmt = $conn->prepare($sql);   
            $stmt->execute();
            $result = $stmt->get_result();
        
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $hashed_password = $row['password'];
        
                // Verify the password
                if (password_verify($admin_password, $hashed_password)) {
                    // Clear output buffer to avoid unwanted HTML
                    if (ob_get_length()) {
                        ob_end_clean();
                    }
        
        
        
            // Set headers for CSV download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=rto-report.csv');
        
            // Open the output stream to write CSV data
            $output = fopen('php://output', 'w');
        
            // Add the header row to the CSV file
            fputcsv($output, ['Reg Num', 'Date', 'Client Name', 'Contact', 'Status']);
        
            // Prepare dynamic SQL query for date range if provided
            if (!empty($start_date) && !empty($end_date)) {
                $sql = "SELECT * FROM bmds_entries WHERE policy_date BETWEEN ? AND ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ss', $start_date, $end_date); // Bind parameters for date range
            } else {
                // If no date range is set, use default query
                $sql = "SELECT * FROM bmds_entries";
                $stmt = $conn->prepare($sql);
            }
        
            // Execute the query
            $stmt->execute();
            $result = $stmt->get_result();
        
            if ($result->num_rows > 0) {
                // Fetch each row and write it to the CSV file
                while ($item = $result->fetch_assoc()) {
                    fputcsv($output, [
                        $item['reg_num'],
                        (new DateTime($item['policy_date']))->format('d/m/Y'),  // Format date as 'd/m/Y'
                        $item['client_name'],
                        $item['contact'],
                        $item['form_status'],
                    ]);
                }
            } else {
                // If no records are found, display a message
                fputcsv($output, ['No records found']);
            }
        
            // Close the database connection
            $conn->close();
        
            // Close the CSV output
            fclose($output);
        
            // End the script
            exit();
        }
        else {
            // Incorrect password
            echo "<script>
                    alert('Incorrect password. Please try again.');
                    window.history.back();
                </script>";
            exit();
        }
    } else {
        // No password stored
        echo "<script>
                alert('Admin password is not set. Please contact support.');
                window.history.back();
            </script>";
        exit();
    }
}

    
         // If user clicks on 'Generate PDF'
        if (isset($_POST['generate_pdf'])) {

            // Get the submitted password
            $admin_password = $_POST['admin_password'] ?? '';
        
            // Fetch the stored hashed password from the database
            $sql = "SELECT password FROM file WHERE file_type = 'PDF' LIMIT 1"; // Assuming only one admin password
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
        
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $hashed_password = $row['password'];
        
                // Verify the password
                if (password_verify($admin_password, $hashed_password)) {
                    // Clear output buffer to avoid unwanted HTML
                    if (ob_get_length()) {
                        ob_end_clean();
                    }

            
            require('fpdf/fpdf.php');
            
            // Instantiate and use the FPDF class 
            $pdf = new FPDF('L', 'mm', 'A3'); // Landscape mode with A3 size
            $pdf->SetMargins(10, 10, 10);
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 10, 'GIC Report', 0, 1, 'C');
            
            // Add column headers
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(20, 10, 'Reg Num', 1);
            $pdf->Cell(25, 10, 'Date', 1);
            $pdf->Cell(70, 10, 'Client Name', 1);
            $pdf->Cell(30, 10, 'Contact', 1);
            $pdf->Cell(70, 10, 'Status', 1);
            
            $pdf->Ln();
            
            // Get start_date and end_date from POST
            $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
            $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
            
            // Prepare dynamic SQL query for date range if provided
            if (!empty($start_date) && !empty($end_date)) {
                // Format dates for SQL query
                $start_date = date('Y-m-d', strtotime($start_date));  // Ensure the date format is correct
                $end_date = date('Y-m-d', strtotime($end_date));
                
                $sql = "SELECT * FROM bmds_entries WHERE policy_date BETWEEN ? AND ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ss', $start_date, $end_date); // Bind parameters for date range
            } else {
                // If no date range is set, use default query
                $sql = "SELECT * FROM bmds_entries";
                $stmt = $conn->prepare($sql);
            }
        
            // Execute query
            $stmt->execute();
            $result = $stmt->get_result(); // Fetch results
            
            // Add data rows if there are results
            $pdf->SetFont('Arial', '', 10);
            if ($result->num_rows > 0) {
                while ($item = $result->fetch_assoc()) {
                    $pdf->Cell(20, 10, $item['reg_num'], 1);
                    $pdf->Cell(25, 10, (new DateTime($item['policy_date']))->format('d/m/Y'), 1);
                    $pdf->Cell(70, 10, $item['client_name'], 1);
                    $pdf->Cell(30, 10, $item['contact'], 1);
                    $pdf->Cell(70, 10, $item['form_status'], 1);
                    
                    $pdf->Ln();
                }
            } else {
                // No data found, display a message in the PDF
                $pdf->Cell(0, 10, 'No records found for the selected date range.', 0, 1, 'C');
            }
            
            // Output the PDF (force download)
            $pdf->Output('D', 'rto-report.pdf'); // Force download
            exit();
        }
        else {
            // Incorrect password
            echo "<script>
                    alert('Incorrect password. Please try again.');
                    window.history.back();
                </script>";
            exit();
        }
            } else {
                // No password stored
                echo "<script>
                        alert('Admin password is not set. Please contact support.');
                        window.history.back();
                    </script>";
                exit();
            }
        }
        
            
            
            // Close the connection
            $conn->close();
    
        // }
            
             
            
?>
        
        
        
         
        
     <div id="reportSection">  
        
     
      <h1>Summary :</h1>
        

        <table class="table table-bordered my-5">
            <thead>
                <tr>
                    <th>Class</th>
                    <th>Total Entry</th>
                    <th>----------</th>
                    <th>----------</th>
                    <th>Premium Amount</th>
                    <th>Recovery Amount</th>
                    <th>Excess Amount</th>
                </tr>
            </thead>
            <tbody> 
                <tr class="text-center">
                    <td colspan="7"><strong>LLR FRESH</strong></td>
                </tr>
                <tr>
                    <td><strong>1</strong></td>
                    <td><?php echo $llr_fresh_count_class1; ?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $llr_fresh_amount_class1; ?></td>
                    <td><?php echo $llr_fresh_recov_amount_class1; ?></td>
                    <td><?php echo $llr_fresh_bal_amount_class1; ?></td>
                </tr>
                <tr>
                    <td><strong>2</strong></td>
                    <td><?php echo $llr_fresh_count_class2; ?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $llr_fresh_amount_class2; ?></td>
                    <td><?php echo $llr_fresh_recov_amount_class2; ?></td>
                    <td><?php echo $llr_fresh_bal_amount_class2; ?></td>
                </tr>
                <tr>
                    <td><strong>3</strong></td>
                    <td><?php echo $llr_fresh_count_class3; ?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $llr_fresh_amount_class3; ?></td>
                    <td><?php echo $llr_fresh_recov_amount_class3; ?></td>
                    <td><?php echo $llr_fresh_bal_amount_class3; ?></td>
                </tr>
                <tr class="text-center">
                    <td colspan="7"><strong>LLR EXEMPTED</strong></td>
                </tr>
                <tr>
                    <td><strong>1</strong></td>
                    <td><?php echo $llr_exempted_count_class1; ?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $llr_exempted_amount_class1; ?></td>
                    <td><?php echo $llr_exempted_recov_amount_class1; ?></td>
                    <td><?php echo $llr_exempted_bal_amount_class1; ?></td>
                </tr>
                <tr>
                    <td><strong>2</strong></td>
                    <td><?php echo $llr_exempted_count_class2; ?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $llr_exempted_amount_class2; ?></td>
                    <td><?php echo $llr_exempted_recov_amount_class2; ?></td>
                    <td><?php echo $llr_exempted_bal_amount_class2; ?></td>
                </tr>
                <tr>
                    <td><strong>3</strong></td>
                    <td><?php echo $llr_exempted_count_class3; ?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $llr_exempted_amount_class3; ?></td>
                    <td><?php echo $llr_exempted_recov_amount_class3; ?></td>
                    <td><?php echo $llr_exempted_bal_amount_class3; ?></td>
                </tr>
                <tr class="text-center">
                    <td colspan="7"><strong>LLR TOTAL</strong></td>
                </tr>
                <tr>
                    <td><strong>LLR</strong></td>
                    <td><?php echo $total_llr; ?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $llr_amount; ?></td>
                    <td><?php echo $llr_recov_amount; ?></td>
                    <td><?php echo $llr_bal_amount; ?></td>
                </tr>
                <tr class="text-center">
                    <td colspan="7"><strong>DL FRESH</strong></td>
                </tr>
                <tr>
                    <td><strong>1</strong></td>
                    <td><?php echo $dl_fresh_count_class1; ?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $dl_fresh_amount_class1; ?></td>
                    <td><?php echo $dl_fresh_recov_amount_class1; ?></td>
                    <td><?php echo $dl_fresh_bal_amount_class1; ?></td>
                </tr>
                <tr>
                    <td><strong>2</strong></td>
                    <td><?php echo $dl_fresh_count_class2; ?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $dl_fresh_amount_class2; ?></td>
                    <td><?php echo $dl_fresh_recov_amount_class2; ?></td>
                    <td><?php echo $dl_fresh_bal_amount_class2; ?></td>
                </tr>
                <tr>
                    <td><strong>3</strong></td>
                    <td><?php echo $dl_fresh_count_class3; ?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $dl_fresh_amount_class3; ?></td>
                    <td><?php echo $dl_fresh_recov_amount_class3; ?></td>
                    <td><?php echo $dl_fresh_bal_amount_class3; ?></td>
                </tr>
                <tr class="text-center">
                    <td colspan="7"><strong>DL ENDST</strong></td>
                </tr>
                <tr>
                    <td><strong>1</strong></td>
                    <td><?php echo $dl_endst_count_class1; ?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $dl_endst_amount_class1; ?></td>
                    <td><?php echo $dl_endst_recov_amount_class1; ?></td>
                    <td><?php echo $dl_endst_bal_amount_class1; ?></td>
                </tr>
                <tr>
                    <td><strong>2</strong></td>
                    <td><?php echo $dl_endst_count_class2; ?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $dl_endst_amount_class2; ?></td>
                    <td><?php echo $dl_endst_recov_amount_class2; ?></td>
                    <td><?php echo $dl_endst_bal_amount_class2; ?></td>
                </tr>
                <tr>
                    <td><strong>3</strong></td>
                    <td><?php echo $dl_endst_count_class3; ?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $dl_endst_amount_class3; ?></td>
                    <td><?php echo $dl_endst_recov_amount_class3; ?></td>
                    <td><?php echo $dl_endst_bal_amount_class3; ?></td>
                </tr>
                <tr class="text-center">
                    <td colspan="7"><strong>DL REVALID</strong></td>
                </tr>
                <tr>
                    <td><strong>1</strong></td>
                    <td><?php echo $dl_revalid_count_class1; ?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $dl_revalid_amount_class1; ?></td>
                    <td><?php echo $dl_revalid_recov_amount_class1; ?></td>
                    <td><?php echo $dl_revalid_bal_amount_class1; ?></td>
                </tr>
                <tr>
                    <td><strong>2</strong></td>
                    <td><?php echo $dl_revalid_count_class2; ?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $dl_revalid_amount_class2; ?></td>
                    <td><?php echo $dl_revalid_recov_amount_class2; ?></td>
                    <td><?php echo $dl_revalid_bal_amount_class2; ?></td>
                </tr>
                <tr>
                    <td><strong>3</strong></td>
                    <td><?php echo $dl_revalid_count_class3; ?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $dl_revalid_amount_class3; ?></td>
                    <td><?php echo $dl_revalid_recov_amount_class3; ?></td>
                    <td><?php echo $dl_revalid_bal_amount_class3; ?></td>
                </tr>
                <tr class="text-center">
                    <td colspan="7"><strong>DL TOTAL</strong></td>
                </tr>
                <tr>
                    <td><strong>DL</strong></td>
                    <td><?php echo $total_dl; ?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $dl_amount; ?></td>
                    <td><?php echo $dl_recov_amount; ?></td>
                    <td><?php echo $dl_bal_amount; ?></td>
                </tr>
                <tr class="text-center">
                    <td colspan="7"><strong>ADM TOTAL</strong></td>
                </tr>
                <tr>
                    <td><strong>ADM</strong></td>
                    <td><?php echo $total_adm; ?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $adm_amount; ?></td>
                    <td><?php echo $adm_recov_amount; ?></td>
                    <td><?php echo $adm_bal_amount; ?></td>
                </tr>
                <tr class="text-center">
                    <td colspan="7"><strong>ALL TOTAL</strong></td>
                </tr>
                <tr>
                    <td><strong>Total</strong></td>
                    <td><?php echo $total_records; ?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $total_amount; ?></td>
                    <td><?php echo $total_recov_amount; ?></td>
                    <td><?php echo $total_bal_amount; ?></td>
                </tr>
            </tbody>
        </table>

       <?php if (!empty($groupedData)): ?>
        <?php foreach ($groupedData as $bmds_type => $entries): ?>

        <div class="text-center">
            <?php
                $formatted_start_date = (!empty($start_date) && strtotime($start_date)) ? date("d/m/Y", strtotime($start_date)) : "00/00/0000";
                $formatted_end_date = (!empty($end_date) && strtotime($end_date)) ? date("d/m/Y", strtotime($end_date)) : "00";
            ?>
            <h1 class="mt-4">
                <?= htmlspecialchars($bmds_type) ?> Report from <?= htmlspecialchars($formatted_start_date) ?> To <?= htmlspecialchars($formatted_end_date) ?>
            </h1>
        </div>

        <form method="POST" id="bulkDeleteForm">
    <div class="float-end pb-3">
        <button type="button" class="btn sub-btn1 mt-4 bg-danger text-light" data-bs-toggle="modal" data-bs-target="#passwordModalSelected">
            Delete Selected
        </button>
    </div>

    

        <table class="table table-bordered my-5">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th scope="col">#</th>
                    <th scope="col" class="action-col">Client ID</th>
                    <th scope="col">Sr No.</th>
                    <th scope="col" class="action-col">Date</th>
                    <th scope="col">Client Name</th>
                    <th scope="col">Birth Date</th>
                    <!-- <th scope="col">Type</th> -->
                    <th scope="col">Sub Type</th>
                    <th scope="col">No Of Class</th>
                    <th scope="col">Class of vehicle</th>
                    <th scope="col">Premium</th>
                    <th scope="col">Recovery</th>
                    <th scope="col">Excess</th>
                    <th scope="col" class="action-col">Status</th>
                    <th scope="col" class="action-col">Action</th>
                    <th scope="col" class="summary-col">__Remark__</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $serial_number = $offset + 1; // Initialize serial number
                foreach ($entries as $row): ?>
                    <tr>
                        <td><input type="checkbox" name="selected_ids[]" value="<?= $row['id'] ?>"></td>
                        <th scope="row"><?= $serial_number++; ?></th>
                        <td class="action-col"> <?php echo htmlspecialchars($row['client_id']); ?></td>
                        <td><?= htmlspecialchars($row['sr_num']); ?></td>
                        <td class="action-col"><?= date('d-m-Y', strtotime($row['policy_date'])); ?></td>
                        <td><?= htmlspecialchars($row['client_name']); ?></td>
                        <td>
                            <?php
                                if (!empty($row['birth_date']) && $row['birth_date'] !== '0000-00-00') {
                                echo date("d-m-Y", strtotime($row['birth_date'])) . "<br>";
                            }
                            ?>
                        </td>
                        <!-- <td>
                            <?= htmlspecialchars($row['bmds_type']); ?> <br>
                            
                        </td> -->
                        <td>
                            <?= htmlspecialchars($row['llr_type']); ?>
                            <?= htmlspecialchars($row['mdl_type']); ?><br>
                        </td>
                         <td>
                            <?= htmlspecialchars($row['class']); ?> <br>
                            
                        </td>
                        <!-- <td>

                            <?php
                            // Show test date if it's not empty and not '0000-00-00'
                            if (!empty($row['test_date']) && $row['test_date'] !== '0000-00-00') {
                                echo date("d-m-Y", strtotime($row['test_date'])) . "<br>";
                            }

                            // Show start and end date range if both are valid
                            if (!empty($row['start_date']) && $row['start_date'] !== '0000-00-00' &&
                                !empty($row['end_date']) && $row['end_date'] !== '0000-00-00') {
                                echo date("d-m-Y", strtotime($row['start_date'])) . " To " . date("d-m-Y", strtotime($row['end_date'])) . "<br>";
                            }

                            // Show start and end time if both are valid
                            if (!empty($row['start_time']) && $row['start_time'] !== '00:00:00' &&
                                !empty($row['end_time']) && $row['end_time'] !== '00:00:00') {
                                echo date("g:i A", strtotime($row['start_time'])) . " To " . date("g:i A", strtotime($row['end_time']));
                            }
                            ?>

                            <?= htmlspecialchars($row['city']); ?>

                        </td> -->

                        <td>
                            <?= htmlspecialchars($row['llr_class']); ?>
                            <?= htmlspecialchars($row['car_type']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['amount']); ?><br>
                        </td>

                        <td><?= htmlspecialchars($row['recov_amount']); ?></td>
                        <td><?= htmlspecialchars($row['bal_amount']); ?></td>
                        <td class="summary-col"></td>

                        <!-- <td>
                            <?php
                                if (!empty($row['start_date']) && $row['start_date'] !== '0000-00-00' && !empty($row['end_date']) && $row['end_date'] !== '0000-00-00') {
                                    echo date("d-m-Y", strtotime($row['start_date'])) . " To " . date("d-m-Y", strtotime($row['end_date']));
                                } else {
                                    echo "-";
                                }
                            ?>
                        </td>
                        <td>
                            <?php
                                $start = $row['start_time'];
                                $end = $row['end_time'];
                                if (!empty($start) && !empty($end) && $start !== '00:00:00' && $end !== '00:00:00') {
                                    echo date("g:i A", strtotime($start)) . " To " . date("g:i A", strtotime($end));
                                } else {
                                    echo "-";
                                }
                            ?>
                        </td> -->

                        <!-- <td><?= htmlspecialchars($row['contact']); ?></td> -->
                         
                        <td class="action-col"><?= htmlspecialchars($row['form_status']); ?></td>
                        <td class="action-col">
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') : ?>
                                <a href="bmds-form.php?action=edit&id=<?= $row['id']; ?>" class="text-decoration-none text-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a> 
                                
                                &nbsp;/&nbsp;
                                
                                <a class="text-decoration-none text-dark" data-bs-toggle="modal" data-bs-target="#passwordModal" data-item-id="<?= $row['id']; ?>">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                        <td class="action-col">
                            <a href="bmds-form.php?action=add_new&id=<?php echo $row['id']; ?>" class="btn sub-btn1 text-dark" >
                                Upgrade
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
                            </form>
    <?php endforeach; ?>
<?php else: ?>
    <p>No records found for today.</p>
<?php endif; ?>
       
       
        
    </div>
        
            <!-- Pagination Links -->
            <!-- PAGINATION -->
            <?php if (isset($total_pages) && $total_pages > 1) : ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination neumorphic-pagination">
                        <li class="page-item <?= ($items_per_page === 'all') ? 'active' : '' ?>">
                            <a class="page-link" href="?page=1&items_per_page=all&<?= http_build_query($_GET) ?>">Show All</a>
                        </li>

                        <?php if ($current_page > 1) : ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>">&laquo;</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                            <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages) : ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>">&raquo;</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        
        </div>
    </div>
</section>


<!-- Password verification Modal for print screen -->
<div class="modal fade" id="printpasswordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <!-- <h5 class="modal-title" id="passwordModalLabel">Enter Password For Print</h5> -->
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="password" id="passwordInput" class="form-control" placeholder="Enter password" />
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="validatePassword()">Submit</button>
      </div>
    </div>
  </div>
</div>

<!-- Password Verification Modal for delete-->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <!-- <h5 class="modal-title" id="passwordModalLabel">Password Verification</h5> -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="verificationForm" action="bmds-delete.php" method="POST">
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

<!-- Excel Download Modal for Entering Password -->
<div class="modal fade" id="passwordModal1" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-header">
        <!-- <h5 class="modal-title" id="passwordModalLabel">Enter Password For EXCEL Download</h5> -->
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="csvDownloadForm" method="post">
        <div class="modal-body">
            <input type="password" name="admin_password" class="form-control" placeholder="Enter Admin Password" required>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="generate_csv" value="generate_csv" class="btn btn-primary" id="downloadButton">Download</button>
        </div>
        </form>
    </div>
    </div>
</div>


<!-- PDF Download Modal for Entering Password -->
<div class="modal fade" id="passwordModal2" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-header">
        <!-- <h5 class="modal-title" id="passwordModalLabel">Enter Password For PDF Download</h5> -->
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="csvDownloadForm" method="post">
        <div class="modal-body">
            <input type="password" name="admin_password" class="form-control" placeholder="Enter Admin Password" required>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="generate_pdf" value="generate_pdf" class="btn btn-primary" id="downloadpdf">Download</button>
        </div>
        </form>
    </div>
    </div>
</div>


<!-- Password Modal for Delete Selected -->
<div class="modal fade" id="passwordModalSelected" tabindex="-1" aria-labelledby="passwordModalLabelSelected" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enter Admin Password to Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="adminPasswordBulk" class="form-label">Admin Password</label>
                    <input type="password" class="form-control" id="adminPasswordBulk" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitBulkDelete()">Verify & Delete</button>
            </div>
        </div>
    </div>
</div>

<script>

    // Script for Delete Selected

    document.getElementById('selectAll').addEventListener('change', function () {
    const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
    checkboxes.forEach(cb => cb.checked = this.checked);
    });

    function submitBulkDelete() {
        const password = document.getElementById('adminPasswordBulk').value;
        if (!password.trim()) {
            alert("Please enter the admin password.");
            return;
        }

        // Collect form data
        const form = document.getElementById('bulkDeleteForm');
        const formData = new FormData(form);
        formData.append('password', password);

        // Send via POST using Fetch API
        fetch('bmds_all_entries_dele.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(response => {
            document.open();
            document.write(response);
            document.close();
        })
        .catch(error => {
            alert('Failed to delete records.');
            console.error(error);
        });
    }
</script>


<!-- script for print password verify -->
<script>
    // Show the password modal when the button is clicked
    function showPasswordModal() {
        $('#printpasswordModal').modal('show');
    }

    // Validate the password entered
    async function validatePassword() {
        const userPassword = document.getElementById('passwordInput').value;

        if (!userPassword) {
            alert("Password is required.");
            return;
        }

        // Validate the entered password with the backend
        const validationResult = await validatePasswordOnServer(userPassword);

        if (validationResult.success) {
            // Password is correct, proceed with print
            window.print();
            $('#printpasswordModal').modal('hide'); // Close the modal
        } else {
            // Show error message if the password is incorrect
            alert(validationResult.error || "Incorrect password!");
        }
    }

    // Function to send password to server for validation
    async function validatePasswordOnServer(userPassword) {
        try {
            const response = await fetch('print_pass.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `password=${encodeURIComponent(userPassword)}`
            });
            const data = await response.json();
            return data;
        } catch (error) {
            console.error("Error validating password:", error);
            return { success: false, error: "Error validating password" };
        }
    }
</script>


<script>


// Script for show data with pagination or not

    // function setItemsPerPage(value) {
    //     // Redirect or submit form with items_per_page
    //     const urlParams = new URLSearchParams(window.location.search);
    //     urlParams.set('items_per_page', value);
    //     window.location.search = urlParams.toString(); // Reload with updated parameter
    // }


// script for delete id

document.getElementById('passwordModal').addEventListener('show.bs.modal', function (event) {
    // Get the anchor element that triggered the modal
    var triggerElement = event.relatedTarget;
    
    // Extract the item ID from the data attribute
    var itemId = triggerElement.getAttribute('data-item-id');
    
    // Find the hidden input field in the modal and set its value
    var modalItemIdInput = document.getElementById('itemId');
    modalItemIdInput.value = itemId;
});

// Excel download Close modal and refresh page when "Download" button is clicked
document.getElementById('downloadButton').addEventListener('click', function () {
    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('passwordModal1'));
    modal.hide();

    // Refresh the page after closing the modal
    setTimeout(function() {
      window.location.reload();  // This refreshes the page
    }, 500); // Delay to ensure modal closes before page reload
  });

// pdf download Close modal and refresh page when "Download" button is clicked
document.getElementById('downloadpdf').addEventListener('click', function () {
    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('passwordModal2'));
    modal.hide();

    // Refresh the page after closing the modal
    setTimeout(function() {
      window.location.reload();  // This refreshes the page
    }, 500); // Delay to ensure modal closes before page reload
  });

    
    // when hover on icon show tooltip

    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>






<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

<?php //} ?>