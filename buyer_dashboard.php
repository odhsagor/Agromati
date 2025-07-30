<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: buyer_Login.php");
    exit;
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

// Get buyer details
$buyer_id = $_SESSION['buyer_id'];
$stmt = $conn->prepare("SELECT buyer_name, buyer_image FROM buyers WHERE buyer_id = ?");
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();
$buyer = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agromati - Buyer Dashboard</title>
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

        /* Crop Cards */
        .crop-card {
            transition: all 0.3s;
            border: none;
            margin-bottom: 20px;
        }

        .crop-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .crop-img {
            height: 180px;
            object-fit: cover;
        }

        .price-tag {
            font-weight: 600;
            color: var(--primary);
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
                <?php if (!empty($buyer['buyer_image'])): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($buyer['buyer_image']); ?>" alt="Profile" class="profile-img">
                <?php else: ?>
                    <img src="images/default-profile.png" alt="Profile" class="profile-img">
                <?php endif; ?>
                <h4 class="profile-name"><?php echo htmlspecialchars($buyer['buyer_name']); ?></h4>
                <div class="profile-role">Buyer</div>
            </div>
            
            <div class="sidebar-menu">
                <a href="buyer_dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="buy_crop.php">
                    <i class="fas fa-shopping-basket"></i>
                    <span>Buy Crops</span>
                </a>
                <a href="buyer_profile.php">
                    <i class="fas fa-user"></i>
                    <span>Your Profile</span>
                </a>
                <a href="buyer_orders.php">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Your Orders</span>
                </a>
                <a href="price_list.php">
                    <i class="fas fa-tags"></i>
                    <span>Price List</span>
                </a>
                <a href="buyer_logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="page-title">
                    <h1>Buyer Dashboard</h1>
                </div>
                <a href="buyer_logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <!-- Dashboard Cards -->
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-shopping-cart"></i> Active Orders</h3>
                    </div>
                    <div class="card-body">
                        <h2 class="text-center">5</h2>
                        <p class="text-center">Orders in progress</p>
                        <a href="buyer_orders.php" class="btn btn-sm btn-primary w-100">View Orders</a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-check-circle"></i> Completed Orders</h3>
                    </div>
                    <div class="card-body">
                        <h2 class="text-center">12</h2>
                        <p class="text-center">Past purchases</p>
                        <a href="buyer_orders.php?filter=completed" class="btn btn-sm btn-primary w-100">View History</a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-star"></i> Favorite Farmers</h3>
                    </div>
                    <div class="card-body">
                        <h2 class="text-center">8</h2>
                        <p class="text-center">Trusted suppliers</p>
                        <a href="#" class="btn btn-sm btn-primary w-100">Manage Favorites</a>
                    </div>
                </div>
            </div>
            
            <!-- Featured Crops -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3><i class="fas fa-seedling"></i> Featured Crops</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-4">
                            <div class="card crop-card h-100">
                                <img src="images/rice.jpg" class="card-img-top crop-img" alt="Rice">
                                <div class="card-body">
                                    <h5 class="card-title">Premium Rice</h5>
                                    <p class="card-text text-muted">From Bogura</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="price-tag">৳45/kg</span>
                                        <a href="#" class="btn btn-sm btn-primary">Buy Now</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card crop-card h-100">
                                <img src="images/potato.jpg" class="card-img-top crop-img" alt="Potato">
                                <div class="card-body">
                                    <h5 class="card-title">Fresh Potato</h5>
                                    <p class="card-text text-muted">From Rangpur</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="price-tag">৳25/kg</span>
                                        <a href="#" class="btn btn-sm btn-primary">Buy Now</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card crop-card h-100">
                                <img src="images/tomato.jpg" class="card-img-top crop-img" alt="Tomato">
                                <div class="card-body">
                                    <h5 class="card-title">Organic Tomato</h5>
                                    <p class="card-text text-muted">From Comilla</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="price-tag">৳60/kg</span>
                                        <a href="#" class="btn btn-sm btn-primary">Buy Now</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card crop-card h-100">
                                <img src="images/mango.jpg" class="card-img-top crop-img" alt="Mango">
                                <div class="card-body">
                                    <h5 class="card-title">Himsagar Mango</h5>
                                    <p class="card-text text-muted">From Rajshahi</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="price-tag">৳120/kg</span>
                                        <a href="#" class="btn btn-sm btn-primary">Buy Now</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <a href="buy_crop.php" class="btn btn-primary">View All Crops</a>
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
                        <h4>New Order Placed</h4>
                        <p>You ordered 50kg of premium rice from Farmer Rahman</p>
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
                        <h4>Order Completed</h4>
                        <p>Your potato order has been delivered successfully</p>
                    </div>
                    <div class="activity-time">
                        1 day ago
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="activity-content">
                        <h4>Farmer Rated</h4>
                        <p>You gave 5 stars to Farmer Akhtar for quality tomatoes</p>
                    </div>
                    <div class="activity-time">
                        3 days ago
                    </div>
                </div>
            </div>
            
            <!-- Price Trends -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line"></i> Current Price Trends</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Crop</th>
                                    <th>Current Price (৳/kg)</th>
                                    <th>Weekly Change</th>
                                    <th>Top Producing Region</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Rice</td>
                                    <td>45</td>
                                    <td><span class="text-success">+2.5% <i class="fas fa-arrow-up"></i></span></td>
                                    <td>Bogura</td>
                                    <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                                <tr>
                                    <td>Potato</td>
                                    <td>25</td>
                                    <td><span class="text-danger">-1.8% <i class="fas fa-arrow-down"></i></span></td>
                                    <td>Rangpur</td>
                                    <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                                <tr>
                                    <td>Tomato</td>
                                    <td>60</td>
                                    <td><span class="text-success">+5.2% <i class="fas fa-arrow-up"></i></span></td>
                                    <td>Comilla</td>
                                    <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                                <tr>
                                    <td>Mango</td>
                                    <td>120</td>
                                    <td><span class="text-success">+8.7% <i class="fas fa-arrow-up"></i></span></td>
                                    <td>Rajshahi</td>
                                    <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                            </tbody>
                        </table>
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