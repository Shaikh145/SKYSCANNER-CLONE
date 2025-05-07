<?php
session_start();
$db_host = 'localhost';
$db_name = 'dbbxwfvewdhinh';
$db_user = 'uklz9ew3hrop3';
$db_pass = 'zyrbspyjlzjb';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if user is logged in
$loggedIn = isset($_SESSION['user_id']);
$username = $loggedIn ? $_SESSION['username'] : '';

// Get booking parameters
$bookingType = isset($_GET['type']) ? $_GET['type'] : '';
$itemName = isset($_GET['name']) ? $_GET['name'] : '';
$price = isset($_GET['price']) ? (float)$_GET['price'] : 0;
$from = isset($_GET['from']) ? $_GET['from'] : '';
$to = isset($_GET['to']) ? $_GET['to'] : '';
$departDate = isset($_GET['depart_date']) ? $_GET['depart_date'] : '';
$returnDate = isset($_GET['return_date']) ? $_GET['return_date'] : '';
$passengers = isset($_GET['passengers']) ? (int)$_GET['passengers'] : 1;
$destination = isset($_GET['destination']) ? $_GET['destination'] : '';
$checkinDate = isset($_GET['checkin_date']) ? $_GET['checkin_date'] : '';
$checkoutDate = isset($_GET['checkout_date']) ? $_GET['checkout_date'] : '';
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 1;

// Calculate total price
$totalPrice = $price;
if ($bookingType === 'flight') {
    $totalPrice *= $passengers;
} else if ($bookingType === 'hotel') {
    // Calculate number of nights
    $checkin = new DateTime($checkinDate);
    $checkout = new DateTime($checkoutDate);
    $nights = $checkout->diff($checkin)->days;
    $totalPrice *= $nights;
}

// Process booking form
$bookingSuccess = false;
$bookingError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form
    $firstName = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $lastName = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $cardName = isset($_POST['card_name']) ? trim($_POST['card_name']) : '';
    $cardNumber = isset($_POST['card_number']) ? trim($_POST['card_number']) : '';
    $cardExpiry = isset($_POST['card_expiry']) ? trim($_POST['card_expiry']) : '';
    $cardCvv = isset($_POST['card_cvv']) ? trim($_POST['card_cvv']) : '';
    
    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || 
        empty($cardName) || empty($cardNumber) || empty($cardExpiry) || empty($cardCvv)) {
        $bookingError = 'Please fill in all required fields';
    } else {
        try {
            // Create bookings table if it doesn't exist
            $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                booking_type VARCHAR(20) NOT NULL,
                item_name VARCHAR(100) NOT NULL,
                origin VARCHAR(100),
                destination VARCHAR(100) NOT NULL,
                depart_date DATE,
                return_date DATE,
                checkin_date DATE,
                checkout_date DATE,
                passengers INT,
                guests INT,
                price DECIMAL(10,2) NOT NULL,
                total_price DECIMAL(10,2) NOT NULL,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                booking_status VARCHAR(20) DEFAULT 'Confirmed',
                booking_reference VARCHAR(10) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Generate a random booking reference
            $bookingReference = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
            
            // Insert booking
            $stmt = $pdo->prepare("INSERT INTO bookings (
                user_id, booking_type, item_name, origin, destination, depart_date, return_date, 
                checkin_date, checkout_date, passengers, guests, price, total_price, 
                first_name, last_name, email, phone, booking_reference
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $userId = $loggedIn ? $_SESSION['user_id'] : null;
            
            $stmt->execute([
                $userId, $bookingType, $itemName, $from, $to, $departDate, $returnDate,
                $checkinDate, $checkoutDate, $passengers, $guests, $price, $totalPrice,
                $firstName, $lastName, $email, $phone, $bookingReference
            ]);
            
            $bookingSuccess = true;
            
            // Redirect to confirmation page
            header("Location: booking-confirmation.php?reference=" . $bookingReference);
            exit();
        } catch (PDOException $e) {
            $bookingError = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Booking - SkyCompare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        /* Header Styles */
        header {
            background-color: #0770e3;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            font-size: 24px;
            font-weight: bold;
        }
        
        .logo i {
            margin-right: 10px;
            font-size: 28px;
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav ul li {
            margin-left: 20px;
        }
        
        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        nav ul li a:hover {
            color: #e6f2ff;
        }
        
        .auth-buttons a {
            display: inline-block;
            padding: 8px 16px;
            background-color: white;
            color: #0770e3;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            margin-left: 10px;
            transition: all 0.3s;
        }
        
        .auth-buttons a:hover {
            background-color: #e6f2ff;
            transform: translateY(-2px);
        }
        
        .user-menu {
            position: relative;
            cursor: pointer;
        }
        
        .user-menu-content {
            display: none;
            position: absolute;
            right: 0;
            top: 40px;
            background-color: white;
            min-width: 180px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            z-index: 1;
            border-radius: 4px;
        }
        
        .user-menu-content a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background-color 0.3s;
        }
        
        .user-menu-content a:hover {
            background-color: #f5f5f5;
        }
        
        .user-menu:hover .user-menu-content {
            display: block;
        }
        
        /* Booking Page Styles */
        .booking-page {
            padding: 40px 0;
        }
        
        .booking-header {
            margin-bottom: 30px;
        }
        
        .booking-header h1 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 10px;
        }
        
        .booking-header p {
            color: #666;
        }
        
        .booking-container {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }
        
        /* Booking Form */
        .booking-form {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .form-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .form-header h2 {
            font-size: 1.3rem;
            color: #333;
        }
        
        .form-content {
            padding: 20px;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h3 {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus, .form-group select:focus {
            border-color: #0770e3;
            outline: none;
        }
        
        .required::after {
            content: "*";
            color: #e53935;
            margin-left: 3px;
        }
        
        .form-footer {
            padding: 20px;
            border-top: 1px solid #eee;
            text-align: right;
        }
        
        .submit-btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #00a698;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
        }
        
        .submit-btn:hover {
            background-color: #008f83;
            transform: translateY(-2px);
        }
        
        /* Booking Summary */
        .booking-summary {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            align-self: flex-start;
            position: sticky;
            top: 20px;
        }
        
        .summary-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .summary-header h2 {
            font-size: 1.3rem;
            color: #333;
        }
        
        .summary-content {
            padding: 20px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .summary-item-label {
            color: #666;
        }
        
        .summary-item-value {
            font-weight: 500;
            color: #333;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .summary-total-label {
            font-weight: 600;
            color: #333;
        }
        
        .summary-total-value {
            font-weight: 700;
            color: #00a698;
            font-size: 1.2rem;
        }
        
        .booking-details {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .booking-detail {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .booking-detail i {
            width: 20px;
            margin-right: 10px;
            color: #0770e3;
        }
        
        .booking-detail-text {
            color: #333;
        }
        
        /* Error Message */
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        /* Footer */
        footer {
            background-color: #333;
            color: white;
            padding: 30px 0;
            margin-top: 80px;
        }
        
        .footer-bottom {
            text-align: center;
            color: #aaa;
            font-size: 0.9rem;
        }
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .booking-container {
                grid-template-columns: 1fr;
            }
            
            .booking-summary {
                position: static;
                order: -1;
            }
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            nav ul {
                margin-top: 15px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 15px;
            }
            
            .form-group {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <i class="fas fa-plane"></i>
                    SkyCompare
                </a>
                
                <nav>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="flights.php">Flights</a></li>
                        <li><a href="hotels.php">Hotels</a></li>
                        <li><a href="#about">About</a></li>
                    </ul>
                </nav>
                
                <?php if ($loggedIn): ?>
                    <div class="user-menu">
                        <a href="#" style="color: white;">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($username); ?>
                        </a>
                        <div class="user-menu-content">
                            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                            <a href="profile.php"><i class="fas fa-user-edit"></i> Profile</a>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="login.php">Login</a>
                        <a href="signup.php">Sign Up</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>
    
    <!-- Booking Page -->
    <section class="booking-page">
        <div class="container">
            <div class="booking-header">
                <h1>Complete Your Booking</h1>
                <p>Please fill in the details below to finalize your reservation</p>
            </div>
            
            <?php if (!empty($bookingError)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($bookingError); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($bookingType) && !empty($itemName)): ?>
                <div class="booking-container">
                    <!-- Booking Form -->
                    <div class="booking-form">
                        <div class="form-header">
                            <h2>Booking Information</h2>
                        </div>
                        
                        <form action="booking.php?<?php echo http_build_query($_GET); ?>" method="POST">
                            <div class="form-content">
                                <!-- Contact Information -->
                                <div class="form-section">
                                    <h3>Contact Information</h3>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="first_name" class="required">First Name</label>
                                            <input type="text" id="first_name" name="first_name" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="last_name" class="required">Last Name</label>
                                            <input type="text" id="last_name" name="last_name" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="email" class="required">Email Address</label>
                                            <input type="email" id="email" name="email" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="phone" class="required">Phone Number</label>
                                            <input type="tel" id="phone" name="phone" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ($bookingType === 'flight'): ?>
                                    <!-- Passenger Information -->
                                    <div class="form-section">
                                        <h3>Passenger Information</h3>
                                        
                                        <?php for ($i = 1; $i <= $passengers; $i++): ?>
                                            <div class="form-row">
                                                <div class="form-group">
                                                    <label for="passenger_first_name_<?php echo $i; ?>" class="required">Passenger <?php echo $i; ?> First Name</label>
                                                    <input type="text" id="passenger_first_name_<?php echo $i; ?>" name="passenger_first_name_<?php echo $i; ?>" required>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="passenger_last_name_<?php echo $i; ?>" class="required">Passenger <?php echo $i; ?> Last Name</label>
                                                    <input type="text" id="passenger_last_name_<?php echo $i; ?>" name="passenger_last_name_<?php echo $i; ?>" required>
                                                </div>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                <?php elseif ($bookingType === 'hotel'): ?>
                                    <!-- Guest Information -->
                                    <div class="form-section">
                                        <h3>Guest Information</h3>
                                        
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label for="special_requests">Special Requests</label>
                                                <input type="text" id="special_requests" name="special_requests" placeholder="e.g., Non-smoking room, early check-in">
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Payment Information -->
                                <div class="form-section">
                                    <h3>Payment Information</h3>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="card_name" class="required">Name on Card</label>
                                            <input type="text" id="card_name" name="card_name" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="card_number" class="required">Card Number</label>
                                            <input type="text" id="card_number" name="card_number" placeholder="XXXX XXXX XXXX XXXX" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="card_expiry" class="required">Expiry Date</label>
                                            <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="card_cvv" class="required">CVV</label>
                                            <input type="text" id="card_cvv" name="card_cvv" placeholder="XXX" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-footer">
                                <button type="submit" class="submit-btn">Complete Booking</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Booking Summary -->
                    <div class="booking-summary">
                        <div class="summary-header">
                            <h2>Booking Summary</h2>
                        </div>
                        
                        <div class="summary-content">
                            <div class="booking-details">
                                <?php if ($bookingType === 'flight'): ?>
                                    <div class="booking-detail">
                                        <i class="fas fa-plane"></i>
                                        <div class="booking-detail-text">
                                            <strong><?php echo htmlspecialchars($itemName); ?></strong>
                                        </div>
                                    </div>
                                    
                                    <div class="booking-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <div class="booking-detail-text">
                                            <?php echo htmlspecialchars($from); ?> to <?php echo htmlspecialchars($to); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="booking-detail">
                                        <i class="fas fa-calendar-alt"></i>
                                        <div class="booking-detail-text">
                                            Depart: <?php echo htmlspecialchars($departDate); ?>
                                            <?php if (!empty($returnDate)): ?>
                                                <br>Return: <?php echo htmlspecialchars($returnDate); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="booking-detail">
                                        <i class="fas fa-user"></i>
                                        <div class="booking-detail-text">
                                            <?php echo htmlspecialchars($passengers); ?> Passenger<?php echo $passengers > 1 ? 's' : ''; ?>
                                        </div>
                                    </div>
                                <?php elseif ($bookingType === 'hotel'): ?>
                                    <div class="booking-detail">
                                        <i class="fas fa-hotel"></i>
                                        <div class="booking-detail-text">
                                            <strong><?php echo htmlspecialchars($itemName); ?></strong>
                                        </div>
                                    </div>
                                    
                                    <div class="booking-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <div class="booking-detail-text">
                                            <?php echo htmlspecialchars($destination); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="booking-detail">
                                        <i class="fas fa-calendar-alt"></i>
                                        <div class="booking-detail-text">
                                            Check-in: <?php echo htmlspecialchars($checkinDate); ?>
                                            <br>Check-out: <?php echo htmlspecialchars($checkoutDate); ?>
                                            <?php
                                            $checkin = new DateTime($checkinDate);
                                            $checkout = new DateTime($checkoutDate);
                                            $nights = $checkout->diff($checkin)->days;
                                            ?>
                                            <br>(<?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?>)
                                        </div>
                                    </div>
                                    
                                    <div class="booking-detail">
                                        <i class="fas fa-user"></i>
                                        <div class="booking-detail-text">
                                            <?php echo htmlspecialchars($guests); ?> Guest<?php echo $guests > 1 ? 's' : ''; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="summary-item">
                                <div class="summary-item-label">Base Price</div>
                                <div class="summary-item-value">$<?php echo number_format($price, 2); ?></div>
                            </div>
                            
                            <?php if ($bookingType === 'flight'): ?>
                                <div class="summary-item">
                                    <div class="summary-item-label">Passengers</div>
                                    <div class="summary-item-value"><?php echo $passengers; ?></div>
                                </div>
                            <?php elseif ($bookingType === 'hotel'): ?>
                                <div class="summary-item">
                                    <div class="summary-item-label">Nights</div>
                                    <div class="summary-item-value"><?php echo $nights; ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="summary-item">
                                <div class="summary-item-label">Taxes & Fees</div>
                                <div class="summary-item-value">Included</div>
                            </div>
                            
                            <div class="summary-total">
                                <div class="summary-total-label">Total</div>
                                <div class="summary-total-value">$<?php echo number_format($totalPrice, 2); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 50px 0;">
                    <h2>No booking information provided</h2>
                    <p>Please select a flight or hotel to book</p>
                    <div style="margin-top: 20px;">
                        <a href="flights.php" style="display: inline-block; padding: 10px 20px; background-color: #0770e3; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;">Search Flights</a>
                        <a href="hotels.php" style="display: inline-block; padding: 10px 20px; background-color: #0770e3; color: white; text-decoration: none; border-radius: 4px;">Search Hotels</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2023 SkyCompare. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script>
        // JavaScript for form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            
            if (form) {
                // Format credit card number with spaces
                const cardNumberInput = document.getElementById('card_number');
                if (cardNumberInput) {
                    cardNumberInput.addEventListener('input', function(e) {
                        // Remove all non-digit characters
                        let value = this.value.replace(/\D/g, '');
                        
                        // Add spaces after every 4 digits
                        value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
                        
                        // Update the input value
                        this.value = value;
                    });
                }
                
                // Format expiry date with slash
                const cardExpiryInput = document.getElementById('card_expiry');
                if (cardExpiryInput) {
                    cardExpiryInput.addEventListener('input', function(e) {
                        // Remove all non-digit characters
                        let value = this.value.replace(/\D/g, '');
                        
                        // Add slash after first 2 digits
                        if (value.length > 2) {
                            value = value.substring(0, 2) + '/' + value.substring(2);
                        }
                        
                        // Limit to MM/YY format
                        if (value.length > 5) {
                            value = value.substring(0, 5);
                        }
                        
                        // Update the input value
                        this.value = value;
                    });
                }
                
                // Limit CVV to 3 or 4 digits
                const cardCvvInput = document.getElementById('card_cvv');
                if (cardCvvInput) {
                    cardCvvInput.addEventListener('input', function(e) {
                        // Remove all non-digit characters
                        let value = this.value.replace(/\D/g, '');
                        
                        // Limit to 3 or 4 digits
                        if (value.length > 4) {
                            value = value.substring(0, 4);
                        }
                        
                        // Update the input value
                        this.value = value;
                    });
                }
                
                form.addEventListener('submit', function(event) {
                    const firstName = document.getElementById('first_name').value;
                    const lastName = document.getElementById('last_name').value;
                    const email = document.getElementById('email').value;
                    const phone = document.getElementById('phone').value;
                    const cardName = document.getElementById('card_name').value;
                    const cardNumber = document.getElementById('card_number').value;
                    const cardExpiry = document.getElementById('card_expiry').value;
                    const cardCvv = document.getElementById('card_cvv').value;
                    
                    if (!firstName || !lastName || !email || !phone || !cardName || !cardNumber || !cardExpiry || !cardCvv) {
                        event.preventDefault();
                        alert('Please fill in all required fields');
                    }
                });
            }
        });
    </script>
</body>
</html>
