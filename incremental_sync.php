<?php
// incremental_sync.php

class IncrementalSync {
    private $local_conn;
    private $hosted_conn;
    private $sync_stats = [];
    
    public function __construct() {
        
        // Get local config
        $config = include 'includes\config.php';
        
        // Local connection using your existing config
        $this->local_conn = new mysqli(
            $config['servername'],
            $config['username'], 
            $config['password'],
            $config['dbname']
        );
        
        // Check local connection
        if ($this->local_conn->connect_error) {
            die("Local connection failed: " . $this->local_conn->connect_error);
        }
        
        // Hosted connection - using REMOTE_DB_ variables from your .env file
        $hosted_host = $_ENV['REMOTE_DB_HOST'] ?? getenv('REMOTE_DB_HOST');
        $hosted_user = $_ENV['REMOTE_DB_USER'] ?? getenv('REMOTE_DB_USER');
        $hosted_pass = $_ENV['REMOTE_DB_PASS'] ?? getenv('REMOTE_DB_PASS');
        $hosted_name = $_ENV['REMOTE_DB_NAME'] ?? getenv('REMOTE_DB_NAME');
        $hosted_port = $_ENV['HOSTED_DB_PORT'] ?? getenv('HOSTED_DB_PORT') ?? '3306';
        
        // Debug hosted connection details
        error_log("Hosted DB Connection Details:");
        error_log("Host: $hosted_host");
        error_log("User: $hosted_user");
        error_log("Database: $hosted_name");
        error_log("Port: $hosted_port");
        
        echo "<!-- Debug: Connecting to hosted DB: $hosted_host as $hosted_user to database $hosted_name on port $hosted_port -->";
        
        $this->hosted_conn = new mysqli(
            $hosted_host,
            $hosted_user,
            $hosted_pass, 
            $hosted_name,
            $hosted_port
        );
        
        // Check hosted connection
        if ($this->hosted_conn->connect_error) {
            die("Hosted connection failed: " . $this->hosted_conn->connect_error . 
                " (Trying to connect to $hosted_host as $hosted_user to database $hosted_name)");
        }
        
        // Increase timeout for large operations
        $this->local_conn->options(MYSQLI_OPT_READ_TIMEOUT, 300);
        $this->hosted_conn->options(MYSQLI_OPT_READ_TIMEOUT, 300);
        set_time_limit(0);
        
        // Set timezone
        date_default_timezone_set('Asia/Kolkata');
        
        echo "<!-- Debug: Both connections established successfully -->";
    }
    
    // ... ALL YOUR EXISTING METHODS REMAIN THE SAME ...
    public function syncAllTables() {
        // Start HTML output with Bootstrap
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Database Sync Report</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
                body { background-color: #f8f9fa; padding: 20px; }
                .sync-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
                .table-container { background: white; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); overflow: hidden; }
                .table th { background-color: #f1f3f4; border-bottom: 2px solid #dee2e6; }
                .status-success { color: #28a745; }
                .status-error { color: #dc3545; }
                .stats-card { background: white; border-radius: 10px; padding: 15px; margin-bottom: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                .count-badge { font-size: 0.9em; padding: 5px 10px; }
                .security-note { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 10px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class="container-fluid">';

        echo '<div class="sync-header text-center">
                <h1><i class="fas fa-database me-2"></i>Database Sync Report</h1>
                <p class="mb-0">Sync started at: ' . date('Y-m-d H:i:s') . '</p>
                
            </div>';

        $tables = [
            'assignby' => ['creation_on', 'modified_on'],
            'assign_to' => ['creation_on', 'modified_on'], 
            'bills' => ['created_at', 'updated_at'],
            'bmds_entries' => ['creation_on', 'modified_on'],
            'cities' => ['creation_on', 'modified_on'],
            'client' => ['creation_on', 'modified_on'],
            'expenses' => ['creation_on', 'modified_on'],
            'expenses_reminders' => ['created_at', 'updated_at'],
            'file' => ['creation_on', 'modified_on'],
            'gic_entries' => ['creation_on', 'modified_on'],
            'goal' => ['creation_on', 'modified_on'],
            'internet_details' => ['creation_on', 'modified_on'],
            'letters' => ['created_at', 'updated_at'],
            'lic_entries' => ['creation_on', 'modified_on'],
            'mf_entries' => ['creation_on', 'modified_on'],
            'property_details' => ['creation_on', 'modified_on'],
            'rto_entries' => ['creation_on', 'modified_on'],
            'tasks' => ['created_at', 'updated_at'],
            'thought' => ['creation_on', 'modified_on'],
            'user' => ['creation_on', 'modified_on'],
            'vehicle_details' => ['creation_on', 'modified_on']
        ];
        
        $this->sync_stats = [];
        
        foreach ($tables as $table => $timestamp_fields) {
            try {
                $table_stats = [
                    'table' => $table,
                    'inserted' => 0,
                    'updated' => 0,
                    'deleted' => 0,
                    'status' => 'success'
                ];
                
                // First sync deletions
                $table_stats['deleted'] = $this->syncDeletions($table);
                
                // Then sync inserts and updates
                $sync_result = $this->syncSingleTable($table, $timestamp_fields);
                $table_stats['inserted'] = $sync_result['inserted'];
                $table_stats['updated'] = $sync_result['updated'];
                
                $this->sync_stats[] = $table_stats;
                
            } catch (Exception $e) {
                $this->sync_stats[] = [
                    'table' => $table,
                    'inserted' => 0,
                    'updated' => 0,
                    'deleted' => 0,
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }
        
        $this->updateLastSyncDate();
        $this->displaySyncReport();
        
        // Close HTML
        echo '</div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            </body>
            </html>';
    }
    
    private function syncDeletions($table) {
        $deleted_count = 0;
        
        // Get all IDs from local database
        $local_ids_result = $this->local_conn->query("SELECT id FROM `$table`");
        if (!$local_ids_result) {
            return 0;
        }
        
        $local_ids = [];
        while ($row = $local_ids_result->fetch_assoc()) {
            $local_ids[] = $row['id'];
        }
        $local_ids_result->free();
        
        if (empty($local_ids)) {
            $this->hosted_conn->query("DELETE FROM `$table`");
            return $this->hosted_conn->affected_rows;
        }
        
        // Get all IDs from hosted database
        $hosted_ids_result = $this->hosted_conn->query("SELECT id FROM `$table`");
        if (!$hosted_ids_result) {
            return 0;
        }
        
        $hosted_ids = [];
        while ($row = $hosted_ids_result->fetch_assoc()) {
            $hosted_ids[] = $row['id'];
        }
        $hosted_ids_result->free();
        
        // Find IDs that exist in hosted but not in local
        $ids_to_delete = array_diff($hosted_ids, $local_ids);
        
        if (!empty($ids_to_delete)) {
            $ids_placeholder = implode(',', array_fill(0, count($ids_to_delete), '?'));
            $sql = "DELETE FROM `$table` WHERE id IN ($ids_placeholder)";
            
            $stmt = $this->hosted_conn->prepare($sql);
            if ($stmt) {
                $types = str_repeat('i', count($ids_to_delete));
                $stmt->bind_param($types, ...$ids_to_delete);
                $stmt->execute();
                $deleted_count = $stmt->affected_rows;
                $stmt->close();
            }
        }
        
        return $deleted_count;
    }
    
    private function syncSingleTable($table, $timestamp_fields) {
        $last_sync = $this->getLastSyncDate();
        
        // Use modified timestamp if available, otherwise use creation timestamp
        list($creation_field, $modified_field) = $timestamp_fields;
        
        $query = "SELECT * FROM `$table` WHERE 1=1";
        
        // Check if modified timestamp field exists and use it for detecting updates
        if ($modified_field && $this->hasColumn($table, $modified_field)) {
            $query .= " AND (`$creation_field` > '$last_sync' OR `$modified_field` > '$last_sync')";
        } elseif ($creation_field && $this->hasColumn($table, $creation_field)) {
            $query .= " AND `$creation_field` > '$last_sync'";
        } else {
            // If no timestamp fields, sync all records (fallback - not recommended for large tables)
            $query = "SELECT * FROM `$table`";
        }
        
        $result = $this->local_conn->query($query);
        
        if (!$result) {
            throw new Exception("Query failed: " . $this->local_conn->error);
        }
        
        $inserted_count = 0;
        $updated_count = 0;
        
        if ($result->num_rows > 0) {
            $this->hosted_conn->begin_transaction();
            
            // Get column names dynamically
            $columns = [];
            $result_meta = $result->fetch_fields();
            foreach ($result_meta as $column) {
                $columns[] = $column->name;
            }
            
            $columns_str = '`' . implode('`, `', $columns) . '`';
            $placeholders = implode(', ', array_fill(0, count($columns), '?'));
            
            // Build INSERT with ON DUPLICATE KEY UPDATE
            $update_clause = $this->buildUpdateClause($columns);
            $sql = "INSERT INTO `$table` ($columns_str) VALUES ($placeholders) 
                    ON DUPLICATE KEY UPDATE $update_clause";
            
            $stmt = $this->hosted_conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->hosted_conn->error);
            }
            
            while ($row = $result->fetch_assoc()) {
                $types = $this->getParamTypes($row);
                $values = array_values($row);
                
                $stmt->bind_param($types, ...$values);
                $stmt->execute();
                
                if ($stmt->affected_rows == 1) {
                    $inserted_count++;
                } else {
                    $updated_count++;
                }
            }
            
            $this->hosted_conn->commit();
            $stmt->close();
        }
        
        $result->free();
        return ['inserted' => $inserted_count, 'updated' => $updated_count];
    }
    
    private function displaySyncReport() {
        $total_inserted = 0;
        $total_updated = 0;
        $total_deleted = 0;
        
        foreach ($this->sync_stats as $stats) {
            $total_inserted += $stats['inserted'];
            $total_updated += $stats['updated'];
            $total_deleted += $stats['deleted'];
        }
        
        $grand_total = $total_inserted + $total_updated + $total_deleted;
        
        // Display summary cards
        echo '<div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card text-center border-success">
                        <h5 class="text-success"><i class="fas fa-plus-circle me-2"></i>Inserted</h5>
                        <h3 class="text-success">' . $total_inserted . '</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center border-warning">
                        <h5 class="text-warning"><i class="fas fa-edit me-2"></i>Updated</h5>
                        <h3 class="text-warning">' . $total_updated . '</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center border-danger">
                        <h5 class="text-danger"><i class="fas fa-trash me-2"></i>Deleted</h5>
                        <h3 class="text-danger">' . $total_deleted . '</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center border-primary">
                        <h5 class="text-primary"><i class="fas fa-chart-bar me-2"></i>Total Changes</h5>
                        <h3 class="text-primary">' . $grand_total . '</h3>
                    </div>
                </div>
            </div>';
        
        // Display detailed table
        echo '<div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th><i class="fas fa-table me-1"></i>Table Name</th>
                                <th class="text-center"><i class="fas fa-plus text-success me-1"></i>Inserted</th>
                                <th class="text-center"><i class="fas fa-edit text-warning me-1"></i>Updated</th>
                                <th class="text-center"><i class="fas fa-trash text-danger me-1"></i>Deleted</th>
                                <th class="text-center"><i class="fas fa-info-circle me-1"></i>Status</th>
                            </tr>
                        </thead>
                        <tbody>';
        
        foreach ($this->sync_stats as $stats) {
            $status_icon = ($stats['status'] == 'success') ? 
                '<i class="fas fa-check-circle text-success"></i>' : 
                '<i class="fas fa-times-circle text-danger"></i>';
            
            $status_text = ($stats['status'] == 'success') ? 
                '<span class="badge bg-success">Success</span>' : 
                '<span class="badge bg-danger">Error: ' . ($stats['message'] ?? 'Unknown error') . '</span>';
            
            echo '<tr>
                    <td><strong>' . htmlspecialchars($stats['table']) . '</strong></td>
                    <td class="text-center">' . ($stats['inserted'] > 0 ? '<span class="badge bg-success count-badge">' . $stats['inserted'] . '</span>' : '0') . '</td>
                    <td class="text-center">' . ($stats['updated'] > 0 ? '<span class="badge bg-warning count-badge">' . $stats['updated'] . '</span>' : '0') . '</td>
                    <td class="text-center">' . ($stats['deleted'] > 0 ? '<span class="badge bg-danger count-badge">' . $stats['deleted'] . '</span>' : '0') . '</td>
                    <td class="text-center">' . $status_text . '</td>
                  </tr>';
        }
        
        echo '</tbody>
                    </table>
                </div>
            </div>';
        
        // Footer
        echo '<div class="mt-4 text-center text-muted">
                <p><i class="fas fa-clock me-1"></i>Sync completed at: ' . date('Y-m-d H:i:s') . '</p>
            </div>';
    }
    
    private function buildUpdateClause($columns) {
        $updates = [];
        foreach ($columns as $column) {
            if ($column !== 'id' && !$this->isAutoIncrementColumn($column)) {
                $updates[] = "`$column` = VALUES(`$column`)";
            }
        }
        return implode(', ', $updates);
    }
    
    private function isAutoIncrementColumn($column) {
        $auto_increment_columns = ['id', 'client_id', 'user_id', 'task_id'];
        return in_array($column, $auto_increment_columns);
    }
    
    private function hasColumn($table, $column) {
        $result = $this->local_conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $result && $result->num_rows > 0;
    }
    
    private function getParamTypes($row) {
        $types = '';
        foreach ($row as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } elseif (is_null($value)) {
                $types .= 's';
            } else {
                $types .= 's';
            }
        }
        return $types;
    }
    
    private function getLastSyncDate() {
        if (!file_exists('last_sync.txt')) {
            $yesterday = date('Y-m-d H:i:s', strtotime('-1 day'));
            file_put_contents('last_sync.txt', $yesterday);
            return $yesterday;
        }
        return trim(file_get_contents('last_sync.txt'));
    }
    
    private function updateLastSyncDate() {
        file_put_contents('last_sync.txt', date('Y-m-d H:i:s'));
    }
    
    public function __destruct() {
        if ($this->local_conn) {
            $this->local_conn->close();
        }
        if ($this->hosted_conn) {
            $this->hosted_conn->close();
        }
    }
}

// Error handling and execution
try {
    $sync = new IncrementalSync();
    $sync->syncAllTables();
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Sync failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
    error_log("Sync error: " . $e->getMessage());
}
?>