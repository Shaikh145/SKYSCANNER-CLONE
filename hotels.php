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
$destination = isset($_GET['destination']) ? $_GET['destination'] : '';
$checkin = isset($_GET['checkin']) ? $_GET['checkin'] : '';
$checkout = isset($_GET['checkout']) ? $_GET['checkout'] : '';
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 1;

// Save search to database if user is logged in
if ($loggedIn && !empty($destination) && !empty($checkin) && !empty($checkout)) {
    try {
        // Create searches table if it doesn't exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS searches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            search_type VARCHAR(20) NOT NULL,
            origin VARCHAR(100),
            destination VARCHAR(100) NOT NULL,
            depart_date DATE,
            return_date DATE,
            passengers INT,
            checkin_date DATE,
            checkout_date DATE,
            guests INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Insert search
        $stmt = $pdo->prepare("INSERT INTO searches (user_id, search_type, destination, checkin_date, checkout_date, guests) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], 'hotel', $destination, $checkin, $checkout, $guests]);
    } catch (PDOException $e) {
        // Silently fail - don't interrupt user experience
    }
}

// Generate dummy hotel data
function generateHotels($destination, $count = 10) {
    $hotelNames = [
        'Grand Hotel', 'Luxury Suites', 'City View Inn', 'Ocean Breeze Resort', 
        'Mountain Lodge', 'Downtown Apartments', 'Riverside Hotel', 'Sunset Resort',
        'Royal Palace Hotel', 'Central Park Inn', 'Skyline Hotel', 'Harbor View Suites'
    ];
    
    $amenities = [
        'Free Wi-Fi', 'Swimming Pool', 'Fitness Center', 'Restaurant', 'Bar',
        'Room Service', 'Spa', 'Parking', 'Airport Shuttle', 'Business Center',
        'Breakfast Included', 'Air Conditioning'
    ];
    
    $hotels = [];
    
    for ($i = 0; $i < $count; $i++) {
        $name = $destination . ' ' . $hotelNames[array_rand($hotelNames)];
        
        // Random price between $50 and $500
        $price = rand(50, 500);
        
        // Random rating between 3.0 and 5.0
        $rating = round(rand(30, 50) / 10, 1);
        
        // Random number of reviews between 10 and 1000
        $reviews = rand(10, 1000);
        
        // Random selection of amenities (3-6)
        $hotelAmenities = [];
        $amenityCount = rand(3, 6);
        $amenityIndices = array_rand($amenities, $amenityCount);
        if (!is_array($amenityIndices)) {
            $amenityIndices = [$amenityIndices];
        }
        foreach ($amenityIndices as $index) {
            $hotelAmenities[] = $amenities[$index];
        }
        
        $hotels[] = [
            'name' => $name,
            'destination' => $destination,
            'address' => rand(100, 999) . ' Main Street, ' . $destination,
            'price' => $price,
            'rating' => $rating,
            'reviews' => $reviews,
            'amenities' => $hotelAmenities,
            'image_index' => rand(1, 5) // For demo purposes, we'll use 5 different placeholder images
        ];
    }
    
    // Sort by rating (highest first)
    usort($hotels, function($a, $b) {
        return $b['rating'] - $a['rating'];
    });
    
    return $hotels;
}

$hotels = [];

if (!empty($destination) && !empty($checkin) && !empty($checkout)) {
    $hotels = generateHotels($destination);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Search Results - SkyCompare</title>
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
        
        .hotels-list {
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
        
        .hotel-card {
            background-color: white;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            overflow: hidden;
        }
        
        .hotel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .hotel-image {
            width: 250px;
            background-size: cover;
            background-position: center;
        }
        
        .hotel-details {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .hotel-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .hotel-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .hotel-address {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .hotel-rating {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .rating-stars {
            color: #ffc107;
            margin-right: 10px;
        }
        
        .rating-number {
            font-weight: 600;
            color: #333;
        }
        
        .rating-count {
            color: #666;
            font-size: 0.9rem;
        }
        
        .hotel-amenities {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
            margin-bottom: 15px;
        }
        
        .amenity {
            display: flex;
            align-items: center;
            background-color: #f5f5f5;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9rem;
            color: #555;
        }
        
        .amenity i {
            margin-right: 5px;
            color: #0770e3;
        }
        
        .hotel-price {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        
        .price-info {
            text-align: right;
        }
        
        .price-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: #00a698;
        }
        
        .price-per-night {
            font-size: 0.8rem;
            color: #666;
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
            
            .hotel-card {
                flex-direction: column;
            }
            
            .hotel-image {
                width: 100%;
                height: 200px;
            }
            
            .hotel-price {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .price-info {
                text-align: left;
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
            <?php if (!empty($destination) && !empty($checkin) && !empty($checkout)): ?>
                <div class="search-summary">
                    <h1>Hotel Search Results</h1>
                    <p>Showing hotels in <?php echo htmlspecialchars($destination); ?></p>
                    
                    <div class="search-details">
                        <div class="search-detail">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Check-in: <?php echo htmlspecialchars($checkin); ?></span>
                        </div>
                        
                        <div class="search-detail">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Check-out: <?php echo htmlspecialchars($checkout); ?></span>
                        </div>
                        
                        <div class="search-detail">
                            <i class="fas fa-user"></i>
                            <span><?php echo htmlspecialchars($guests); ?> Guest<?php echo $guests > 1 ? 's' : ''; ?></span>
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
                                    <input type="range" id="price-slider" min="0" max="500" value="500">
                                    <div class="price-labels">
                                        <span>$0</span>
                                        <span>$500</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <h3>
                                Star Rating
                                <i class="fas fa-chevron-down"></i>
                            </h3>
                            <div class="filter-options">
                                <div class="filter-option">
                                    <input type="checkbox" id="rating-5" checked>
                                    <label for="rating-5">5 Stars</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="rating-4" checked>
                                    <label for="rating-4">4 Stars</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="rating-3" checked>
                                    <label for="rating-3">3 Stars</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="rating-2" checked>
                                    <label for="rating-2">2 Stars</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="rating-1" checked>
                                    <label for="rating-1">1 Star</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <h3>
                                Amenities
                                <i class="fas fa-chevron-down"></i>
                            </h3>
                            <div class="filter-options">
                                <div class="filter-option">
                                    <input type="checkbox" id="amenity-wifi" checked>
                                    <label for="amenity-wifi">Free Wi-Fi</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="amenity-pool" checked>
                                    <label for="amenity-pool">Swimming Pool</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="amenity-breakfast" checked>
                                    <label for="amenity-breakfast">Breakfast Included</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="amenity-fitness" checked>
                                    <label for="amenity-fitness">Fitness Center</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="amenity-parking" checked>
                                    <label for="amenity-parking">Free Parking</label>
                                </div>
                            </div>
                        </div>
                        
                        <a href="#" class="reset-filters">Reset All Filters</a>
                    </div>
                    
                    <!-- Hotels List -->
                    <div class="hotels-list">
                        <!-- Sort Options -->
                        <div class="sort-options">
                            <div class="sort-by">
                                <label for="sort-select">Sort by:</label>
                                <select id="sort-select">
                                    <option value="recommended">Recommended</option>
                                    <option value="price-low">Price (Lowest first)</option>
                                    <option value="price-high">Price (Highest first)</option>
                                    <option value="rating">Rating (Highest first)</option>
                                </select>
                            </div>
                            
                            <div class="results-count">
                                <?php echo count($hotels); ?> results found
                            </div>
                        </div>
                        
                        <!-- Hotel Cards -->
                        <?php foreach ($hotels as $hotel): ?>
                            <div class="hotel-card">
                                <div class="hotel-image" style="background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80');"></div>
                                <div class="hotel-details">
                                    <div class="hotel-header">
                                        <div>
                                            <h3 class="hotel-name"><?php echo htmlspecialchars($hotel['name']); ?></h3>
                                            <p class="hotel-address"><?php echo htmlspecialchars($hotel['address']); ?></p>
                                            
                                            <div class="hotel-rating">
                                                <div class="rating-stars">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <?php if ($i <= floor($hotel['rating'])): ?>
                                                            <i class="fas fa-star"></i>
                                                        <?php elseif ($i - 0.5 <= $hotel['rating']): ?>
                                                            <i class="fas fa-star-half-alt"></i>
                                                        <?php else: ?>
                                                            <i class="far fa-star"></i>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                </div>
                                                <span class="rating-number"><?php echo htmlspecialchars($hotel['rating']); ?></span>
                                                <span class="rating-count">(<?php echo htmlspecialchars($hotel['reviews']); ?> reviews)</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="hotel-amenities">
                                        <?php foreach ($hotel['amenities'] as $amenity): ?>
                                            <div class="amenity">
                                                <?php 
                                                $icon = 'fas fa-check';
                                                if (strpos($amenity, 'Wi-Fi') !== false) $icon = 'fas fa-wifi';
                                                elseif (strpos($amenity, 'Pool') !== false) $icon = 'fas fa-swimming-pool';
                                                elseif (strpos($amenity, 'Fitness') !== false) $icon = 'fas fa-dumbbell';
                                                elseif (strpos($amenity, 'Breakfast') !== false) $icon = 'fas fa-coffee';
                                                elseif (strpos($amenity, 'Parking') !== false) $icon = 'fas fa-parking';
                                                elseif (strpos($amenity, 'Air') !== false) $icon = 'fas fa-wind';
                                                ?>
                                                <i class="<?php echo $icon; ?>"></i>
                                                <span><?php echo htmlspecialchars($amenity); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="hotel-price">
                                        <div class="price-info">
                                            <div class="price-amount">$<?php echo htmlspecialchars($hotel['price']); ?></div>
                                            <div class="price-per-night">per night</div>
                                        </div>
                                        
                                        <a href="#" class="book-btn" onclick="redirectToBooking('<?php echo htmlspecialchars($hotel['name']); ?>', <?php echo htmlspecialchars($hotel['price']); ?>)">
                                            View Deal
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <h2>Search for Hotels</h2>
                    <p>Enter your destination and dates to find the best hotel deals</p>
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
        // JavaScript for filtering and sorting hotels
        document.addEventListener('DOMContentLoaded', function() {
            // Price slider functionality
            const priceSlider = document.getElementById('price-slider');
            if (priceSlider) {
                priceSlider.addEventListener('input', function() {
                    const maxPrice = this.value;
                    document.querySelectorAll('.hotel-card').forEach(card => {
                        const priceText = card.querySelector('.price-amount').textContent;
                        const price = parseInt(priceText.replace('$', ''));
                        
                        if (price > maxPrice) {
                            card.style.display = 'none';
                        } else {
                            card.style.display = 'flex';
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
                    
                    // Show all hotels
                    document.querySelectorAll('.hotel-card').forEach(card => {
                        card.style.display = 'flex';
                    });
                });
            }
        });
        
        // Function to redirect to booking
        function redirectToBooking(hotelName, price) {
            // In a real application, this would redirect to a booking page
            // For this demo, we'll just show an alert
            alert(`You selected ${hotelName} for $${price} per night. In a real application, you would be redirected to the booking page.`);
        }
    </script>
</body>
</html>
