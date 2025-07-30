<?php
session_start();

// Redirect to login if not authenticated as buyer
// if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'buyer') {
//     header("Location: buyer_Login.php");
//     exit;
// }

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agromati";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get buyer's orders
$buyer_id = $_SESSION['buyer_id'];
$query = "SELECT o.order_id, o.quantity, o.total_price, o.order_date, o.status,
                 c.crop_name, c.crop_type, c.crop_image, c.price_per_kg,
                 f.farmer_name, f.farmer_image
          FROM orders o
          JOIN crops c ON o.crop_id = c.crop_id
          JOIN farmers f ON o.farmer_id = f.farmer_id
          WHERE o.buyer_id = ?
          ORDER BY o.order_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();

// Get buyer details for sidebar
$stmt = $conn->prepare("SELECT buyer_name, buyer_image FROM buyers WHERE buyer_id = ?");
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$buyer_result = $stmt->get_result();
$buyer = $buyer_result->fetch_assoc();
$stmt->close();

$conn->close();

// Status tracking steps
$status_steps = [
    'pending' => ['icon' => 'fa-hourglass-half', 'color' => 'text-warning', 'text' => 'Order placed'],
    'confirmed' => ['icon' => 'fa-check-circle', 'color' => 'text-primary', 'text' => 'Farmer confirmed'],
    'shipped' => ['icon' => 'fa-truck', 'color' => 'text-info', 'text' => 'Shipped'],
    'delivered' => ['icon' => 'fa-box-open', 'color' => 'text-success', 'text' => 'Delivered'],
    'cancelled' => ['icon' => 'fa-times-circle', 'color' => 'text-danger', 'text' => 'Cancelled']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders | Agromati</title>
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

        /* Sidebar - Same as buyer dashboard */
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

        /* Order Cards */
        .order-card {
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .order-header {
            background: rgba(40, 167, 69, 0.1);
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .order-body {
            padding: 20px;
        }

        .order-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }

        .farmer-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        .status-tracker {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 30px 0;
        }

        .status-tracker::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 3px;
            background: #e9ecef;
            z-index: 1;
        }

        .status-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .status-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 5px;
            background: var(--white);
            border: 3px solid #e9ecef;
        }

        .status-step.active .status-icon {
            border-color: var(--primary);
            background: var(--primary);
            color: var(--white);
        }

        .status-step.completed .status-icon {
            border-color: var(--primary);
            background: var(--primary);
            color: var(--white);
        }

        .status-text {
            font-size: 0.8rem;
            text-align: center;
            color: var(--text-light);
        }

        .status-step.active .status-text,
        .status-step.completed .status-text {
            color: var(--dark);
            font-weight: 500;
        }

        .order-details {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .detail-label {
            color: var(--text-light);
        }

        .detail-value {
            font-weight: 500;
        }

        .btn-contact {
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 5px;
            padding: 8px 15px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-contact:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
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
            
            .status-tracker {
                flex-wrap: wrap;
                justify-content: flex-start;
                gap: 15px;
            }
            
            .status-tracker::before {
                display: none;
            }
            
            .status-step {
                flex-direction: row;
                align-items: center;
                gap: 10px;
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
                <a href="buyer_dashboard.php">
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
                <a href="buyer_orders.php" class="active">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Your Orders</span>
                </a>
                <a href="price_list.php">
                    <i class="fas fa-tags"></i>
                    <span>Price List</span>
                </a>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="page-title">
                    <h1><i class="fas fa-clipboard-list"></i> Your Orders</h1>
                </div>
            </div>
            
            <?php if ($result->num_rows > 0): ?>
                <?php while ($order = $result->fetch_assoc()): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Order #<?php echo $order['order_id']; ?></h5>
                                <span class="badge bg-<?php 
                                    echo $order['status'] == 'pending' ? 'warning' : 
                                         ($order['status'] == 'confirmed' ? 'primary' : 
                                         ($order['status'] == 'shipped' ? 'info' : 
                                         ($order['status'] == 'delivered' ? 'success' : 'danger'))); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="order-body">
                            <div class="row">
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <?php if (!empty($order['crop_image'])): ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($order['crop_image']); ?>" class="order-img" alt="<?php echo htmlspecialchars($order['crop_name']); ?>">
                                    <?php else: ?>
                                        <img src="images/default-crop.jpg" class="order-img" alt="Default Crop Image">
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6">
                                    <h4><?php echo htmlspecialchars($order['crop_name']); ?></h4>
                                    <p class="text-muted mb-2"><?php echo ucfirst($order['crop_type']); ?></p>
                                    
                                    <div class="d-flex align-items-center mb-3">
                                        <?php if (!empty($order['farmer_image'])): ?>
                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($order['farmer_image']); ?>" class="farmer-img" alt="<?php echo htmlspecialchars($order['farmer_name']); ?>">
                                        <?php else: ?>
                                            <img src="images/default-profile.png" class="farmer-img" alt="Default Farmer Image">
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($order['farmer_name']); ?></span>
                                    </div>
                                    
                                    <div class="status-tracker">
                                        <?php foreach ($status_steps as $status => $step): ?>
                                            <div class="status-step <?php 
                                                echo $order['status'] == $status ? 'active' : 
                                                    (array_search($order['status'], array_keys($status_steps)) > array_search($status, array_keys($status_steps)) ? 'completed' : ''); ?>">
                                                <div class="status-icon">
                                                    <i class="fas <?php echo $step['icon']; ?>"></i>
                                                </div>
                                                <div class="status-text"><?php echo $step['text']; ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="order-details">
                                        <div class="detail-row">
                                            <span class="detail-label">Order Date:</span>
                                            <span class="detail-value"><?php echo date('M j, Y', strtotime($order['order_date'])); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Quantity:</span>
                                            <span class="detail-value"><?php echo number_format($order['quantity'], 2); ?> kg</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Price Per Kg:</span>
                                            <span class="detail-value">৳<?php echo number_format($order['price_per_kg'], 2); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Total Price:</span>
                                            <span class="detail-value">৳<?php echo number_format($order['total_price'], 2); ?></span>
                                        </div>
                                        
                                        <button class="btn-contact w-100 mt-3">
                                            <i class="fas fa-envelope"></i> Contact Farmer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x mb-3 text-muted"></i>
                        <h3>No Orders Found</h3>
                        <p class="text-muted">You haven't placed any orders yet.</p>
                        <a href="buy_crop.php" class="btn btn-primary">
                            <i class="fas fa-shopping-basket"></i> Browse Crops
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>