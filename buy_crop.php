<?php
session_start();

// Redirect to login if not authenticated as buyer
// if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'buyer') {
//     header("Location: buyer_Login.php");
//     exit;
// }

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agromati";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['buy_crop'])) {
    $crop_id = $_POST['crop_id'];
    $quantity = $_POST['quantity'];
    $buyer_id = $_SESSION['buyer_id'];
    
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("SELECT farmer_id, quantity, price_per_kg FROM crops WHERE crop_id = ? AND status = 'available' FOR UPDATE");
        $stmt->bind_param("i", $crop_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            throw new Exception("Crop not available for purchase");
        }
        
        $crop = $result->fetch_assoc();
        $farmer_id = $crop['farmer_id'];
        $available_quantity = $crop['quantity'];
        $price_per_kg = $crop['price_per_kg'];
        
        if ($quantity <= 0 || $quantity > $available_quantity) {
            throw new Exception("Invalid quantity requested");
        }
        
        $total_price = $quantity * $price_per_kg;
        
        $stmt = $conn->prepare("INSERT INTO orders (buyer_id, crop_id, farmer_id, quantity, total_price) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiidd", $buyer_id, $crop_id, $farmer_id, $quantity, $total_price);
        $stmt->execute();
        
        $new_quantity = $available_quantity - $quantity;
        $status = ($new_quantity <= 0) ? 'sold' : 'available';
        
        $stmt = $conn->prepare("UPDATE crops SET quantity = ?, status = ? WHERE crop_id = ?");
        $stmt->bind_param("dsi", $new_quantity, $status, $crop_id);
        $stmt->execute();
        
        $conn->commit();
        $success = "Purchase successful! You've bought $quantity kg.";
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

$query = "SELECT c.*, f.farmer_name, f.farmer_image 
          FROM crops c 
          JOIN farmers f ON c.farmer_id = f.farmer_id 
          WHERE c.status = 'available' 
          ORDER BY c.post_date DESC";
$result = $conn->query($query);

$stmt = $conn->prepare("SELECT buyer_name, buyer_image FROM buyers WHERE buyer_id = ?");
$stmt->bind_param("i", $_SESSION['buyer_id']);
$stmt->execute();
$buyer_result = $stmt->get_result();
$buyer = $buyer_result->fetch_assoc();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Crops | Agromati</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/buy_crop.css">
</head>
<body>

    <div class="menu-toggle" id="mobile-menu-toggle">
        <i class="fas fa-ellipsis-v"></i>
    </div>

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
                <a href="buy_crop.php" class="active">
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
                    <h1><i class="fas fa-shopping-basket"></i> Buy Crops</h1>
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
            
            <div class="row">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($crop = $result->fetch_assoc()): ?>
                        <div class="col-md-4">
                            <div class="crop-card">
                                <div class="crop-img-container">
                                    <?php if (!empty($crop['crop_image'])): ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($crop['crop_image']); ?>" class="crop-img" alt="<?php echo htmlspecialchars($crop['crop_name']); ?>">
                                    <?php else: ?>
                                        <img src="images/default-crop.jpg" class="crop-img" alt="Default Crop Image">
                                    <?php endif; ?>
                                </div>
                                <div class="crop-body">
                                    <div class="farmer-info">
                                        <?php if (!empty($crop['farmer_image'])): ?>
                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($crop['farmer_image']); ?>" class="farmer-img" alt="<?php echo htmlspecialchars($crop['farmer_name']); ?>">
                                        <?php else: ?>
                                            <img src="images/default-profile.png" class="farmer-img" alt="Default Farmer Image">
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($crop['farmer_name']); ?></span>
                                    </div>
                                    
                                    <h3><?php echo htmlspecialchars($crop['crop_name']); ?></h3>
                                    <p><strong>Type:</strong> <?php echo htmlspecialchars($crop['crop_type']); ?></p>
                                    <p><strong>Harvested:</strong> <?php echo date('M j, Y', strtotime($crop['harvest_date'])); ?></p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="crop-price">৳<?php echo number_format($crop['price_per_kg'], 2); ?>/kg</span>
                                        <span class="crop-quantity"><?php echo number_format($crop['quantity'], 2); ?> kg available</span>
                                    </div>
                                    
                                    <span class="status-available">Available</span>
                                    
                                    <form class="buy-form" method="post">
                                        <input type="hidden" name="crop_id" value="<?php echo $crop['crop_id']; ?>">
                                        
                                        <div class="mb-2">
                                            <label for="quantity_<?php echo $crop['crop_id']; ?>" class="form-label">Quantity (kg)</label>
                                            <input type="number" step="0.01" min="0.01" max="<?php echo $crop['quantity']; ?>" 
                                                   class="form-control" id="quantity_<?php echo $crop['crop_id']; ?>" 
                                                   name="quantity" required>
                                        </div>
                                        
                                        <button type="submit" name="buy_crop" class="btn-buy w-100">
                                            <i class="fas fa-cart-plus"></i> Buy Now
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            No crops available for purchase at the moment. Please check back later.
                        </div>
                    </div>
                <?php endif; ?>
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