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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkyCompare - Find Cheap Flights & Hotels</title>
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
        
        /* Hero Section */
        .hero {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }
        
        .hero-content {
            max-width: 800px;
        }
        
        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);
        }
        
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.6);
        }
        
        /* Search Form */
        .search-container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-top: -70px;
            position: relative;
            z-index: 2;
        }
        
        .search-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .search-tab {
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .search-tab.active {
            color: #0770e3;
            border-bottom-color: #0770e3;
        }
        
        .search-form {
            display: none;
        }
        
        .search-form.active {
            display: block;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20px;
            gap: 15px;
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
        
        .search-btn {
            background-color: #00a698;
            color: white;
            border: none;
            padding: 14px 30px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
            width: 100%;
        }
        
        .search-btn:hover {
            background-color: #008f83;
            transform: translateY(-2px);
        }
        
        /* Features Section */
        .features {
            padding: 80px 0;
            background-color: white;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-title h2 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 15px;
        }
        
        .section-title p {
            color: #666;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .features-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
        }
        
        .feature-card {
            flex: 1;
            min-width: 250px;
            max-width: 350px;
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: #0770e3;
            margin-bottom: 20px;
        }
        
        .feature-card h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: #333;
        }
        
        .feature-card p {
            color: #666;
        }
        
        /* Popular Destinations */
        .destinations {
            padding: 80px 0;
            background-color: #f5f5f5;
        }
        
        .destinations-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        
        .destination-card {
            flex: 1;
            min-width: 280px;
            max-width: 380px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }
        
        .destination-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        
        .destination-img {
            height: 200px;
            background-size: cover;
            background-position: center;
        }
        
        .destination-info {
            padding: 20px;
            background-color: white;
        }
        
        .destination-info h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: #333;
        }
        
        .destination-info p {
            color: #666;
            margin-bottom: 15px;
        }
        
        .destination-price {
            font-size: 1.2rem;
            font-weight: 600;
            color: #00a698;
        }
        
        .destination-btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #0770e3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        
        .destination-btn:hover {
            background-color: #0559b3;
        }
        
        /* Footer */
        footer {
            background-color: #333;
            color: white;
            padding: 60px 0 30px;
        }
        
        .footer-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-column {
            flex: 1;
            min-width: 200px;
        }
        
        .footer-column h3 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: #fff;
        }
        
        .footer-column ul {
            list-style: none;
        }
        
        .footer-column ul li {
            margin-bottom: 10px;
        }
        
        .footer-column ul li a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-column ul li a:hover {
            color: white;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
        }
        
        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: #444;
            color: white;
            border-radius: 50%;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.3s;
        }
        
        .social-links a:hover {
            background-color: #0770e3;
            transform: translateY(-3px);
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #444;
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
            
            .hero {
                height: 400px;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .search-container {
                margin-top: -50px;
                padding: 20px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 15px;
            }
            
            .form-group {
                width: 100%;
            }
            
            .feature-card, .destination-card {
                min-width: 100%;
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
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Find the Best Deals on Flights & Hotels</h1>
            <p>Compare prices from hundreds of travel sites and save big on your next trip</p>
        </div>
    </section>
    
    <!-- Search Form -->
    <div class="container">
        <div class="search-container">
            <div class="search-tabs">
                <div class="search-tab active" data-tab="flights">
                    <i class="fas fa-plane"></i> Flights
                </div>
                <div class="search-tab" data-tab="hotels">
                    <i class="fas fa-hotel"></i> Hotels
                </div>
            </div>
            
            <!-- Flights Search Form -->
            <form id="flights-form" class="search-form active" action="flights.php" method="GET">
                <div class="form-row">
                    <div class="form-group">
                        <label for="flight-from">From</label>
                        <input type="text" id="flight-from" name="from" placeholder="City or Airport" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="flight-to">To</label>
                        <input type="text" id="flight-to" name="to" placeholder="City or Airport" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="flight-depart">Depart</label>
                        <input type="date" id="flight-depart" name="depart" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="flight-return">Return</label>
                        <input type="date" id="flight-return" name="return">
                    </div>
                    
                    <div class="form-group">
                        <label for="flight-passengers">Passengers</label>
                        <select id="flight-passengers" name="passengers">
                            <option value="1">1 Adult</option>
                            <option value="2">2 Adults</option>
                            <option value="3">3 Adults</option>
                            <option value="4">4 Adults</option>
                            <option value="5">5 Adults</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Search Flights
                </button>
            </form>
            
            <!-- Hotels Search Form -->
            <form id="hotels-form" class="search-form" action="hotels.php" method="GET">
                <div class="form-row">
                    <div class="form-group">
                        <label for="hotel-destination">Destination</label>
                        <input type="text" id="hotel-destination" name="destination" placeholder="City or Hotel" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="hotel-checkin">Check-in</label>
                        <input type="date" id="hotel-checkin" name="checkin" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="hotel-checkout">Check-out</label>
                        <input type="date" id="hotel-checkout" name="checkout" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="hotel-guests">Guests</label>
                        <select id="hotel-guests" name="guests">
                            <option value="1">1 Guest</option>
                            <option value="2">2 Guests</option>
                            <option value="3">3 Guests</option>
                            <option value="4">4 Guests</option>
                            <option value="5">5+ Guests</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Search Hotels
                </button>
            </form>
        </div>
    </div>
    
    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose SkyCompare?</h2>
                <p>We help millions of travelers find the best deals on flights and hotels every month</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-search-dollar"></i>
                    </div>
                    <h3>Best Price Guarantee</h3>
                    <p>We compare hundreds of travel sites to find the best prices for your trip</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-filter"></i>
                    </div>
                    <h3>Smart Filters</h3>
                    <p>Filter results by price, duration, airline, and more to find your perfect match</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3>Price Alerts</h3>
                    <p>Get notified when prices drop for your saved searches</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Popular Destinations -->
    <section class="destinations">
        <div class="container">
            <div class="section-title">
                <h2>Popular Destinations</h2>
                <p>Explore these trending destinations with great deals</p>
            </div>
            
            <div class="destinations-grid">
                <div class="destination-card">
                    <div class="destination-img" style="background-image: url('https://images.unsplash.com/photo-1502602898657-3e91760cbb34?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80');"></div>
                    <div class="destination-info">
                        <h3>Paris, France</h3>
                        <p>The city of love and lights</p>
                        <div class="destination-price">From $299</div>
                        <a href="flights.php?to=Paris" class="destination-btn">Explore Deals</a>
                    </div>
                </div>
                
                <div class="destination-card">
                    <div class="destination-img" style="background-image: url('https://images.unsplash.com/photo-1538970272646-f61fabb3a8a2?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80');"></div>
                    <div class="destination-info">
                        <h3>New York, USA</h3>
                        <p>The city that never sleeps</p>
                        <div class="destination-price">From $349</div>
                        <a href="flights.php?to=New+York" class="destination-btn">Explore Deals</a>
                    </div>
                </div>
                
                <div class="destination-card">
                    <div class="destination-img" style="background-image: url('https://images.unsplash.com/photo-1533929736458-ca588d08c8be?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80');"></div>
                    <div class="destination-info">
                        <h3>Tokyo, Japan</h3>
                        <p>A blend of traditional and ultramodern</p>
                        <div class="destination-price">From $599</div>
                        <a href="flights.php?to=Tokyo" class="destination-btn">Explore Deals</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>SkyCompare</h3>
                    <p>Find the best deals on flights and hotels from hundreds of travel providers.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3>Company</h3>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Press</a></li>
                        <li><a href="#">Partners</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Support</h3>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Subscribe</h3>
                    <p>Get the latest deals and travel inspiration</p>
                    <form action="#" method="POST">
                        <div style="display: flex; margin-top: 10px;">
                            <input type="email" placeholder="Your email" style="flex: 1; padding: 10px; border: none; border-radius: 4px 0 0 4px;">
                            <button type="submit" style="background-color: #0770e3; color: white; border: none; padding: 10px 15px; border-radius: 0 4px 4px 0; cursor: pointer;">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2023 SkyCompare. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script>
        // Tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.search-tab');
            const forms = document.querySelectorAll('.search-form');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs and forms
                    tabs.forEach(t => t.classList.remove('active'));
                    forms.forEach(f => f.classList.remove('active'));
                    
                    // Add active class to clicked tab and corresponding form
                    this.classList.add('active');
                    const formId = this.getAttribute('data-tab') + '-form';
                    document.getElementById(formId).classList.add('active');
                });
            });
            
            // Set minimum dates for date inputs
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('flight-depart').min = today;
            document.getElementById('flight-return').min = today;
            document.getElementById('hotel-checkin').min = today;
            document.getElementById('hotel-checkout').min = today;
            
            // Set return date to be after depart date
            document.getElementById('flight-depart').addEventListener('change', function() {
                document.getElementById('flight-return').min = this.value;
            });
            
            // Set checkout date to be after checkin date
            document.getElementById('hotel-checkin').addEventListener('change', function() {
                document.getElementById('hotel-checkout').min = this.value;
            });
        });
    </script>
</body>
</html>
