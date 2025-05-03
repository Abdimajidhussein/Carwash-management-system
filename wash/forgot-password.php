<?php
session_start();
include('includes/config.php'); // Database connection

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = trim($_POST['phone']);

    if (!empty($phone)) {
        $query = "SELECT id FROM users WHERE phone = ?";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $phone);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                // Generate a unique reset code (e.g., 6 digits)
                $resetCode = rand(100000, 999999);
                $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes')); // Shorter expiry for SMS codes

                // Store reset code in database
                $updateQuery = "UPDATE users SET reset_token = ?, reset_expiry = ? WHERE phone_number = ?";
                $updateStmt = mysqli_prepare($conn, $updateQuery);
                mysqli_stmt_bind_param($updateStmt, "sss", password_hash($resetCode, PASSWORD_DEFAULT), $expiry, $phoneNumber); // Hash the code for security
                mysqli_stmt_execute($updateStmt);

                // --- SEND SMS CODE HERE ---
                // This is where you would integrate with an SMS gateway.
                // Example (replace with your actual SMS sending logic):
                $message = "Your password reset code is: $resetCode";
                // $smsResult = sendSMS($phoneNumber, $message);
                // if ($smsResult['success']) {
                //     $success = "A password reset code has been sent to your phone number.";
                // } else {
                //     $error = "Failed to send SMS. Please try again later.";
                // }
                $success = "A password reset code has been sent to your phone number (implementation for SMS sending needed)."; // Placeholder message

            } else {
                $error = "No account found with that phone number.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Database error.";
        }
    } else {
        $error = "Please enter your phone number.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/loginstyle.css">
</head>
<body>
    <div class="container">
        <h2>Reset Your Password</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (!empty($success)) echo "<p class='success'>$success</p>"; ?>

        <form action="forgot-password.php" method="POST">
            <label>Phone Number:</label>
            <input type="tel" name="phone_number" required>
            <button type="submit">Send Reset Code</button>
        </form>
    </div>
</body>
</html>