<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('includes/config.php'); // Database connection file

// Check if database connection exists
if (!isset($conn) || !$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$error = ""; // Initialize error message

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        if (!empty($username) && !empty($password)) {
            // Use prepared statements to prevent SQL injection
            $query = "SELECT id, username, password FROM users WHERE username = ?";
            $stmt = mysqli_prepare($conn, $query);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if ($result && mysqli_num_rows($result) > 0) {
                    $user = mysqli_fetch_assoc($result);

                    // Verify password
                    if (password_verify($password, $user['password'])) {
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];

                        // Redirect to index
                        header("Location: index.php");
                        exit();
                    } else {
                        $error = "Incorrect username or password.";
                    }
                } else {
                    $error = "Incorrect username or password.";
                }
                mysqli_stmt_close($stmt);
            } else {
                $error = "Query error: " . mysqli_error($conn);
            }
        } else {
            $error = "Please fill in all fields.";
        }
    } else {
        $error = "Invalid form submission.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
    <link rel="stylesheet" href="css/loginstyle.css">
    
</head>
<body>
    <header>
        <h1>Car wash management system</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a href="register.php">Register</a></li>
                <li><a href="promotions.php">Promotions</a></li>
               
                
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>Login to Your Account</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        
        <form action="login.php" method="POST">
    <label>Username:</label>
    <input type="text" name="username" required>
    
    <label>Password:</label>
    <input type="password" name="password" required>
    
    <button type="submit">Login</button>
    
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

