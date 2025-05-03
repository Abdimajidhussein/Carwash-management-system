<?php
session_start();
include('includes/config.php'); // Include database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$query = "SELECT * FROM users WHERE id='$user_id' LIMIT 1";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    $update_query = "UPDATE users SET username='$username', email='$email', phone='$phone' WHERE id='$user_id'";
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['username'] = $username;
        $message = "Profile updated successfully!";
    } else {
        $error = "Error updating profile: " . mysqli_error($conn);
    }

    // Handle password change
    if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Fetch stored password
        $fetch_password = "SELECT password FROM users WHERE id='$user_id'";
        $res = mysqli_query($conn, $fetch_password);
        $row = mysqli_fetch_assoc($res);

        if (password_verify($current_password, $row['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_pass = "UPDATE users SET password='$hashed_password' WHERE id='$user_id'";
                if (mysqli_query($conn, $update_pass)) {
                    $message .= "<br>Password changed successfully!";
                } else {
                    $error = "Error updating password: " . mysqli_error($conn);
                }
            } else {
                $error = "New passwords do not match.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
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
        h2, h3 {
            text-align: center;
            color: #333;
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
        .message {
            color: green;
            text-align: center;
        }
        .error {
            color: red;
            text-align: center;
        }
        hr {
            margin: 20px 0;
            border: none;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="profile.php">My Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    
    <div class="container">
        <h2>My Profile</h2>
        <?php if (isset($message)) echo "<p class='message'>$message</p>"; ?>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="profile.php" method="POST">
            <label>Username:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            
            <label>Phone:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>

            <hr>

            <h3>Change Password</h3>
            <label>Current Password:</label>
            <input type="password" name="current_password" placeholder="Enter current password">

            <label>New Password:</label>
            <input type="password" name="new_password" placeholder="Enter new password">

            <label>Confirm New Password:</label>
            <input type="password" name="confirm_password" placeholder="Confirm new password">

            <button type="submit">Update Profile</button>
        </form>
    </div>
    
    <footer>
        <p>&copy; 2025 Car Wash Management. All rights reserved.</p>
    </footer>
</body>
</html>
