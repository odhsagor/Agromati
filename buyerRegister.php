<?php
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
$success = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['buyer_name'];
    $email = $_POST['buyer_email'];
    $phone = $_POST['buyer_phone'];
    $address = $_POST['buyer_address'];
    $password = password_hash($_POST['buyer_password'], PASSWORD_DEFAULT);
    
    // Handle image upload
    $image = null;
    if (isset($_FILES['buyer_image']) && $_FILES['buyer_image']['error'] == 0) {
        $image = file_get_contents($_FILES['buyer_image']['tmp_name']);
    }
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO buyers (buyer_name, buyer_email, buyer_phone, buyer_address, buyer_image, buyer_password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $phone, $address, $image, $password);
    
    if ($stmt->execute()) {
        $success = "Registration successful! You can now login.";
    } else {
        $error = "Error: " . $stmt->error;
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Registration | Agromati</title>
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
        }

        .registration-container {
            max-width: 800px;
            margin: 80px auto;
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .registration-header {
            background: var(--primary);
            color: var(--white);
            padding: 20px;
            text-align: center;
        }

        .registration-header h2 {
            margin: 0;
            font-weight: 700;
        }

        .registration-body {
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

        .btn-register {
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }

        .btn-register:hover {
            background: var(--primary-dark);
        }

        .image-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
            display: block;
            margin: 10px auto;
        }

        .nav-menu a.active {
            color: var(--primary);
            font-weight: 700;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875em;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
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
                <a href="buyerRegister.php" class="active">Buyer Register</a>
                <a href="buyer_Login.php">Buyer Login</a>
                <a href="farmerRegister.php">Farmer Register</a>
                <a href="farmer_Login.php">Farmer Login</a>
                <a href="admin_login.php">Admin</a>
            </div>
        </div>
    </nav>

    <!-- Registration Form Section -->
        <div class="registration-container">
    <div class="registration-header">
        <h2>Buyer Registration</h2>
    </div>
    
    <div class="registration-body">
        <?php if ($success): ?>
            <div class="success-message">
                Registration successful! You can now <a href="buyer_Login.php">login</a>.
            </div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="buyer_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control <?php echo isset($errors['buyer_name']) ? 'is-invalid' : ''; ?>" 
                               id="buyer_name" name="buyer_name" 
                               value="<?php echo htmlspecialchars($buyer_name ?? ''); ?>">
                        <?php if (isset($errors['buyer_name'])): ?>
                            <div class="error-message"><?php echo $errors['buyer_name']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="buyer_email" class="form-label">Email Address</label>
                        <input type="email" class="form-control <?php echo isset($errors['buyer_email']) ? 'is-invalid' : ''; ?>" 
                               id="buyer_email" name="buyer_email" 
                               value="<?php echo htmlspecialchars($buyer_email ?? ''); ?>">
                        <?php if (isset($errors['buyer_email'])): ?>
                            <div class="error-message"><?php echo $errors['buyer_email']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="buyer_phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control <?php echo isset($errors['buyer_phone']) ? 'is-invalid' : ''; ?>" 
                               id="buyer_phone" name="buyer_phone" 
                               value="<?php echo htmlspecialchars($buyer_phone ?? ''); ?>">
                        <?php if (isset($errors['buyer_phone'])): ?>
                            <div class="error-message"><?php echo $errors['buyer_phone']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="farmer_image" class="form-label">Profile Image</label>
                        <input type="file" class="form-control" id="buyer_image" name="buyer_image" accept="image/*">
                        <img id="imagePreview" src="#" alt="Preview" class="image-preview" style="display: none;">
                        <small class="text-muted">(Optional, max 2MB)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="buyer_password" class="form-label">Password</label>
                        <input type="password" class="form-control <?php echo isset($errors['buyer_password']) ? 'is-invalid' : ''; ?>" 
                               id="buyer_password" name="buyer_password">
                        <?php if (isset($errors['buyer_password'])): ?>
                            <div class="error-message"><?php echo $errors['buyer_password']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                               id="confirm_password" name="confirm_password">
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="error-message"><?php echo $errors['confirm_password']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="buyer_address" class="form-label">Address</label>
                <textarea class="form-control <?php echo isset($errors['buyer_address']) ? 'is-invalid' : ''; ?>" 
                          id="buyer_address" name="buyer_address" rows="3"><?php echo htmlspecialchars($buyer_address ?? ''); ?></textarea>
                <?php if (isset($errors['buyer_address'])): ?>
                    <div class="error-message"><?php echo $errors['buyer_address']; ?></div>
                <?php endif; ?>
            </div>
            
            <?php if (isset($errors['database'])): ?>
                <div class="alert alert-danger"><?php echo $errors['database']; ?></div>
            <?php endif; ?>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-register">Register Now</button>
            </div>
            
            <div class="text-center mt-3">
                <p>Already have an account? <a href="buyer_Login.php">Login here</a></p>
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
                    <p>Empowering Bangladeshi farmers with fair prices and direct market access since 2025.</p>
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
                <p>&copy; 2025 Agromati. All rights reserved.</p>
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
    <script>
        // Image preview functionality
        document.getElementById('farmer_image').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

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