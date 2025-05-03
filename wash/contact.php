<?php
session_start();
include('includes/config.php'); // Include database connection file

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // User is logged in, allow message sending
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['message'])) {
            $name = mysqli_real_escape_string($conn, $_POST['name']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $message = mysqli_real_escape_string($conn, $_POST['message']);

            // Insert data using prepared statement
            $query = "INSERT INTO contact_messages (name, email, message, user_id) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $message, $_SESSION['user_id']);

                if (mysqli_stmt_execute($stmt)) {
                    $success = "Your message has been sent successfully!";
                } else {
                    $error = "Error executing query: " . mysqli_error($conn);
                }

                mysqli_stmt_close($stmt);
            } else {
                $error = "SQL Prepare Error: " . mysqli_error($conn);
            }
        } else {
            $error = "All fields are required!";
        }
    }
} else {
    // User is not logged in, redirect to login page
    header("Location: login.php"); // Replace 'login.php' with your actual login page URL
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="css/style.css">

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
        input, textarea {
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
        .message {
            text-align: center;
            padding: 10px;
            font-weight: bold;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 10px 0;
            margin-top: 20px;
        }
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
                <li><a href="register.php">Register</a></li>
                <li><a href="promotions.php">Promotions</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>Get in Touch</h2>

        <?php
        if (isset($success)) {
            echo "<p style='color:green;'>$success</p>";
        } elseif (isset($error)) {
            echo "<p style='color:red;'>$error</p>";
        }
        ?>

        <form action="" method="POST">
            <label>Name:</label>
            <input type="text" name="name" required>

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Message:</label>
            <textarea name="message" rows="5" required></textarea>

            <button type="submit">Send Message</button>
        </form>
    </div>

    <footer>
    <nav>
            <ul>
                <li><a href="privacy.php">Privacy Policy</a></li>
                <li><a href="terms.php">Terms of Service</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
        <p>&copy; 2025 Car Wash Management. All rights reserved.</p>

    </footer>
</body>
</html>