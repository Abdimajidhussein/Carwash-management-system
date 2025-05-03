<?php
session_start();
include 'includes/config.php'; // Database connection
// Set default timezone
date_default_timezone_set('America/New_York');

// Function to generate CSV
function generateCSV($data, $filename) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add headers
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
    }
    
    // Add data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

function getDBLastModified($conn) {
    $tables = ['appointments', 'users', 'services'];
    $lastUpdates = [];
    
    foreach ($tables as $table) {
        // Get UPDATE_TIME from information_schema
        $query = "SELECT UPDATE_TIME FROM information_schema.tables 
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $table);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $row = $result->fetch_assoc()) {
            if ($row['UPDATE_TIME']) {
                $lastUpdates[] = strtotime($row['UPDATE_TIME']);
            }
        }
        
        // Fix: Define timestamp fields per table
        $timestampField = match ($table) {
            'appointments' => 'updated_at',
            'users' => 'modified_at',  // Example: Use actual column names
            'services' => 'modified_at', // Adjust based on your schema
            default => 'created_at' // Only if it exists for other tables
        };

        // Validate column existence (optional but safer)
        $checkColumn = $conn->query("SHOW COLUMNS FROM $table LIKE '$timestampField'");
        if ($checkColumn->num_rows > 0) {
            $fallbackQuery = "SELECT MAX($timestampField) AS last_update FROM $table";
            $fallbackResult = $conn->query($fallbackQuery);
            if ($fallbackResult && $row = $fallbackResult->fetch_assoc()) {
                if ($row['last_update']) {
                    $lastUpdates[] = strtotime($row['last_update']);
                }
            }
        }
    }
    
    return !empty($lastUpdates) ? max($lastUpdates) : time();
}

// Flag to determine if this is an AJAX request for real-time updates
$isAjaxRequest = isset($_GET['ajax']) && $_GET['ajax'] == 'true';

// Store the report parameters in session for persistence between AJAX calls
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['report_params'] = [
        'report_type' => $_POST['report_type'] ?? '',
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? ''
    ];
    
    if (isset($_POST['export_csv'])) {
        $report_data = generateReportData($conn, $_SESSION['report_params'], $isAjaxRequest);
        if (!empty($report_data)) {
            generateCSV($report_data, "Report_" . date('Y-m-d'));
        } else {
            $_SESSION['error'] = "No data available to export";
        }
    }
}

// Function to generate report data based on parameters
function generateReportData($conn, $params, $isAjaxRequest = false) {
    $report_type = $params['report_type'] ?? '';
    $start_date = $params['start_date'] ?? '';
    $end_date = $params['end_date'] ?? '';
    
    // Validate dates
    if (!empty($start_date)) {
        $start_date = date('Y-m-d', strtotime($start_date));
    }
    if (!empty($end_date)) {
        $end_date = date('Y-m-d', strtotime($end_date));
    }
    
    $where = [];
    if (!empty($start_date) && !empty($end_date)) {
        $where[] = "a.date BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
    } elseif (!empty($start_date)) {
        $where[] = "a.date >= '$start_date 00:00:00'";
    } elseif (!empty($end_date)) {
        $where[] = "a.date <= '$end_date 23:59:59'";
    }
    
    $where_clause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);
    
    switch ($report_type) {
        case 'appointments':
            $query = "SELECT a.id, u.name AS customer, a.vehicle_plate, a.service, 
                     a.date, a.time, a.status, a.payment_method, a.amount_paid, 
                     a.payment_date, a.created_at
                     FROM appointments a
                     JOIN users u ON a.user_id = u.id
                     $where_clause
                     ORDER BY a.date DESC, a.time DESC";
            break;
            
        case 'revenue':
            $query = "SELECT 
                        DATE(a.date) AS wash_date,
                        COUNT(*) AS total_washes,
                        SUM(a.amount_paid) AS total_revenue,
                        AVG(a.amount_paid) AS average_revenue_per_wash,
                        a.payment_method
                     FROM appointments a
                     $where_clause
                     GROUP BY wash_date, a.payment_method
                     ORDER BY wash_date DESC";
            break;
            
        case 'services':
            $query = "SELECT 
                        s.service_name,
                        COUNT(a.id) AS total_requests,
                        SUM(a.amount_paid) AS total_revenue,
                        AVG(a.amount_paid) AS average_revenue
                     FROM services s
                     LEFT JOIN appointments a ON a.service = s.service_name
                     $where_clause
                     GROUP BY s.service_name
                     ORDER BY total_revenue DESC";
            break;
            
        case 'customers':
            $query = "SELECT 
                        u.id, u.name, u.email, u.phone,
                        COUNT(a.id) AS total_washes,
                        SUM(a.amount_paid) AS total_spent,
                        MAX(a.date) AS last_visit
                     FROM users u
                     LEFT JOIN appointments a ON a.user_id = u.id
                     $where_clause
                     GROUP BY u.id, u.name, u.email, u.phone
                     ORDER BY total_spent DESC";
            break;
            
        default:
            return [];
    }
    
    $result = $conn->query($query);
    
    if (!$result) {
        if (!$isAjaxRequest) {
            $_SESSION['error'] = "Error generating report: " . $conn->error;
        }
        return [];
    }
    
    $report_data = [];
    while ($row = $result->fetch_assoc()) {
        $report_data[] = $row;
    }
    
    return $report_data;
}
function getQuickStats($conn) {
    $stats = [];

    // Total appointments
    $result = $conn->query("SELECT COUNT(*) AS total FROM appointments");
    $stats['total_appointments'] = $result->fetch_assoc()['total'] ?? 0;

    // Completed appointments (case-insensitive match)
    $result = $conn->query("SELECT COUNT(*) AS total FROM appointments WHERE LOWER(status) = 'completed'");
    $stats['completed_appointments'] = $result->fetch_assoc()['total'] ?? 0;

    // Total revenue from completed
    $result = $conn->query("SELECT SUM(amount_paid) AS total FROM appointments WHERE LOWER(status) = 'completed'");
    $row = $result->fetch_assoc();
    $stats['total_revenue'] = $row['total'] !== null ? (float) $row['total'] : 0;

    // Active customers (distinct users who made any appointment)
    $result = $conn->query("SELECT COUNT(DISTINCT user_id) AS total FROM appointments");
    $stats['active_customers'] = $result->fetch_assoc()['total'] ?? 0;

    return $stats;
}


// If this is an AJAX request, return only the necessary data
if ($isAjaxRequest) {
    header('Content-Type: application/json');
    
    // Get the last modified timestamp from the client
    $clientLastModified = isset($_GET['last_modified']) ? intval($_GET['last_modified']) : 0;
    
    // Get the current DB timestamp
    $serverLastModified = getDBLastModified($conn);
    
    // Prepare the response
    $response = [
        'last_modified' => $serverLastModified,
        'has_updates' => ($serverLastModified > $clientLastModified)
    ];
    
    // Only send new data if there are updates
    if ($response['has_updates']) {
        // Generate reports if we have parameters
        if (isset($_SESSION['report_params']) && !empty($_SESSION['report_params']['report_type'])) {
            $report_data = generateReportData($conn, $_SESSION['report_params'], $isAjaxRequest);
            $response['report_data'] = $report_data;
            $response['report_count'] = count($report_data);
        }
        
        // Get updated stats
        $response['stats'] = getQuickStats($conn);
    }
    
    echo json_encode($response);
    exit;
}

// If not an AJAX request, process normally
$report_data = [];
if (isset($_SESSION['report_params']) && !empty($_SESSION['report_params']['report_type'])) {
    $report_data = generateReportData($conn, $_SESSION['report_params'], $isAjaxRequest);
}

// Get quick stats for initial page load
$stats = getQuickStats($conn);

// Get current DB timestamp for initial page load
$currentDBTimestamp = getDBLastModified($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Reports - Car Wash System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 80px;
            --transition-speed: 0.3s;
        }
        
        body {
            display: flex;
            transition: margin-left var(--transition-speed);
            margin-left: var(--sidebar-width);
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding: 20px 0;
            transition: width var(--transition-speed);
            overflow: hidden;
            z-index: 1000;
        }
        
        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }
        
        .sidebar.collapsed h2,
        .sidebar.collapsed ul li a span {
            display: none;
        }
        
        .sidebar h2 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            padding: 0 15px;
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar ul li {
            padding: 10px 15px;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }
        
        .sidebar ul li:hover {
            background-color: #495057;
            border-left: 4px solid #0d6efd;
        }
        
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .sidebar ul li a i {
            margin-right: 10px;
            min-width: 25px;
            text-align: center;
        }
        
        .sidebar ul li.active {
            background-color: #495057;
            border-left: 4px solid #0d6efd;
        }
        
        .toggle-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }
        
        .main-content {
            flex-grow: 1;
            padding: 20px;
            transition: margin-left var(--transition-speed);
        }
        
        body.collapsed {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        .report-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .report-header {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .stats-card {
            border-left: 4px solid #0d6efd;
        }
        
        /* Real-time update indicators */
        .update-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-left: 8px;
            background-color: #28a745;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                opacity: 1;
            }
            50% {
                opacity: 0.3;
            }
            100% {
                opacity: 1;
            }
        }
        
        .update-text {
            font-size: 0.85rem;
            color: #28a745;
            margin-left: 5px;
            display: none;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            display: none;
        }
        
        .loading-spinner {
            width: 2rem;
            height: 2rem;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <button class="toggle-btn" id="toggleBtn">☰</button>
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php"><i>📊</i><span>Dashboard</span></a></li>
            <li><a href="manage-users.php"><i>👥</i><span>Manage Users</span></a></li>
            <li><a href="manage-appointments.php"><i>📅</i><span>Manage Bookings</span></a></li>
            <li><a href="manage-promotions.php"><i>🎁</i><span>Manage Promotions</span></a></li>
            <li><a href="manage-services.php"><i>🧼</i><span>Manage Services</span></a></li>
            <li><a href="manage-feedback.php"><i>📩</i><span>Manage Feedback</span></a></li>
            <li><a href="admin_reports.php" class="active"><i>📈</i><span>Reports</span></a></li>
            <li><a href="logout.php"><i>🚪</i><span>Logout</span></a></li>
        </ul>
    </div>

    <div class="main-content container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="text-center">Car Wash Management System - Admin Reports</h2>
                <p class="text-center text-muted">
                    <span id="realtime-indicator">
                        <span class="update-indicator"></span>
                        <span class="update-text">Real-time updates enabled</span>
                    </span>
                </p>
            </div>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-4">
                <div class="report-container">
                    <h4 class="report-header">Generate Report</h4>
                    <form method="POST" id="reportForm">
                        <div class="mb-3">
                            <label for="report_type" class="form-label">Report Type</label>
                            <select class="form-select" id="report_type" name="report_type" required>
                                <option value="">Select Report Type</option>
                                <option value="appointments" <?= (isset($_SESSION['report_params']['report_type']) && $_SESSION['report_params']['report_type'] == 'appointments') ? 'selected' : '' ?>>Appointments</option>
                                <option value="revenue" <?= (isset($_SESSION['report_params']['report_type']) && $_SESSION['report_params']['report_type'] == 'revenue') ? 'selected' : '' ?>>Revenue</option>
                                <option value="services" <?= (isset($_SESSION['report_params']['report_type']) && $_SESSION['report_params']['report_type'] == 'services') ? 'selected' : '' ?>>Services Analysis</option>
                                <option value="customers" <?= (isset($_SESSION['report_params']['report_type']) && $_SESSION['report_params']['report_type'] == 'customers') ? 'selected' : '' ?>>Customer Insights</option>
                            </select>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $_SESSION['report_params']['start_date'] ?? '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $_SESSION['report_params']['end_date'] ?? '' ?>">
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Generate Report</button>
                            <button type="submit" name="export_csv" class="btn btn-success">Export to CSV</button>
                        </div>
                    </form>
                </div>
                
                <!-- Quick Stats -->
                <div class="report-container mt-4">
                    <h4 class="report-header">Quick Statistics</h4>
                    
                    <div class="card mb-3 stats-card">
                        <div class="card-body">
                            <h5 class="card-title">Total Appointments</h5>
                            <p class="card-text display-6" id="stat-total-appointments"><?= number_format($stats['total_appointments']) ?></p>
                        </div>
                    </div>
                    
                    <div class="card mb-3 stats-card">
                        <div class="card-body">
                            <h5 class="card-title">Completed Washes</h5>
                            <p class="card-text display-6" id="stat-completed-appointments"><?= number_format($stats['completed_appointments']) ?></p>
                        </div>
                    </div>
                    
                    <div class="card mb-3 stats-card">
                        <div class="card-body">
                            <h5 class="card-title">Total Revenue</h5>
                            <p class="card-text display-6" id="stat-total-revenue">$ <?= number_format($stats['total_revenue'], 2) ?></p>
                        </div>
                    </div>
                    
                    <div class="card mb-3 stats-card">
                        <div class="card-body">
                            <h5 class="card-title">Active Customers</h5>
                            <p class="card-text display-6" id="stat-active-customers"><?= number_format($stats['active_customers']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="report-container">
                    <h4 class="report-header">Report Results</h4>
                    
                    <div class="loading" id="loading">
                        <div class="spinner-border loading-spinner text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Updating report data...</p>
                    </div>
                    
                    <div id="report-results-container">
                        <?php if (!empty($report_data)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="report-table">
                                    <thead class="table-dark">
                                        <tr>
                                            <?php foreach (array_keys($report_data[0]) as $column): ?>
                                                <th><?= ucwords(str_replace('_', ' ', $column)) ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($report_data as $row): ?>
                                            <tr>
                                                <?php foreach ($row as $value): ?>
                                                    <td>
                                                        <?php 
                                                        if (strtotime($value) !== false && !is_numeric($value)) {
                                                            echo date('M j, Y', strtotime($value));
                                                        } elseif (is_numeric($value) && strpos($value, '.') !== false) {
                                                            echo '$ ' . number_format($value, 2);
                                                        } else {
                                                            echo htmlspecialchars($value);
                                                        }
                                                        ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-3">
                                <p class="text-muted" id="report-timestamp">
                                    Report generated on <?= date('F j, Y \a\t g:i a') ?> | 
                                    <span id="record-count"><?= count($report_data) ?></span> records found
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info" id="no-data-message">
                                No report data to display. Please generate a report using the form.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar
        document.getElementById('toggleBtn').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.body.classList.toggle('collapsed');
        });
        
        // Set end date to today by default if not set
        if (!document.getElementById('end_date').value) {
            document.getElementById('end_date').valueAsDate = new Date();
        }
        
        // Simple date validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                alert('Start date cannot be after end date');
                e.preventDefault();
            }
        });
        
        // Real-time data updates
        document.addEventListener('DOMContentLoaded', function() {
            // Store the last modified timestamp
            let lastModified = <?= $currentDBTimestamp ?>;
            let isFirstUpdate = true;
            
            // Show real-time indicator after page loads
            setTimeout(function() {
                document.querySelector('.update-text').style.display = 'inline';
            }, 1000);
            
            // Function to check for updates
            function checkForUpdates() {
                fetch('admin_reports.php?ajax=true&last_modified=' + lastModified)
                    .then(response => response.json())
                    .then(data => {
                        // Update the timestamp
                        lastModified = data.last_modified;
                        
                        // If there are updates, refresh the data
                        if (data.has_updates && !isFirstUpdate) {
                            // Show loading indicator
                            document.getElementById('loading').style.display = 'block';
                            
                            // Update stats
                            if (data.stats) {
                                document.getElementById('stat-total-appointments').textContent = 
                                    new Intl.NumberFormat().format(data.stats.total_appointments);
                                document.getElementById('stat-completed-appointments').textContent = 
                                    new Intl.NumberFormat().format(data.stats.completed_appointments);
                                document.getElementById('stat-total-revenue').textContent = 
                                    '$ ' + new Intl.NumberFormat().format(data.stats.total_revenue);
                                document.getElementById('stat-active-customers').textContent = 
                                    new Intl.NumberFormat().format(data.stats.active_customers);
                            }
                            
                            // Update report data if available
                            if (data.report_data && data.report_data.length > 0) {
                                updateReportTable(data.report_data);
                                document.getElementById('record-count').textContent = data.report_count;
                                
                                // Update timestamp
                                document.getElementById('report-timestamp').textContent = 
                                    'Report updated on ' + new Date().toLocaleString() + ' | ' + 
                                    data.report_count + ' records found';
                                
                                // Hide no data message if it exists
                                const noDataMessage = document.getElementById('no-data-message');
                                if (noDataMessage) {
                                    noDataMessage.style.display = 'none';
                                }
                            }
                            
                            // Flash the indicator to show updates
                            const indicator = document.querySelector('.update-indicator');
                            indicator.style.backgroundColor = '#dc3545'; // Red
                            setTimeout(() => {
                                indicator.style.backgroundColor = '#28a745'; // Green
                                document.getElementById('loading').style.display = 'none';
                            }, 1000);
                        }
                        
                        // No longer first update
                        isFirstUpdate = false;
                    })
                    .catch(error => {
                        console.error('Error checking for updates:', error);
                    });
            }
            
            // Function to update the report table
            function updateReportTable(reportData) {
                if (!reportData || reportData.length === 0) return;
                
                // Get or create the table
                let tableContainer = document.querySelector('.table-responsive');
                let reportTable = document.getElementById('report-table');
                
                if (!tableContainer) {
                    // Create table container if it doesn't exist
                    tableContainer = document.createElement('div');
                    tableContainer.className = 'table-responsive';
                    document.getElementById('report-results-container').prepend(tableContainer);
                }
                
                if (!reportTable) {
                    // Create new table if it doesn't exist
                    reportTable = document.createElement('table');
                    reportTable.id = 'report-table';
                    reportTable.className = 'table table-striped table-hover';
                    tableContainer.appendChild(reportTable);
                    
                    // Create table header
                    const thead = document.createElement('thead');
                    thead.className = 'table-dark';
                    const headerRow = document.createElement('tr');
                    
                    Object.keys(reportData[0]).forEach(column => {
                        const th = document.createElement('th');
                        th.textContent = column.replace(/_/g, ' ')
                            .replace(/\b\w/g, l => l.toUpperCase());
                        headerRow.appendChild(th);
                    });
                    
                    thead.appendChild(headerRow);
                    reportTable.appendChild(thead);
                    
                    // Create table body
                    const tbody = document.createElement('tbody');
                    reportTable.appendChild(tbody);
                } else {
                    // Clear existing table body
                    const tbody = reportTable.querySelector('tbody');
                    if (tbody) {
                        tbody.innerHTML = '';
                    }
                }
                
                // Add rows to table body
                const tbody = reportTable.querySelector('tbody');
                reportData.forEach(row => {
                    const tr = document.createElement('tr');
                    
                    Object.values(row).forEach(value => {
                        const td = document.createElement('td');
                        
                        // Format date and numeric values
                        if (value && !isNaN(Date.parse(value)) && isNaN(value)) {
                            td.textContent = new Date(value).toLocaleDateString('en-US', {
                                month: 'short',
                                day: 'numeric',
                                year: 'numeric'
                            });
                        } else if (value && !isNaN(value) && value.toString().includes('.')) {
                            td.textContent = '$ ' + new Intl.NumberFormat().format(parseFloat(value));
                        } else {
                            td.textContent = value;
                        }
                        
                        tr.appendChild(td);
                    });
                    
                    tbody.appendChild(tr);
                });
            }
            
            // Check for updates every 5 seconds
            setInterval(checkForUpdates, 5000);
            
            // Initial check
            setTimeout(checkForUpdates, 1000);
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>