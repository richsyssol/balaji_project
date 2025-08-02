<?php
include 'includes/db_conn.php';

$goal = "SELECT * from goal";

// Initialize default values
$today = date('Y-m-d');
$first_day_last_month = date('Y-m-01', strtotime('first day of last month'));
$last_day_last_month = date('Y-m-t', strtotime('last month'));
$first_day_of_month = date('Y-m-01'); // First day of the current month


// Get user-selected date range or use default values
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : $today;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : $today;

$rto_entries_today = $rto_total_amount_today = $rto_nt_today = $rto_tr_today = $rto_dl_today = 0;
$rto_entries_range = $rto_total_amount_range = 0;

// Todays Thought

function getDailyThought($conn) {
    // Query to get the latest thought
    $query = "SELECT thought FROM thought ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch the thought if available
        $row = $result->fetch_assoc();
        $thought = $row['thought'];
    } else {
        // Set default message if no thought is available
        $thought = "IT'S MY DAY";
    }

    // Close the statement
    $stmt->close();

    return $thought;
}

// Call function
$thought = getDailyThought($conn);


// Function to get totals for date range
function get_totals_range($conn, $table, $columns, $date_condition, $date_values) {
    $query = "
        SELECT 
            COUNT(*) AS total_entries,
            SUM($columns) AS total_amount
        FROM $table
        WHERE $date_condition
    ";

    $stmt = $conn->prepare($query);

    if (strpos($date_condition, 'BETWEEN') !== false) {
        // For 'BETWEEN ? AND ?'
        $stmt->bind_param("ss", $date_values[0], $date_values[1]);
    } else {
        // For 'date = ?'
        $stmt->bind_param("s", $date_values[0]);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    $entries = $data['total_entries'] ?? 0;
    $total_amount = $data['total_amount'] ?? 0.0;

    $stmt->close();

    return ['entries' => $entries, 'total_amount' => $total_amount];
}

// Function to get RTO totals by category
function get_rto_totals($conn, $policy_date) {
    $query = "
        SELECT 
            category,
            COUNT(*) AS total_entries_today
        FROM rto_entries
        WHERE policy_date = ? AND category IN ('NT', 'TR', 'DL')
        GROUP BY category
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $policy_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $rto_totals = ['NT' => 0, 'TR' => 0, 'DL' => 0]; // Default values

    while ($data = $result->fetch_assoc()) {
        $rto_totals[$data['category']] = $data['total_entries_today'];
    }

    $stmt->close();

    return $rto_totals;
}

// Fetch today's totals
$rto_today = get_rto_totals($conn, $today);
$rto_nt_today = $rto_today['NT'];
$rto_tr_today = $rto_today['TR'];
$rto_dl_today = $rto_today['DL'];

// Function to get totals grouped by category
function get_totals_by_category($conn, $start_date, $end_date) {
    $query = "
        SELECT 
            category,
            COUNT(*) AS total_entries,
            SUM(other_amount) AS total_amount
        FROM rto_entries
        WHERE policy_date BETWEEN ? AND ?
        GROUP BY category
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $totals = ['NT' => ['entries' => 0, 'amount' => 0], 'TR' => ['entries' => 0, 'amount' => 0], 'DL' => ['entries' => 0, 'amount' => 0]];

    while ($row = $result->fetch_assoc()) {
        $category = $row['category'];
        if (isset($totals[$category])) {
            $totals[$category]['entries'] = $row['total_entries'];
            $totals[$category]['amount'] = $row['total_amount'];
        }
    }

    $stmt->close();
    return $totals;
}

// Fetch totals grouped by category
$rto_totals = get_totals_by_category($conn, $start_date, $end_date);

// Calculate overall totals
$total_entries = array_sum(array_column($rto_totals, 'entries'));
$total_amount = array_sum(array_column($rto_totals, 'amount'));


// Function to get BMDS totals by type
function get_class_vehicles_totals($conn, $policy_date) {
    $query = "
        SELECT 
            bmds_type,
            COUNT(*) AS total_entries_today
        FROM bmds_entries
        WHERE policy_date = ? AND bmds_type IN ('LLR', 'DL', 'ADM')
        GROUP BY bmds_type
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $policy_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $class_totals = ['LLR' => 0, 'DL' => 0, 'ADM' => 0]; // Default values

    while ($data = $result->fetch_assoc()) {
        $class_totals[$data['bmds_type']] = $data['total_entries_today'];
    }

    $stmt->close();

    return $class_totals;
}

// Fetch today's totals
$class_today = get_class_vehicles_totals($conn, $today);
$class_llr_today = $class_today['LLR'];
$class_dl_today = $class_today['DL'];
$class_adm_today = $class_today['ADM'];


// Function to get totals grouped by type
function get_class_totals_by_category($conn, $start_date, $end_date) {
    $query = "
        SELECT 
            bmds_type,
            COUNT(*) AS total_entries,
            SUM(COALESCE(amount, 0)) AS total_amount
        FROM bmds_entries
        WHERE policy_date BETWEEN ? AND ?
        GROUP BY bmds_type
    ";

    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        die("SQL Error: " . $conn->error); // Debugging step
    }

    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $class_totals = ['LLR' => ['entries' => 0, 'amount' => 0], 'DL' => ['entries' => 0, 'amount' => 0], 'ADM' => ['entries' => 0, 'amount' => 0]];

    while ($row = $result->fetch_assoc()) {
        $bmds_type = $row['bmds_type'];
        if (isset($class_totals[$bmds_type])) {
            $class_totals[$bmds_type]['entries'] = $row['total_entries'];
            $class_totals[$bmds_type]['amount'] = $row['total_amount'];
        }
    }

    $stmt->close();
    return $class_totals;
}


// Fetch totals grouped by category
$class_totals = get_class_totals_by_category($conn, $start_date, $end_date);


// Calculate overall totals
$total_entries = array_sum(array_column($class_totals, 'entries'));
$total_amount = array_sum(array_column($class_totals, 'amount'));



// Function to fetch total clients
function get_total_clients($conn) {
    $query = "SELECT COUNT(*) AS total_clients FROM client";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $total_clients = $data['total_clients'] ?? 0;
    $stmt->close();

    return $total_clients;
}

function get_todays_clients($conn, $today) {
    $query = "SELECT COUNT(*) AS total_clients_today FROM client WHERE DATE(policy_date) = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    return $data['total_clients_today'] ?? 0;
}

function get_lic_range($conn, $table, $amount_column, $date_condition, $date_params, $additional_conditions = "") {
    // Construct SQL query
    $sql = "SELECT COUNT(*) AS entries, SUM($amount_column) AS total_amount 
            FROM `$table` 
            WHERE $date_condition $additional_conditions";
    
    // Prepare and execute statement
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Prepare Error: " . $conn->error . "\nQuery: " . $sql);
    }
    
    // Bind parameters dynamically
    $param_types = str_repeat('s', count($date_params));
    $stmt->bind_param($param_types, ...$date_params);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch and return totals
    $totals = $result->fetch_assoc();
    $totals['total_amount'] = $totals['total_amount'] ?: 0; // Ensure total_amount is 0 if null
    return $totals;
}


// Get total clients
$total_client = get_total_clients($conn);
$todays_total_client = get_todays_clients($conn, $today);


function get_collection_totals($conn, $table, $amount_column, $condition, $params, $collection_job) {
    $sql = "SELECT COUNT(*) as entries, SUM($amount_column) as total_amount 
            FROM lic_entries 
            WHERE $condition AND collection_job = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    // Add collection_job to parameters
    $params[] = $collection_job;
    $param_types = str_repeat('s', count($params));

    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    return [
        'entries' => $data['entries'] ?? 0,
        'total_amount' => $data['total_amount'] ?? 0,
    ];
}

// from 1st date to todays date total count

// Fetch New Business Totals
$new_business_totals = get_collection_totals(
    $conn,
    'lic_entries',
    'policy_amt',
    'policy_date BETWEEN ? AND ?',
    [$first_day_of_month, $today],
    'New Business'
);
$new_business_entries = $new_business_totals['entries'];
$new_business_total_amount = $new_business_totals['total_amount'];

// Fetch Renewal Business Totals
$renewal_business_totals = get_collection_totals(
    $conn,
    'lic_entries',
    'policy_amt',
    'policy_date BETWEEN ? AND ?',
    [$first_day_of_month, $today],
    'Renewal Business'
);
$renewal_business_entries = $renewal_business_totals['entries'];
$renewal_business_total_amount = $renewal_business_totals['total_amount'];  

// LIC - Today's totals
$lic_today = get_totals_range($conn, 'lic_entries', 'policy_amt', 'policy_date = ?', [$today, $today]);
$lic_entries_today = $lic_today['entries'];
$lic_total_amount_today = $lic_today['total_amount'];

// LIC - Totals for selected date range
$lic_range = get_totals_range($conn, 'lic_entries', 'policy_amt', 'policy_date BETWEEN ? AND ?', [$start_date, $end_date]);
$lic_entries_range = $lic_range['entries'];
$lic_total_amount_range = $lic_range['total_amount'];



// LIC - Totals from the start of the month to today's date
$lic_month_to_date = get_totals_range($conn, 'lic_entries', 'policy_amt', 'policy_date BETWEEN ? AND ?', [$first_day_of_month, $today]);
$lic_entries_month_to_date = $lic_month_to_date['entries'];
$lic_total_amount_month_to_date = $lic_month_to_date['total_amount'];

// Today's totals for New Business
$new_business_today = get_lic_range($conn, 'lic_entries', 'policy_amt', 'policy_date = ?', [$today], " AND collection_job = 'New Business'");
$new_business_entries_today = $new_business_today['entries'];
$new_business_total_today = $new_business_today['total_amount'];

// Today's totals for Renewal Business
$renewal_business_today = get_lic_range($conn, 'lic_entries', 'policy_amt', 'policy_date = ?', [$today], " AND collection_job = 'Renewal Business'");
$renewal_business_entries_today = $renewal_business_today['entries'];
$renewal_business_total_today = $renewal_business_today['total_amount'];

// Last month's totals for New Business
$new_business_last_month = get_lic_range($conn, 'lic_entries', 'policy_amt', 'policy_date BETWEEN ? AND ?', [$first_day_last_month, $last_day_last_month], " AND collection_job = 'New Business'");
$new_business_entries_last_month = $new_business_last_month['entries'];
$new_business_total_last_month = $new_business_last_month['total_amount'];

// Last month's totals for Renewal Business
$renewal_business_last_month = get_lic_range($conn, 'lic_entries', 'policy_amt', 'policy_date BETWEEN ? AND ?', [$first_day_last_month, $last_day_last_month], " AND collection_job = 'Renewal Business'");
$renewal_business_entries_last_month = $renewal_business_last_month['entries'];
$renewal_business_total_last_month = $renewal_business_last_month['total_amount'];


// GIC - Today's totals
$gic_today = get_totals_range($conn, 'gic_entries', 'amount', 'policy_date = ?', [$today, $today]);
$gic_entries_today = $gic_today['entries'];
$gic_total_amount_today = $gic_today['total_amount'];

// GIC - Totals for selected date range
$gic_range = get_totals_range($conn, 'gic_entries', 'amount', 'policy_date BETWEEN ? AND ?', [$start_date, $end_date]);
$gic_entries_range = $gic_range['entries'];
$gic_total_amount_range = $gic_range['total_amount'];

// Fetch last month's totals for GIC
$gic_last_month = get_totals_range($conn, 'gic_entries', 'amount', 'policy_date BETWEEN ? AND ?', [$first_day_last_month, $last_day_last_month]);
$gic_entries_last_month = $gic_last_month['entries'];
$gic_total_amount_last_month = $gic_last_month['total_amount'];

// GIC - Totals from the start of the month to today's date
$gic_month_to_date = get_totals_range($conn, 'gic_entries', 'amount', 'policy_date BETWEEN ? AND ?', [$first_day_of_month, $today]);
$gic_entries_month_to_date = $gic_month_to_date['entries'];
$gic_total_amount_month_to_date = $gic_month_to_date['total_amount'];

// BMDS - Today's totals
$bmds_today = get_totals_range($conn, 'bmds_entries', 'amount', 'policy_date = ?', [$today, $today]);
$bmds_entries_today = $bmds_today['entries'];
$bmds_total_amount_today = $bmds_today['total_amount'];

// BMDS - Totals for selected date range
$bmds_range = get_totals_range($conn, 'bmds_entries', 'amount', 'policy_date BETWEEN ? AND ?', [$start_date, $end_date]);
$bmds_entries_range = $bmds_range['entries'];
$bmds_total_amount_range = $bmds_range['total_amount'];

// Fetch last month's totals for BMDS
$bmds_last_month = get_totals_range($conn, 'bmds_entries', 'amount', 'policy_date BETWEEN ? AND ?', [$first_day_last_month, $last_day_last_month]);
$bmds_entries_last_month = $bmds_last_month['entries'];
$bmds_total_amount_last_month = $bmds_last_month['total_amount'];

// BMDS - Totals from the start of the month to today's date
$bmds_month_to_date = get_totals_range($conn, 'bmds_entries', 'amount', 'policy_date BETWEEN ? AND ?', [$first_day_of_month, $today]);
$bmds_entries_month_to_date = $bmds_month_to_date['entries'];
$bmds_total_amount_month_to_date = $bmds_month_to_date['total_amount'];

// MF - Today's totals
$mf_today = get_totals_range($conn, 'mf_entries', 'amount', 'policy_date = ?', [$today, $today]);
$mf_entries_today = $mf_today['entries'];
$mf_total_amount_today = $mf_today['total_amount'];

// MF - Totals for selected date range
$mf_range = get_totals_range($conn, 'mf_entries', 'amount', 'policy_date BETWEEN ? AND ?', [$start_date, $end_date]);
$mf_entries_range = $mf_range['entries'];
$mf_total_amount_range = $mf_range['total_amount'];

// Fetch last month's totals for BMDS
$mf_last_month = get_totals_range($conn, 'mf_entries', 'amount', 'policy_date BETWEEN ? AND ?', [$first_day_last_month, $last_day_last_month]);
$mf_entries_last_month = $mf_last_month['entries'];
$mf_total_amount_last_month = $mf_last_month['total_amount'];

// MF - Totals from the start of the month to today's date
$mf_month_to_date = get_totals_range($conn, 'mf_entries', 'amount', 'policy_date BETWEEN ? AND ?', [$first_day_of_month, $today]);
$mf_entries_month_to_date = $mf_month_to_date['entries'];
$mf_total_amount_month_to_date = $mf_month_to_date['total_amount'];

// RTO - Today's totals
$rto_today = get_totals_range($conn, 'rto_entries', 'amount', 'policy_date = ?', [$today, $today]);
$rto_entries_today = $rto_today['entries'];
$rto_total_amount_today = $rto_today['total_amount'];

// Fetch range totals
$rto_range = get_totals_range($conn, 'rto_entries', 'other_amount', 'policy_date BETWEEN ? AND ?', [$start_date, $end_date]);
$rto_entries_range = $rto_range['entries'];
$rto_total_amount_range = $rto_range['total_amount'];

// Fetch last month's totals
$rto_last_month = get_totals_range($conn, 'rto_entries', 'other_amount', 'policy_date BETWEEN ? AND ?', [$first_day_last_month, $last_day_last_month]);
$rto_entries_last_month = $rto_last_month['entries'];
$rto_total_amount_last_month = $rto_last_month['total_amount'];

// Fetch month-to-date totals
$rto_month_to_date = get_totals_range($conn, 'rto_entries', 'other_amount', 'policy_date BETWEEN ? AND ?', [$first_day_of_month, $today]);
$rto_entries_month_to_date = $rto_month_to_date['entries'];
$rto_total_amount_month_to_date = $rto_month_to_date['total_amount'];


// Expense Total Count

$expense_today = get_totals_range($conn, 'expenses', 'amount', 'policy_date = ?', [$today, $today]);
$expense_entries_today = $expense_today['entries'];
$expense_total_amount_today = $expense_today['total_amount'];

// Expense - Totals for selected date range
$expense_range = get_totals_range($conn, 'expenses', 'amount', 'policy_date BETWEEN ? AND ?', [$start_date, $end_date]);
$expense_entries_range = $expense_range['entries'];
$expense_total_amount_range = $expense_range['total_amount'];

// Fetch last month's totals for Expense
$expense_last_month = get_totals_range($conn, 'expenses', 'amount', 'policy_date BETWEEN ? AND ?', [$first_day_last_month, $last_day_last_month]);
$expense_entries_last_month = $expense_last_month['entries'];
$expense_total_amount_last_month = $expense_last_month['total_amount'];

// Expense - Totals from the start of the month to today's date
$expense_month_to_date = get_totals_range($conn, 'expenses', 'amount', 'policy_date BETWEEN ? AND ?', [$first_day_of_month, $today]);
$expense_entries_month_to_date = $expense_month_to_date['entries'];
$expense_total_amount_month_to_date = $expense_month_to_date['total_amount'];



// Show goals with perticular job

// Initialize an array to store goals with default values
$goals = [
    'GIC' => 'Not Set',
    'LIC' => 'Not Set',
    'RTO' => 'Not Set',
    'BMDS' => 'Not Set',
    'MF' => 'Not Set',
    'CLIENT' => 'Not Set',
];

// Query to fetch goals for all job types
$sql = "SELECT job_type, goal FROM goal"; // Corrected to fetch 'goal' field
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $job_type = $row['job_type'];
        $goal_value = $row['goal'];
        
        // Update the array if job_type exists in the predefined list
        if (array_key_exists($job_type, $goals)) {
            $goals[$job_type] = htmlspecialchars($goal_value); // Sanitize the output
        }
    }
} else {
    echo "Error fetching data: " . $conn->error;
}

?>