<?php
include('includes/config.php'); // Ensure your database connection is established

// Example admin credentials
$username = "admin";
$password = password_hash("admin123", PASSWORD_DEFAULT); // Hash the password

// Insert query
$query = "INSERT INTO admins (username, password) VALUES (?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $username, $password);

if ($stmt->execute()) {
    echo "Admin user added successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
