<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- DATABASE CONNECTION ---
$conn = new mysqli("localhost", "root", "", "agromati");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- SESSION & USER DATA (for the dashboard layout) ---
// In a real app, you'd have more robust session checks.
$session_farmer_name = $_SESSION['user_name'] ?? 'Farmer'; 
$session_farmer_photo = $_SESSION['user_photo'] ?? 'default.jpg'; // Assuming photo name is stored in session

// --- CROP MANAGEMENT LOGIC ---
$errors = [];
$success = '';
$edit_mode = false;
$crop_to_edit = null;

// HANDLE DELETE CROP
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_crop'])) {
    $crop_id = (int)$_POST['crop_id'];
    $stmt = $conn->prepare("DELETE FROM crops WHERE id = ?");
    $stmt->bind_param("i", $crop_id);
    if ($stmt->execute()) {
        $success = "Crop deleted successfully.";
    } else {
        $errors[] = "Failed to delete crop.";
    }
    $stmt->close();
}

// HANDLE ADD / UPDATE CROP
if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['add_crop']) || isset($_POST['update_crop']))) {
    // Sanitize and retrieve form data
    $farmer_name = trim($_POST['farmer_name']);
    $crop_name = trim($_POST['crop_name']);
    $crop_type = trim($_POST['crop_type']);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_FLOAT);
    $price = filter_input(INPUT_POST, 'price_per_unit', FILTER_VALIDATE_FLOAT);
    $harvest_date = trim($_POST['harvest_date']);
    $description = trim($_POST['description']);
    $image_data = null;
    $image_type_data = null;
    
    // Validate required fields
    if (empty($farmer_name)) $errors[] = "Farmer name is required.";
    if (empty($crop_name)) $errors[] = "Crop name is required.";
    if ($quantity === false || $quantity <= 0) $errors[] = "Please enter a valid quantity.";
    if ($price === false || $price <= 0) $errors[] = "Please enter a valid price.";
    if (empty($harvest_date)) $errors[] = "Harvest date is required.";

    // Handle image upload if a file is provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
        $image_type = $_FILES['image']['type'];

        if (!in_array($image_type, $allowed_types)) {
            $errors[] = "Invalid image type. Only JPG, JPEG, PNG, and GIF are allowed.";
        } else {
            $image_data = file_get_contents($_FILES['image']['tmp_name']);
            $image_type_data = $image_type;
        }
    }

    if (empty($errors)) {
        // --- UPDATE CROP ---
        if (isset($_POST['update_crop'])) {
            $crop_id = (int)$_POST['crop_id'];
            // If a new image was uploaded, update the image fields as well
            if ($image_data !== null && $image_type_data !== null) {
                $stmt = $conn->prepare("UPDATE crops SET farmer_name=?, crop_name=?, crop_type=?, quantity=?, price_per_unit=?, harvest_date=?, description=?, image=?, image_type=? WHERE id=?");
                $stmt->bind_param("sssddssisi", $farmer_name, $crop_name, $crop_type, $quantity, $price, $harvest_date, $description, $image_data, $image_type_data, $crop_id);
            } else {
                // Otherwise, update without changing the image
                $stmt = $conn->prepare("UPDATE crops SET farmer_name=?, crop_name=?, crop_type=?, quantity=?, price_per_unit=?, harvest_date=?, description=? WHERE id=?");
                $stmt->bind_param("sssddssi", $farmer_name, $crop_name, $crop_type, $quantity, $price, $harvest_date, $description, $crop_id);
            }

            if ($stmt->execute()) {
                 $success = "Crop updated successfully.";
                 // Redirect to clean the form and URL
                 header("Location: post_for_sell.php?success=1");
                 exit();
            } else {
                $errors[] = "Failed to update crop.";
            }
            $stmt->close();
        
        // --- ADD NEW CROP ---
        } else {
            $stmt = $conn->prepare("INSERT INTO crops (farmer_name, crop_name, crop_type, quantity, price_per_unit, harvest_date, description, image, image_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssddssis", $farmer_name, $crop_name, $crop_type, $quantity, $price, $harvest_date, $description, $image_data, $image_type_data);

            if ($stmt->execute()) {
                $success = "Crop posted successfully!";
            } else {
                $errors[] = "Failed to post crop.";
            }
            $stmt->close();
        }
    }
}

// --- FETCH FOR EDIT ---
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $crop_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM crops WHERE id = ?");
    $stmt->bind_param("i", $crop_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $crop_to_edit = $result->fetch_assoc();
    } else {
        // Crop not found, prevent edit mode
        $edit_mode = false;
        $errors[] = "The crop you are trying to edit was not found.";
    }
    $stmt->close();
}

if (isset($_GET['success'])) {
    $success = "Crop updated successfully.";
}


// --- FETCH ALL CROPS FOR DISPLAY ---
$crops = [];
$result = $conn->query("SELECT id, farmer_name, crop_name, quantity, price_per_unit, image, image_type FROM crops ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $crops[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Crops - Agromati</title>
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
            --text: #333; 
            --text-light: #6c757d;
        }
        body {
            font-family: 'Poppins', 
            sans-serif; 
            background-color: #f5f5f5; 
        }
        .dashboard { 
            display: flex; 
            min-height: 100vh; 
        }
        .sidebar {
            width: 280px; 
            background: var(--primary); 
            color: white; 
            position: fixed; 
            height: 100vh;
            transition: all 0.3s; 
            z-index: 1000;
        }
        .sidebar-header {
            padding: 20px; 
            text-align: center; 
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-header .farmer-avatar {
            width: 80px; 
            height: 80px; 
            border-radius: 50%; 
            object-fit: cover;
            border: 3px solid rgba(255,255,255,0.2); 
            margin-bottom: 15px;
        }
        .sidebar-header h3 { 
            color: white; 
            font-weight: 600; 
            margin-bottom: 5px; 
        }
        .sidebar-header p { 
            color: rgba(255,255,255,0.8); 
            font-size: 0.9rem; 
        }
        .sidebar-menu { 
            padding: 20px 0; 
        }
        .sidebar-menu a {
            display: flex; 
            align-items: center; 
            padding: 12px 25px; 
            color: rgba(255,255,255,0.8);
            transition: all 0.3s; 
            font-weight: 500; 
            border-left: 3px solid transparent; 
            text-decoration: none;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.1); 
            color: white; 
            border-left: 3px solid white;
        }
        .sidebar-menu a i { 
            margin-right: 12px; 
            width: 20px; 
            text-align: center; 
            font-size: 1.1rem; }
        .main-content { 
            flex: 1; 
            margin-left: 280px; 
            padding-top: 80px; 
            transition: all 0.3s; 
        }
        .top-navbar {
            padding: 15px 25px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            width: calc(100% - 280px); 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            transition: all 0.3s;
        }
        .content-area {
             padding: 25px; 
            }
        .dashboard-card {
            background: white; 
            border-radius: 10px; 
            padding: 25px; 
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); 
            transition: all 0.3s;
        }
        .dashboard-card h4 {
            color: var(--primary); 
            margin-bottom: 20px; 
            font-weight: 600;
            display: flex; 
            align-items: center;
        }
        .dashboard-card h4 i { 
            margin-right: 10px; 
        }
        .table img.crop-thumb { 
            width: 60px; 
            height: 60px; 
            object-fit: cover; 
            border-radius: 8px; 
            background-color: #eee; }

        .nav-toggle { 
            display: none; 
            background: transparent; 
            border: none; 
            cursor: pointer; 
            z-index: 1001; 
        }
        .overlay {
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background: rgba(0,0,0,0.5);
            z-index: 999; 
            opacity: 0; 
            visibility: hidden; 
            transition: all 0.3s ease;
        }
        @media (max-width: 768px) {
            .nav-toggle { display: block; 
            }
            .sidebar { 
                left: -280px; 
            } 
            .sidebar.active 
            { left: 0; 
            }
            .main-content {
                 margin-left: 0; 
                } 
                .top-navbar {
                     width: 100%; 
                    }
            .overlay.active {
                 opacity: 1; 
                 visibility: visible; 
                }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="images/farmers/<?php echo htmlspecialchars($session_farmer_photo); ?>" class="farmer-avatar" alt="Farmer">
                <p>Farmer Member</p>
            </div>
            <div class="sidebar-menu">
                <a href="farmer_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="post_for_sell.php" class="active"><i class="fas fa-leaf"></i> Manage Crops</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="main-content">
            <div class="top-navbar">
                <button class="nav-toggle">
                    <i class="fas fa-bars text-primary fs-4"></i>
                </button>
                <h4>Manage Your Crops</h4>
                <div class="user-info">
                    <span><?php echo date('l, F j, Y'); ?></span>
                </div>
            </div>

            <div class="content-area">
                <div class="dashboard-card">
                    <h4><i class="fas <?php echo $edit_mode ? 'fa-edit' : 'fa-plus-circle'; ?>"></i> <?php echo $edit_mode ? "Edit Crop Details" : "Post a New Crop"; ?></h4>
                    <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error) echo "<p class='mb-0'>$error</p>"; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="post_for_sell.php" enctype="multipart/form-data">
                        <?php if ($edit_mode): ?><input type="hidden" name="crop_id" value="<?php echo $crop_to_edit['id']; ?>"><?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Farmer Name*</label><input name="farmer_name" class="form-control" placeholder="e.g., John Doe" required value="<?php echo htmlspecialchars($crop_to_edit['farmer_name'] ?? $session_farmer_name); ?>"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Crop Name*</label><input name="crop_name" class="form-control" placeholder="e.g., Tomato" required value="<?php echo htmlspecialchars($crop_to_edit['crop_name'] ?? ''); ?>"></div>
                        </div>
                         <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Crop Type</label><input name="crop_type" class="form-control" placeholder="e.g., Vegetable" value="<?php echo htmlspecialchars($crop_to_edit['crop_type'] ?? ''); ?>"></div>
                             <div class="col-md-6 mb-3"><label class="form-label">Harvest Date*</label><input type="date" name="harvest_date" class="form-control" required value="<?php echo htmlspecialchars($crop_to_edit['harvest_date'] ?? ''); ?>"></div>
                        </div>
                        <div class="row">
                           <div class="col-md-6 mb-3"><label class="form-label">Quantity (kg)*</label><input type="number" step="0.01" name="quantity" class="form-control" placeholder="e.g., 50.5" required value="<?php echo htmlspecialchars($crop_to_edit['quantity'] ?? ''); ?>"></div>
                           <div class="col-md-6 mb-3"><label class="form-label">Price per Unit (৳)*</label><input type="number" step="0.01" name="price_per_unit" class="form-control" placeholder="e.g., 120.00" required value="<?php echo htmlspecialchars($crop_to_edit['price_per_unit'] ?? ''); ?>"></div>
                        </div>
                        <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" placeholder="Add a brief description of the crop..."><?php echo htmlspecialchars($crop_to_edit['description'] ?? ''); ?></textarea></div>
                        <div class="mb-3"><label class="form-label">Crop Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
                        
                        <div class="text-end">
                             <button type="submit" name="<?php echo $edit_mode ? 'update_crop' : 'add_crop'; ?>" class="btn btn-success"><?php echo $edit_mode ? 'Update Crop' : 'Post Crop'; ?></button>
                             <?php if ($edit_mode): ?><a href="post_for_sell.php" class="btn btn-secondary">Cancel Edit</a><?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="dashboard-card">
                    <h4><i class="fas fa-list-ul"></i> All Listed Crops</h4>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Image</th><th>Crop Name</th><th>Farmer</th><th>Qty</th><th>Price</th><th>Actions</th></tr></thead>
                            <tbody>
                            <?php if (empty($crops)): ?>
                                <tr><td colspan="6" class="text-center">No crops have been posted yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($crops as $crop): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($crop['image']) && !empty($crop['image_type'])): ?>
                                                <img src="data:<?php echo htmlspecialchars($crop['image_type']); ?>;base64,<?php echo base64_encode($crop['image']); ?>" alt="<?php echo htmlspecialchars($crop['crop_name']); ?>" class="crop-thumb">
                                            <?php else: ?>
                                                <img src="images/default_crop.png" alt="No image" class="crop-thumb">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($crop['crop_name']); ?></td>
                                        <td><?php echo htmlspecialchars($crop['farmer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($crop['quantity']); ?> kg</td>
                                        <td>৳<?php echo number_format($crop['price_per_unit'], 2); ?></td>
                                        <td>
                                            <a href="?edit=<?php echo $crop['id']; ?>" class="btn btn-sm btn-primary mb-1"><i class="fas fa-edit"></i></a>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this crop?');">
                                                <input type="hidden" name="crop_id" value="<?php echo $crop['id']; ?>">
                                                <button type="submit" name="delete_crop" class="btn btn-sm btn-danger mb-1"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="overlay"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navToggle = document.querySelector('.nav-toggle');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.overlay');

            function toggleMenu() {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            }

            if (navToggle) navToggle.addEventListener('click', toggleMenu);
            if (overlay) overlay.addEventListener('click', toggleMenu);
        });
    </script>
</body>
</html>