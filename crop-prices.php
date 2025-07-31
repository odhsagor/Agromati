<?php
session_start();

// Redirect to login if not authenticated as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agromati";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_crop'])) {
        // Add new crop price
        $crop_name = $conn->real_escape_string($_POST['crop_name']);
        $price = floatval($_POST['price']);
        $weekly_change = floatval($_POST['weekly_change']);
        $region = $conn->real_escape_string($_POST['region']);
        
        $stmt = $conn->prepare("INSERT INTO crop_prices (crop_name, price, weekly_change, region) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdds", $crop_name, $price, $weekly_change, $region);
        $stmt->execute();
        $stmt->close();
        
    } elseif (isset($_POST['update_crop'])) {
        // Update existing crop price
        $id = intval($_POST['id']);
        $price = floatval($_POST['price']);
        $weekly_change = floatval($_POST['weekly_change']);
        $region = $conn->real_escape_string($_POST['region']);
        
        $stmt = $conn->prepare("UPDATE crop_prices SET price = ?, weekly_change = ?, region = ? WHERE id = ?");
        $stmt->bind_param("ddsi", $price, $weekly_change, $region, $id);
        $stmt->execute();
        $stmt->close();
        
    } elseif (isset($_POST['delete_crop'])) {
        // Delete crop price
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM crop_prices WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Get all crop prices
$crop_prices = $conn->query("SELECT * FROM crop_prices ORDER BY crop_name");
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Crop Prices | Agromati Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #28a745;
            --primary-dark: #218838;
            --secondary: #ffc107;
            --dark: #343a40;
            --light: #f8f9fa;
            --white: #fff;
            --text: #333;
            --text-light: #6c757d;
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light);
            color: var(--text);
        }
        
        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar styles same as admin dashboard */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary);
            color: var(--white);
            position: fixed;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .sidebar-header h3 {
            color: var(--white);
            font-weight: 700;
            margin: 0;
        }

        .sidebar-menu {
            padding: 20px 0;
            flex-grow: 1;
        }

        .sidebar-menu a {
            display: block;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
            border-left: 4px solid transparent;
            transition: all 0.2s ease;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            border-left-color: var(--secondary);
        }

        .sidebar-menu a i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .top-navbar {
            background: var(--white);
            padding: 12px 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 999;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .content-area {
            padding: 25px;
            flex-grow: 1;
        }
        
        /* Crop Price Management Styles */
        .price-card {
            background: var(--white);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: var(--shadow);
        }
        
        .price-table th {
            background: var(--primary);
            color: var(--white);
            font-weight: 600;
        }
        
        .price-up {
            color: var(--primary);
        }
        
        .price-down {
            color: #dc3545;
        }
        
        .modal-header {
            background: var(--primary);
            color: var(--white);
        }
        
        .form-label.required::after {
            content: " *";
            color: #dc3545;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            :root { --sidebar-width: 220px; }
            .main-content { margin-left: 0; }
            .sidebar { left: calc(-1 * var(--sidebar-width)); }
            .sidebar.active { left: 0; }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-leaf"></i> Agromati</h3>
            </div>
            <nav class="sidebar-menu">
                <a href="admin_dashboard.php" ><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="crop-prices.php" class="active"><i class="fas fa-seedling"></i> Manage Crops price</a>
                <a href="farmersList.php"><i class="fas fa-users"></i> Farmers List</a>
                <a href="buyersList.php"><i class="fas fa-store"></i> Buyers List</a>
                <a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <h4 class="mb-0"><i class="fas fa-seedling"></i> Manage Crop Prices</h4>
            </header>
            
            <div class="content-area">
                <div class="price-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">Current Crop Prices</h5>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCropModal">
                            <i class="fas fa-plus"></i> Add New Crop
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover price-table">
                            <thead>
                                <tr>
                                    <th>Crop Name</th>
                                    <th>Current Price (৳/kg)</th>
                                    <th>Weekly Change</th>
                                    <th>Top Producing Region</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($crop_prices->num_rows > 0): ?>
                                    <?php while ($crop = $crop_prices->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($crop['crop_name']); ?></td>
                                            <td>৳<?php echo number_format($crop['price'], 2); ?></td>
                                            <td class="<?php echo $crop['weekly_change'] >= 0 ? 'price-up' : 'price-down'; ?>">
                                                <?php if ($crop['weekly_change'] >= 0): ?>
                                                    <i class="fas fa-arrow-up"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-arrow-down"></i>
                                                <?php endif; ?>
                                                <?php echo abs($crop['weekly_change']); ?>%
                                            </td>
                                            <td><?php echo htmlspecialchars($crop['region']); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary edit-btn" 
                                                        data-id="<?php echo $crop['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($crop['crop_name']); ?>"
                                                        data-price="<?php echo $crop['price']; ?>"
                                                        data-change="<?php echo $crop['weekly_change']; ?>"
                                                        data-region="<?php echo htmlspecialchars($crop['region']); ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="id" value="<?php echo $crop['id']; ?>">
                                                    <button type="submit" name="delete_crop" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Are you sure you want to delete this crop price?')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            No crop prices found. Add your first crop price.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Crop Modal -->
    <div class="modal fade" id="addCropModal" tabindex="-1" aria-labelledby="addCropModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCropModalLabel">Add New Crop Price</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="crop_name" class="form-label required">Crop Name</label>
                            <input type="text" class="form-control" id="crop_name" name="crop_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label required">Current Price (৳/kg)</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="weekly_change" class="form-label required">Weekly Change (%)</label>
                            <input type="number" step="0.01" class="form-control" id="weekly_change" name="weekly_change" required>
                            <small class="text-muted">Use negative value for decrease</small>
                        </div>
                        <div class="mb-3">
                            <label for="region" class="form-label required">Top Producing Region</label>
                            <input type="text" class="form-control" id="region" name="region" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_crop" class="btn btn-success">Save Crop Price</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Crop Modal -->
    <div class="modal fade" id="editCropModal" tabindex="-1" aria-labelledby="editCropModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCropModalLabel">Edit Crop Price</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_crop_name" class="form-label required">Crop Name</label>
                            <input type="text" class="form-control" id="edit_crop_name" name="crop_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_price" class="form-label required">Current Price (৳/kg)</label>
                            <input type="number" step="0.01" class="form-control" id="edit_price" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_weekly_change" class="form-label required">Weekly Change (%)</label>
                            <input type="number" step="0.01" class="form-control" id="edit_weekly_change" name="weekly_change" required>
                            <small class="text-muted">Use negative value for decrease</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_region" class="form-label required">Top Producing Region</label>
                            <input type="text" class="form-control" id="edit_region" name="region" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_crop" class="btn btn-primary">Update Crop Price</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle edit button clicks
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('editCropModal'));
                
                document.getElementById('edit_id').value = this.dataset.id;
                document.getElementById('edit_crop_name').value = this.dataset.name;
                document.getElementById('edit_price').value = this.dataset.price;
                document.getElementById('edit_weekly_change').value = this.dataset.change;
                document.getElementById('edit_region').value = this.dataset.region;
                
                modal.show();
            });
        });
        
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>