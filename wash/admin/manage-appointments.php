<?php
session_start();
include 'includes/config.php'; // Include database connection

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle appointment actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    if ($_GET['action'] == 'approve' && isset($_GET['payment_method']) && isset($_GET['amount_paid'])) {
        $payment_method = mysqli_real_escape_string($conn, $_GET['payment_method']);
        $amount_paid = floatval($_GET['amount_paid']);

        $query = "UPDATE appointments
                  SET status='Completed',
                      payment_method='$payment_method',
                      amount_paid='$amount_paid',
                      payment_date=NOW()
                  WHERE id=$id";
        mysqli_query($conn, $query);
    }

    // 🔧 Add cancel logic
    if ($_GET['action'] == 'cancel') {
        $query = "UPDATE appointments
                  SET status='Cancelled',
                      payment_method=NULL,
                      amount_paid=NULL,
                      payment_date=NULL
                  WHERE id=$id";
        mysqli_query($conn, $query);
    }

    header("Location: manage-appointments.php");
    exit();
}



// Fetch new (pending) appointments
$newAppointments = mysqli_query($conn, "SELECT appointments.*, users.name AS user_name
                                        FROM appointments
                                        JOIN users ON appointments.user_id = users.id
                                        WHERE appointments.status='Pending'
                                        ORDER BY appointments.date DESC");

// Fetch completed and cancelled appointments
$completedAppointments = mysqli_query($conn,
    "SELECT appointments.*, users.name AS user_name,
            appointments.payment_method,
            appointments.payment_date
     FROM appointments
     JOIN users ON appointments.user_id = users.id
     WHERE appointments.status IN ('Completed', 'Cancelled')
     ORDER BY appointments.payment_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments</title>
    <link rel="stylesheet" href="css/admin-style.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 80px;
            --transition-speed: 0.3s;
        }

        body {
            display: flex;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
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
            overflow-y: auto;
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
            font-style: normal; /* Prevent icon from being italic */
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
            z-index: 1001;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
            margin-left: var(--sidebar-width);
            transition: margin-left var(--transition-speed);
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        .container {
            width: 90%;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        h3 {
            color: #555;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .search-container {
            text-align: right;
            margin-bottom: 15px;
        }

        .search-container input {
            padding: 8px;
            width: 250px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .search-container button {
            padding: 8px 12px;
            background-color: #ff9800;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 5px;
        }

        .search-container button:hover {
            background-color: #e68900;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #333;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .btn {
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            color: white;
            font-size: 14px;
            display: inline-block;
            margin: 3px;
        }

        .approve {
            background-color: green;
        }

        .cancel {
            background-color: red;
        }

        .btn:hover {
            opacity: 0.8;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const toggleBtn = document.querySelector(".toggle-btn");
            const sidebar = document.querySelector(".sidebar");
            const mainContent = document.querySelector(".main-content");

            toggleBtn.addEventListener("click", function () {
                sidebar.classList.toggle("collapsed");
                mainContent.classList.toggle("expanded");
            });

            // Function to adjust sidebar height
            function adjustSidebarHeight() {
                const contentHeight = mainContent.scrollHeight;
                sidebar.style.minHeight = contentHeight + 'px';
            }

            // Initial adjustment and on resize
            adjustSidebarHeight();
            window.addEventListener('resize', adjustSidebarHeight);
        });

        function searchTable(inputId, tableId) {
            // Get input value and convert to uppercase for case-insensitive comparison
            const input = document.getElementById(inputId);
            const filter = input.value.toUpperCase();
            const table = document.getElementById(tableId);
            const rows = table.getElementsByTagName("tr");
            
            // Loop through all table rows starting from index 1 (to skip the header row)
            for (let i = 1; i < rows.length; i++) {
                // Find the cell with the plate number (3rd column, index 2)
                const plateCell = rows[i].getElementsByTagName("td")[2];
                
                if (plateCell) {
                    const plateNumber = plateCell.textContent || plateCell.innerText;
                    
                    // Show row if plate number contains the search string, hide otherwise
                    if (plateNumber.toUpperCase().indexOf(filter) > -1) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <button class="toggle-btn" id="toggleBtn">☰</button>
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php"><i style="font-style: normal;">📊</i><span>Dashboard</span></a></li>
            <li><a href="manage-users.php"><i style="font-style: normal;">👥</i><span>Manage Users</span></a></li>
            <li class="active"><a href="manage-appointments.php"><i style="font-style: normal;">📅</i><span>Manage Bookings</span></a></li>
            <li><a href="manage-promotions.php"><i style="font-style: normal;">🎁</i><span>Manage Promotions</span></a></li>
            <li><a href="manage-services.php"><i style="font-style: normal;">🧼</i><span>Manage Services</span></a></li>
            <li><a href="manage-feedback.php"><i>📩</i><span>Manage Feedback</span></a></li>
            <li><a href="admin_reports.php"><i style="font-style: normal;">📈</i><span>Reports</span></a></li>
            <li><a href="logout.php"><i style="font-style: normal;">🚪</i><span>Logout</span></a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="container">
            <h2>Manage Bookings</h2>

            <h3>New Bookings (Pending)</h3>

            <div class="search-container">
                <input type="text" id="searchPending" placeholder="Search by Plate Number...">
                <button onclick="searchTable('searchPending', 'pendingTable')">Search</button>
            </div>

            <table id="pendingTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Plate Number</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($newAppointments)) { ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['user_name']; ?></td>
                            <td><?php echo $row['vehicle_plate']; ?></td>
                            <td><?php echo $row['service']; ?></td>
                            <td><?php echo $row['date']; ?></td>
                            <td><?php echo $row['time']; ?></td>
                            <td><?php echo $row['status']; ?></td>
                            <td>
                                <a href="payment.php?id=<?php echo $row['id']; ?>" class="btn approve">Approve</a>
                                <a href="manage-appointments.php?action=cancel&id=<?php echo $row['id']; ?>" class="btn cancel">Cancel</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <h3>Completed & Cancelled Bookings</h3>

            <div class="search-container">
                <input type="text" id="searchCompleted" placeholder="Search by Plate Number...">
                <button onclick="searchTable('searchCompleted', 'completedTable')">Search</button>
            </div>

            <table id="completedTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Plate Number</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Payment Date</th>
                        <th>Payment Method</th>
                        <th>Amount Paid</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($completedAppointments)) { ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['user_name']; ?></td>
                            <td><?php echo $row['vehicle_plate']; ?></td>
                            <td><?php echo $row['service']; ?></td>
                            <td><?php echo $row['status']; ?></td>
                            <td><?php echo ($row['payment_date'] ? date("Y-m-d H:i", strtotime($row['payment_date'])) : '-'); ?></td>
                            <td><?php echo ($row['status'] == 'Cancelled') ? '-' : $row['payment_method']; ?></td>
                            <td><?php echo ($row['status'] == 'Cancelled') ? '-' : '$' . number_format($row['amount_paid'], 2); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>