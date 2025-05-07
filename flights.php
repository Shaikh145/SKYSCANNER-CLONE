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

// Get search parameters
$from = isset($_GET['from']) ? $_GET['from'] : '';
$to = isset($_GET['to']) ? $_GET['to'] : '';
$depart = isset($_GET['depart']) ? $_GET['depart'] : '';
$return = isset($_GET['return']) ? $_GET['return'] : '';
$passengers = isset($_GET['passengers']) ? (int)$_GET['passengers'] : 1;

// Save search to database if user is logged in
if ($loggedIn && !empty($from) && !empty($to) && !empty($depart)) {
    try {
        // Create searches table if it doesn't exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS searches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            search_type VARCHAR(20) NOT NULL,
            origin VARCHAR(100) NOT NULL,
            destination VARCHAR(100) NOT NULL,
            depart_date DATE NOT NULL,
            return_date DATE,
            passengers INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Insert search
        $stmt = $pdo->prepare("INSERT INTO searches (user_id, search_type, origin, destination, depart_date, return_date, passengers) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], 'flight', $from, $to, $depart, $return, $passengers]);
    } catch (PDOException $e) {
        // Silently fail - don't interrupt user experience
    }
}

// Generate dummy flight data
function generateFlights($from, $to, $date, $count = 10) {
    $airlines = ['SkyAir', 'GlobalWings', 'AeroJet', 'StarFly', 'BlueSky', 'EagleAir', 'SunExpress', 'OceanAir'];
    $flights = [];
    
    for ($i = 0; $i < $count; $i++) {
        $airline = $airlines[array_rand($airlines)];
        $flightNumber = strtoupper(substr($airline, 0, 2)) . rand(1000, 9999);
        
        // Random departure time between 00:00 and 23:59
        $departHour = str_pad(rand(0, 23), 2, '0', STR_PAD_LEFT);
        $departMinute = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
        $departTime = $departHour . ':' . $departMinute;
        
        // Random duration between 1h30m and 12h
        $durationHours = rand(1, 12);
        $durationMinutes = rand(0, 59);
        $duration = $durationHours . 'h ' . $durationMinutes . 'm';
        
        // Calculate arrival time
        $departDateTime = new DateTime($date . ' ' . $departTime);
        $departDateTime->add(new DateInterval('PT' . $durationHours . 'H' . $durationMinutes . 'M'));
        $arrivalTime = $departDateTime->format('H:i');
        $arrivalDate = $departDateTime->format('Y-m-d');
        
        // Random price between $50 and $1500
        $price = rand(50, 1500);
        
        // Random number of stops (0, 1, or 2)
        $stops = rand(0, 2);
        
        $flights[] = [
            'airline' => $airline,
            'flight_number' => $flightNumber,
            'from' => $from,
            'to' => $to,
            'depart_date' => $date,
            'depart_time' => $departTime,
            'arrival_date' => $arrivalDate,
            'arrival_time' => $arrivalTime,
            'duration' => $duration,
            'stops' => $stops,
            'price' => $price,
            'available_seats' => rand(1, 50)
        ];
    }
    
    // Sort by price (lowest first)
    usort($flights, function($a, $b) {
        return $a['price'] - $b['price'];
    });
    
    return $flights;
}

$outboundFlights = [];
$returnFlights = [];

if (!empty($from) && !empty($to) && !empty($depart)) {
    $outboundFlights = generateFlights($from, $to, $depart);
    
    if (!empty($return)) {
        $returnFlights = generateFlights($to, $from, $return);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Search Results - SkyCompare</title>
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
        
        /* Search Results Styles */
        .search-results {
            padding: 40px 0;
        }
        
        .search-summary {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .search-summary h1 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #333;
        }
        
        .search-summary p {
            color: #666;
        }
        
        .search-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 15px;
        }
        
        .search-detail {
            display: flex;
            align-items: center;
            color: #555;
        }
        
        .search-detail i {
            margin-right: 8px;
            color: #0770e3;
        }
        
        .results-container {
            display: flex;
            gap: 30px;
        }
        
        .filters {
            width: 280px;
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            align-self: flex-start;
            position: sticky;
            top: 20px;
        }
        
        .filters h2 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: #333;
        }
        
        .filter-group {
            margin-bottom: 25px;
        }
        
        .filter-group h3 {
            font-size: 1rem;
            margin-bottom: 15px;
            color: #555;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .filter-group h3 i {
            font-size: 0.8rem;
            cursor: pointer;
        }
        
        .filter-options {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .filter-option {
            display: flex;
            align-items: center;
        }
        
        .filter-option input {
            margin-right: 10px;
        }
        
        .price-range {
            margin-top: 15px;
        }
        
        .price-range input {
            width: 100%;
        }
        
        .price-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 5px;
            font-size: 0.9rem;
            color: #666;
        }
        
        .reset-filters {
            display: inline-block;
            padding: 8px 16px;
            background-color: #f5f5f5;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            margin-top: 10px;
            transition: background-color 0.3s;
            text-align: center;
        }
        
        .reset-filters:hover {
            background-color: #e0e0e0;
        }
        
        .flights-list {
            flex: 1;
        }
        
        .sort-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            background-color: white;
            border-radius: 8px;
            padding: 15px 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .sort-by {
            display: flex;
            align-items: center;
        }
        
        .sort-by label {
            margin-right: 10px;
            font-weight: 500;
            color: #555;
        }
        
        .sort-by select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
            color: #333;
        }
        
        .results-count {
            color: #666;
        }
        
        .flight-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .flight-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .flight-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .airline-info {
            display: flex;
            align-items: center;
        }
        
        .airline-logo {
            width: 40px;
            height: 40px;
            background-color: #f5f5f5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #0770e3;
            font-size: 1.2rem;
        }
        
        .airline-name {
            font-weight: 600;
            color: #333;
        }
        
        .flight-number {
            color: #666;
            font-size: 0.9rem;
        }
        
        .flight-price {
            text-align: right;
        }
        
        .price-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: #00a698;
        }
        
        .price-per-person {
            font-size: 0.8rem;
            color: #666;
        }
        
        .flight-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .flight-route {
            flex: 1;
            display: flex;
            align-items: center;
        }
        
        .departure, .arrival {
            text-align: center;
        }
        
        .time {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
        }
        
        .date, .airport {
            font-size: 0.9rem;
            color: #666;
        }
        
        .flight-path {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 20px;
        }
        
        .duration {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 5px;
        }
        
        .stops-line {
            width: 100%;
            height: 2px;
            background-color: #ddd;
            position: relative;
            margin: 5px 0;
        }
        
        .stops-line::before, .stops-line::after {
            content: "";
            position: absolute;
            width: 8px;
            height: 8px;
            background-color: #0770e3;
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .stops-line::before {
            left: 0;
        }
        
        .stops-line::after {
            right: 0;
        }
        
        .stops-info {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        
        .flight-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .flight-features {
            display: flex;
            gap: 15px;
        }
        
        .feature {
            display: flex;
            align-items: center;
            color: #666;
            font-size: 0.9rem;
        }
        
        .feature i {
            margin-right: 5px;
            color: #0770e3;
        }
        
        .book-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #00a698;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            transition: background-color 0.3s, transform 0.3s;
        }
        
        .book-btn:hover {
            background-color: #008f83;
            transform: translateY(-2px);
        }
        
        .flight-section {
            margin-bottom: 40px;
        }
        
        .flight-section h2 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: #333;
        }
        
        .no-results {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .no-results h2 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: #333;
        }
        
        .no-results p {
            color: #666;
            margin-bottom: 20px;
        }
        
        .no-results a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #0770e3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        .no-results a:hover {
            background-color: #0559b3;
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
            .results-container {
                flex-direction: column;
            }
            
            .filters {
                width: 100%;
                position: static;
                margin-bottom: 30px;
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
            
            .flight-details {
                flex-direction: column;
                gap: 20px;
            }
            
            .flight-path {
                padding: 20px 0;
            }
            
            .flight-footer {
                flex-direction: column;
                gap: 20px;
            }
            
            .flight-features {
                flex-wrap: wrap;
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
    
    <!-- Search Results -->
    <section class="search-results">
        <div class="container">
            <?php if (!empty($from) && !empty($to) && !empty($depart)): ?>
                <div class="search-summary">
                    <h1>Flight Search Results</h1>
                    <p>Showing flights from <?php echo htmlspecialchars($from); ?> to <?php echo htmlspecialchars($to); ?></p>
                    
                    <div class="search-details">
                        <div class="search-detail">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Depart: <?php echo htmlspecialchars($depart); ?></span>
                        </div>
                        
                        <?php if (!empty($return)): ?>
                            <div class="search-detail">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Return: <?php echo htmlspecialchars($return); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="search-detail">
                            <i class="fas fa-user"></i>
                            <span><?php echo htmlspecialchars($passengers); ?> Passenger<?php echo $passengers > 1 ? 's' : ''; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="results-container">
                    <!-- Filters -->
                    <div class="filters">
                        <h2>Filter Results</h2>
                        
                        <div class="filter-group">
                            <h3>
                                Price Range
                                <i class="fas fa-chevron-down"></i>
                            </h3>
                            <div class="filter-options">
                                <div class="price-range">
                                    <input type="range" id="price-slider" min="0" max="1500" value="1500">
                                    <div class="price-labels">
                                        <span>$0</span>
                                        <span>$1500</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <h3>
                                Airlines
                                <i class="fas fa-chevron-down"></i>
                            </h3>
                            <div class="filter-options">
                                <div class="filter-option">
                                    <input type="checkbox" id="airline-skyair" checked>
                                    <label for="airline-skyair">SkyAir</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="airline-globalwings" checked>
                                    <label for="airline-globalwings">GlobalWings</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="airline-aerojet" checked>
                                    <label for="airline-aerojet">AeroJet</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="airline-starfly" checked>
                                    <label for="airline-starfly">StarFly</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="airline-bluesky" checked>
                                    <label for="airline-bluesky">BlueSky</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <h3>
                                Stops
                                <i class="fas fa-chevron-down"></i>
                            </h3>
                            <div class="filter-options">
                                <div class="filter-option">
                                    <input type="checkbox" id="stops-direct" checked>
                                    <label for="stops-direct">Direct (no stops)</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="stops-1" checked>
                                    <label for="stops-1">1 Stop</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="stops-2" checked>
                                    <label for="stops-2">2+ Stops</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <h3>
                                Departure Time
                                <i class="fas fa-chevron-down"></i>
                            </h3>
                            <div class="filter-options">
                                <div class="filter-option">
                                    <input type="checkbox" id="depart-morning" checked>
                                    <label for="depart-morning">Morning (5:00 - 11:59)</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="depart-afternoon" checked>
                                    <label for="depart-afternoon">Afternoon (12:00 - 17:59)</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="depart-evening" checked>
                                    <label for="depart-evening">Evening (18:00 - 23:59)</label>
                                </div>
                            </div>
                        </div>
                        
                        <a href="#" class="reset-filters">Reset All Filters</a>
                    </div>
                    
                    <!-- Flights List -->
                    <div class="flights-list">
                        <!-- Sort Options -->
                        <div class="sort-options">
                            <div class="sort-by">
                                <label for="sort-select">Sort by:</label>
                                <select id="sort-select">
                                    <option value="price">Price (Lowest first)</option>
                                    <option value="duration">Duration (Shortest first)</option>
                                    <option value="departure">Departure (Earliest first)</option>
                                    <option value="arrival">Arrival (Earliest first)</option>
                                </select>
                            </div>
                            
                            <div class="results-count">
                                <?php echo count($outboundFlights); ?> results found
                            </div>
                        </div>
                        
                        <!-- Outbound Flights -->
                        <div class="flight-section">
                            <h2>Outbound Flights</h2>
                            
                            <?php foreach ($outboundFlights as $flight): ?>
                                <div class="flight-card">
                                    <div class="flight-header">
                                        <div class="airline-info">
                                            <div class="airline-logo">
                                                <i class="fas fa-plane"></i>
                                            </div>
                                            <div>
                                                <div class="airline-name"><?php echo htmlspecialchars($flight['airline']); ?></div>
                                                <div class="flight-number"><?php echo htmlspecialchars($flight['flight_number']); ?></div>
                                            </div>
                                        </div>
                                        
                                        <div class="flight-price">
                                            <div class="price-amount">$<?php echo htmlspecialchars($flight['price']); ?></div>
                                            <div class="price-per-person">per person</div>
                                        </div>
                                    </div>
                                    
                                    <div class="flight-details">
                                        <div class="flight-route">
                                            <div class="departure">
                                                <div class="time"><?php echo htmlspecialchars($flight['depart_time']); ?></div>
                                                <div class="date"><?php echo htmlspecialchars($flight['depart_date']); ?></div>
                                                <div class="airport"><?php echo htmlspecialchars($flight['from']); ?></div>
                                            </div>
                                        </div>
                                        
                                        <div class="flight-path">
                                            <div class="duration"><?php echo htmlspecialchars($flight['duration']); ?></div>
                                            <div class="stops-line"></div>
                                            <div class="stops-info">
                                                <?php if ($flight['stops'] == 0): ?>
                                                    Direct Flight
                                                <?php else: ?>
                                                    <?php echo htmlspecialchars($flight['stops']); ?> Stop<?php echo $flight['stops'] > 1 ? 's' : ''; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="flight-route">
                                            <div class="arrival">
                                                <div class="time"><?php echo htmlspecialchars($flight['arrival_time']); ?></div>
                                                <div class="date"><?php echo htmlspecialchars($flight['arrival_date']); ?></div>
                                                <div class="airport"><?php echo htmlspecialchars($flight['to']); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flight-footer">
                                        <div class="flight-features">
                                            <div class="feature">
                                                <i class="fas fa-suitcase"></i>
                                                <span>Baggage included</span>
                                            </div>
                                            <div class="feature">
                                                <i class="fas fa-wifi"></i>
                                                <span>In-flight Wi-Fi</span>
                                            </div>
                                            <div class="feature">
                                                <i class="fas fa-utensils"></i>
                                                <span>Meal included</span>
                                            </div>
                                        </div>
                                        
                                        <a href="#" class="book-btn" onclick="redirectToBooking('<?php echo htmlspecialchars($flight['airline']); ?>', '<?php echo htmlspecialchars($flight['flight_number']); ?>', <?php echo htmlspecialchars($flight['price']); ?>)">
                                            Select Flight
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Return Flights -->
                        <?php if (!empty($return) && !empty($returnFlights)): ?>
                            <div class="flight-section">
                                <h2>Return Flights</h2>
                                
                                <?php foreach ($returnFlights as $flight): ?>
                                    <div class="flight-card">
                                        <div class="flight-header">
                                            <div class="airline-info">
                                                <div class="airline-logo">
                                                    <i class="fas fa-plane"></i>
                                                </div>
                                                <div>
                                                    <div class="airline-name"><?php echo htmlspecialchars($flight['airline']); ?></div>
                                                    <div class="flight-number"><?php echo htmlspecialchars($flight['flight_number']); ?></div>
                                                </div>
                                            </div>
                                            
                                            <div class="flight-price">
                                                <div class="price-amount">$<?php echo htmlspecialchars($flight['price']); ?></div>
                                                <div class="price-per-person">per person</div>
                                            </div>
                                        </div>
                                        
                                        <div class="flight-details">
                                            <div class="flight-route">
                                                <div class="departure">
                                                    <div class="time"><?php echo htmlspecialchars($flight['depart_time']); ?></div>
                                                    <div class="date"><?php echo htmlspecialchars($flight['depart_date']); ?></div>
                                                    <div class="airport"><?php echo htmlspecialchars($flight['from']); ?></div>
                                                </div>
                                            </div>
                                            
                                            <div class="flight-path">
                                                <div class="duration"><?php echo htmlspecialchars($flight['duration']); ?></div>
                                                <div class="stops-line"></div>
                                                <div class="stops-info">
                                                    <?php if ($flight['stops'] == 0): ?>
                                                        Direct Flight
                                                    <?php else: ?>
                                                        <?php echo htmlspecialchars($flight['stops']); ?> Stop<?php echo $flight['stops'] > 1 ? 's' : ''; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="flight-route">
                                                <div class="arrival">
                                                    <div class="time"><?php echo htmlspecialchars($flight['arrival_time']); ?></div>
                                                    <div class="date"><?php echo htmlspecialchars($flight['arrival_date']); ?></div>
                                                    <div class="airport"><?php echo htmlspecialchars($flight['to']); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="flight-footer">
                                            <div class="flight-features">
                                                <div class="feature">
                                                    <i class="fas fa-suitcase"></i>
                                                    <span>Baggage included</span>
                                                </div>
                                                <div class="feature">
                                                    <i class="fas fa-wifi"></i>
                                                    <span>In-flight Wi-Fi</span>
                                                </div>
                                                <div class="feature">
                                                    <i class="fas fa-utensils"></i>
                                                    <span>Meal included</span>
                                                </div>
                                            </div>
                                            
                                            <a href="#" class="book-btn" onclick="redirectToBooking('<?php echo htmlspecialchars($flight['airline']); ?>', '<?php echo htmlspecialchars($flight['flight_number']); ?>', <?php echo htmlspecialchars($flight['price']); ?>)">
                                                Select Flight
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <h2>Search for Flights</h2>
                    <p>Enter your travel details to find the best flight deals</p>
                    <a href="index.php">Go to Search</a>
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
        // JavaScript for filtering and sorting flights
        document.addEventListener('DOMContentLoaded', function() {
            // Price slider functionality
            const priceSlider = document.getElementById('price-slider');
            if (priceSlider) {
                priceSlider.addEventListener('input', function() {
                    const maxPrice = this.value;
                    document.querySelectorAll('.flight-card').forEach(card => {
                        const priceText = card.querySelector('.price-amount').textContent;
                        const price = parseInt(priceText.replace('$', ''));
                        
                        if (price > maxPrice) {
                            card.style.display = 'none';
                        } else {
                            card.style.display = 'block';
                        }
                    });
                });
            }
            
            // Reset filters
            const resetButton = document.querySelector('.reset-filters');
            if (resetButton) {
                resetButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Reset price slider
                    if (priceSlider) {
                        priceSlider.value = priceSlider.max;
                    }
                    
                    // Reset checkboxes
                    document.querySelectorAll('.filter-option input[type="checkbox"]').forEach(checkbox => {
                        checkbox.checked = true;
                    });
                    
                    // Show all flights
                    document.querySelectorAll('.flight-card').forEach(card => {
                        card.style.display = 'block';
                    });
                });
            }
        });
        
        // Function to redirect to booking
        function redirectToBooking(airline, flightNumber, price) {
            // In a real application, this would redirect to a booking page
            // For this demo, we'll just show an alert
            alert(`You selected ${airline} flight ${flightNumber} for $${price}. In a real application, you would be redirected to the booking page.`);
        }
    </script>
</body>
</html>
