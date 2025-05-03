<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

include 'includes/config.php';

$success_message = $error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = $_SESSION['admin_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $result = mysqli_query($conn, "SELECT password FROM admins WHERE id = '$admin_id'");
    $row = mysqli_fetch_assoc($result);

    if (!$row || !password_verify($current_password, $row['password'])) {
        $error_message = "Current password is incorrect!";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $error_message = "New password must be at least 6 characters!";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE admins SET password='$hashed_password' WHERE id='$admin_id'");

        if (mysqli_affected_rows($conn) > 0) {
            $success_message = "Password changed successfully!";
        } else {
            $error_message = "Error updating password. Try again!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="css/admin-dashboard.css">
    <style>
        :root {
            --sidebar-width: 220px;
            --sidebar-collapsed-width: 60px;
            --transition-speed: 0.3s;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
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
            transition: 0.3s;
        }

        .sidebar ul li:hover,
        .sidebar ul li.active {
            background-color: #495057;
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
            padding: 50px 20px;
            transition: margin-left var(--transition-speed);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-collapsed-width);
        }

        .container {
            background: white;
            padding: 30px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
        }

        h2 {
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .btn {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn:hover {
            background: #0056b3;
        }

        .message {
            margin-top: 10px;
            font-size: 14px;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        .back-link {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: #007bff;
        }

        .back-link:hover {
            text-decoration: underline;
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
        <div class="container">
            <h2>Change Password</h2>

            <?php if ($success_message): ?>
                <p class="message success"><?php echo $success_message; ?></p>
            <?php elseif ($error_message): ?>
                <p class="message error"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <form method="POST">
                <input type="password" name="current_password" placeholder="Current Password" required>
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                <button type="submit" class="btn">Change Password</button>
            </form>

            <a href="dashboard.php" class="back-link">Back to Dashboard</a>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleBtn');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });
    </script>
</body>
</html>
