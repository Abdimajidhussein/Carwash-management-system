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
    <meta name="author" content="Abdul" />
    <meta name=description content="Car wash in Mombasa Kenya" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Car Wash Management System</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Link to external CSS file -->
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
                <li><a href="promotions.php">Promotions</a></li>
              
              

               
                <?php if ($isLoggedIn): ?>
                    <li><a href="my-appointments.php">My Bookings</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="admin/login.php">Admin</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- Main Content Section -->
    <main>
        <!-- Welcome Section -->
        <section class="hero">
            <div class="hero-content">
                <h2>Keep Your Car Shining, Inside and Out</h2>
                <p>Experience professional car cleaning services with top-quality products and equipment.</p>
                <a href="book_service.php" class="btn">Book a Service</a>
            </div>
        </section>

        <!-- Service Overview Section -->
        <section class="services">
            <h3><u>Our Services</u></h3>
            <p>We offer a variety of car wash and detailing services, from exterior washes to interior cleaning and waxing. Our services are designed to keep your car looking new and well-maintained.</p>
            
            <div class="service-cards">
                <div class="service-card">
                    <img src="images/exteriorwash.jpeg" alt="Exterior Wash">
                    <h4>Exterior Wash</h4>
                    <p>Clean and shine your car's exterior with our hand wash, waxing, and detailing services.</p>
                    <a href="services.php#exterior-wash" class="btn">Learn More</a>
                </div>
                <div class="service-card">
                    <img src="images/interior.jpeg" alt="Interior Cleaning">
                    <h4>Interior Cleaning</h4>
                    <p>We thoroughly clean your car’s interior, including seats, floor mats, and dashboard.</p>
                    <a href="services.php#interior-cleaning" class="btn">Learn More</a>
                </div>
                <div class="service-card">
                    <img src="images/waxing.jpeg" alt="Waxing and Polishing">
                    <h4>Waxing & Polishing</h4>
                    <p>Protect your car’s paint with high-quality waxing and polishing services for a long-lasting shine.</p>
                    <a href="services.php#waxing-polishing" class="btn">Learn More</a>
                </div>
            </div>
        </section>

        <!-- Featured Promotions Section -->
        <section class="promotions">
            <h3><u>Special Offers & Promotions</u></h3>
            <p>Take advantage of our limited-time promotions to save on your next car wash.</p>

            <div class="promotion-cards">
                <div class="promotion-card">
                    <h4>Winter Special</h4>
                    <p>Get 15% off on all exterior washes to protect your car from the harsh winter conditions.</p>
                    <a href="promotions.php" class="btn">See More Offers</a>
                </div>
                <div class="promotion-card">
                    <h4>Ramadan Offers</h4>
                    <p>Get free oil change when you pay for both exterior and interior wash.</p>
                    <a href="promotions.php" class="btn">See More Offers</a>
                </div>
            </div>
        </section>
                


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

        <!-- Call to Action Section -->
        <section class="cta">
            <h2>Ready to Get Your Car Cleaned?</h2>
            <p>Whether you need a quick exterior wash or a full-service detail, we’re here to help. Book a service today!</p>
            <a href="book_service.php" class="btn">Book Your Service</a>
        </section>
    </main>

    <!-- Footer Section -->
    <footer>
        <div class="footer-content">

        <nav>
                <ul>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="admin/login.php">Admin</a></li>
                </ul>
            </nav>
            <p>&copy; 2025 Car Wash Management System | All Rights Reserved</p>
           
        </div>
    </footer>

</body>
</html>
