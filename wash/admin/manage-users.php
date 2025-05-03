<?php
session_start();
include('includes/config.php'); // Database connection

$error = "";
$success = "";

// Handle form submission for adding a user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['email']) && !empty($_POST['name']) && !empty($_POST['phone'])) {
        $username = trim($_POST['username']);
        $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        $email = trim($_POST['email']);
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);

        $query = "INSERT INTO users (username, password, email, name, phone) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $username, $password, $email, $name, $phone);

        if ($stmt->execute()) {
            $success = "User added successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Please fill in all fields.";
    }
}

// Handle deletion of user
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM users WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $delete_id);

    if ($delete_stmt->execute()) {
        $success = "User deleted successfully!";
    } else {
        $error = "Error deleting user: " . $delete_stmt->error;
    }
    $delete_stmt->close();
}

// Fetch users for display
$query = "SELECT * FROM users";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
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
            font-family: sans-serif;
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
            overflow-y: auto; /* Enable vertical scrolling if content overflows */
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
            padding: 5px;
            z-index: 1001; /* Ensure it's above the sidebar */
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

        .form-container {
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-container label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        .form-container input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-container button {
            width: 100%;
            padding: 10px;
            margin-top: 15px;
            background: #ff9800;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        .form-container button:hover {
            background: #e68900;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        .success {
            color: green;
            margin-bottom: 10px;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #2c3e50;
            color: white;
            text-align: center;
        }

        td {
            text-align: center;
        }

        tr:nth-child(even) {
            background: #f4f4f4;
        }

        .btn {
            display: inline-block;
            padding: 8px 15px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
            margin: 5px;
        }

        .btn.delete {
            background: red;
        }

        .btn.delete:hover {
            background: darkred;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const toggleBtn = document.getElementById("toggleBtn");
            const sidebar = document.getElementById("sidebar");
            const mainContent = document.querySelector(".main-content");

            toggleBtn.addEventListener("click", function () {
                sidebar.classList.toggle("collapsed");
                mainContent.classList.toggle("expanded");
            });

            // Function to adjust sidebar height based on main content height
            function adjustSidebarHeight() {
                sidebar.style.height = `${mainContent.offsetHeight}px`;
            }

            // Initial adjustment and listen for changes (you might need a more robust way to detect content changes)
            adjustSidebarHeight();
            window.addEventListener('resize', adjustSidebarHeight);
            // You might need to call adjustSidebarHeight() after dynamic content loading as well
        });
    </script>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <button class="toggle-btn" id="toggleBtn">☰</button>
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php"><i style="font-style: normal;">📊</i><span>Dashboard</span></a></li>
            <li class="active"><a href="manage-users.php"><i style="font-style: normal;">👥</i><span>Manage Users</span></a></li>
            <li><a href="manage-appointments.php"><i style="font-style: normal;">📅</i><span>Manage Bookings</span></a></li>
            <li><a href="manage-promotions.php"><i style="font-style: normal;">🎁</i><span>Manage Promotions</span></a></li>
            <li><a href="manage-services.php"><i style="font-style: normal;">🧼</i><span>Manage Services</span></a></li>
            <li><a href="manage-feedback.php"><i>📩</i><span>Manage Feedback</span></a></li>
            <li><a href="admin_reports.php"><i style="font-style: normal;">📈</i><span>Reports</span></a></li>
            <li><a href="logout.php"><i style="font-style: normal;">🚪</i><span>Logout</span></a></li>
        </ul>
    </div>
    <div class="main-content">
        <h2>Manage Users</h2>

        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (!empty($success)) echo "<p class='success'>$success</p>"; ?>

        <div class="form-container">
            <h3>Add New User</h3>
            <form action="manage-users.php" method="POST">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>

                <label for="phone">Phone Number:</label>
                <input type="text" id="phone" name="phone" required>

                <button type="submit">Add User</button>
            </form>
        </div>

        <div class="table-container">
            <h3>User List</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['id']}</td>
                                    <td>{$row['username']}</td>
                                    <td>{$row['email']}</td>
                                    <td>{$row['name']}</td>
                                    <td>{$row['phone']}</td>
                                    <td>
                                        <a href='manage-users.php?delete_id={$row['id']}' class='btn delete' onclick=\"return confirm('Are you sure you want to delete this user?')\">Delete</a>
                                    </td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No users found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>