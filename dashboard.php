<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

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

$username = $_SESSION['username'];
$userId = $_SESSION['user_id'];

// Get user's saved searches
$searches = [];
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
    
    $stmt = $pdo->prepare("SELECT * FROM searches WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$userId]);
    $searches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error silently
}

// Delete search if requested
if (isset($_GET['delete_search']) && !empty($_GET['delete_search'])) {
    $searchId = (int)$_GET['delete_search'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM searches WHERE id = ? AND user_id = ?");
        $stmt->execute([$searchId, $userId]);
        
        // Redirect to remove the query parameter
        header("Location: dashboard.php");
        exit();
    } catch (PDOException $e) {
        // Handle error silently
    }
}

// Generate dummy bookings
function generateBookings($count = 5) {
    $bookings = [];
    $types = ['flight', 'hotel'];
    $airlines = ['SkyAir', 'GlobalWings', 'AeroJet', 'StarFly', 'BlueSky'];
    $hotels = ['Grand Hotel', 'Luxury Suites', 'City View Inn', 'Ocean Breeze Resort', 'Mountain Lodge'];
    $destinations = ['New York', 'Paris', 'Tokyo', 'London', 'Sydney', 'Rome', 'Dubai', 'Bangkok'];
    
    for ($i = 0; $i < $count; $i++) {
        $type = $types[array_rand($types)];
        $destination = $destinations[array_rand($destinations)];
        
        if ($type === 'flight') {
            $airline = $airlines[array_rand($airlines)];
            $flightNumber = strtoupper(substr($airline, 0, 2)) . rand(1000, 9999);
            
            // Random dates within the next 6 months
            $departDate = date('Y-m-d', strtotime('+' . rand(1, 180) . ' days'));
            $returnDate = date('Y-m-d', strtotime($departDate . ' +' . rand(1, 14) . ' days'));
            
            $bookings[] = [
                'id' => rand(10000, 99999),
                'type' => 'flight',
                'title' => $airline . ' - ' . $flightNumber,
                'destination' => 'From: New York To: ' . $destination,
                'date' => 'Depart: ' . $departDate . ' Return: ' . $returnDate,
                'price' => rand(200, 1500),
                'status' => rand(0, 10) > 2 ? 'Confirmed' : 'Pending'
            ];
        } else {
            $hotel = $hotels[array_rand($hotels)];
            
            // Random dates within the next 6 months
            $checkinDate = date('Y-m-d', strtotime('+' . rand(1, 180) . ' days'));
            $checkoutDate = date('Y-m-d', strtotime($checkinDate . ' +' . rand(1, 7) . ' days'));
            
            $bookings[] = [
                'id' => rand(10000, 99999),
                'type' => 'hotel',
                'title' => $destination . ' ' . $hotel,
                'destination' => $destination,
                'date' => 'Check-in: ' . $checkinDate . ' Check-out: ' . $checkoutDate,
                'price' => rand(100, 1000),
                'status' => rand(0, 10) > 2 ? 'Confirmed' : 'Pending'
            ];
        }
    }
    
    return $bookings;
}

$bookings = generateBookings();

// Generate dummy price alerts
function generatePriceAlerts($count = 3) {
    $alerts = [];
    $destinations = ['New York', 'Paris', 'Tokyo', 'London', 'Sydney', 'Rome', 'Dubai', 'Bangkok'];
    
    for ($i = 0; $i < $count; $i++) {
        $from = 'New York';
        $to = $destinations[array_rand($destinations)];
        
        // Random dates within the next 6 months
        $departDate = date('Y-m-d', strtotime('+' . rand(1, 180) . ' days'));
        $returnDate = date('Y-m-d', strtotime($departDate . ' +' . rand(1, 14) . ' days'));
        
        $oldPrice = rand(500, 1500);
        $newPrice = $oldPrice - rand(50, 300);
        $percentChange = round(($oldPrice - $newPrice) / $oldPrice * 100);
        
        $alerts[] = [
            'id' => rand(10000, 99999),
            'from' => $from,
            'to' => $to,
            'dates' => $departDate . ' - ' . $returnDate,
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'percent_change' => $percentChange,
            'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 48) . ' hours'))
        ];
    }
    
    return $alerts;
}

$priceAlerts = generatePriceAlerts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SkyCompare</title>
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
        
        /* Dashboard Styles */
        .dashboard {
            padding: 40px 0;
        }
        
        .dashboard-header {
            margin-bottom: 30px;
        }
        
        .dashboard-header h1 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 10px;
        }
        
        .dashboard-header p {
            color: #666;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 30px;
        }
        
        .dashboard-main {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        
        .dashboard-sidebar {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        
        .dashboard-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .dashboard-card-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .dashboard-card-header h2 {
            font-size: 1.3rem;
            color: #333;
        }
        
        .dashboard-card-header a {
            color: #0770e3;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .dashboard-card-header a:hover {
            color: #0559b3;
            text-decoration: underline;
        }
        
        .dashboard-card-content {
            padding: 20px;
        }
        
        .dashboard-card-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
            text-align: center;
        }
        
        .dashboard-card-footer a {
            color: #0770e3;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .dashboard-card-footer a:hover {
            color: #0559b3;
            text-decoration: underline;
        }
        
        /* Bookings */
        .bookings-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .booking-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-radius: 8px;
            background-color: #f9f9f9;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .booking-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .booking-info {
            flex: 1;
        }
        
        .booking-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .booking-details {
            color: #666;
            font-size: 0.9rem;
        }
        
        .booking-price {
            font-weight: 600;
            color: #00a698;
            margin-right: 20px;
        }
        
        .booking-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .booking-status.confirmed {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .booking-status.pending {
            background-color: #fff8e1;
            color: #f57c00;
        }
        
        .booking-type {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: #e3f2fd;
            color: #0770e3;
            border-radius: 50%;
            margin-right: 15px;
        }
        
        .booking-type.hotel {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .booking-left {
            display: flex;
            align-items: center;
        }
        
        .booking-right {
            display: flex;
            align-items: center;
        }
        
        /* Saved Searches */
        .searches-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .search-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-radius: 8px;
            background-color: #f9f9f9;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .search-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .search-info {
            flex: 1;
        }
        
        .search-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .search-details {
            color: #666;
            font-size: 0.9rem;
        }
        
        .search-date {
            color: #666;
            font-size: 0.8rem;
        }
        
        .search-actions {
            display: flex;
            gap: 10px;
        }
        
        .search-action {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 4px;
            background-color: #f5f5f5;
            color: #555;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
        }
        
        .search-action:hover {
            background-color: #e0e0e0;
            color: #333;
        }
        
        .search-action.delete:hover {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .search-type {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: #e3f2fd;
            color: #0770e3;
            border-radius: 50%;
            margin-right: 15px;
        }
        
        .search-type.hotel {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .search-left {
            display: flex;
            align-items: center;
        }
        
        /* Price Alerts */
        .alerts-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .alert-item {
            padding: 15px;
            border-radius: 8px;
            background-color: #f9f9f9;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .alert-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .alert-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .alert-title {
            font-weight: 600;
            color: #333;
        }
        
        .alert-date {
            color: #666;
            font-size: 0.8rem;
        }
        
        .alert-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .alert-route {
            display: flex;
            align-items: center;
            color: #555;
        }
        
        .alert-route i {
            margin: 0 10px;
            color: #0770e3;
        }
        
        .alert-dates {
            color: #666;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        .alert-price {
            text-align: right;
        }
        
        .alert-price-old {
            text-decoration: line-through;
            color: #666;
            font-size: 0.9rem;
        }
        
        .alert-price-new {
            font-weight: 600;
            color: #00a698;
            font-size: 1.1rem;
        }
        
        .alert-price-change {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            background-color: #e8f5e9;
            color: #2e7d32;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 5px;
        }
        
        /* User Profile */
        .user-profile {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        
        .user-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #e3f2fd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0770e3;
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .user-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .user-email {
            color: #666;
            margin-bottom: 20px;
        }
        
        .user-stats {
            display: flex;
            justify-content: space-around;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .user-stat {
            text-align: center;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: #0770e3;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .user-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
        }
        
        .user-action {
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 4px;
            background-color: #f5f5f5;
            color: #555;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
        }
        
        .user-action:hover {
            background-color: #e0e0e0;
            color: #333;
        }
        
        .user-action i {
            margin-right: 10px;
        }
        
        /* Quick Links */
        .quick-links {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .quick-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            border-radius: 8px;
            background-color: #f9f9f9;
            text-decoration: none;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .quick-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .quick-link i {
            font-size: 2rem;
            color: #0770e3;
            margin-bottom: 10px;
        }
        
        .quick-link span {
            color: #333;
            font-weight: 500;
            text-align: center;
        }
        
        /* Empty State */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px;
            text-align: center;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        .empty-state h3 {
            font-size: 1.2rem;
            color: #555;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #666;
            margin-bottom: 20px;
        }
