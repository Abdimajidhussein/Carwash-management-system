<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Ensure this file contains the necessary styles -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 60%;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: white;
        }
        h2 {
            color:#33
        }
        p {
            color: #666;
            line-height: 1.6;
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

        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 15px 0;
            position: relative;
            bottom: 0;
            width: 100%;
        }
        nav ul {
            list-style: none;
            padding: 0;
        }
        nav ul li {
            display: inline;
            margin: 0 15px;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <header>
        <h1>Car Wash Management</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="services.php">Services</a></li>
               
                <li><a href="promotions.php">Promotions</a></li>
                <li><a href="contact.php">Contact</a></li>
               
            </ul>
        </nav>
    </header>
    <div class="container">
        <h2>Privacy Policy</h2>
        <h2>Introduction</h2>
        <p>Your privacy is important to us. This policy explains how we collect, use, and protect your information.</p>
        
        <h2>Information We Collect</h2>
        <p>We collect personal information such as name, contact details, and service preferences when you use our website.</p>
        
        <h2>How We Use Your Information</h2>
        <p>We use your data to process appointments, improve our services, and communicate with you.</p>
        
        <h2>Data Protection</h2>
        <p>We implement security measures to protect your personal data from unauthorized access.</p>
        
        <h2>Third-Party Disclosure</h2>
        <p>We do not share your personal data with third parties without your consent, except where required by law.</p>
        
        <h2>Contact Us</h2>
        <p>If you have any questions about this privacy policy, please <a href="contact.php">contact us</a>.</p>
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
