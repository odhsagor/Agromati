<?php
include('db.php');
// Initialize variables
$email = $password = '';
$errors = array();

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate inputs
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validation
    if (empty($email)) $errors[] = "Email is required";
    if (empty($password)) $errors[] = "Password is required";

    // If no errors, authenticate user
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, name, email, password, user_type FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // Redirect based on user type
                if ($user['user_type'] == 'farmer') {
                    header("Location: farmer_dashboard.php");
                } else {
                    header("Location: buyer_dashboard.php");
                }
                exit();
            } else {
                $errors[] = "Invalid email or password";
            }
        } else {
            $errors[] = "Invalid email or password";
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
    <title>Login - Agromati</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">



    <style>
    /* Login Container */
        .login-container {
            padding: 6rem 0 3rem;
            display: flex;
            justify-content: center;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('images/farm-background.png') no-repeat center center/cover;
        }

        .login-card {
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
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
        .login-form {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1.5rem 0;
        }

        .form-check {
            display: flex;
            align-items: center;
        }

        .form-check input {
            margin-right: 0.5rem;
        }

        .forgot-link {
            color: var(--primary);
            text-decoration: none;
        }

        .forgot-link:hover {
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

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-light);
        }

        .register-link a {
            color: var(--primary);
            text-decoration: none;
        }

        .register-link a:hover {
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
                <a href="register.php">Register</a>
                <a href="login.php" class="active">Login</a>
                <a href="admin_login.php">Admin</a>
            </div>
        </div>
    </nav>

    <!-- Login Form -->
    <div class="login-container">
        <div class="login-card">
            <div class="card-header">
                <h2>Login to Your Account</h2>
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

            <form class="login-form" action="login.php" method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <div class="form-options">
                    <div class="form-check">
                        <input type="checkbox" id="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="forgot_password.php" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="submit-btn">Login</button>
            </form>

            <div class="register-link">
                Don't have an account? <a href="register.php">Register here</a>
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