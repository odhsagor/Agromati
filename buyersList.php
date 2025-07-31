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

// Handle account suspension
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_status'])) {
    $buyer_id = intval($_POST['buyer_id']);
    $current_status = intval($_POST['current_status']);
    $new_status = $current_status ? 0 : 1;
    
    $stmt = $conn->prepare("UPDATE buyers SET is_active = ? WHERE buyer_id = ?");
    $stmt->bind_param("ii", $new_status, $buyer_id);
    $stmt->execute();
    $stmt->close();
    
    // Set success message
    $_SESSION['message'] = $new_status ? "Buyer account activated successfully" : "Buyer account suspended successfully";
    header("Location: buyersList.php");
    exit();
}

// Get all buyers
$buyers = $conn->query("SELECT buyer_id, buyer_name, buyer_image, buyer_phone, buyer_address, is_active FROM buyers");
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyers List | Agromati Admin</title>
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
        
        /* Buyers List Styles */
        .profile-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }
        
        .table th {
            background: var(--primary);
            color: var(--white);
            font-weight: 600;
        }
        
        .status-active {
            color: var(--primary);
            font-weight: 600;
        }
        
        .status-suspended {
            color: #dc3545;
            font-weight: 600;
        }
        
        .btn-suspend {
            background: #dc3545;
            color: white;
        }
        
        .btn-activate {
            background: var(--primary);
            color: white;
        }
        
        .address-cell {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .address-cell {
                max-width: 150px;
            }
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
                <a href="crop-prices.php"><i class="fas fa-seedling"></i> Manage Crops price</a>
                <a href="farmersList.php"><i class="fas fa-users"></i> Farmers List</a>
                <a href="buyersList.php" class="active"><i class="fas fa-store"></i> Buyers List</a>
                <a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <h4 class="mb-0"><i class="fas fa-store"></i> Buyers List</h4>
            </header>
            
            <div class="content-area">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Photo</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Address</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($buyers->num_rows > 0): ?>
                                        <?php while ($buyer = $buyers->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($buyer['buyer_image'])): ?>
                                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($buyer['buyer_image']); ?>" class="profile-img" alt="<?php echo htmlspecialchars($buyer['buyer_name']); ?>">
                                                    <?php else: ?>
                                                        <img src="images/default-profile.png" class="profile-img" alt="Default Profile">
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($buyer['buyer_name']); ?></td>
                                                <td><?php echo htmlspecialchars($buyer['buyer_phone']); ?></td>
                                                <td class="address-cell" title="<?php echo htmlspecialchars($buyer['buyer_address']); ?>">
                                                    <?php echo htmlspecialchars($buyer['buyer_address']); ?>
                                                </td>
                                                <td class="<?php echo $buyer['is_active'] ? 'status-active' : 'status-suspended'; ?>">
                                                    <?php echo $buyer['is_active'] ? 'Active' : 'Suspended'; ?>
                                                </td>
                                                <td>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="buyer_id" value="<?php echo $buyer['buyer_id']; ?>">
                                                        <input type="hidden" name="current_status" value="<?php echo $buyer['is_active']; ?>">
                                                        <button type="submit" name="toggle_status" class="btn btn-sm <?php echo $buyer['is_active'] ? 'btn-suspend' : 'btn-activate'; ?>">
                                                            <?php echo $buyer['is_active'] ? '<i class="fas fa-lock"></i> Suspend' : '<i class="fas fa-unlock"></i> Activate'; ?>
                                                        </button>
                                                    </form>
                                                    <a href="buyer_details.php?id=<?php echo $buyer['buyer_id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                No buyers found in the system.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>