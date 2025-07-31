<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agromati";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$buyer_id = $_SESSION['buyer_id'];
$stmt = $conn->prepare("SELECT buyer_name, buyer_image FROM buyers WHERE buyer_id = ?");
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();
$buyer = $result->fetch_assoc();
$crop_prices = $conn->query("SELECT * FROM crop_prices ORDER BY crop_name");
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop Price List | Agromati</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/price_list.css">

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
                <a href="buy_crop.php" >
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
                <a href="price_list.php" class="active">
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
            <div class="price-card">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                    <div>
                        <h2>Current Crop Prices</h2>
                        <span class="last-updated">
                            <i class="fas fa-clock me-1"></i> Last updated: <?php echo date('F j, Y, g:i a'); ?>
                        </span>
                    </div>
                    <div class="search-box mt-3 mt-md-0">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search crops...">
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table price-table">
                        <thead>
                            <tr>
                                <th>Crop Name</th>
                                <th>Current Price (৳/kg)</th>
                                <th>Weekly Change</th>
                                <th>Top Producing Region</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($crop_prices->num_rows > 0): ?>
                                <?php while ($crop = $crop_prices->fetch_assoc()): ?>
                                    <tr>
                                        <td data-label="Crop Name"><?php echo htmlspecialchars($crop['crop_name']); ?></td>
                                        <td data-label="Price">৳<?php echo number_format($crop['price'], 2); ?></td>
                                        <td data-label="Weekly Change" class="<?php echo $crop['weekly_change'] >= 0 ? 'price-up' : 'price-down'; ?>">
                                            <?php if ($crop['weekly_change'] >= 0): ?>
                                                <i class="fas fa-arrow-up me-1"></i>
                                            <?php else: ?>
                                                <i class="fas fa-arrow-down me-1"></i>
                                            <?php endif; ?>
                                            <?php echo abs($crop['weekly_change']); ?>%
                                        </td>
                                        <td data-label="Top Region"><?php echo htmlspecialchars($crop['region']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <i class="fas fa-info-circle me-2"></i> No crop prices available
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
        document.querySelector('.search-box input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.price-table tbody tr');
            
            rows.forEach(row => {
                const cropName = row.querySelector('td:first-child').textContent.toLowerCase();
                row.style.display = cropName.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
</body>
</html>