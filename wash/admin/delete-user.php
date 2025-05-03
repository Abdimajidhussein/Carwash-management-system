<?php
session_start();
include('includes/config.php'); // Database connection

// Check if user ID is provided
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    
    // Start a transaction to ensure data integrity
    $conn->begin_transaction();
    
    try {
        // Delete associated records (adjust queries as needed)
        $conn->query("DELETE FROM appointments WHERE user_id = $user_id");
        $conn->query("DELETE FROM testimonials WHERE user_id = $user_id");
        
        // Delete user from users table
        $deleteUser = $conn->prepare("DELETE FROM users WHERE id = ?");
        $deleteUser->bind_param("i", $user_id);
        
        if ($deleteUser->execute()) {
            $conn->commit(); // Commit transaction
            $_SESSION['success_msg'] = "User deleted successfully!";
        } else {
            throw new Exception("Error deleting user.");
        }
    } catch (Exception $e) {
        $conn->rollback(); // Rollback if there's an error
        $_SESSION['error_msg'] = "Failed to delete user: " . $e->getMessage();
    }
    
    // Redirect back to manage users page
    header("Location: manage-users.php");
    exit();
} else {
    $_SESSION['error_msg'] = "Invalid request.";
    header("Location: manage-users.php");
    exit();
}
?>
