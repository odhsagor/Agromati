<?php
include('db.php');

// Initialize variables
$name = $email = $phone = $address = $user_type = '';
$errors = array();

// Process registration form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize inputs
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = trim($_POST['address']);
    $user_type = $_POST['user_type'];

    // Validation
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($phone)) $errors[] = "Phone number is required";
    if (empty($password)) $errors[] = "Password is required";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    if (empty($user_type)) $errors[] = "Please select user type";

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $errors[] = "Email already exists";
    }
    $stmt->close();

    // Register user if no errors
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, user_type, address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $phone, $hashed_password, $user_type, $address);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Registration successful! Please login.";
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Agromati</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

    <style>
        /* Registration Container */
        .register-container {
            padding: 6rem 0 3rem;
            display: flex;
            justify-content: center;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('images/farm-background.png') no-repeat center center/cover;
        }

        .register-card {
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            overflow: hidden;
        }

        .card-header {
            background-color: var(--primary);
            color: var(--white);
            padding: 1.5rem;
            text-align: center;
        }

        .card-header h2 {
            font-size: 1.8rem;
        }

        /* Form Styles */
        .register-form {
            padding: 2rem;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            flex: 1;
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
        }

        .form-group textarea {
            min-height: 80px;
        }

        .form-check {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
        }

        .form-check input {
            margin-right: 0.5rem;
        }

        .terms-link {
            color: var(--primary);
            text-decoration: none;
        }

        .terms-link:hover {
            text-decoration: underline;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background-color: var(--primary-dark);
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-light);
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        /* Error Box */
        .error-box {
            background-color: #f8d7da;
            color: #721c24;
            padding: 1rem;
            margin: 0 2rem 1rem;
            border-radius: 5px;
        }

        .error-box ul {
            margin-bottom: 0;
            padding-left: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="main-nav">
        <div class="container">
            <a class="logo" href="index.php">
                <img src="images/logo.png" alt="Agromati">
            </a>
            <button class="nav-toggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="nav-menu">
                <a href="index.php">Home</a>
                <a href="prices.html">Crop Prices</a>
                <a href="register.php" class="active">Register</a>
                <a href="login.php">Login</a>
                <a href="admin_login.php">Admin</a>
            </div>
        </div>
    </nav>

    <!-- Registration Form -->
    <div class="register-container">
        <div class="register-card">
            <div class="card-header">
                <h2>Create Your Account</h2>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="register-form" action="register.php" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>I am a</label>
                        <select name="user_type" required>
                            <option value="">Select Type</option>
                            <option value="farmer" <?php echo ($user_type == 'farmer') ? 'selected' : ''; ?>>Farmer</option>
                            <option value="buyer" <?php echo ($user_type == 'buyer') ? 'selected' : ''; ?>>Buyer</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="2"><?php echo htmlspecialchars($address); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                </div>

                <div class="form-check">
                    <input type="checkbox" id="terms" required>
                    <label for="terms">I agree to the <a href="#" class="terms-link">Terms</a></label>
                </div>

                <button type="submit" class="submit-btn">Register Now</button>
            </form>

            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <img src="images/logo.png" alt="Agromati">
                    <p>Empowering Bangladeshi farmers with fair prices and direct market access since 2023.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-whatsapp"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h5>Quick Links</h5>
                    <a href="index.php">Home</a>
                    <a href="prices.html">Crop Prices</a>
                    <a href="sell.html">Sell Crops</a>
                    <a href="buy.html">Buy Crops</a>
                    <a href="blog.html">Blog</a>
                </div>
                
                <div class="footer-col">
                    <h5>Resources</h5>
                    <a href="#">Selling Guide</a>
                    <a href="#">FAQ</a>
                    <a href="#">Agriculture Tips</a>
                    <a href="#">Market Trends</a>
                </div>
                
                <div class="footer-col">
                    <h5>Contact Us</h5>
                    <p><i class="fas fa-map-marker-alt"></i> Farmgate, Dhaka 1215, Bangladesh</p>
                    <p><i class="fas fa-phone"></i> Helpline: 16123</p>
                    <p><i class="fas fa-envelope"></i> support@agromati.com.bd</p>
                    <p><i class="fas fa-clock"></i> Sat-Thu: 8:00 AM - 6:00 PM</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2023 Agromati. All rights reserved.</p>
                <div>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top -->
    <a href="#" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>