<?php
session_start();

// Check if farmer is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: farmer_Login.php");
    exit();
}

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
            background-color: #f5f5f5;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: var(--white);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100%;
            padding: 20px 0;
            transition: all 0.3s;
        }

        .sidebar-header {
            text-align: center;
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .profile-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
            margin: 0 auto 15px;
        }

        .profile-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .profile-role {
            color: var(--primary);
            font-size: 0.9rem;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--text);
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover, 
        .sidebar-menu a.active {
            background: rgba(40, 167, 69, 0.1);
            color: var(--primary);
            border-left: 3px solid var(--primary);
        }

        .sidebar-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: var(--white);
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .page-title h1 {
            font-size: 1.5rem;
            margin-bottom: 0;
            color: var(--primary);
        }

        .logout-btn {
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: var(--primary-dark);
        }

        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            padding: 15px 20px;
            background: rgba(40, 167, 69, 0.1);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-header h3 {
            font-size: 1.2rem;
            margin-bottom: 0;
            color: var(--primary);
        }

        .card-body {
            padding: 20px;
        }

        /* Recent Activity */
        .recent-activity {
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 30px;
        }

        .activity-item {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(40, 167, 69, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--primary);
        }

        .activity-content h4 {
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .activity-content p {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        .activity-time {
            color: var(--text-light);
            font-size: 0.8rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
                overflow: hidden;
            }
            
            .sidebar-header .profile-name,
            .sidebar-menu a span {
                display: none;
            }
            
            .sidebar-menu a {
                justify-content: center;
                padding: 12px 0;
            }
            
            .sidebar-menu a i {
                margin-right: 0;
                font-size: 1.2rem;
            }
            
            .main-content {
                margin-left: 80px;
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .dashboard-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
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
                
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <div class="activity-content">
                        <h4>Payment Received</h4>
                        <p>Payment of ₹5,200 received for potato order</p>
                    </div>
                    <div class="activity-time">
                        3 days ago
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
        // Mobile sidebar toggle (can be added later if needed)
    </script>
</body>
</html>