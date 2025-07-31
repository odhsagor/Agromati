<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agromati";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$errors = [];
$success = false;

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $farmer_name = trim($_POST['farmer_name']);
    $farmer_email = trim($_POST['farmer_email']);
    $farmer_phone = trim($_POST['farmer_phone']);
    $farmer_address = trim($_POST['farmer_address']);
    $farmer_password = $_POST['farmer_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate image
    $farmer_image = null;
    if (isset($_FILES['farmer_image'])) {
        $image = $_FILES['farmer_image'];
        if ($image['error'] === UPLOAD_ERR_OK) {
            $farmer_image = file_get_contents($image['tmp_name']);
        }
    }
    
    // Validation
    if (empty($farmer_name)) {
        $errors['farmer_name'] = "Farmer name is required";
    }
    
    if (empty($farmer_email)) {
        $errors['farmer_email'] = "Email is required";
    } elseif (!filter_var($farmer_email, FILTER_VALIDATE_EMAIL)) {
        $errors['farmer_email'] = "Invalid email format";
    }
    
    if (empty($farmer_phone)) {
        $errors['farmer_phone'] = "Phone number is required";
    }
    
    if (empty($farmer_address)) {
        $errors['farmer_address'] = "Address is required";
    }
    
    if (empty($farmer_password)) {
        $errors['farmer_password'] = "Password is required";
    } elseif (strlen($farmer_password) < 8) {
        $errors['farmer_password'] = "Password must be at least 8 characters";
    }
    
    if ($farmer_password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match";
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT farmer_id FROM farmers WHERE farmer_email = ?");
    $stmt->bind_param("s", $farmer_email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $errors['farmer_email'] = "Email already registered";
    }
    $stmt->close();
    
    // If no errors, insert into database
    if (empty($errors)) {
        $hashed_password = password_hash($farmer_password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO farmers (farmer_name, farmer_email, farmer_phone, farmer_address, farmer_image, farmer_password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $farmer_name, $farmer_email, $farmer_phone, $farmer_address, $farmer_image, $hashed_password);
        
        if ($stmt->execute()) {
            $success = true;
            // Clear form
            $farmer_name = $farmer_email = $farmer_phone = $farmer_address = '';
        } else {
            $errors['database'] = "Error: " . $stmt->error;
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
    <title>Agromati - Farmer Registration</title>
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
                <a href="buyerRegister.php">Buyer Register</a>
                <a href="buyer_Login.php">Buyer Login</a>
                <a href="farmerRegister.php" class="active">Farmer Register</a>
                <a href="farmer_Login.php">Farmer Login</a>
                <a href="admin_login.php">Admin</a>
            </div>
        </div>
    </nav>

    <div class="registration-container">
        <div class="registration-header">
            <h2>Farmer Registration</h2>
        </div>
        
        <div class="registration-body">
            <?php if ($success): ?>
                <div class="success-message">
                    Registration successful! You can now <a href="farmer_Login.php">login</a>.
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="farmer_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control <?php echo isset($errors['farmer_name']) ? 'is-invalid' : ''; ?>" 
                                   id="farmer_name" name="farmer_name" 
                                   value="<?php echo htmlspecialchars($farmer_name ?? ''); ?>">
                            <?php if (isset($errors['farmer_name'])): ?>
                                <div class="error-message"><?php echo $errors['farmer_name']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="farmer_email" class="form-label">Email Address</label>
                            <input type="email" class="form-control <?php echo isset($errors['farmer_email']) ? 'is-invalid' : ''; ?>" 
                                   id="farmer_email" name="farmer_email" 
                                   value="<?php echo htmlspecialchars($farmer_email ?? ''); ?>">
                            <?php if (isset($errors['farmer_email'])): ?>
                                <div class="error-message"><?php echo $errors['farmer_email']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="farmer_phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control <?php echo isset($errors['farmer_phone']) ? 'is-invalid' : ''; ?>" 
                                   id="farmer_phone" name="farmer_phone" 
                                   value="<?php echo htmlspecialchars($farmer_phone ?? ''); ?>">
                            <?php if (isset($errors['farmer_phone'])): ?>
                                <div class="error-message"><?php echo $errors['farmer_phone']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="farmer_image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="farmer_image" name="farmer_image" accept="image/*">
                            <img id="imagePreview" src="#" alt="Preview" class="image-preview" style="display: none;">
                            <small class="text-muted">(Optional, max 2MB)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="farmer_password" class="form-label">Password</label>
                            <input type="password" class="form-control <?php echo isset($errors['farmer_password']) ? 'is-invalid' : ''; ?>" 
                                   id="farmer_password" name="farmer_password">
                            <?php if (isset($errors['farmer_password'])): ?>
                                <div class="error-message"><?php echo $errors['farmer_password']; ?></div>
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
                    <label for="farmer_address" class="form-label">Address</label>
                    <textarea class="form-control <?php echo isset($errors['farmer_address']) ? 'is-invalid' : ''; ?>" 
                              id="farmer_address" name="farmer_address" rows="3"><?php echo htmlspecialchars($farmer_address ?? ''); ?></textarea>
                    <?php if (isset($errors['farmer_address'])): ?>
                        <div class="error-message"><?php echo $errors['farmer_address']; ?></div>
                    <?php endif; ?>
                </div>
                
                <?php if (isset($errors['database'])): ?>
                    <div class="alert alert-danger"><?php echo $errors['database']; ?></div>
                <?php endif; ?>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-register">Register Now</button>
                </div>
                
                <div class="text-center mt-3">
                    <p>Already have an account? <a href="farmer_Login.php">Login here</a></p>
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
<?php
$conn->close();
?>