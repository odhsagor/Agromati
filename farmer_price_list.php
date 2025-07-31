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
            background-color: #f8f9fa;
            color: var(--text);
            line-height: 1.6;
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

        .price-list-container {
            padding: 2rem 0;
            margin-left: 250px; /* Match sidebar width */
        }

        .price-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 2.5rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .price-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .price-card h2 {
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 1rem;
            font-size: 1.8rem;
        }

        .last-updated {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 1.5rem;
            display: block;
        }

        .search-box {
            margin-bottom: 2rem;
        }

        .search-box .input-group {
            max-width: 400px;
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius);
        }

        .search-box .form-control {
            border-radius: var(--border-radius) !important;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(0, 0, 0, 0.08);
        }

        .search-box .input-group-text {
            background-color: var(--white);
            border-right: none;
            border-radius: var(--border-radius) 0 0 var(--border-radius) !important;
        }

        /* Table Improvements */
        .price-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            overflow: hidden;
        }

        .price-table thead th {
            background-color: var(--primary);
            color: var(--white);
            font-weight: 600;
            padding: 1rem;
            border: none;
            position: sticky;
            top: 0;
        }

        .price-table tbody tr {
            transition: all 0.2s ease;
        }

        .price-table tbody tr:hover {
            background-color: rgba(40, 167, 69, 0.05);
            transform: scale(1.005);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .price-table td {
            padding: 1.25rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .price-table td:first-child {
            font-weight: 500;
        }

        .price-up {
            color: var(--primary);
            font-weight: 600;
        }

        .price-down {
            color: #dc3545;
            font-weight: 600;
        }

        .no-results {
            padding: 2rem;
            text-align: center;
            color: var(--text-light);
            font-style: italic;
        }

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 99;
        }

        .back-to-top.visible {
            opacity: 1;
        }

        .back-to-top:hover {
            background-color: var(--primary-dark);
            transform: translateY(-3px);
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .price-list-container {
                margin-left: 0;
                padding-top: 1rem;
            }
            
            .price-card {
                padding: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .price-table thead {
                display: none;
            }
            
            .price-table tr {
                display: block;
                margin-bottom: 1rem;
                border-radius: var(--border-radius);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }
            
            .price-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.75rem 1rem;
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            }
            
            .price-table td::before {
                content: attr(data-label);
                font-weight: 600;
                margin-right: 1rem;
                color: var(--text-light);
                flex: 1;
            }
            
            .price-table td span {
                flex: 2;
                text-align: right;
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
                <a href="farmer_orders.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
                <a href="farmer_price_list.php" class="active">
                    <i class="fas fa-chart-line"></i>
                    <span>Price List</span>
                </a>
                <a href="farmer_logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
        
    <!-- Price List Section -->
    <section class="price-list-container">
            <div class="container">
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
                                <input type="text" class="form-control" id="searchInput" placeholder="Search crops...">
                                <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table price-table" id="priceTable">
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
                                        <td colspan="4" class="no-results">
                                            <i class="fas fa-info-circle me-2"></i> No crop prices available at the moment
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

    <!-- Back to Top -->
    <a href="#" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        // Search functionality
        $(document).ready(function(){
            $("#searchInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#priceTable tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
            
            // Back to top button
            $(window).scroll(function() {
                if ($(this).scrollTop() > 300) {
                    $('.back-to-top').fadeIn();
                } else {
                    $('.back-to-top').fadeOut();
                }
            });
            
            $('.back-to-top').click(function(e) {
                e.preventDefault();
                $('html, body').animate({scrollTop: 0}, '300');
            });
        });
    </script>
</body>
</html>