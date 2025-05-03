<?php
session_start();
include('includes/config.php'); // Database connection

$error = "";
$success = "";
$edit_mode = false;
$edit_id = "";
$title = "";
$description = "";
$discount = "";
$valid_until = "";

// Handle form submission for adding or updating a promotion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['title']) && !empty($_POST['description']) && !empty($_POST['discount']) && !empty($_POST['valid_until'])) {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $discount = trim($_POST['discount']);
        $valid_until = trim($_POST['valid_until']);

        if (!empty($_POST['edit_id'])) { // Updating an existing promotion
            $edit_id = $_POST['edit_id'];
            $query = "UPDATE promotions SET title=?, description=?, discount=?, valid_until=? WHERE id=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssi", $title, $description, $discount, $valid_until, $edit_id);
            if ($stmt->execute()) {
                $success = "Promotion updated successfully!";
            } else {
                $error = "Error: " . $stmt->error;
            }
            $stmt->close();
        } else { // Adding a new promotion
            $query = "INSERT INTO promotions (title, description, discount, valid_until) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $title, $description, $discount, $valid_until);
            if ($stmt->execute()) {
                $success = "Promotion added successfully!";
            } else {
                $error = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $error = "Please fill in all fields.";
    }
}

// Handle editing a promotion (fetching details for editing)
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $query = "SELECT * FROM promotions WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $title = $row['title'];
        $description = $row['description'];
        $discount = $row['discount'];
        $valid_until = $row['valid_until'];
        $edit_mode = true;
    }
    $stmt->close();
}

// Handle deleting a promotion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $query = "DELETE FROM promotions WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success = "Promotion deleted successfully!";
    } else {
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch all promotions for display
$query_select = "SELECT * FROM promotions";
$result_select = $conn->query($query_select);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Promotions - Admin Panel</title>
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

        /* Form Styles */
        form {
            display: flex;
            flex-direction: column;
            max-width: 500px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            font-weight: bold;
            margin-top: 10px;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box; /* Ensure padding doesn't affect width */
        }

        button[type="submit"] {
            margin-top: 15px;
            background: #ff9800;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button[type="submit"]:hover {
            background: #e68900;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 5px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #333;
            color: white;
            text-align: center;
        }

        td {
            text-align: center;
        }

        tr:nth-child(even) {
            background: #f4f4f4;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: center; /* Center the action buttons */
        }

        .action-buttons a {
            text-decoration: none;
            padding: 8px 12px;
            font-size: 14px;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
            transition: all 0.3s ease-in-out;
        }

        .edit {
            background: blue;
            color: white;
        }

        .edit:hover {
            background: darkblue;
            transform: scale(1.05);
        }

        .delete {
            background: red;
            color: white;
        }

        .delete:hover {
            background: darkred;
            transform: scale(1.05);
        }

        /* Success and Error Messages */
        .success {
            color: green;
            text-align: center;
            font-weight: bold;
            margin-top: 10px;
        }

        .error {
            color: red;
            text-align: center;
            font-weight: bold;
            margin-top: 10px;
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
                sidebar.style.height = `${mainContent.offsetHeight}px`;
            }

            // Initial adjustment and on resize
            adjustSidebarHeight();
            window.addEventListener('resize', adjustSidebarHeight);

            // You might need to call adjustSidebarHeight() after dynamic content loads
        });
    </script>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <button class="toggle-btn" id="toggleBtn">☰</button>
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php"><i style="font-style: normal;">📊</i><span>Dashboard</span></a></li>
            <li><a href="manage-users.php"><i style="font-style: normal;">👥</i><span>Manage Users</span></a></li>
            <li><a href="manage-appointments.php"><i style="font-style: normal;">📅</i><span>Manage Bookings</span></a></li>
            <li class="active"><a href="manage-promotions.php"><i style="font-style: normal;">🎁</i><span>Manage Promotions</span></a></li>
            <li><a href="manage-services.php"><i style="font-style: normal;">🧼</i><span>Manage Services</span></a></li>
            <li><a href="manage-feedback.php"><i>📩</i><span>Manage Feedback</span></a></li>
            <li><a href="admin_reports.php"><i style="font-style: normal;">📈</i><span>Reports</span></a></li>
            <li><a href="logout.php"><i style="font-style: normal;">🚪</i><span>Logout</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="container">
            <h2>Manage Promotions</h2>
            <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
            <?php if (!empty($success)) echo "<p class='success'>$success</p>"; ?>

            <form action="manage-promotions.php" method="POST">
                <input type="hidden" name="edit_id" value="<?php echo $edit_mode ? $edit_id : ''; ?>">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>

                <label for="description">Description:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($description); ?></textarea>

                <label for="discount">Discount (%):</label>
                <input type="number" id="discount" name="discount" value="<?php echo htmlspecialchars($discount); ?>" required>

                <label for="valid_until">Valid Until:</label>
                <input type="date" id="valid_until" name="valid_until" value="<?php echo htmlspecialchars($valid_until); ?>" required>

                <button type="submit" name="add_promotion"> <?php echo $edit_mode ? "Update Promotion" : "Add Promotion"; ?> </button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Discount</th>
                        <th>Valid Until</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_select->num_rows > 0) {
                        while ($row = $result_select->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['title']; ?></td>
                                <td><?php echo $row['description']; ?></td>
                                <td><?php echo $row['discount']; ?>%</td>
                                <td><?php echo $row['valid_until']; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="manage-promotions.php?edit=<?php echo $row['id']; ?>" class="edit">Edit</a>
                                        <a href="manage-promotions.php?delete=<?php echo $row['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this promotion?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='6'>No promotions found.</td></tr>";
                    } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>