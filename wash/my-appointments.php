<?php
session_start();
include('includes/config.php'); // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get logged-in user ID

// Fetch user appointments with vehicle plate number
$query = "SELECT id, service, date, vehicle_plate, status, created_at 
          FROM appointments 
          WHERE user_id = ? 
          ORDER BY 
              CASE 
                  WHEN status = 'Pending' THEN 1 
                  WHEN status = 'Completed' THEN 2 
                  WHEN status = 'Cancelled' THEN 3 
                  ELSE 4 
              END, 
              created_at ASC"; // Sort by creation date (oldest first)

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Link to the main stylesheet -->
    <style>
        /* General Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        /* Header Styles */
header {
    background-color: #003366;
    color: white;
    padding: 20px 0;
}

header .logo h1 {
    text-align: center;
    font-size: 2.5rem;
    letter-spacing: 2px;
}

nav {
    text-align: center;
    margin-top: 10px;
}

nav ul {
    list-style: none;
    padding: 0;
}

nav ul li {
    display: inline-block;
    margin: 0 15px;
}

nav ul li a {
    color: white;
    text-decoration: none;
    font-size: 1rem;
    font-weight: bold;
}

nav ul li a:hover {
    color: #ffcc00;
}


        .container {
            width: 90%;
            max-width: 900px;
            margin: 30px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        /* Styled Table with Vertical Borders */
        .appointment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 2px solid #333; /* Dark border around the entire table */
        }

        .appointment-table th,
        .appointment-table td {
            border: 2px solid #333; /* Vertical and horizontal borders */
            padding: 12px;
            text-align: center;
        }

        .appointment-table th {
            background: #333;
            color: white;
            font-weight: bold;
        }

        .appointment-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        /* Status Badge Styling */
        .status {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
        }

        .pending {
            background: #ffcc00;
            color: #333;
        }

        .completed {
            background: #28a745;
            color: white;
        }

        .cancelled {
            background: #dc3545;
            color: white;
        }

        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 15px 0;
            position: relative;
            bottom: 0;
            width: 100%;
        }

        @media (max-width: 768px) {
            .appointment-table th,
            .appointment-table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>My Appointments</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="services.php">Services</a></li>
              
                <li><a href="promotions.php">Promotions</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>Your Booked Appointments</h2>
        <?php if ($result->num_rows > 0): ?>
            <table class="appointment-table">
                <tr>
                    <th>Service</th>
                    <th>Appointment Date</th>
                    <th>Vehicle Plate Number</th>
                    <th>Status</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['service']); ?></td>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                        <td><?php echo htmlspecialchars($row['vehicle_plate']); ?></td>
                        <td>
    <?php 
        // Check if status is empty, default to "Pending"
        $status = !empty($row['status']) ? $row['status'] : 'Pending'; 
    ?>
    <span class="status <?php echo strtolower($status); ?>">
        <?php echo htmlspecialchars($status); ?>
    </span>
</td>

                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p style="text-align:center;">No appointments booked yet.</p>
        <?php endif; ?>
    </div>

      <!-- Footer Section -->
      <footer>
        <div class="footer-content">

        <nav>
                <ul>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="terms.php">Terms of Service</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </nav>
            <p>&copy; 2025 Car Wash Management System | All Rights Reserved</p>
           
        </div>
    </footer>
</body>
</html>

<?php $stmt->close(); ?>
