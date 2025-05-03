<?php
// Start the session
session_start();
include 'includes/config.php'; // Include database connection

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Fetch services from the database
$query = "SELECT * FROM services ORDER BY id ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Wash Services</title>
    <link rel="stylesheet" href="css/styleser.css">
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
                <li><a href="promotions.php">Promotions</a></li>
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
        <section class="services">
            <h3><u>Our Services</u></h3>
            <p>We offer a range of high-quality car wash and detailing services to keep your vehicle in top condition.</p>

            <!-- Service Listings -->
            <div class="service-cards">
            <?php
while ($row = mysqli_fetch_assoc($result)) {
    $imagePath = !empty($row['image']) ? $row['image'] : "images/default.jpg"; // Use default if no image
    ?>

    <div class="service-card">
        <figure>
            <img src="<?= htmlspecialchars($imagePath); ?>" alt="<?= htmlspecialchars($row['service_name']); ?>" onerror="this.onerror=null; this.src='images/default.jpg';">
            <figcaption><?= htmlspecialchars($row['service_name']); ?></figcaption>
        </figure>
        <p><?= htmlspecialchars($row['description']); ?></p>
        <p><strong>Price: $<?= htmlspecialchars($row['price']); ?></strong></p>
        <a href="book_service.php" class="btn">Book Now</a>
    </div>

    <?php
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
