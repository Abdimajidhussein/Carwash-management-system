<?php
include 'includes/config.php';

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    switch ($action) {
        case 'delete':
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $id = (int)$_GET['id'];
                $sql = "DELETE FROM feedback WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);

                if ($stmt->execute()) {
                    echo "<script>alert('Feedback deleted successfully.');</script>";
                } else {
                    echo "<script>alert('Error deleting feedback: " . $stmt->error . "');</script>";
                }
                $stmt->close();
            } else {
                echo "<script>alert('Invalid feedback ID.');</script>";
            }
            break;
    }
}

// Get feedback entries
$sql = "SELECT id, name, email, message, created_at FROM contact_messages ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Feedback</title>
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 60px;
            --transition-speed: 0.3s ease;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            min-height: 100vh;
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
            transition: width var(--transition-speed);
            overflow: hidden;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            transition: opacity var(--transition-speed);
        }

        .sidebar.collapsed h2 {
            opacity: 0;
        }

        .sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0;
    margin-top: 10px;
}


        .sidebar ul li {
            padding: 12px 20px;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .sidebar ul li a i {
            margin-right: 10px;
        }

        .sidebar ul li a span {
            transition: opacity var(--transition-speed);
        }

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
            z-index: 1001;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left var(--transition-speed), width var(--transition-speed);
            width: calc(100% - var(--sidebar-width));
            padding: 20px;
            overflow-x: auto;
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-collapsed-width);
            width: calc(100% - var(--sidebar-collapsed-width));
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f0f0f0;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        a {
            color: #0078d7;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <button class="toggle-btn" id="toggleBtn">☰</button>
        <h2>Admin Panel</h2>
        <ul class="bottom-menu">
            <li class="active"><a href="dashboard.php"><i>📊</i><span>Dashboard</span></a></li>
            <li><a href="manage-users.php"><i>👥</i><span>Manage Users</span></a></li>
            <li><a href="manage-appointments.php"><i>📅</i><span>Manage Bookings</span></a></li>
            <li><a href="manage-promotions.php"><i>🎁</i><span>Manage Promotions</span></a></li>
            <li><a href="manage-services.php"><i>🧼</i><span>Manage Services</span></a></li>
            <li><a href="manage-feedback.php"><i>📩</i><span>Manage Feedback</span></a></li>
            <li><a href="admin_reports.php"><i>📈</i><span>Reports</span></a></li>
            <li><a href="logout.php"><i>🚪</i><span>Logout</span></a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <h2>Manage Feedback</h2>
        <table border='1' cellpadding='10' id='feedbackTable'>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Date/Time</th><th>Action</th></tr>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['name'] . "</td>";
                    echo "<td>" . $row['email'] . "</td>";
                    echo "<td><a href='#' onclick='showMessage(\"" . str_replace(["\r", "\n"], '', addslashes($row['message'])) . "\")'>View Message</a></td>";
                    echo "<td>" . $row['created_at'] . "</td>";
                    echo "<td><a href='manage-feedback.php?action=delete&id=" . $row['id'] . "' onclick='return confirm(\"Are you sure you want to delete this feedback?\")'>Delete</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No feedback available.</td></tr>";
            }
            ?>
        </table>
    </div>

    <!-- Scripts -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const toggleBtn = document.getElementById("toggleBtn");
            const sidebar = document.getElementById("sidebar");

            toggleBtn.addEventListener("click", () => {
                sidebar.classList.toggle("collapsed");
            });
        });

        function showMessage(message) {
            alert(message);
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>
