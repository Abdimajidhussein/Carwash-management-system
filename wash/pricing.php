<?php
// Start the session
session_start();

// Example: check if the user is logged in (assuming login is handled elsewhere)
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Wash Pricing</title>
    <link rel="stylesheet" href="css/pricestyle.css"> <!-- Link to external CSS file -->
    <script src="script.js" defer></script> <!-- Optional JS for extra functionality -->
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
                <li><a href="pricing.php">Pricing</a></li>
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
        <section class="pricing">
            <h3><u>Our Pricing</u></h3>
            <p>Here at Car Wash Management, we offer a range of services at affordable prices to keep your vehicle looking its best. Below are the details of our pricing for each service.</p>

            <!-- Pricing Table -->
            <div class="pricing-table">
                <!-- Exterior Wash -->
                <div class="pricing-item">
                    <h4>Exterior Wash</h4>
                    <p>A hand wash for the exterior of your car, including rim cleaning and tire shine.</p>
                    <p><strong>Price: $20</strong></p>
                    <a href="book_service.php" class="btn">Book Now</a>
                </div>

                <!-- Interior Cleaning -->
                <div class="pricing-item">
                    <h4>Interior Cleaning</h4>
                    <p>Vacuuming, cleaning of seats, dashboard, windows, and deodorizing for a fresh interior.</p>
                    <p><strong>Price: $35</strong></p>
                    <a href="book_service.php" class="btn">Book Now</a>
                </div>

                <!-- Full Detailing -->
                <div class="pricing-item">
                    <h4>Full Detailing</h4>
                    <p>Complete interior and exterior cleaning, including waxing, polishing, and carpet shampoo.</p>
                    <p><strong>Price: $100</strong></p>
                    <a href="book_service.php" class="btn">Book Now</a>
                </div>

                <!-- Waxing & Polishing -->
                <div class="pricing-item">
                    <h4>Waxing & Polishing</h4>
                    <p>Restore the shine and protect the paint with a professional waxing and polishing service.</p>
                    <p><strong>Price: $50</strong></p>
                    <a href="book_service.php" class="btn">Book Now</a>
                </div>

                <!-- Engine Cleaning -->
                <div class="pricing-item">
                    <h4>Engine Cleaning</h4>
                    <p>Thorough cleaning of the engine bay, removing dirt and debris for a polished engine.</p>
                    <p><strong>Price: $40</strong></p>
                    <a href="book_service.php" class="btn">Book Now</a>
                </div>

                <!-- Headlight Restoration -->
                <div class="pricing-item">
                    <h4>Headlight Restoration</h4>
                    <p>Restores clarity and brightness to headlights, improving night driving visibility.</p>
                    <p><strong>Price: $30</strong></p>
                    <a href="book_service.php" class="btn">Book Now</a>
                </div>

                <!-- Paint Protection -->
                <div class="pricing-item">
                    <h4>Paint Protection</h4>
                    <p>Advanced protection for your vehicle’s paint, shielding it from contaminants and UV rays.</p>
                    <p><strong>Price: $120</strong></p>
                    <a href="book_service.php" class="btn">Book Now</a>
                </div>

                <!-- Ceramic Coating -->
                <div class="pricing-item">
                    <h4>Ceramic Coating</h4>
                    <p>A high-performance coating that offers long-lasting protection for your car’s paint.</p>
                    <p><strong>Price: $200</strong></p>
                    <a href="book_service.php" class="btn">Book Now</a>
                </div>
            </div>
        </section>
    </main>

     <!-- Customer Testimonials Section -->
     <section class="testimonials">
            <h3><u>What Our Customers Say</u></h3>

            <div class="testimonial-cards">
                <div class="testimonial-card">
                    <p>"Fantastic service! My car has never looked better, and the staff was incredibly friendly and professional."</p>
                    <p><strong>John D.</strong></p>
                </div>
                <div class="testimonial-card">
                    <p>"I love the attention to detail they provide. My car feels brand new after each visit."</p>
                    <p><strong>Sarah P.</strong></p>
                </div>
                <div class="testimonial-card">
                    <p>"Quick and efficient. I was in and out in no time, and my car was spotless!"</p>
                    <p><strong>Mark T.</strong></p>
                </div>
            </div>
        </section>


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
