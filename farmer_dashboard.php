<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: farmer_Login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agromati";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get farmer details
$stmt = $conn->prepare("SELECT farmer_name, farmer_image FROM farmers WHERE farmer_id = ?");
$stmt->bind_param("i", $_SESSION['farmer_id']);
$stmt->execute();
$result = $stmt->get_result();
$farmer = $result->fetch_assoc();
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agromati - Farmer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/farmer_dashboard.css">
</head>
<body>
    <div class="menu-toggle" id="mobile-menu-toggle">
        <i class="fas fa-ellipsis-v"></i>
    </div>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <?php if (!empty($farmer['farmer_image'])): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($farmer['farmer_image']); ?>" alt="Profile" class="profile-img">
                <?php else: ?>
                    <img src="images/default-profile.png" alt="Profile" class="profile-img">
                <?php endif; ?>
                <h4 class="profile-name"><?php echo htmlspecialchars($farmer['farmer_name']); ?></h4>
                <div class="profile-role">Farmer</div>
            </div>
            
            <div class="sidebar-menu">
                <a href="farmer_dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="sell_post.php">
                    <i class="fas fa-plus-circle"></i>
                    <span>Sell Post</span>
                </a>
                <a href="farmer_profile.php">
                    <i class="fas fa-user"></i>
                    <span>Your Profile</span>
                </a>
                <a href="farmer_orders.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
                <a href="farmer_price_list.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Price List</span>
                </a>
                <a href="farmer_logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="page-title">
                    <h1>Farmer Dashboard</h1>
                </div>
                <a href="farmer_logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <!-- Dashboard Cards -->
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-seedling"></i> Today's Crop Prices</h3>
                    </div>
                    <div class="card-body">
                        <p>Check the latest market prices for your crops and get the best deals.</p>
                        <a href="price_list.php" class="btn btn-sm btn-primary">View Prices</a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-plus-circle"></i> Create Sell Post</h3>
                    </div>
                    <div class="card-body">
                        <p>List your crops for sale and connect directly with buyers in your area.</p>
                        <a href="sell_post.php" class="btn btn-sm btn-primary">Sell Now</a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-calendar-alt"></i> Seasonal Tips</h3>
                    </div>
                    <div class="card-body">
                        <p>Get expert advice on what to plant this season for maximum yield.</p>
                        <a href="#" class="btn btn-sm btn-primary">View Tips</a>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="recent-activity">
                <h2><i class="fas fa-history"></i> Recent Activity</h2>
                
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="activity-content">
                        <h4>New Order Received</h4>
                        <p>Buyer "Fresh Market Ltd" ordered 50kg of your rice</p>
                    </div>
                    <div class="activity-time">
                        2 hours ago
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="activity-content">
                        <h4>Crop Listed Successfully</h4>
                        <p>Your wheat crop is now visible to buyers</p>
                    </div>
                    <div class="activity-time">
                        1 day ago
                    </div>
                </div>
            </div>
            
            <!-- Crop Management Section -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-tractor"></i> Crop Management</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h4>Current Crops</h4>
                            <ul class="list-group">
                                <li class="list-group-item">Rice (Ready in 15 days)</li>
                                <li class="list-group-item">Wheat (Ready in 30 days)</li>
                                <li class="list-group-item">Potatoes (Harvested)</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h4>Upcoming Tasks</h4>
                            <ul class="list-group">
                                <li class="list-group-item">Irrigate rice field (Tomorrow)</li>
                                <li class="list-group-item">Apply fertilizer to wheat (Next week)</li>
                                <li class="list-group-item">Prepare land for next season</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h4>Weather Forecast</h4>
                            <div class="alert alert-info">
                                <strong>Next 3 days:</strong> Sunny with 10% chance of rain
                            </div>
                            <p>Ideal conditions for harvesting your wheat crop.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('mobile-menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.getElementById('mobile-menu-toggle');
            
            if (window.innerWidth <= 992 && 
                !sidebar.contains(event.target) && 
                event.target !== toggleBtn && 
                !toggleBtn.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>