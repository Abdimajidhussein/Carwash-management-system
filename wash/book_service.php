<?php
session_start();
include('includes/config.php'); // Include database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details from the database
$userQuery = "SELECT name, phone FROM users WHERE id = '$user_id'";
$userResult = mysqli_query($conn, $userQuery);

if ($userRow = mysqli_fetch_assoc($userResult)) {
    $name = $userRow['name'];
    $phone = $userRow['phone'];
} else {
    die("Error: User details not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Check if required fields are provided
    if (!isset($_POST['vehicle_plate'], $_POST['service'], $_POST['date'], $_POST['time'])) {
        die("Error: Missing form data.");
    }

    // Sanitize inputs
    $vehicle_plate = mysqli_real_escape_string($conn, $_POST['vehicle_plate']);
    $service = mysqli_real_escape_string($conn, $_POST['service']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $time = mysqli_real_escape_string($conn, $_POST['time']);

    // Check if the selected time slot is already booked
    $checkQuery = "SELECT * FROM appointments WHERE date='$date' AND time='$time'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        $error = "This time slot is already booked. Please choose another time.";
    } else {
        // Insert booking details into the database
        $query = "INSERT INTO appointments (user_id, vehicle_plate, service, date, time, status)
        VALUES ('$user_id', '$vehicle_plate', '$service', '$date', '$time', 'Pending')";

        if (!mysqli_query($conn, $query)) {
            die("Error inserting record: " . mysqli_error($conn)); // Show SQL error
        } else {
            $success = "Your appointment has been booked successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Service</title>
<style>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 50%;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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


        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
        }
        input, select {
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            margin-top: 15px;
            padding: 10px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #218838;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
       /* Footer Styles */
        footer {
            background-color: #333;
            color: white;
            padding: 20px 0;
            text-align: center;
        }
        footer .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }
        footer nav ul {
            list-style: none;
            padding: 0;
        }
        footer nav ul li {
            display: inline-block;
            margin: 0 15px;
        }
        footer nav ul li a {
            color: white;
            text-decoration: none;
        }
        footer nav ul li a:hover {
            color: #ffcc00;
        }
    </style>

</style>
</head>
<body>

    <header>
        <div class="logo">
            <h1>Car Wash Management</h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a href="pricing.php">Pricing</a></li>
                <li><a href="promotions.php">Promotions</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

        <form action="book_service.php" method="POST">
            <label>Name:</label>
            <input type="text" value="<?php echo htmlspecialchars($name); ?>" disabled>

            <label>Phone:</label>
            <input type="text" value="<?php echo htmlspecialchars($phone); ?>" disabled>

            <label>Vehicle Plate Number:</label>
            <input type="text" name="vehicle_plate" required>

            <label>Service:</label>
            <select name="service" required>
                <option value="Exterior Wash">Exterior Wash</option>
                <option value="Interior Cleaning">Interior Cleaning</option>
                <option value="Full Service">Full Service</option>
                <option value="Waxing & Polishing">Waxing & Polishing</option>
                <option value="Engine Cleaning">Engine Cleaning</option>
                <option value="Headlight Restoration">Headlight Restoration </option>
                <option value="Paint Protection "> Paint Protection</option>
                <option value="Ceramic Coating ">Ceramic Coating </option>
            </select>

            <label>Date:</label>
            <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>">

            <label>Time:</label>
            <input type="time" name="time" required>

            <button type="submit">Book Now</button>
        </form>
    </div>

    <footer>
        <p>&copy; 2025 Car Wash Management. All rights reserved.</p>
        <nav>
            <ul>
                <li><a href="privacy.php">Privacy Policy</a></li>
                <li><a href="terms.php">Terms of Service</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
    </footer>

</body>
</html>