<?php
session_start();
include 'includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage-appointments.php");
    exit();
}

// Prevent SQL injection by using prepared statements
$id = intval($_GET['id']);

// Fetch appointment details using prepared statement
$query = "SELECT a.id, a.user_id, a.service, u.name AS user_name, s.price
          FROM appointments a
          JOIN users u ON a.user_id = u.id
          JOIN services s ON a.service = s.service_name  /* Join to get the price */
          WHERE a.id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo "Invalid appointment ID.";
    exit();
}

$appointment = mysqli_fetch_assoc($result);
$name = $appointment['user_name'];
$service = $appointment['service'];
$price = $appointment['price']; // Get the price from the query
$appointment_id = $appointment['id'];
$user_id = $appointment['user_id'];  //added


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $amount_paid = floatval($_POST['amount_paid']);

    // Validate the amount paid
    if ($amount_paid < $price) {
        echo "<p style='color:red; text-align:center;'>Error: Amount paid is less than the service price.</p>";
        exit();
    }

    // Update using prepared statement
    $updateQuery = "UPDATE appointments
                    SET status='Completed', payment_method=?, amount_paid=?, payment_date=NOW()
                    WHERE id=?";

    $updateStmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($updateStmt, "sdi", $payment_method, $amount_paid, $id);

    if (mysqli_stmt_execute($updateStmt)) {
        $date = date("Y-m-d H:i:s");

        // Generate receipt HTML directly for display
        $receiptHTML = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <title>Car Wash Payment Receipt</title>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: #f5f5f5;
                    padding: 20px;
                    margin: 0;
                }
                .receipt-box {
                    max-width: 600px;
                    margin: auto;
                    background: #fff;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                }
                .receipt-header {
                    text-align: center;
                    border-bottom: 2px solid #28a745;
                    padding-bottom: 15px;
                    margin-bottom: 20px;
                }
                .receipt-logo {
                    font-size: 24px;
                    font-weight: bold;
                    color: #28a745;
                }
                h2 {
                    text-align: center;
                    color: #28a745;
                    margin: 10px 0;
                }
                .receipt-info {
                    margin: 20px 0;
                }
                p {
                    font-size: 16px;
                    line-height: 1.6;
                    margin: 8px 0;
                }
                .receipt-details {
                    border-top: 1px dashed #ccc;
                    border-bottom: 1px dashed #ccc;
                    padding: 15px 0;
                    margin: 15px 0;
                }
                .receipt-footer {
                    text-align: center;
                    margin-top: 30px;
                }
                .thank-you {
                    text-align: center;
                    margin-top: 20px;
                    font-size: 18px;
                    color: #444;
                }
                .receipt-id {
                    text-align: right;
                    font-size: 14px;
                    color: #777;
                }
                .print-btn {
                    display: block;
                    margin: 30px auto 0;
                    background-color: #007bff;
                    color: white;
                    padding: 12px 20px;
                    border: none;
                    border-radius: 5px;
                    font-size: 16px;
                    cursor: pointer;
                    transition: background-color 0.3s;
                }
                .print-btn:hover {
                    background-color: #0056b3;
                }
                @media print {
                    body {
                        background: white;
                        padding: 0;
                        margin: 0;
                    }
                    .receipt-box {
                        box-shadow: none;
                        max-width: 100%;
                    }
                    .print-btn {
                        display: none !important;
                    }
                }
            </style>
        </head>
        <body>
            <div class='receipt-box'>
                <div class='receipt-header'>
                    <div class='receipt-logo'>MAJID CAR WASH </div>
                    <h2>Payment Receipt</h2>
                    <div class='receipt-id'>Receipt #" . $appointment_id . "-" . time() . "</div>
                </div>

                <div class='receipt-info'>
                    <p><strong>Customer Name:</strong> " . htmlspecialchars($name) . "</p>
                    <p><strong>Service:</strong> " . htmlspecialchars($service) . "</p>
                </div>

                <div class='receipt-details'>
                    <p><strong>Amount Paid:</strong> $ " . number_format($amount_paid, 2) . "</p>
                    <p><strong>Payment Method:</strong> " . htmlspecialchars($payment_method) . "</p>
                    <p><strong>Date:</strong> " . htmlspecialchars($date) . "</p>
                </div>

                <div class='receipt-footer'>
                    <p class='thank-you'>Thank you for choosing our services!</p>
                    <p><small>This is an electronically generated receipt.</small></p>
                </div>

                <div class='no-print'>
                    <button class='print-btn' onclick='window.print(); return false;'>🖨️ Print Receipt</button>
                    <button class='print-btn' style='background-color: #28a745; margin-top: 10px;' onclick='window.location.href = \"manage-appointments.php\"; return false;'>Back to Appointments</button>
                </div>
            </div>

            <script>
                // Auto-print prompt when page loads (optional)
                // window.onload = function() {
                //      window.print();
                // };
            </script>
        </body>
        </html>";

        echo $receiptHTML;
        exit();
    } else {
        echo "<p style='color:red; text-align:center;'>Error updating appointment: " . mysqli_error($conn) . "</p>";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Process Payment</title>
    <link rel="stylesheet" href="css/admin-style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: white;
            height: 100vh;
            position: fixed;
            transition: width 0.3s;
        }

        .sidebar.collapsed {
            width: 60px;
        }

        .sidebar h2 {
            text-align: center;
            padding: 20px 0;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            padding: 12px 20px;
            transition: background 0.3s;
        }

        .sidebar ul li:hover {
            background-color: #495057;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
            flex-grow: 1;
            transition: margin-left 0.3s;
        }

        .sidebar.collapsed + .main-content {
            margin-left: 60px;
        }

        .toggle-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }

        .container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            margin: auto;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            text-align: left;
            font-weight: bold;
            margin: 10px 0 5px;
        }

        select, input {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        button {
            width: 100%;
            background-color: #28a745;
            color: white;
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
        }

        button:hover {
            background-color: #218838;
        }

        .cancel-btn {
            background-color: #dc3545;
            margin-top: 10px;
        }

        .cancel-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <button class="toggle-btn" id="toggleBtn">☰</button>
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php">📊 Dashboard</a></li>
            <li><a href="manage-users.php">👥 Manage Users</a></li>
            <li><a href="manage-appointments.php">📅 Manage Bookings</a></li>
            <li><a href="manage-promotions.php">🎁 Manage Promotions</a></li>
            <li><a href="manage-services.php">🧼 Manage Services</a></li>
            <li><a href="admin_reports.php">📈 Reports</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="container">
            <h2>Process Payment</h2>
            <p><strong>Name:</strong> <?= htmlspecialchars($name); ?></p>
            <p><strong>Service:</strong> <?= htmlspecialchars($service); ?></p>
            <p><strong>Price:</strong> $<?= number_format($price, 2); ?></p>
            <p><strong>Payment Date:</strong> <?= date("Y-m-d H:i:s"); ?></p>

            <form method="POST">
                <label for="payment_method">Payment Method:</label>
                <select name="payment_method" required>
                    <option value="Cash">Cash</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="Mobile payment">M-Pesa</option>
                </select>

                <label for="amount_paid">Amount Paid:</label>
                <input type="number" step="0.01" name="amount_paid" placeholder="Enter amount" required>

                <button type="submit">Confirm Payment</button>
            </form>

            <a href="manage-appointments.php">
                <button class="cancel-btn">Cancel</button>
            </a>
        </div>
    </div>

    <script>
        document.getElementById("toggleBtn").addEventListener("click", function () {
            const sidebar = document.getElementById("sidebar");
            const mainContent = document.querySelector(".main-content");
            sidebar.classList.toggle("collapsed");
            mainContent.classList.toggle("expanded");
        });
    </script>
</body>
</html>
