<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$db_host = 'localhost';
$db_name = 'dbbxwfvewdhinh';
$db_user = 'uklz9ew3hrop3';
$db_pass = 'zyrbspyjlzjb';

$error = '';
$success = '';

// Process signup form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate input
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = 'Please fill in all fields';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Email already exists';
            } else {
                // Create users table if it doesn't exist
                $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) NOT NULL,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
                
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password]);
                
                $success = 'Account created successfully! You can now log in.';
            }
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - SkyCompare</title>
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
        
        /* Signup Form Styles */
        .signup-container {
            max-width: 600px;
            margin: 80px auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }
        
        .signup-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .signup-header h1 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 10px;
        }
        
        .signup-header p {
            color: #666;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            border-color: #0770e3;
            outline: none;
        }
        
        .terms {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .terms input {
            margin-right: 10px;
            margin-top: 5px;
        }
        
        .signup-btn {
            background-color: #0770e3;
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
        
        .signup-btn:hover {
            background-color: #0559b3;
            transform: translateY(-2px);
        }
        
        .signup-footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
        }
        
        .signup-footer a {
            color: #0770e3;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .signup-footer a:hover {
            color: #0559b3;
            text-decoration: underline;
        }
        
        .social-signup {
            margin-top: 30px;
            text-align: center;
        }
        
        .social-signup p {
            margin-bottom: 15px;
            color: #666;
            position: relative;
        }
        
        .social-signup p::before,
        .social-signup p::after {
            content: "";
            display: inline-block;
            width: 40%;
            height: 1px;
            background-color: #ddd;
            position: absolute;
            top: 50%;
        }
        
        .social-signup p::before {
            left: 0;
        }
        
        .social-signup p::after {
            right: 0;
        }
        
        .social-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #f5f5f5;
            color: #333;
            text-decoration: none;
            font-size: 1.2rem;
            transition: all 0.3s;
        }
        
        .social-btn:hover {
            transform: translateY(-3px);
        }
        
        .social-btn.facebook {
            background-color: #3b5998;
            color: white;
        }
        
        .social-btn.google {
            background-color: #db4437;
            color: white;
        }
        
        .social-btn.twitter {
            background-color: #1da1f2;
            color: white;
        }
        
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success-message {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        /* Password Strength Meter */
        .password-strength {
            margin-top: 5px;
            height: 5px;
            border-radius: 3px;
            background-color: #ddd;
            position: relative;
            overflow: hidden;
        }
        
        .password-strength-meter {
            height: 100%;
            width: 0;
            transition: width 0.3s, background-color 0.3s;
        }
        
        .password-strength-text {
            font-size: 0.8rem;
            margin-top: 5px;
            color: #666;
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
            
            .signup-container {
                width: 90%;
                padding: 30px;
                margin: 50px auto;
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
            </div>
        </div>
    </header>
    
    <!-- Signup Form -->
    <div class="container">
        <div class="signup-container">
            <div class="signup-header">
                <h1>Create an Account</h1>
                <p>Join SkyCompare to save your searches and get price alerts</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form action="signup.php" method="POST" id="signup-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a password" required>
                    <div class="password-strength">
                        <div class="password-strength-meter" id="password-meter"></div>
                    </div>
                    <div class="password-strength-text" id="password-text">Password strength</div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                </div>
                
                <div class="terms">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the <a href="#terms">Terms of Service</a> and <a href="#privacy">Privacy Policy</a></label>
                </div>
                
                <button type="submit" class="signup-btn">Create Account</button>
                
                <div class="signup-footer">
                    <p>Already have an account? <a href="login.php">Sign In</a></p>
                </div>
            </form>
            
            <div class="social-signup">
                <p>Or sign up with</p>
                <div class="social-buttons">
                    <a href="#" class="social-btn facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-btn google">
                        <i class="fab fa-google"></i>
                    </a>
                    <a href="#" class="social-btn twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2023 SkyCompare. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script>
        // JavaScript for form validation and password strength meter
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('signup-form');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordMeter = document.getElementById('password-meter');
            const passwordText = document.getElementById('password-text');
            
            // Password strength meter
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                let message = '';
                
                if (password.length >= 8) strength += 25;
                if (password.match(/[a-z]+/)) strength += 25;
                if (password.match(/[A-Z]+/)) strength += 25;
                if (password.match(/[0-9]+/)) strength += 25;
                
                passwordMeter.style.width = strength + '%';
                
                if (strength <= 25) {
                    passwordMeter.style.backgroundColor = '#f44336';
                    message = 'Weak';
                } else if (strength <= 50) {
                    passwordMeter.style.backgroundColor = '#ff9800';
                    message = 'Fair';
                } else if (strength <= 75) {
                    passwordMeter.style.backgroundColor = '#4caf50';
                    message = 'Good';
                } else {
                    passwordMeter.style.backgroundColor = '#2e7d32';
                    message = 'Strong';
                }
                
                passwordText.textContent = 'Password strength: ' + message;
            });
            
            // Form validation
            form.addEventListener('submit', function(event) {
                const username = document.getElementById('username').value;
                const email = document.getElementById('email').value;
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                const terms = document.getElementById('terms').checked;
                
                if (!username || !email || !password || !confirmPassword) {
                    event.preventDefault();
                    alert('Please fill in all fields');
                    return;
                }
                
                if (password !== confirmPassword) {
                    event.preventDefault();
                    alert('Passwords do not match');
                    return;
                }
                
                if (password.length < 6) {
                    event.preventDefault();
                    alert('Password must be at least 6 characters long');
                    return;
                }
                
                if (!terms) {
                    event.preventDefault();
                    alert('You must agree to the Terms of Service and Privacy Policy');
                    return;
                }
            });
        });
    </script>
</body>
</html>
