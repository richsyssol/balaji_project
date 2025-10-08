<?php 
ob_start();
    include 'include/header.php'; 
    include 'include/head.php'; 
    include 'session_check.php';
?>



<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container data-table p-5 ">
        <div class="ps-5">
            <div>
                <h1>GIC</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">GIC</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-3">
        

        <?php
            // Process all GET values (not POST)
            $search_query = $_GET['search_query'] ?? '';
            $search_mv = $_GET['search_mv'] ?? '';
            $search_reg = $_GET['search_reg'] ?? '';
            $status = $_GET['status'] ?? '';
            $policy = $_GET['policy'] ?? '';
            $sub_type_selected = $_GET['sub_type'] ?? '';
            $nonmotor_subtype_selected = $_GET['nonmotor_subtype_select'] ?? '';
            $sort = $_GET['sort'] ?? '';
            $duration = $_GET['duration'] ?? '';
            $start_date = $_GET['start_date'] ?? '';
            $end_date = $_GET['end_date'] ?? date('Y-m-d');
            $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $items_per_page = $_GET['items_per_page'] ?? 10;

            // Sorting
            $sortColumn = '';
            $order = '';
            if ($sort !== '') {
                if ($sort === 'reg_num_desc') {
                    $sortColumn = 'reg_num';
                    $order = 'DESC';
                } elseif ($sort === 'reg_num_asc') {
                    $sortColumn = 'reg_num';
                    $order = 'ASC';
                } elseif ($sort === 'date_desc') {
                    $sortColumn = 'date';
                    $order = 'DESC';
                } elseif ($sort === 'date_asc') {
                    $sortColumn = 'date';
                    $order = 'ASC';
                } elseif ($sort === 'exp_asc') {
                    $sortColumn = 'end_date';
                    $order = 'ASC';
                }elseif ($sort === 'exp_desc') {
                    $sortColumn = 'end_date';
                    $order = 'DESC';
                }
            }

            // Fetch options
            $sub_type_options = [];
            $nonmotor_subtype_select_options = [];

            $sub_type_result = $conn->query("SELECT DISTINCT sub_type FROM gic_entries WHERE is_deleted = 0");
            while ($row = $sub_type_result->fetch_assoc()) {
                $sub_type_options[] = $row['sub_type'];
            }

            $nonmotor_result = $conn->query("SELECT DISTINCT nonmotor_subtype_select FROM gic_entries WHERE is_deleted = 0");
            while ($row = $nonmotor_result->fetch_assoc()) {
                $nonmotor_subtype_select_options[] = $row['nonmotor_subtype_select'];
            }
            ?>

            <!-- Search Form -->
            <form method="GET" class="p-3">
                <div class="row">
                    <div class="col-md-3 field">
                        <label class="form-label">Search (Use Comma For Multi-Search) :</label>
                        <input type="text" name="search_query" class="form-control" value="<?= htmlspecialchars($search_query) ?>" placeholder="Name And Contact" />
                    </div>

                    <div class="col-md-2 field">
                        <label class="form-label">Search by Reg No :</label>
                        <input type="text" name="search_reg" class="form-control" value="<?= htmlspecialchars($search_reg) ?>" placeholder="Search by Reg No" />
                    </div>

                    <div class="col-md-2 field">
                        <label class="form-label">Search by MV No :</label>
                        <input type="text" name="search_mv" class="form-control" value="<?= htmlspecialchars($search_mv) ?>" placeholder="Search by MV No" />
                    </div>

                    <div class="col-md-1 field">
                        <label class="form-label">Status :</label>
                        <select name="status" class="form-control">
                            <option value="">All</option>
                            <?php
                            foreach (['Pending', 'Complete', 'CDA', 'CANCELLED', 'OTHER', 'Expiry Date'] as $val) {
                                echo "<option value=\"$val\" " . ($status === $val ? 'selected' : '') . ">$val</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-1 field">
                        <label class="form-label">Policy Type :</label>
                        <select name="policy" class="form-control">
                            <option value="">All</option>
                            <option value="Motor" <?= ($policy === 'Motor') ? 'selected' : '' ?>>Motor</option>
                            <option value="NonMotor" <?= ($policy === 'NonMotor') ? 'selected' : '' ?>>NonMotor</option>
                        </select>
                    </div>

                    <div class="col-md-1 field" id="motor_sub_type" style="display: <?= ($policy == 'Motor') ? 'block' : 'none'; ?>;">
                        <label class="form-label">M Sub Type</label>
                        <select name="sub_type" class="form-control">
                            <option value="">All</option>
                            <?php foreach ($sub_type_options as $option): ?>
                                <option value="<?= htmlspecialchars($option) ?>" <?= $sub_type_selected === $option ? 'selected' : '' ?>><?= htmlspecialchars($option) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-1 field" id="nonmotor_sub_type" style="display: <?= ($policy == 'NonMotor') ? 'block' : 'none'; ?>;">
                        <label class="form-label">NM Sub Type</label>
                        <select name="nonmotor_subtype_select" class="form-control">
                            <option value="">All</option>
                            <?php foreach ($nonmotor_subtype_select_options as $option): ?>
                                <option value="<?= htmlspecialchars($option) ?>" <?= $nonmotor_subtype_selected === $option ? 'selected' : '' ?>><?= htmlspecialchars($option) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-1 field">
                        <label class="form-label">Sort By:</label>
                        <select name="sort" class="form-control">
                            <option value="">Sort By</option>
                            <option value="reg_num_desc" <?= ($sort === 'reg_num_desc') ? 'selected' : '' ?>>Reg Num (DESC)</option>
                            <option value="reg_num_asc" <?= ($sort === 'reg_num_asc') ? 'selected' : '' ?>>Reg Num (ASC)</option>
                            <option value="date_desc" <?= ($sort === 'date_desc') ? 'selected' : '' ?>>Date (DESC)</option>
                            <option value="date_asc" <?= ($sort === 'date_asc') ? 'selected' : '' ?>>Date (ASC)</option>
                            <option value="exp_asc" <?= ($sort === 'exp_asc') ? 'selected' : '' ?>>Expiry Date (ASC)</option>
                            <option value="exp_desc" <?= ($sort === 'exp_desc') ? 'selected' : '' ?>>Expiry Date (DESC)</option>
                        </select>
                    </div>

                    <div class="col-md-1 field">
                        <label class="form-label">Policy Duration:</label>
                        <select name="duration" class="form-control">
                            <option value="">All</option>
                            <option value="1YR" <?= ($duration === '1YR') ? 'selected' : '' ?>>1 Year</option>
                            <option value="SHORT" <?= ($duration === 'SHORT') ? 'selected' : '' ?>>Short Term</option>
                            <option value="LONG" <?= ($duration === 'LONG') ? 'selected' : '' ?>>Long Term</option>
                        </select>
                    </div>

                    <div class="col-md-2 field">
                        <label class="form-label">Start Date :</label>
                        <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>" />
                    </div>

                    <div class="col-md-2 field">
                        <label class="form-label">End Date :</label>
                        <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>" />
                    </div>

                    <div class="col-md-1">
                        <button type="submit" class="btn sub-btn1 mt-4">Search</button>
                    </div>

                    <div class="col-md-1">
                        <a href="gic" class="btn sub-btn1 mt-4">Reset</a>
                    </div>
                </div>

                <?php if ($_SESSION['role'] == 'admin') : ?>
                    <div class="row justify-content-center align-item-center">
                        <div class="col-md-1">
                            <button type="button" class="btn sub-btn1 mt-4" data-bs-toggle="modal" data-bs-target="#passwordModal1">EXCEL</button>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn sub-btn1 mt-4" data-bs-toggle="modal" data-bs-target="#passwordModal2">PDF</button>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn sub-btn1 mt-4" onclick="showPasswordModal()">Print</button>
                        </div>
                        <div class="col-md-3">
                            <a href="gic-finance" class="btn sub-btn1 mt-4 w-75">Insurance Company</a>
                        </div>
                        <div class="col-md-2">
                            <a href="gic-expire" class="btn sub-btn1 mt-4">Expire Msg</a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="text-center my-4">
                    <label class="mx-3"><input type="radio" name="tableToggle" value="company" checked> Show Policy Detail Table</label>
                    <label><input type="radio" name="tableToggle" value="clients"> Show Total Report</label>
                </div>
            </form>
        
        <?php 
        
        include 'includes/db_conn.php';

        // Update task status
        if (isset($_POST['update_status'])) {
            $id = (int)$_POST['id'];
            $newStatus = $conn->real_escape_string($_POST['status']);
            $message = isset($_POST['message']) ? $conn->real_escape_string($_POST['message']) : null;
            $scheduled_date = !empty($_POST['scheduled_date']) ? $conn->real_escape_string($_POST['scheduled_date']) : null;

            if ($scheduled_date) {
                // If scheduling for later, update scheduled_status and date
                $query = "UPDATE gic_entries SET scheduled_status = '$newStatus', scheduled_date = '$scheduled_date', message = '$message' WHERE id = $id";
            } else {
                // Immediate status update
                $query = "UPDATE gic_entries SET status = '$newStatus', message = '$message', scheduled_status = NULL, scheduled_date = NULL WHERE id = $id";
            }

            if ($conn->query($query)) {
                // Redirect with search filters
                header("Location: ".$_SERVER['PHP_SELF']."?status=".$_POST['search_status']."&start_date=".$_POST['start_date']."&end_date=".$_POST['end_date']);
                exit();
            } else {
                echo "Error updating status: " . $conn->error;
            }
        }

        $report = [];
          
            // Initialize search variables
            $search_query = isset($_POST['search_query']) ? trim($_POST['search_query']) : (isset($_GET['search_query']) ? trim($_GET['search_query']) : '');
            $search_mv = isset($_POST['search_mv']) ? trim($_POST['search_mv']) : (isset($_GET['search_mv']) ? trim($_GET['search_mv']) : '');
            $search_reg = isset($_POST['search_reg']) ? trim($_POST['search_reg']) : (isset($_GET['search_reg']) ? trim($_GET['search_reg']) : '');
            $status = isset($_POST['status']) ? trim($_POST['status']) : (isset($_GET['status']) ? trim($_GET['status']) : '');
            $policy = isset($_POST['policy']) ? trim($_POST['policy']) : (isset($_GET['policy']) ? trim($_GET['policy']) : '');
            $sub_type = isset($_POST['sub_type']) ? trim($_POST['sub_type']) : (isset($_GET['sub_type']) ? trim($_GET['sub_type']) : '');
            $nonmotor_subtype_select = isset($_POST['nonmotor_subtype_select']) ? trim($_POST['nonmotor_subtype_select']) : (isset($_GET['nonmotor_subtype_select']) ? trim($_GET['nonmotor_subtype_select']) : '');
            $start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : (isset($_GET['start_date']) ? trim($_GET['start_date']) : '');
            $end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : (isset($_GET['end_date']) ? trim($_GET['end_date']) : '');
            $duration = isset($_POST['duration']) ? trim($_POST['duration']) : (isset($_GET['duration']) ? trim($_GET['duration']) : '');
            
            
            // total count variable
            $total_records = 0;
            $total_amount = 0;
            $total_recov_amount = 0;
            $motor_count = 0;
            $motor_amount = 0;
            $motor_recov_amount = 0;
            $nonmotor_count = 0;
            $nonmotor_amount = 0;
            $nonmotor_recov_amount = 0;
            
            
            // Get current page number from the query string, default to 1
            $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $items_per_page = isset($_GET['items_per_page']) && $_GET['items_per_page'] === 'all' ? 'all' : 10; // Records per page
            
            // Default sorting values
                $currentYear = date('Y');
                $currentMonth = date('m');
                
                // Calculate fiscal year start and end
                if ($currentMonth >= 4) { // April or later
                    $fiscalYearStart = $currentYear;
                    $fiscalYearEnd = $currentYear + 1;
                } else { // Before April
                    $fiscalYearStart = $currentYear - 1;
                    $fiscalYearEnd = $currentYear;
                }
                
                $fiscalYearStartDate = "$fiscalYearStart-04-01";
                $fiscalYearEndDate = "$fiscalYearEnd-03-31";
                
                // Default sorting values
                $sortColumn = 'reg_num';
                $order = 'DESC';
                
                // Check if a sorting option has been selected
                if (isset($_POST['sort'])) {
                    $sortOption = $_POST['sort'];
                    if ($sortOption === 'reg_num_asc') {
                        $sortColumn = 'reg_num';
                        $order = 'ASC';
                    } elseif ($sortOption === 'reg_num_desc') {
                        $sortColumn = 'reg_num';
                        $order = 'DESC';
                    } elseif ($sortOption === 'date_asc') {
                        $sortColumn = 'policy_date';
                        $order = 'ASC';
                    } elseif ($sortOption === 'date_desc') {
                        $sortColumn = 'policy_date';
                        $order = 'DESC';
                    } elseif ($sortOption === 'exp_asc') {
                        $sortColumn = 'end_date';
                        $order = 'ASC';
                    }elseif ($sortOption === 'exp_desc') {
                        $sortColumn = 'end_date';
                        $order = 'DESC';
                    }
                }

            
            // Prepare the SQL query based on the search input
            if (!empty($search_query) || !empty($status) || !empty($policy)|| !empty($search_mv) || !empty($start_date) || !empty($end_date) || !empty($sub_type) || !empty($nonmotor_subtype_select) || !empty($motor_subtype_select) || !empty($reg_num) || !empty($duration)) {
                // Validate and convert date format if necessary
                if (!empty($start_date)) {
                    $start_date = date('Y-m-d', strtotime($start_date));
                }
                if (!empty($end_date)) {
                    $end_date = date('Y-m-d', strtotime($end_date));
                }
            
                $sql = "SELECT * FROM `gic_entries` 
                        WHERE is_deleted = 0";
                        
                

            
                $params = [];
                $param_types = '';
                
                

                $client_name_display = '';

                if (!empty($search_query)) {
                    // Search client_name based on client_name/contact/contact_alt
                    $client_check_sql = "SELECT client_name FROM gic_entries 
                                         WHERE is_deleted = 0 
                                         AND (client_name LIKE ? OR contact LIKE ? OR contact_alt LIKE ?)
                                         LIMIT 1";
                    
                    $client_check_stmt = $conn->prepare($client_check_sql);
                    
                    $search_param = "%$search_query%";
                    $client_check_stmt->bind_param('sss', $search_param, $search_param, $search_param);
                    
                    $client_check_stmt->execute();
                    $client_check_result = $client_check_stmt->get_result();
                    
                    if ($client_check_result && $client_check_result->num_rows > 0) {
                        $client_row = $client_check_result->fetch_assoc();
                        $client_name_display = $client_row['client_name'];
                    }
                }

                // Utility function to split and sanitize input
                function prepareSearchTerms($input) {
                    $terms = array_map('trim', explode(',', $input)); // Split by comma and trim
                    return array_filter($terms); // Remove empty entries
                }

                // Handle multi-term search for name/contact/etc.
                if (!empty($search_query)) {
                    $search_terms = prepareSearchTerms($search_query);
                    $sql .= " AND (";
                    $conditions = [];
                    foreach ($search_terms as $term) {
                        $conditions[] = "(client_name LIKE ? OR contact LIKE ? OR contact_alt LIKE ? OR policy_type LIKE ? OR adviser_name LIKE ?)";
                        for ($i = 0; $i < 5; $i++) {
                            $params[] = '%' . $term . '%';
                            $param_types .= 's';
                        }
                    }
                    $sql .= implode(' OR ', $conditions) . ")";
                }

                // Handle multi-term search for reg numbers
                if (!empty($search_reg)) {
                    $search_terms = prepareSearchTerms($search_reg);
                    $sql .= " AND (";
                    $conditions = [];
                    foreach ($search_terms as $term) {
                        $conditions[] = "reg_num LIKE ?";
                        $params[] = '%' . $term . '%';
                        $param_types .= 's';
                    }
                    $sql .= implode(' OR ', $conditions) . ")";
                }

                // Handle multi-term search for MV numbers
                if (!empty($search_mv)) {
                    $search_terms = prepareSearchTerms($search_mv);
                    $sql .= " AND (";
                    $conditions = [];
                    foreach ($search_terms as $term) {
                        $conditions[] = "mv_number LIKE ?";
                        $params[] = '%' . $term . '%';
                        $param_types .= 's';
                    }
                    $sql .= implode(' OR ', $conditions) . ")";
                }


            
                // Handle Status Filter
                if (!empty($status)) {
                    if ($status === 'Pending' || $status === 'Complete' || $status === 'CDA' || $status === 'CANCELLED' || $status === 'OTHER') {
                        $sql .= " AND form_status = ?";
                        $params[] = $status;
                        $param_types .= 's';
                    } elseif ($status === 'Recovery Amount') {
                        $sql .= " AND recov_amount != 0";
                    }
                }
                
                // Add ORDER BY clause for Expiry Date
                if (!empty($status)) {
                    if ($status === 'Expiry Date') {
                        // For normal policies (1 year) or the final year of long-term policies
                        $sql .= " AND (
                                    (policy_duration IN ('1YR', 'SHORT') AND end_date BETWEEN ? AND ?) 
                                    OR 
                                    (policy_duration = 'LONG' AND end_date BETWEEN ? AND ?)
                                )";
                        
                        // For long-term policies, also check virtual yearly expiry dates
                        $sql .= " OR (
                                    policy_duration = 'LONG' 
                                    AND YEAR(end_date) - year_count + 1 <= YEAR(?) 
                                    AND YEAR(end_date) >= YEAR(?)
                                    AND DATE(CONCAT(YEAR(?), '-', MONTH(end_date), '-', DAY(end_date))) BETWEEN ? AND ?
                                )";
                        
                        // Add parameters for both cases
                        $params[] = $start_date;
                        $params[] = $end_date;
                        $params[] = $start_date;
                        $params[] = $end_date;
                        $params[] = $end_date; // For YEAR comparison
                        $params[] = $start_date; // For YEAR comparison
                        $params[] = $end_date; // For virtual date construction
                        $params[] = $start_date;
                        $params[] = $end_date;
                        
                        $param_types .= str_repeat('s', 9); // 9 string parameters
                        
                        // Check for expired policies that are not renewed
                        $sql .= " AND is_renewed = 0"; // Policy should not be marked as renewed
                        $sql .= " AND id NOT IN (SELECT renewal_of FROM gic_entries WHERE renewal_of IS NOT NULL)";
                    }
                }

                
                if (!empty($policy)) {
                    if ($policy === 'Motor' || $policy === 'NonMotor') {
                        $sql .= " AND policy_type = ?";
                        $params[] = $policy;
                        $param_types .= 's';
                    }
                }

                if (!empty($duration)) {
                    if ($duration === '1YR' || $duration === 'SHORT' || $duration === 'LONG') {
                        $sql .= " AND policy_duration = ?";
                        $params[] = $duration;
                        $param_types .= 's';
                    }
                }
            
                 // Handle Search Query
                 if (!empty($sub_type)) {
                    $sql .= " AND (sub_type LIKE ?)";
                    $search_param = "%$sub_type%";
                    $params[] = $search_param;
                    $param_types .= 's'; // 's' stands for string type in prepared statements
                }

                 // Handle Search Query
                 if (!empty($nonmotor_subtype_select)) {
                    $sql .= " AND (nonmotor_subtype_select LIKE ?)";
                    $search_param = "%$nonmotor_subtype_select%";
                    $params[] = $search_param;
                    $param_types .= 's'; // 's' stands for string type in prepared statements
                }

                // Handle General Date Range
                if ($status !== 'Expiry Date') {
                    if (!empty($start_date) && !empty($end_date)) {
                        $sql .= " AND policy_date BETWEEN ? AND ?";
                        $params[] = $start_date;
                        $params[] = $end_date;
                        $param_types .= 'ss';
                    } elseif (!empty($start_date)) {
                        $sql .= " AND policy_date >= ?";
                        $params[] = $start_date;
                        $param_types .= 's';
                    } elseif (!empty($end_date)) {
                        $sql .= " AND policy_date <= ?";
                        $params[] = $end_date;
                        $param_types .= 's';
                    }
                }
                
                // Handle Renewal data
                if (!empty($status)) {
                    if ($status === 'Renewal') {
                        $sql .= " AND is_renewed = 1"; // Ensure only renewed data is shown
                        if (!empty($start_date) && !empty($end_date)) {
                            $sql .= " AND policy_date BETWEEN ? AND ?";
                            $params[] = $start_date;
                            $params[] = $end_date;
                            $param_types .= 'ss';
                        } elseif (!empty($start_date)) {
                            $sql .= " AND policy_date >= ?";
                            $params[] = $start_date;
                            $param_types .= 's';
                        } elseif (!empty($end_date)) {
                            $sql .= " AND policy_date <= ?";
                            $params[] = $end_date;
                            $param_types .= 's';
                        }
                    }
                }
                
                

                // Add ORDER BY clause
                    if ($status === 'Expiry Date') {
                        $sql .= " ORDER BY end_date ASC, reg_num ASC"; // Combine sorting conditions
                    } else {
                        $sql .= " ORDER BY 
                                CASE 
                                    WHEN policy_date >= '$fiscalYearStartDate' AND policy_date <= '$fiscalYearEndDate' THEN 1 
                                    ELSE 2 
                                END, 
                                $sortColumn $order"; // Default sorting
                    }
            
                // Add LIMIT for pagination
                if ($items_per_page !== 'all') {
                    $offset = ($current_page - 1) * $items_per_page;
                    $sql .= " LIMIT ?, ?";
                    $params[] = $offset;
                    $params[] = $items_per_page;
                    $param_types .= 'ii';
                }

                // Prepare the query
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("SQL Prepare Error: " . $conn->error . "\nQuery: " . $sql);
                }

                if (!empty($param_types)) {
                    $stmt->bind_param($param_types, ...$params);
                }

                // Execute and fetch results
                $stmt->execute();
                $result = $stmt->get_result();
            
                // Fetch total number of records and total amounts for pagination
                $count_sql = "SELECT 
                COUNT(*) as total, 
                SUM(amount) as total_amount, 
                SUM(recov_amount) as total_recov_amount,
                SUM(CASE WHEN policy_type = 'Motor' THEN 1 ELSE 0 END) as motor_count,
                SUM(CASE WHEN policy_type = 'Motor' THEN amount ELSE 0 END) as motor_amount,
                SUM(CASE WHEN policy_type = 'Motor' THEN recov_amount ELSE 0 END) as motor_recov_amount,
                SUM(CASE WHEN policy_type = 'NonMotor' THEN 1 ELSE 0 END) as nonmotor_count,
                SUM(CASE WHEN policy_type = 'NonMotor' THEN amount ELSE 0 END) as nonmotor_amount,
                SUM(CASE WHEN policy_type = 'NonMotor' THEN recov_amount ELSE 0 END) as nonmotor_recov_amount
                FROM `gic_entries` 
                WHERE is_deleted = 0
                AND policy_entry_status = 'Active' 
                ";

                // Initialize the parameters array and type string
                $count_params = [];
                $count_param_types = '';

                // Handle multi-term search for name/contact/etc.
                if (!empty($search_query)) {
                    $search_terms = prepareSearchTerms($search_query);
                    $count_sql .= " AND (";
                    $conditions = [];
                    foreach ($search_terms as $term) {
                        $conditions[] = "(client_name LIKE ? OR contact LIKE ? OR contact_alt LIKE ? OR policy_type LIKE ? OR adviser_name LIKE ?)";
                        for ($i = 0; $i < 5; $i++) {
                            $count_params[] = '%' . $term . '%';
                            $count_param_types .= 's';
                        }
                    }
                    $count_sql .= implode(' OR ', $conditions) . ")";
                }

                // Handle multi-term search for reg numbers
                if (!empty($search_reg)) {
                    $search_terms = prepareSearchTerms($search_reg);
                    $count_sql .= " AND (";
                    $conditions = [];
                    foreach ($search_terms as $term) {
                        $conditions[] = "reg_num LIKE ?";
                        $count_params[] = '%' . $term . '%';
                        $count_param_types .= 's';
                    }
                    $count_sql .= implode(' OR ', $conditions) . ")";
                }

                // Handle multi-term search for MV numbers
                if (!empty($search_mv)) {
                    $search_terms = prepareSearchTerms($search_mv);
                    $count_sql .= " AND (";
                    $conditions = [];
                    foreach ($search_terms as $term) {
                        $conditions[] = "mv_number LIKE ?";
                        $count_params[] = '%' . $term . '%';
                        $count_param_types .= 's';
                    }
                    $count_sql .= implode(' OR ', $conditions) . ")";
                }

                // Apply Status Filter for COUNT query
                if (!empty($status)) {
                    if ($status === 'Pending' || $status === 'Complete' || $status === 'CDA' || $status === 'CANCELLED' || $status === 'OTHER') {
                        $count_sql .= " AND form_status = ?";
                        $count_params[] = $status;
                        $count_param_types .= 's'; // Adding 's' for string type
                    } elseif ($status === 'Recovery Amount') {
                        $count_sql .= " AND recov_amount != 0";
                    } 
                }

                // Add ORDER BY clause for Expiry Date
                
                if (!empty($status)) {
                    if ($status === 'Expiry Date') {
                        // For normal policies (1 year) or the final year of long-term policies
                        $count_sql .= " AND (
                                    (policy_duration IN ('1YR', 'SHORT') AND end_date BETWEEN ? AND ?) 
                                    OR 
                                    (policy_duration = 'LONG' AND end_date BETWEEN ? AND ?)
                                )";
                        
                        // For long-term policies, also check virtual yearly expiry dates
                        $count_sql .= " OR (
                                    policy_duration = 'LONG' 
                                    AND YEAR(end_date) - year_count + 1 <= YEAR(?) 
                                    AND YEAR(end_date) >= YEAR(?)
                                    AND DATE(CONCAT(YEAR(?), '-', MONTH(end_date), '-', DAY(end_date))) BETWEEN ? AND ?
                                )";
                        
                        // Add parameters for both cases
                        $count_params[] = $start_date;
                        $count_params[] = $end_date;
                        $count_params[] = $start_date;
                        $count_params[] = $end_date;
                        $count_params[] = $end_date; // For YEAR comparison
                        $count_params[] = $start_date; // For YEAR comparison
                        $count_params[] = $end_date; // For virtual date construction
                        $count_params[] = $start_date;
                        $count_params[] = $end_date;
                        
                        $count_param_types .= str_repeat('s', 9); // 9 string parameters
                        
                        // Check for expired policies that are not renewed
                        $count_sql .= " AND is_renewed = 0"; // Policy should not be marked as renewed
                        $count_sql .= " AND id NOT IN (SELECT renewal_of FROM gic_entries WHERE renewal_of IS NOT NULL)";
                    }
                }

                // Filter for Policy Type
                if (!empty($policy)) {
                    if ($policy === 'Motor' || $policy === 'NonMotor') {
                        $count_sql .= " AND policy_type = ?";
                        $count_params[] = $policy;
                        $count_param_types .= 's';
                    }
                }


                if (!empty($duration)) {
                    if ($duration === '1YR' || $duration === 'SHORT' || $duration === 'LONG') {
                        $count_sql .= " AND policy_duration = ?";
                        $count_params[] = $duration;
                        $count_param_types .= 's';
                    }
                }

                // Handle Search Query
                if (!empty($sub_type)) {
                    $count_sql .= " AND (sub_type LIKE ?)";
                    $search_param = "%$sub_type%";
                    $count_params[] = $search_param;
                    $count_param_types .= 's'; // 's' stands for string type in prepared statements
                }

                 // Handle Search Query
                 if (!empty($nonmotor_subtype_select)) {
                    $count_sql .= " AND (nonmotor_subtype_select LIKE ?)";
                    $search_param = "%$nonmotor_subtype_select%";
                    $count_params[] = $search_param;
                    $count_param_types .= 's'; // 's' stands for string type in prepared statements
                }

                // General Date Range Filters
                if ($status !== 'Expiry Date') {
                    if (!empty($start_date) && !empty($end_date)) {
                        $count_sql .= " AND policy_date BETWEEN ? AND ?";
                        $count_params[] = $start_date;
                        $count_params[] = $end_date;
                        $count_param_types .= 'ss'; // Adding 'ss' for two date parameters
                    } elseif (!empty($start_date)) {
                        $count_sql .= " AND policy_date >= ?";
                        $count_params[] = $start_date;
                        $count_param_types .= 's'; // Adding 's' for a single date parameter
                    } elseif (!empty($end_date)) {
                        $count_sql .= " AND policy_date <= ?";
                        $count_params[] = $end_date;
                        $count_param_types .= 's'; // Adding 's' for a single date parameter
                    }
                }

                // Handle Renewal data
                if (!empty($status)) {
                    if ($status === 'Renewal') {
                        $count_sql .= " AND is_renewed = 1"; // Ensure only renewed data is shown
                        if (!empty($start_date) && !empty($end_date)) {
                            $count_sql .= " AND policy_date BETWEEN ? AND ?";
                            $count_params[] = $start_date;
                            $count_params[] = $end_date;
                            $count_param_types .= 'ss';
                        } elseif (!empty($start_date)) {
                            $count_sql .= " AND policy_date >= ?";
                            $count_params[] = $start_date;
                            $count_param_types .= 's';
                        } elseif (!empty($end_date)) {
                            $count_sql .= " AND policy_date <= ?";
                            $count_params[] = $end_date;
                            $count_param_types .= 's';
                        }
                    }
                }

                // Prepare and Execute the COUNT Query
                $count_stmt = $conn->prepare($count_sql);

                if ($count_stmt === false) {
                    die('Error preparing query: ' . $conn->error);
                }

                if (!empty($count_param_types)) {
                    // Bind parameters for COUNT query
                    $count_stmt->bind_param($count_param_types, ...$count_params);
                }

                 // Execute the query
                 $count_stmt->execute();

               
                 $count_result = $count_stmt->get_result();
                 $count_data = $count_result->fetch_assoc();
             
                 // Extract the Values for pagination
                 $total_records = $count_data['total'];
                 $total_amount = $count_data['total_amount'] ?? 0; // Fallback to 0 if null
                 $total_recov_amount = $count_data['total_recov_amount'] ?? 0; // Fallback to 0 if null
 
                 // Policy type specific counts and amounts
                 $motor_count = $count_data['motor_count'] ?? 0;
                 $motor_amount = $count_data['motor_amount'] ?? 0;
                 $motor_recov_amount = $count_data['motor_recov_amount'] ?? 0;
                 $nonmotor_count = $count_data['nonmotor_count'] ?? 0;
                 $nonmotor_amount = $count_data['nonmotor_amount'] ?? 0;
                 $nonmotor_recov_amount = $count_data['nonmotor_recov_amount'] ?? 0;
                 
 
             
                 
                 $total_pages = $items_per_page === 'all' ? 1 : ceil($total_records / $items_per_page);  
             
                 // Check if the data was returned
                 if ($result->num_rows > 0) {
                     // Process your results here (e.g., display the entries)
                 } else {
                     // No records found
                     echo "No entries found for the selected filters.";
                 }
             }

                
                
            else {
                
                $result = null; //if no search execute show empty table
            }

            
        if (isset($_POST['generate_csv'])) {

            // Get the submitted password
            $admin_password = $_POST['admin_password'] ?? '';
        
            // Fetch the stored hashed password from the database
            $sql = "SELECT password FROM file WHERE file_type = 'CSV' LIMIT 1"; // Assuming only one admin password
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
                header('Content-Disposition: attachment; filename=gic-report.csv');
            
                // Open the output stream to write CSV data
                $output = fopen('php://output', 'w');
            
                // Add the header row to the CSV file
                fputcsv($output, ['Reg Num', 'Date', 'Client Name', 'Contact', 'Policy Type','Non-Motor Type','Sub Type','MV Number','Vehicle Type','Sub Type','Premium','Recovery','Pay Mode','Company','Policy Number','Expiry','Status']);
            
                // Prepare dynamic SQL query for date range if provided
                if (!empty($start_date) && !empty($end_date)) {
                    $sql = "SELECT * FROM gic_entries WHERE policy_date BETWEEN ? AND ? AND (
                            policy_entry_status = 'Active' 
                        )";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('ss', $start_date, $end_date); // Bind parameters for date range
                } else {
                    // If no date range is set, use default query
                    $sql = "SELECT * FROM gic_entries WHERE (
                            policy_entry_status = 'Active' 
                        )";
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
                            $item['policy_type'],
                            $item['nonmotor_type_select'],
                            $item['nonmotor_subtype_select'],
                            $item['mv_number'],
                            $item['vehicle'],
                            $item['sub_type'],
                            $item['amount'],
                            $item['recov_amount'],
                            $item['pay_mode'],
                            $item['policy_company'],
                            $item['policy_number'],
                            $item['end_date'],
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

    
        if (isset($_POST['generate_pdf'])) {
            $admin_password = $_POST['admin_password'] ?? '';
        
            $sql = "SELECT password FROM file WHERE file_type = 'PDF' LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
        
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $hashed_password = $row['password'];
        
                if (password_verify($admin_password, $hashed_password)) {
                    if (ob_get_length()) {
                        ob_end_clean();
                    }
        
                    require('fpdf/fpdf.php');
        
                    class PDF extends FPDF {
                        function Footer() {
                            $this->SetY(-8);
                            $this->SetFont('Arial', 'I', 10);
                            $this->Cell(0, 10, $this->PageNo() . '/{nb}', 0, 0, 'R');
                        }
                    }
        
                    function renderTableHeader($pdf, $headers, $widths, $lineHeight) {
                        $pdf->SetFont('Arial', 'B', 9);
                        $x = $pdf->GetX();
                        $y = $pdf->GetY();
                        $maxLines = max(array_map(fn($h) => substr_count($h, "\n") + 1, $headers));
                        $headerHeight = $lineHeight * $maxLines;
        
                        foreach ($headers as $key => $header) {
                            $currentX = $pdf->GetX();
                            $currentY = $pdf->GetY();
                            $pdf->MultiCell($widths[$key], $lineHeight, $header, 0, 'C');
                            $pdf->Rect($currentX, $currentY, $widths[$key], $headerHeight);
                            $pdf->SetXY($currentX + $widths[$key], $currentY);
                        }
        
                        $pdf->SetXY($x, $y + $headerHeight);
                        return $headerHeight;
                    }
        
                    $pdf = new PDF('L', 'mm', 'A4');
                    $pdf->AliasNbPages();
                    $pdf->SetMargins(6, 6, 10);
                    $pdf->AddPage();
        
                    // HEADER: GIC SUMMARY
                
                    // Centered Heading
                    $pdf->SetFont('Arial', 'B', 14);
                    // Manually center the heading
                    $pdf->Cell(0, 5, '*** GIC REPORT DUE LIST FOR 01-03-2025 TO 31-03-2025 ***', 0, 1, 'C');

                    // Move Y back up to write the heading on same line
                    $pdf->SetY($pdf->GetY() - 6);

                    // Print Date on the left
                    $pdf->SetFont('Arial', '', 10);
                    $pdf->Cell(0, 6, 'Print Date: ' . date('d-m-Y'), 0, 1, 'R');

                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->Cell(120, 8, 'Policy Type', 1, 0, 'L');
                    $pdf->Cell(60, 8, 'Number of Renewals', 1, 0, 'C');
                    $pdf->Cell(60, 8, 'Total Basic Premium', 1, 1, 'C');
        
                    $sql = "SELECT policy_type, COUNT(*) AS total_entries, SUM(amount) AS total_premium 
                            FROM gic_entries 
                            WHERE policy_entry_status = 'Active' 
                            GROUP BY policy_type";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->get_result();
        
                    $pdf->SetFont('Arial', '', 10);
                    while ($row = $result->fetch_assoc()) {
                        $pdf->Cell(120, 8, $row['policy_type'], 1, 0, 'L');
                        $pdf->Cell(60, 8, number_format($row['total_entries']), 1, 0, 'C');
                        $pdf->Cell(60, 8, number_format($row['total_premium'], 2), 1, 1, 'C');
                    }
        
                    // CHECKPOINT TABLE
                    $pdf->Ln(4);
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->Cell(60, 8, 'Check Points', 1);
                    $pdf->Cell(40, 8, 'Deadline', 1);
                    $pdf->Cell(40, 8, 'Check', 1);
                    $pdf->Cell(40, 8, 'Dates', 1);
                    $pdf->Cell(60, 8, '', 1, 1);
        
                    $pdf->SetFont('Arial', '', 10);
                    $checklist = [
                        ['Update List / SB / Maturity', 'Day 1'],
                        ['SMS', 'Upto 5'],
                        ['Letters', 'Upto 5'],
                        ['WhatsApp', 'Upto 5'],
                        ['Calls', 'Upto 5'],
                        ['Follow-Up SMS', 'Day 20'],
                        ['Follow-Up Calls', 'Day 25'],
                        ['Recheck', 'Day 27'],
                        ['Final Check', 'Day 30'],
                        ['Dispatch', '']
                    ];
                    foreach ($checklist as $row) {
                        $pdf->Cell(60, 8, $row[0], 1);
                        $pdf->Cell(40, 8, $row[1], 1);
                        $pdf->Cell(40, 8, '', 1);
                        $pdf->Cell(40, 8, '', 1);
                        $pdf->Cell(60, 8, '', 1, 1);
                    }
        
                    // FINAL SECTION
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->Cell(160, 8, 'Position At Month End', 1, 0, 'C');
                    $pdf->Cell(40, 8, 'Pending Renewals', 1, 0, 'C');
                    $pdf->Cell(40, 8, 'Total Basic Premium', 1, 1, 'C');
        
                    $pdf->SetFont('Arial', '', 10);
                    $pdf->Cell(160, 8, '', 1, 0, 'C');
                    $pdf->Cell(40, 8, '', 1, 0, 'C');
                    $pdf->Cell(40, 8, '', 1, 1, 'C');
        
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->Cell(240, 8, 'Remark:', 1, 1, 'L');
        
                    $pdf->Ln(5);
                    $pdf->Cell(50, 8, 'Priority', 1, 0, 'C');
                    $pdf->Cell(50, 8, 'Speed', 1, 0, 'C');
                    $pdf->Cell(50, 8, 'Accuracy', 1, 0, 'C');
                    $pdf->Cell(50, 8, 'Deadline', 1, 0, 'C');
                    $pdf->Cell(50, 8, 'Delivery', 1, 1, 'C');
        
                    // After your summary content, once all data is written on first page
                    $currentY = $pdf->GetY(); // Get current Y position
                    $footerY = $currentY + 1; // Add 5mm spacing below data

                    $pdf->Image('asset/image/footer1.jpeg', 10, $footerY, 280, 45);

        
                    // NEW PAGE FOR TABLE
                    $pdf->AddPage();
                    $headers = [
                        'Reg', 'Date', "Client Name\n \n ", 'Policy Type',
                        "Non-Motor Type\nSub Type", "MV Number\nVehicle Type\nSub Type",
                        "Company\nPolicy Number\nExpiry\nPremium", 'Remark'
                    ];
                    $widths = [10, 20, 60, 20, 45, 30, 60, 40];
                    $lineHeight = 5;
                    renderTableHeader($pdf, $headers, $widths, $lineHeight);
                    $pdf->SetFont('Arial', '', 9);
        
                    // Date filters
                    $start_date = $_POST['start_date'] ?? '';
                    $end_date = $_POST['end_date'] ?? '';
        
                    if (!empty($start_date) && !empty($end_date)) {
                        $start_date = date('Y-m-d', strtotime($start_date));
                        $end_date = date('Y-m-d', strtotime($end_date));
                        $sql = "SELECT * FROM gic_entries WHERE policy_date BETWEEN ? AND ? AND policy_entry_status = 'Active'";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param('ss', $start_date, $end_date);
                    } else {
                        $sql = "SELECT * FROM gic_entries WHERE policy_entry_status = 'Active'";
                        $stmt = $conn->prepare($sql);
                    }
        
                    $stmt->execute();
                    $result = $stmt->get_result();
        
                    while ($row = $result->fetch_assoc()) {
                        $data = [
                            $row['reg_num'],
                            (new DateTime($row['policy_date']))->format('d/m/Y'),
                            $row['client_name'] . "\n" . $row['contact'] . "\n" . $row['address'],
                            $row['policy_type'],
                            $row['nonmotor_type_select'] . "\n" . $row['nonmotor_subtype_select'],
                            $row['mv_number'] . "\n" . $row['vehicle'] . "\n" . $row['sub_type'],
                            $row['policy_company'] . "\n" . $row['policy_number'] . "\n" . (new DateTime($row['end_date']))->format('d/m/Y') . "\n" . $row['amount'],
                            ''
                        ];
        
                        $lineCounts = array_map(fn($text) => substr_count($text, "\n") + 1, $data);
                        $rowHeight = $lineHeight * max($lineCounts);
        
                        if ($pdf->GetY() + $rowHeight > $pdf->GetPageHeight() - 15) {
                            $pdf->AddPage();
                            renderTableHeader($pdf, $headers, $widths, $lineHeight);
                        }
        
                        foreach ($data as $i => $cell) {
                            $x = $pdf->GetX();
                            $y = $pdf->GetY();
                            $pdf->MultiCell($widths[$i], $lineHeight, $cell, 0);
                            $pdf->Rect($x, $y, $widths[$i], $rowHeight);
                            $pdf->SetXY($x + $widths[$i], $y);
                        }
                        $pdf->Ln($rowHeight);
                    }
        
                    $pdf->Output('I', 'GIC_Report.pdf');
                    exit;
                } else {
                    echo "Invalid password.";
                }
            } else {
                echo "No admin password found.";
            }
        }
    
    
            
    // Close the connection
    $conn->close();
    
            
             
            
?>

        <!-- button for hide/unhide contact number row -->
        <div class="row mb-3 action-col">
            <div class="col-md-5 mt-4">
                
                <button class="btn btn-warning" id="toggleContact" type="button">Contact Hide</button>
            </div>
        </div>
        
        <hr></hr>
        
        
        
        
    <div id="reportSection">   

        <div  id="companyReport" style="display: block;">

            <div class="action-col">
                <h3>Summary : </h3>
                <table class="table table-bordered my-5">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>No Of Policies</th>
                            <th>Premium</th>
                            <th>Recovery</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Motor</strong></td>
                            <td><?php echo $motor_count; ?></td>
                            <td><?php echo $motor_amount; ?></td>
                            <td><?php echo $motor_recov_amount; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Non-Motor</strong></td>
                            <td><?php echo $nonmotor_count; ?></td>
                            <td><?php echo $nonmotor_amount; ?></td>
                            <td><?php echo $nonmotor_recov_amount; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td><?php echo $total_records; ?></td>
                            <td><?php echo $total_amount; ?></td>
                            <td><?php echo $total_recov_amount; ?></td>
                        </tr>
                        
                    </tbody>
                </table>
            </div>
        
            <div class="heading">
                <?php
                    // Check and format start date
                    if (!empty($start_date) && strtotime($start_date)) {
                        $formatted_start_date = date("d/m/Y", strtotime($start_date));
                    } else {
                        $formatted_start_date = "00/00/0000";
                    }

                    // Check and format end date
                    if (!empty($end_date) && strtotime($end_date)) {
                        $formatted_end_date = date("d/m/Y", strtotime($end_date));
                    } else {
                        $formatted_end_date = "00/00/0000";
                    }

                    // Display heading
                    if (!empty($client_name_display)) {
                        echo "<h3 class='text-center'>GIC REPORT FOR : <strong>" . htmlspecialchars($client_name_display) . "</strong><br>FROM $formatted_start_date TO $formatted_end_date</h3>";
                    } else {
                        echo "<h3 class='text-center'>GIC REPORT FROM $formatted_start_date TO $formatted_end_date</h3>";
                    }
                ?>
            </div>

            <form id="whatsappForm">
                

              



            <table class="table table-bordered my-5">
                <thead>
                    <tr>
                        <th scope="col" class="action-col">#</th>
                        <th scope="col" class="action-col">Client ID</th>
                        <th scope="col">Reg No.</th>
                        <th scope="col">Date</th>
                        <th scope="col">Client Name</th>
                        <th class="contact-data" scope="col">Contact </th>
                        <th scope="col">Policy Type</th>
                        
                        <?php 
                        // Check if there are results to determine headers
                        if (isset($result) && $result->num_rows > 0) {
                            $first_row = $result->fetch_assoc();
                            // Reset the result pointer back to the start
                            $result->data_seek(0); 
            
                            if ($first_row['policy_type'] === 'Motor') : 
                                ?>
                                <th scope="col">MV Number</th>
                                <th scope="col">Sub Type</th>
                            <?php 
                            elseif ($first_row['policy_type'] === 'NonMotor') : 
                                ?>
                                <th scope="col">Non-Motor Type</th>
                                <th scope="col">Sub Type</th>
                            <?php 
                            endif; 
                            
                        }
                        ?>
                        
                        <th scope="col">Premium</th>
                        <th scope="col" class="action-col">Recovery</th>
                        <th scope="col" class="action-col">Pay Mode</th>
                        <th scope="col">Company</th>
                        <th scope="col">Policy Number</th>
                        <th scope="col">Expiry</th>
                        <th scope="col">Status</th>
                        <th scope="col">Duration</th>
                        <th scope="col" class="summary-col">Remark</th> <!-- Add Summary column for print -->
                        <th scope="col" class="action-col">Action</th>
                        <th scope="col" class="action-col"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (isset($result) && $result->num_rows > 0) {
                        $srNo = 1; // Initialize serial number
                        while ($row = $result->fetch_assoc()) {
                            
                            // Get current date
                            $current_date = date('Y-m-d');
                            
                            // Get the expiry date from the row
                            $expiry_date = $row['end_date'];
            
                            // Check if the policy is expired
                            $is_expired = strtotime($expiry_date) < strtotime($current_date); // Returns true if expired
                            ?>
                            
                            <tr>
                                <th scope="row" class="action-col"><?= $srNo++ ?></th>
                                <td class="action-col"> <?php echo htmlspecialchars($row['client_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['reg_num']); ?></td>
                                <td>
                                    <?php 
                                    $original_date = $row['policy_date']; // date in YYYY-MM-DD format from database
                                    $formatted_date = DateTime::createFromFormat('Y-m-d', $original_date)->format('d/m/Y'); // format to DD/MM/YYYY
                                    echo htmlspecialchars($formatted_date); 
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['client_name']); ?></td>
                                <td class="contact-data"><?php echo htmlspecialchars($row['contact']); ?></td>
                                <td><?php echo htmlspecialchars($row['policy_type']); ?></td>
            
                                <!-- Check for policy_type and display appropriate data -->
                                <?php 
                                if ($row['policy_type'] === 'Motor') : 
                                    ?>
                                    <td><?php echo htmlspecialchars($row['mv_number']); ?> <br> <?php echo htmlspecialchars($row['vehicle']); ?></td>
                                    <td><?php echo htmlspecialchars($row['sub_type']); ?></td>
                                <?php 
                                elseif ($row['policy_type'] === 'NonMotor') : 
                                    ?>
                                    <td><?php echo htmlspecialchars($row['nonmotor_type_select']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nonmotor_subtype_select']); ?></td>
                                <?php 
                                else : 
                                    ?>
                                    <td colspan="2">No subtype available</td>
                                <?php 
                                endif; 
                                ?>
                                
                                
                                <td><?php echo ($row['amount'] ?? 0) == 0 ? '' : htmlspecialchars($row['amount']); ?></td>
                                <td class="action-col"><?php echo ($row['recov_amount'] ?? 0) == 0 ? '' : htmlspecialchars($row['recov_amount']); ?></td>
                                <td class="action-col"><?php echo htmlspecialchars($row['pay_mode']); ?></td>
                                <td><?php echo htmlspecialchars($row['policy_company']); ?></td>
                                <td><?php echo htmlspecialchars($row['policy_number']); ?></td>
                                
                                <td>
                                    <?php
                                    if (!empty($row['end_date']) && $row['end_date'] !== '0000-00-00') {
                                        echo date("d/m/Y", strtotime($row['end_date'])) . "<br>";
                                    }
                                    ?>
                                </td>

                                <td><?php echo htmlspecialchars($row['form_status']); ?></td>
                                <td><?php echo htmlspecialchars($row['policy_duration']); ?></td>
                                    
                                <td class="summary-col"></td> <!-- Blank Summary column for print -->
                                    
                                <td class="action-col">
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') : ?>
                                        <a href="gic-form.php?action=edit&id=<?php echo $row['id']; ?>" class="text-decoration-none text-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a> &nbsp;/&nbsp;
                                        <a class="text-decoration-none text-dark" data-bs-toggle="modal" data-bs-target="#passwordModal" data-item-id="<?php echo $row['id']; ?>">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                        <!-- &nbsp;/&nbsp; -->
                                    <?php endif; ?>
                                    
                                    <!-- <a href="gic-form.php?action=add_new&id=<?php echo $row['id']; ?>" class="btn sub-btn1 text-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Renewal">
                                    Renewal
                                    </a> -->
                                </td>

                                <td class="action-col">
                                    <a href="gic-form.php?action=add_new&id=<?php echo $row['id']; ?>" class="btn sub-btn1 text-dark" >
                                        Renewal
                                    </a><br><br>
                                    <a href="letter-creation.php?id=<?php echo $row['id']; ?>" class="btn sub-btn1 text-dark" >
                                        Letter Creation
                                    </a>
                                </td>

                                


                                <?php if ($status === "Expiry Date") :?>
                                    <td class="action-col">
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            
                                            <!-- Retain Search Parameters -->
                                            <input type="hidden" name="search_status" value="<?= htmlspecialchars($status) ?>">
                                            <input type="hidden" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                                            <input type="hidden" name="end_date" value="<?= htmlspecialchars($end_date) ?>">

                                            <!-- Status Dropdown -->
                                            <select name="status" class="form-select form-select-sm" required>
                                                <option value="PENDING" <?= ($row['status'] == 'PENDING') ? 'selected' : '' ?>>PENDING</option>
                                                <option value="WIP" <?= ($row['status'] == 'WIP') ? 'selected' : '' ?>>WIP</option>
                                                <option value="COMPLETED" <?= ($row['status'] == 'COMPLETED') ? 'selected' : '' ?>>COMPLETED</option>
                                                <option value="DND" <?= ($row['status'] == 'DND') ? 'selected' : '' ?>>DND</option>
                                            </select>

                                            <!-- Schedule Checkbox -->
                                            <div class="form-check mt-1">
                                                <input type="checkbox" class="form-check-input" id="scheduleCheckbox_<?= $row['id'] ?>" onclick="toggleSchedule(<?= $row['id'] ?>)"
                                                <?= !empty($row['scheduled_date']) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="scheduleCheckbox_<?= $row['id'] ?>">Schedule Later</label>
                                            </div>

                                            <!-- Schedule Date (Shows existing date if available) -->
                                            <input type="datetime-local" name="scheduled_date" id="scheduledDate_<?= $row['id'] ?>" 
                                                value="<?= !empty($row['scheduled_date']) ? htmlspecialchars($row['scheduled_date']) : '' ?>" 
                                                class="form-control form-control-sm mt-1" 
                                                style="<?= !empty($row['scheduled_date']) ? 'display:block;' : 'display:none;' ?>">

                                            <!-- Custom Message Checkbox -->
                                            <div class="form-check mt-1">
                                                <input type="checkbox" class="form-check-input" id="customMessageCheckbox_<?= $row['id'] ?>" onclick="toggleMessage(<?= $row['id'] ?>)"
                                                <?= !empty($row['message']) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="customMessageCheckbox_<?= $row['id'] ?>">Add Custom Message</label>
                                            </div>

                                            <!-- Custom Message Input (Shows existing message if available) -->
                                            <input type="text" name="message" id="customMessage_<?= $row['id'] ?>" 
                                                value="<?= !empty($row['message']) ? htmlspecialchars($row['message']) : '' ?>" 
                                                class="form-control form-control-sm mt-1" placeholder="Enter a message"
                                                style="<?= !empty($row['message']) ? 'display:block;' : 'display:none;' ?>">

                                            <!-- Submit Button -->
                                            <button type="submit" name="update_status" class="btn btn-primary btn-sm mt-1">Update Status</button>
                                        </form>


                                    </td>

                                <?php endif; ?>
                                
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='16'>No records found.</td></tr>";
                    }
                    ?>
                </tbody>
                
            </table>

            
            <table class="table table-bordered my-5 summary-col">
                <h2>Summary : </h2>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>No Of Policies</th>
                            <th>Premium</th>
                            <th>Recovery</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Motor</strong></td>
                            <td><?php echo $motor_count; ?></td>
                            <td><?php echo $motor_amount; ?></td>
                            <td><?php echo $motor_recov_amount; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Non-Motor</strong></td>
                            <td><?php echo $nonmotor_count; ?></td>
                            <td><?php echo $nonmotor_amount; ?></td>
                            <td><?php echo $nonmotor_recov_amount; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td><?php echo $total_records; ?></td>
                            <td><?php echo $total_amount; ?></td>
                            <td><?php echo $total_recov_amount; ?></td>
                        </tr>
                        
                    </tbody>
                </table>
            </form>

            
            <div class="summary-col pt-5">
                <p><strong>Note : </strong>This is computer generated report. Kindly refer original documents for more information and verification.</p>
            </div>

            

            
            

        </div>
    
        <div id="clientReport" style="display: block;">

            <?php include "gic-report.php" ?>
        </div>
     </div>   
     
        <!-- Pagination Links -->
        <?php if (isset($total_pages) && $total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination neumorphic-pagination">

                    <?php
                    // Merge current GET params into pagination links
                    $queryParams = $_GET;
                    ?>

                    <li class="page-item <?= ($items_per_page === 'all') ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => 1, 'items_per_page' => 'all'])) ?>">Show All</a>
                    </li>

                    <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => $current_page - 1])) ?>">&laquo;</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($i === $current_page) ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => $current_page + 1])) ?>">&raquo;</a>
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


<!-- Password Verification Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <!-- <h5 class="modal-title" id="passwordModalLabel">Password Verification</h5> -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="verificationForm" action="gic-delete.php" method="POST">
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


<!-- Add these modals before the closing </body> tag -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel">Send Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Do you want to send a message to this client?</p>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="messageType" id="whatsappRadio" value="whatsapp" checked>
                    <label class="form-check-label" for="whatsappRadio">WhatsApp</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="messageType" id="smsRadio" value="sms">
                    <label class="form-check-label" for="smsRadio">SMS</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="sendBoth" id="sendBoth">
                    <label class="form-check-label" for="sendBoth">Send Both</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button type="button" class="btn btn-primary" id="sendMessageBtn">Yes, Send</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responseModalLabel">Message Response</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="responseMessage">
                <!-- Response will be shown here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Show modal if URL has show_message_prompt parameter
    const urlParams = new URLSearchParams(window.location.search);
    const showPrompt = urlParams.get('show_message_prompt');
    const clientId = urlParams.get('id');
    
    if (showPrompt && clientId) {
        const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
        messageModal.show();
        
        // Remove the parameter from URL without reloading
        history.replaceState(null, null, window.location.pathname);
        
        // Handle send message button click
        $('#sendMessageBtn').click(function() {
            const whatsappChecked = $('#whatsappRadio').is(':checked');
            const smsChecked = $('#smsRadio').is(':checked');
            const sendBoth = $('#sendBoth').is(':checked');
            
            let messageTypes = [];
            
            if (sendBoth) {
                messageTypes = ['whatsapp', 'sms'];
            } else {
                if (whatsappChecked) messageTypes.push('whatsapp');
                if (smsChecked) messageTypes.push('sms');
            }
            
            if (messageTypes.length === 0) {
                alert('Please select at least one message type');
                return;
            }
            
            // Show loading state
            $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');
            $(this).prop('disabled', true);

            // Hide the message modal immediately when Yes is clicked
            messageModal.hide();
            
            // Get client details
            $.ajax({
                url: 'get-client-details.php',
                method: 'POST',
                data: { id: clientId },
                dataType: 'json',
                success: function(client) {
                    if (!client) {
                        alert('Client not found');
                        $('#sendMessageBtn').html('Yes, Send').prop('disabled', false);
                        return;
                    }
                    
                    // Send messages sequentially
                    sendMessagesSequentially(client, messageTypes, 0, messageModal);
                },
                error: function() {
                    alert('Error fetching client details');
                    $('#sendMessageBtn').html('Yes, Send').prop('disabled', false);
                }
            });
        });
    }
});

function sendMessagesSequentially(client, messageTypes, index, messageModal) {
    if (index >= messageTypes.length) {
        // All messages sent, reset button
        $('#sendMessageBtn').html('Yes, Send').prop('disabled', false);
        return;
    }
    
    const type = messageTypes[index];
    const formData = new FormData();
    formData.append('action', type);
    formData.append('selected_clients[]', `${client.contact}|${client.client_name}`);
    
    fetch("gic-msg.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        // Show response
        const responseModal = new bootstrap.Modal(document.getElementById('responseModal'));
        let responseHtml = $('#responseMessage').html();
        responseHtml += `<div class="alert alert-${data.includes('success') ? 'success' : 'danger'}">
            <strong>${type.toUpperCase()} to ${client.client_name} (${client.contact}):</strong><br>
            ${data}
        </div>`;
        $('#responseMessage').html(responseHtml);
        
        if (index === 0) {
            // Show modal only for first response
            responseModal.show();
        }
        
        // Send next message
        sendMessagesSequentially(client, messageTypes, index + 1, messageModal);
    })
    .catch(err => {
        let responseHtml = $('#responseMessage').html();
        responseHtml += `<div class="alert alert-danger">
            <strong>Error sending ${type} to ${client.client_name} (${client.contact}):</strong><br>
            ${err}
        </div>`;
        $('#responseMessage').html(responseHtml);
        
        const responseModal = new bootstrap.Modal(document.getElementById('responseModal'));
        responseModal.show();
        
        // Continue with next message even if this one failed
        sendMessagesSequentially(client, messageTypes, index + 1, messageModal);
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
function toggleSchedule(id) {
    let scheduleCheckbox = document.getElementById("scheduleCheckbox_" + id);
    let scheduledDateField = document.getElementById("scheduledDate_" + id);

    if (scheduleCheckbox.checked) {
        scheduledDateField.style.display = "block";
        scheduledDateField.setAttribute("required", "required");
    } else {
        scheduledDateField.style.display = "none";
        scheduledDateField.removeAttribute("required");
    }
}

function toggleMessage(id) {
    let messageCheckbox = document.getElementById("customMessageCheckbox_" + id);
    let messageField = document.getElementById("customMessage_" + id);

    if (messageCheckbox.checked) {
        messageField.style.display = "block";
    } else {
        messageField.style.display = "none";
    }
}
</script>


<script>


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
    
    
    // script for selection of motor & nonmotor subtype dropdown
    
    document.addEventListener("DOMContentLoaded", function () {
    const policySelect = document.getElementById("policy");
    const motorDiv = document.getElementById("motor_sub_type");
    const nonMotorDiv = document.getElementById("nonmotor_sub_type");

    function toggleSubType() {
        const selectedValue = policySelect.value;

        if (selectedValue === "Motor") {
            motorDiv.style.display = "block";
            nonMotorDiv.style.display = "none";
        } else if (selectedValue === "NonMotor") {
            motorDiv.style.display = "none";
            nonMotorDiv.style.display = "block";
        } else {
            motorDiv.style.display = "none";
            nonMotorDiv.style.display = "none";
        }
    }

    policySelect.addEventListener("change", toggleSubType);
});

</script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const radios = document.querySelectorAll('input[name="tableToggle"]');
        const companySection = document.getElementById('companyReport');
        const clientSection = document.getElementById('clientReport');

        // Hide both first to be safe, then show only the selected one
        companySection.style.display = 'none';
        clientSection.style.display = 'none';

        const selected = document.querySelector('input[name="tableToggle"]:checked');
        if (selected && selected.value === 'company') {
            companySection.style.display = 'block';
        } else if (selected && selected.value === 'clients') {
            clientSection.style.display = 'block';
        }

        radios.forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.value === 'company') {
                    companySection.style.display = 'block';
                    clientSection.style.display = 'none';
                } else if (this.value === 'clients') {
                    companySection.style.display = 'none';
                    clientSection.style.display = 'block';
                }
            });
        });
    });
</script>

<script>
// script for send whatsapp and text sms 

    // let clickedAction = null;

    // // Detect which button was clicked
    // document.querySelectorAll('#whatsappForm button[type="submit"]').forEach(button => {
    //     button.addEventListener("click", function () {
    //         clickedAction = this.value;
    //     });
    // });

    // document.getElementById("whatsappForm").addEventListener("submit", function (e) {
    //     e.preventDefault();

    //     const form = new FormData(this);
    //     form.append("action", clickedAction); // Pass action: whatsapp or sms

    //     fetch("gic-msg.php", {
    //         method: "POST",
    //         body: form
    //     })
    //     .then(res => res.text())
    //     .then(data => {
    //         alert(data);
    //         location.reload();
    //     })
    //     .catch(err => {
    //         alert("Error: " + err);
    //     });
    // });


    // script for hide and unhide contact column from table
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('toggleContact').addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent event bubbling
        e.preventDefault(); // Prevent default behavior
        
        const contactCells = document.querySelectorAll('.contact-data');
        const isHidden = contactCells[0].classList.contains('hidden');
        
        contactCells.forEach(cell => {
            cell.classList.toggle('hidden');
        });
        
        this.textContent = isHidden ? 'Contact Hide' : 'Contact Show';
    });
});
</script>

<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

<?php //} ?>