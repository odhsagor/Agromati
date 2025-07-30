<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Change to your MySQL username
$password = ""; // Change to your MySQL password
$dbname = "agromati";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$error = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        // Check buyer credentials including is_active status
        $stmt = $conn->prepare("SELECT buyer_id, buyer_name, buyer_password, is_active FROM buyers WHERE buyer_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $buyer = $result->fetch_assoc();
            
            // Check if account is suspended
            if ($buyer['is_active'] == 0) {
                $error = "Your account has been suspended by admin. Please contact support.";
            } elseif (password_verify($password, $buyer['buyer_password'])) {
                // Login successful
                $_SESSION['buyer_id'] = $buyer['buyer_id'];
                $_SESSION['buyer_name'] = $buyer['buyer_name'];
                $_SESSION['loggedin'] = true;
                
                // Redirect to dashboard
                header("Location: buyer_dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password";
            }
        } else {
            $error = "Invalid email or password";
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Login | Agromati</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --primary: #28a745;
            --primary-dark: #218838;
            --secondary: #ffc107;
            --dark: #343a40;
            --light: #f8f9fa;
            --white: #fff;
            --black: #000;
            --text: #333;
            --text-light: #6c757d;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #000000ff;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .login-container {
            max-width: 500px;
            margin: 80px auto;
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            flex: 1;
        }

        .login-header {
            background: var(--primary);
            color: var(--white);
            padding: 20px;
            text-align: center;
        }

        .login-header h2 {
            margin: 0;
            font-weight: 700;
        }

        .login-body {
            padding: 30px;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
        }

        .form-control {
            border-radius: 5px;
            padding: 10px 15px;
            border: 1px solid #ddd;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
        }

        .btn-login {
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: var(--primary-dark);
        }

        .nav-menu a.active {
            color: var(--primary);
            font-weight: 700;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875em;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- WhatsApp Button -->
    <a href="https://wa.me/88016123" class="whatsapp-btn" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>

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
                <a href="buyerRegister.php">Buyer Register</a>
                <a href="buyer_Login.php" class="active">Buyer Login</a>
                <a href="farmerRegister.php">Farmer Register</a>
                <a href="farmer_Login.php">Farmer Login</a>
                <a href="admin_login.php">Admin</a>
            </div>
        </div>
    </nav>

    <!-- Login Section -->
    <div class="login-container">
    <div class="login-header">
        <h2>Buyer Login</h2>
    </div>
    
    <div class="login-body">
        <?php if (isset($errors['login'])): ?>
            <div class="alert-danger">
                <?php echo $errors['login']; ?>
            </div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                       id="email" name="email" 
                       value="<?php echo htmlspecialchars($email ?? ''); ?>">
                <?php if (isset($errors['email'])): ?>
                    <div class="error-message"><?php echo $errors['email']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                       id="password" name="password">
                <?php if (isset($errors['password'])): ?>
                    <div class="error-message"><?php echo $errors['password']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-login">Login</button>
            </div>
            
            <div class="text-center mt-3">
                <p>Don't have an account? <a href="buyerRegister.php">Register here</a></p>
                <p><a href="forgot_password.php">Forgot password?</a></p>
            </div>
        </form>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile navigation toggle
        document.querySelector('.nav-toggle').addEventListener('click', function() {
            this.classList.toggle('active');
            document.querySelector('.nav-menu').classList.toggle('active');
        });

        // Back to top button
        window.addEventListener('scroll', function() {
            const backToTop = document.querySelector('.back-to-top');
            if (window.pageYOffset > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });

        document.querySelector('.back-to-top').addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
    </script>
</body>
</html>