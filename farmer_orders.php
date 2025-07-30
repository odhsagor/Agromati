<?php
session_start();

// Redirect to login if not authenticated as farmer
// if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'farmer') {
//     header("Location: farmer_Login.php");
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

// Update order status if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $farmer_id = $_SESSION['farmer_id'];
    
    // Verify the order belongs to this farmer
    $stmt = $conn->prepare("SELECT o.order_id FROM orders o 
                           JOIN crops c ON o.crop_id = c.crop_id 
                           WHERE o.order_id = ? AND c.farmer_id = ?");
    $stmt->bind_param("ii", $order_id, $farmer_id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        // Update status
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $success = "Order status updated successfully!";
        } else {
            $error = "Failed to update order status.";
        }
    } else {
        $error = "Order not found or you don't have permission to update it.";
    }
    $stmt->close();
}

// Get farmer's orders
$farmer_id = $_SESSION['farmer_id'];
$query = "SELECT o.order_id, o.quantity, o.total_price, o.order_date, o.status,
                 c.crop_name, c.crop_image,
                 b.buyer_name, b.buyer_image
          FROM orders o
          JOIN crops c ON o.crop_id = c.crop_id
          JOIN buyers b ON o.buyer_id = b.buyer_id
          WHERE c.farmer_id = ?
          ORDER BY o.order_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();

// Get farmer details for sidebar
$stmt = $conn->prepare("SELECT farmer_name, farmer_image FROM farmers WHERE farmer_id = ?");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$farmer_result = $stmt->get_result();
$farmer = $farmer_result->fetch_assoc();
$stmt->close();

$conn->close();
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

        /* Sidebar - Same as farmer dashboard */
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

        /* Orders Table */
        .orders-table {
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .table thead th {
            background: rgba(40, 167, 69, 0.1);
            color: var(--primary);
            border-bottom: none;
        }

        .order-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }

        .buyer-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-shipped {
            background-color: #d4edda;
            color: #155724;
        }

        .status-delivered {
            background-color: #c3e6cb;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .form-select {
            border-radius: 5px;
            padding: 5px 10px;
            font-size: 0.9rem;
        }

        .btn-update {
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 5px;
            padding: 5px 15px;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .btn-update:hover {
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
            
            .table-responsive {
                overflow-x: auto;
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
                <a href="farmer_dashboard.php">
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
                <a href="farmer_orders.php" class="active">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
                <a href="price_list.php">
                    <i class="fas fa-chart-line"></i>
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
                    <h1><i class="fas fa-shopping-cart"></i> Your Orders</h1>
                </div>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="orders-table">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Crop</th>
                                <th>Buyer</th>
                                <th>Quantity</th>
                                <th>Total Price</th>
                                <th>Order Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($order = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $order['order_id']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($order['crop_image'])): ?>
                                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($order['crop_image']); ?>" class="order-img me-2" alt="<?php echo htmlspecialchars($order['crop_name']); ?>">
                                                <?php else: ?>
                                                    <img src="images/default-crop.jpg" class="order-img me-2" alt="Default Crop Image">
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($order['crop_name']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($order['buyer_image'])): ?>
                                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($order['buyer_image']); ?>" class="buyer-img" alt="<?php echo htmlspecialchars($order['buyer_name']); ?>">
                                                <?php else: ?>
                                                    <img src="images/default-profile.png" class="buyer-img" alt="Default Buyer Image">
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($order['buyer_name']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo number_format($order['quantity'], 2); ?> kg</td>
                                        <td>৳<?php echo number_format($order['total_price'], 2); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="post" class="d-flex">
                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                <select name="status" class="form-select me-2">
                                                    <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                    <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                    <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                    <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                                <button type="submit" name="update_status" class="btn-update">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-shopping-cart fa-2x mb-3 text-muted"></i>
                                        <h5>No orders found</h5>
                                        <p class="text-muted">You haven't received any orders yet.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>