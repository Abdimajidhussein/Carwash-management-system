<?php
session_start();
include 'includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Add Service
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_service'])) {
    $service_name = mysqli_real_escape_string($conn, $_POST['service_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $image = $_FILES['image']['name'];
        $targetFile = $uploadDir . basename($image);

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $query = "INSERT INTO services (service_name, description, price, image)
                      VALUES ('$service_name', '$description', '$price', '$targetFile')";
            if (mysqli_query($conn, $query)) {
                echo "<script>alert('Service added successfully!'); window.location='manage-services.php';</script>";
            } else {
                echo "<script>alert('Database error: " . mysqli_error($conn) . "');</script>";
            }
        } else {
            echo "<script>alert('Error uploading file.');</script>";
        }
    } else {
        echo "<script>alert('No file uploaded or an error occurred.');</script>";
    }
}

// Delete Service
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM services WHERE id=$id");
    header("Location: manage-services.php");
    exit();
}

// Edit Service
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_query = mysqli_query($conn, "SELECT * FROM services WHERE id=$edit_id");
    $edit_data = mysqli_fetch_assoc($edit_query);
}

// Update Service
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_service'])) {
    $id = intval($_POST['service_id']);
    $service_name = mysqli_real_escape_string($conn, $_POST['service_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);

    $update_query = "UPDATE services SET service_name='$service_name', description='$description', price='$price'";

    if (!empty($_FILES['image']['name'])) {
        $uploadDir = "uploads/";
        $image = $_FILES['image']['name'];
        $targetFile = $uploadDir . basename($image);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $update_query .= ", image='$targetFile'";
        } else {
            echo "<script>alert('Error uploading new image.');</script>";
        }
    }

    $update_query .= " WHERE id=$id";
    if (mysqli_query($conn, $update_query)) {
        echo "<script>alert('Service updated successfully!'); window.location='manage-services.php';</script>";
    } else {
        echo "<script>alert('Database error: " . mysqli_error($conn) . "');</script>";
    }
}

$services = mysqli_query($conn, "SELECT * FROM services");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Services</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --sidebar-width: 240px;
            --sidebar-collapsed-width: 60px;
            --transition-speed: 0.3s;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background-color: #343a40;
            color: white;
            transition: width var(--transition-speed);
            position: fixed;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar h2 {
            text-align: center;
            padding: 15px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            padding: 10px 15px;
        }

        .sidebar ul li:hover, .sidebar ul li.active {
            background-color: #495057;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .sidebar ul li a span {
            margin-left: 10px;
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

        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left var(--transition-speed);
            padding: 20px;
            width: 100%;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin: 20px auto;
            width: 50%;
        }

        input, textarea, button {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
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

        img {
            max-width: 100px;
            border-radius: 5px;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .action-buttons a {
            text-decoration: none;
            padding: 6px 10px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 4px;
            color: white;
        }

        .edit { background-color: blue; }
        .edit:hover { background-color: darkblue; }

        .delete { background-color: red; }
        .delete:hover { background-color: darkred; }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const toggleBtn = document.querySelector(".toggle-btn");
            const sidebar = document.querySelector(".sidebar");
            const content = document.querySelector(".main-content");

            toggleBtn.addEventListener("click", () => {
                sidebar.classList.toggle("collapsed");
                content.classList.toggle("expanded");
            });
        });
    </script>
</head>
<body>

<div class="sidebar">
    <button class="toggle-btn">☰</button>
    <h2>Admin Panel</h2>
    <ul>
        <li><a href="dashboard.php">📊 <span>Dashboard</span></a></li>
        <li><a href="manage-users.php">👥 <span>Manage Users</span></a></li>
        <li><a href="manage-appointments.php">📅 <span>Manage Bookings</span></a></li>
        <li><a href="manage-promotions.php">🎁 <span>Manage Promotions</span></a></li>
        <li class="active"><a href="manage-services.php">🧼 <span>Manage Services</span></a></li>
        <li><a href="manage-feedback.php"><i>📩</i><span>Manage Feedback</span></a></li>
        <li><a href="admin_reports.php">📈 <span>Reports</span></a></li>
        <li><a href="logout.php">🚪 <span>Logout</span></a></li>
    </ul>
</div>

<div class="main-content">
    <div class="container">
        <h2>Manage Services</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="service_id" value="<?php echo isset($edit_data['id']) ? $edit_data['id'] : ''; ?>">
            <input type="text" name="service_name" placeholder="Service Name" required value="<?php echo $edit_data['service_name'] ?? ''; ?>">
            <textarea name="description" placeholder="Description" required><?php echo $edit_data['description'] ?? ''; ?></textarea>
            <input type="number" name="price" step="0.01" placeholder="Price" required value="<?php echo $edit_data['price'] ?? ''; ?>">
            <input type="file" name="image" accept="image/*">
            <?php if (!empty($edit_data['image'])): ?>
                <p>Current Image: <img src="<?php echo $edit_data['image']; ?>" width="50"></p>
            <?php endif; ?>
            <button type="submit" name="<?php echo $edit_data ? 'update_service' : 'add_service'; ?>">
                <?php echo $edit_data ? 'Update Service' : 'Add Service'; ?>
            </button>
        </form>

        <table>
            <tr>
                <th>Image</th>
                <th>Service Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Action</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($services)): ?>
                <tr>
                    <td><img src="<?php echo $row['image']; ?>" width="50"></td>
                    <td><?php echo $row['service_name']; ?></td>
                    <td><?php echo $row['description']; ?></td>
                    <td>$<?php echo number_format($row['price'], 2); ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="manage-services.php?edit=<?php echo $row['id']; ?>" class="edit">Edit</a>
                            <a href="manage-services.php?delete=<?php echo $row['id']; ?>" class="delete" onclick="return confirm('Are you sure?')">Delete</a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>

