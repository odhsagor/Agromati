<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agromati - Digital Farming Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

    <style>
        .nav-menu a.active {
            color: var(--primary);
            font-weight: 700;
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
                <a href="index.php" class="active">Home</a>
                <a href="buyerRegister.php">Buyer Register</a>
                <a href="buyer_Login.php"> Buyer Login</a>
                <a href="farmerRegister.php"> farmer Register</a>
                <a href="farmer_Login.php"> Farmer Login</a>
                <a href="admin_login.php">Admin</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Sell Your Crops at <span>Best Prices</span></h1>
            <p>Direct from Farm to Market - Zero Middlemen</p>
            <div class="hero-btns">
                <a href="prices.html" class="btn-main">
                    <i class="fas fa-chart-line"></i> Check Today's Prices
                </a>
                <a href="register.php" class="btn-secondary">
                    <i class="fas fa-user-plus"></i> Register Now
                </a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <h3 class="counter" data-target="10000">0</h3>
                    <p>Farmers Connected</p>
                </div>
                <div class="stat-card">
                    <h3 class="counter" data-target="2500">0</h3>
                    <p>Buyers Registered</p>
                </div>
                <div class="stat-card">
                    <h3 class="counter" data-target="500">0</h3>
                    <p>Daily Transactions</p>
                </div>
                <div class="stat-card">
                    <h3 class="counter" data-target="20">0</h3>
                    <p>Crop Categories</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2>Why Farmers <span>Choose</span> Agromati</h2>
            <p class="subtitle">We're revolutionizing agriculture in Bangladesh with technology</p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h4>Live Price Tracker</h4>
                    <p>Real-time crop prices for your region with historical trends</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h4>Direct Buyer Connect</h4>
                    <p>Negotiate directly with businesses without intermediaries</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h4>Logistics Support</h4>
                    <p>Arrange transport easily with our trusted partners</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-sms"></i>
                    </div>
                    <h4>SMS Alerts</h4>
                    <p>Get updates without internet in your local language</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Video Section -->
    <section class="video-section">
        <div class="container">
            <div class="video-content">
                <div class="video-text">
                    <h2>See How <span>Agromati</span> Works</h2>
                    <p>Our platform connects farmers directly with buyers, eliminating middlemen and ensuring fair prices.</p>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Simple registration process</li>
                        <li><i class="fas fa-check-circle"></i> Easy crop listing with photos</li>
                        <li><i class="fas fa-check-circle"></i> Secure payment system</li>
                        <li><i class="fas fa-check-circle"></i> Transparent transaction history</li>
                    </ul>
                    <a href="register.php" class="btn-main">Get Started Now</a>
                </div>
                <div class="video-box">
                    <img src="images/video-thumbnail.jpg" alt="How Agromati Works">
                    <div class="play-btn">
                        <i class="fas fa-play"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <div class="container">
            <h2>Trusted by <span>10,000+</span> Farmers</h2>
            
            <div class="testimonial-slider">
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <img src="images/farmers/farmer1.jpg" alt="Farmer">
                        <div>
                            <h5>Rojina Begum</h5>
                            <small>Bogura, Bangladesh</small>
                        </div>
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p>"Got 20% better prices than local market for my wheat crop through Agromati. The process was so simple!"</p>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <img src="images/farmers/farmer2.jpg" alt="Farmer">
                        <div>
                            <h5>Abdul Karim</h5>
                            <small>Rangpur, Bangladesh</small>
                        </div>
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                    <p>"Direct buyers means no commission and timely payments. Received my payment within 24 hours of delivery."</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Partners -->
    <section class="partners">
        <div class="container">
            <h4>Our <span>Trusted</span> Partners</h4>
            <div class="partners-grid">
                <img src="images/ministry-of-agriculture.jpg" alt="Ministry of Agriculture">
                <img src="images/badc.png" alt="BADC">
                <img src="images/dae.jpeg" alt="DAE">
                <img src="images/brac.png" alt="BRAC">
                <img src="images/undp.png" alt="UNDP">
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter">
        <div class="container">
            <h3>Subscribe to Our <span>Newsletter</span></h3>
            <p>Get the latest updates on crop prices, farming tips, and special offers</p>
            <form class="newsletter-form">
                <input type="email" placeholder="Your email address" required>
                <button type="submit" class="btn-main">Subscribe</button>
            </form>
        </div>
    </section>

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