<?php
session_start();
include('includes/config.php'); // Include database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash password for security

    $query = "INSERT INTO users (name, email, phone, username, password) VALUES ('$name', '$email', '$phone', '$username', '$password')";
    
    if (mysqli_query($conn, $query)) {
        $success = "Registration successful! You can now log in.";
        // Redirect to dashboard
        header("Location: login.php");
        exit();
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
        input {
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
    


 <!-- Header Section -->
 <header>
        <div class="logo">
            <h1>Car Wash Management</h1>
        </div>

        <!-- Navigation Menu -->
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a href="promotions.php">Promotions</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="login.php">Login</a></li>
               
        </nav>
    </header>




    <div class="container">
        <h2>Create an Account</h2>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        
        <form action="register.php" method="POST">
            <label>Name:</label>
            <input type="text" name="name" required>
            
            <label>Email:</label>
            <input type="email" name="email" required>
            
            <label>Phone:</label>
            <input type="text" name="phone" required>
            
            <label>Username:</label>
            <input type="text" name="username" required>
            
            <label>Password:</label>
            <input type="password" name="password" required>
            
            <button type="submit">Register</button>
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
