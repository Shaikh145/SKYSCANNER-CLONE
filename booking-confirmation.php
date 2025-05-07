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

// Get booking reference
$bookingReference = isset($_GET['reference']) ? $_GET['reference'] : '';
$booking = null;

if (!empty($bookingReference)) {
    try {
        // Get booking details
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_reference = ?");
        $stmt->execute([$bookingReference]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Handle error silently
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - SkyCompare</title>
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
        
        /* Confirmation Page Styles */
        .confirmation-page {
            padding: 60px 0;
        }
        
        .confirmation-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .confirmation-header {
            background-color: #e8f5e9;
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid #c8e6c9;
        }
        
        .confirmation-icon {
            font-size: 3rem;
            color: #2e7d32;
            margin-bottom: 15px;
        }
        
        .confirmation-header h1 {
            font-size: 2rem;
            color: #2e7d32;
            margin-bottom: 10px;
        }
        
        .confirmation-header p {
            color: #555;
        }
        
        .confirmation-content {
            padding: 30px;
        }
        
        .confirmation-section {
            margin-bottom: 30px;
        }
        
        .confirmation-section h2 {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .booking-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .booking-info-item {
            margin-bottom: 15px;
        }
        
        .booking-info-label {
            font-weight: 500;
            color: #666;
            margin-bottom: 5px;
        }
        
        .booking-info-value {
            font-weight: 600;
            color: #333;
        }
        
        .booking-reference {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .booking-reference-label {
            font-weight: 500;
            color: #666;
            margin-bottom: 5px;
        }
        
        .booking-reference-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0770e3;
            letter-spacing: 2px;
        }
        
        .confirmation-footer {
            padding: 20px 30px;
            background-color: #f9f9f9;
            border-top: 1px solid #eee;
            text-align: center;
        }
        
        .confirmation-footer p {
            color: #666;
            margin-bottom: 20px;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .action-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #0770e3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            transition: background-color 0.3s, transform 0.3s;
        }
        
        .action-btn:hover {
            background-color: #0559b3;
            transform: translateY(-2px);
        }
        
        .action-btn.secondary {
            background-color: #f5f5f5;
            color: #333;
        }
        
        .action-btn.secondary:hover {
            background-color: #e0e0e0;
        }
        
        .no-booking {
            text-align: center;
            padding: 50px 0;
        }
        
        .no-booking h2 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 15px;
        }
        
        .no-booking p {
            color: #666;
            margin-bottom: 20px;
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
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            nav ul {
                margin-top: 15px;
            }
            
            .booking-info {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 10px;
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
    
    <!-- Confirmation Page -->
    <section class="confirmation-page">
        <div class="container">
            <?php if ($booking): ?>
                <div class="confirmation-container">
                    <div class="confirmation-header">
                        <div class="confirmation-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h1>Booking Confirmed!</h1>
                        <p>Your <?php echo $booking['booking_type'] === 'flight' ? 'flight' : 'hotel'; ?> has been successfully booked</p>
                    </div>
                    
                    <div class="confirmation-content">
                        <div class="booking-reference">
                            <div class="booking-reference-label">Booking Reference</div>
                            <div class="booking-reference-value"><?php echo htmlspecialchars($booking['booking_reference']); ?></div>
                        </div>
                        
                        <div class="confirmation-section">
                            <h2><?php echo $booking['booking_type'] === 'flight' ? 'Flight' : 'Hotel'; ?> Details</h2>
                            
                            <div class="booking-info">
                                <div class="booking-info-item">
                                    <div class="booking-info-label"><?php echo $booking['booking_type'] === 'flight' ? 'Flight' : 'Hotel'; ?></div>
                                    <div class="booking-info-value"><?php echo htmlspecialchars($booking['item_name']); ?></div>
                                </div>
                                
                                <?php if ($booking['booking_type'] === 'flight'): ?>
                                    <div class="booking-info-item">
                                        <div class="booking-info-label">Route</div>
                                        <div class="booking-info-value"><?php echo htmlspecialchars($booking['origin']); ?> to <?php echo htmlspecialchars($booking['destination']); ?></div>
                                    </div>
                                    
                                    <div class="booking-info-item">
                                        <div class="booking-info-label">Departure Date</div>
                                        <div class="booking-info-value"><?php echo htmlspecialchars($booking['depart_date']); ?></div>
                                    </div>
                                    
                                    <?php if (!empty($booking['return_date'])): ?>
                                        <div class="booking-info-item">
                                            <div class="booking-info-label">Return Date</div>
                                            <div class="booking-info-value"><?php echo htmlspecialchars($booking['return_date']); ?></div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="booking-info-item">
                                        <div class="booking-info-label">Passengers</div>
                                        <div class="booking-info-value"><?php echo htmlspecialchars($booking['passengers']); ?></div>
                                    </div>
                                <?php else: ?>
                                    <div class="booking-info-item">
                                        <div class="booking-info-label">Destination</div>
                                        <div class="booking-info-value"><?php echo htmlspecialchars($booking['destination']); ?></div>
                                    </div>
                                    
                                    <div class="booking-info-item">
                                        <div class="booking-info-label">Check-in Date</div>
                                        <div class="booking-info-value"><?php echo htmlspecialchars($booking['checkin_date']); ?></div>
                                    </div>
                                    
                                    <div class="booking-info-item">
                                        <div class="booking-info-label">Check-out Date</div>
                                        <div class="booking-info-value"><?php echo htmlspecialchars($booking['checkout_date']); ?></div>
                                    </div>
                                    
                                    <div class="booking-info-item">
                                        <div class="booking-info-label">Guests</div>
                                        <div class="booking-info-value"><?php echo htmlspecialchars($booking['guests']); ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="booking-info-item">
                                    <div class="booking-info-label">Price</div>
                                    <div class="booking-info-value">$<?php echo number_format($booking['total_price'], 2); ?></div>
                                </div>
                                
                                <div class="booking-info-item">
                                    <div class="booking-info-label">Status</div>
                                    <div class="booking-info-value"><?php echo htmlspecialchars($booking['booking_status']); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="confirmation-section">
                            <h2>Contact Information</h2>
                            
                            <div class="booking-info">
                                <div class="booking-info-item">
                                    <div class="booking-info-label">Name</div>
                                    <div class="booking-info-value"><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></div>
                                </div>
                                
                                <div class="booking-info-item">
                                    <div class="booking-info-label">Email</div>
                                    <div class="booking-info-value"><?php echo htmlspecialchars($booking['email']); ?></div>
                                </div>
                                
                                <div class="booking-info-item">
                                    <div class="booking-info-label">Phone</div>
                                    <div class="booking-info-value"><?php echo htmlspecialchars($booking['phone']); ?></div>
                                </div>
                                
                                <div class="booking-info-item">
                                    <div class="booking-info-label">Booking Date</div>
                                    <div class="booking-info-value"><?php echo htmlspecialchars($booking['created_at']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="confirmation-footer">
                        <p>A confirmation email has been sent to <?php echo htmlspecialchars($booking['email']); ?></p>
                        
                        <div class="action-buttons">
                            <a href="dashboard.php" class="action-btn">Go to Dashboard</a>
                            <a href="#" class="action-btn secondary" onclick="window.print()">Print Confirmation</a>
                            <a href="index.php" class="action-btn secondary">Return to Home</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="confirmation-container">
                    <div class="no-booking">
                        <h2>Booking Not Found</h2>
                        <p>We couldn't find a booking with the provided reference. Please check your booking reference and try again.</p>
                        <div class="action-buttons">
                            <a href="index.php" class="action-btn">Return to Home</a>
                            <a href="dashboard.php" class="action-btn secondary">Go to Dashboard</a>
                        </div>
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
</body>
</html>
