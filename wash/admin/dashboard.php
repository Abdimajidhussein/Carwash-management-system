<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

include 'includes/config.php';

// Fetch counts
$user_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$booking_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM appointments"))['count'];
$promotion_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM promotions"))['count'];
$service_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM services"))['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/admin-dashboard.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 60px;
            --transition-speed: 0.3s;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            transition: width var(--transition-speed);
            overflow: hidden;
            z-index: 1000;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            padding: 12px 20px;
            border-left: 4px solid transparent;
            transition: 0.3s;
        }

        .sidebar ul li:hover,
        .sidebar ul li.active {
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

        .sidebar.collapsed h2,
        .sidebar.collapsed ul li a span {
            display: none;
        }

        .toggle-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            background: none;
            border: none;
            color: white;
            cursor: pointer;
        }

        /* Main content */
        .main-content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 20px;
            transition: margin-left var(--transition-speed);
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Dashboard */
        .dashboard-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-top: 20px;
        }

        .card {
            width: 250px;
            padding: 20px;
            border-radius: 10px;
            color: white;
            text-align: center;
            transition: transform 0.3s;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .card:hover {
            transform: scale(1.05);
        }

        .card h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .btn {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 15px;
            background: rgba(0,0,0,0.2);
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .btn:hover {
            background: rgba(0,0,0,0.4);
        }

        /* Card colors */
        .card.users { background: #3498db; }
        .card.bookings { background: #e74c3c; }
        .card.promotions { background: #2ecc71; }
        .card.services { background: #fd198f; }
        .card.reports { background: #cc7d2e; }

        /* Change password */
        .change-password {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
        }

        .change-password:hover {
            background: #0056b3;
        }

        @media (max-width: 768px) {
            .dashboard-cards {
                flex-direction: column;
                align-items: center;
            }

            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
            }
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
            <li><a href="admin_reports.php"><i>📈</i><span>Reports</span></a></li>
            <li><a href="logout.php"><i>🚪</i><span>Logout</span></a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <a href="changepassword.php" class="change-password">Change Password</a>
        <h1>Admin Dashboard</h1>
        <p>Welcome, Admin! Manage the system from here.</p>

        <div class="dashboard-cards">
            <div class="card users">
                <h3>Users: <?php echo $user_count; ?></h3>
                <p>Registered users</p>
                <a href="manage-users.php" class="btn">View</a>
            </div>
            <div class="card bookings">
                <h3>Bookings: <?php echo $booking_count; ?></h3>
                <p>Customer bookings</p>
                <a href="manage-appointments.php" class="btn">View</a>
            </div>
            <div class="card promotions">
                <h3>Promotions: <?php echo $promotion_count; ?></h3>
                <p>Discounts & offers</p>
                <a href="manage-promotions.php" class="btn">View</a>
            </div>
            <div class="card services">
                <h3>Services: <?php echo $service_count; ?></h3>
                <p>Car wash packages</p>
                <a href="manage-services.php" class="btn">View</a>
            </div>
            <div class="card reports">
                <h3>Reports</h3>
                <p>Generate reports</p>
                <a href="admin_reports.php" class="btn">View</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const toggleBtn = document.getElementById("toggleBtn");
            const sidebar = document.getElementById("sidebar");

            toggleBtn.addEventListener("click", () => {
                sidebar.classList.toggle("collapsed");
            });
        });
    </script>
</body>
</html>
