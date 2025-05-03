<?php
// Start the session
session_start();
include 'includes/config.php'; // Include database connection

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Fetch promotions from the database
$query = "SELECT title, description, valid_until FROM promotions ORDER BY valid_until ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Wash Promotions</title>
    <link rel="stylesheet" href="css/promstyle.css"> 
    <script src="script.js" defer></script> 
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
                <li><a href="promotions.php" class="active">Promotions</a></li>
                <li><a href="contact.php">Contact</a></li>
                <?php if ($isLoggedIn): ?>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- Main Content Section -->
    <main>
        <section class="promotions">
            <h3>Current Promotions</h3>
            <p>Take advantage of our exclusive promotions and discounts to get the best value on our car wash services.</p>

            <!-- Promotions List -->
            <div class="promotions-list">
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<div class='promotion-item'>";
                        echo "<h4>" . htmlspecialchars($row['title']) . "</h4>";
                        echo "<p>" . htmlspecialchars($row['description']) . "</p>";
                        echo "<p><strong>Offer Ends: " . htmlspecialchars($row['valid_until']) . "</strong></p>";
                        echo "<a href='book_service.php' class='btn'>Book Now</a>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No promotions available at the moment. Check back later!</p>";
                }
                ?>
            </div>
        </section>
    </main>

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
