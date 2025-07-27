<?php
session_start();
include('db.php');

// Security Check: Ensure user is logged in and is a farmer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'farmer') {
    header("Location: post_for_sell.php");
    exit();
}

// The farmer's name from the session is now the primary identifier for their crops
$session_farmer_name = $_SESSION['user_name'];

$errors = [];
$success = '';
$edit_mode = false;
$crop_to_edit = null;

// --- HANDLE DELETE REQUEST ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_crop'])) {
    $crop_id_to_delete = (int)$_POST['crop_id'];
    
    // Verify the crop belongs to the logged-in farmer by name before deleting
    $stmt = $conn->prepare("SELECT image FROM crops WHERE id = ? AND farmer_name = ?");
    $stmt->bind_param("is", $crop_id_to_delete, $session_farmer_name);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $crop_image = $result->fetch_assoc()['image'];

        $delete_stmt = $conn->prepare("DELETE FROM crops WHERE id = ?");
        $delete_stmt->bind_param("i", $crop_id_to_delete);
        if ($delete_stmt->execute()) {
            // Also delete the image file if it's not the default one
            if ($crop_image !== 'default_crop.png' && file_exists('uploads/crops/' . $crop_image)) {
                unlink('uploads/crops/' . $crop_image);
            }
            $success = "Crop deleted successfully.";
        } else {
            $errors[] = "Failed to delete crop.";
        }
        $delete_stmt->close();
    } else {
        $errors[] = "You do not have permission to delete this crop.";
    }
    $stmt->close();
}

// --- HANDLE ADD/UPDATE SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['add_crop']) || isset($_POST['update_crop']))) {
    // Sanitize and retrieve form data
    $crop_name = trim($_POST['crop_name']);
    $crop_type = trim($_POST['crop_type']);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_FLOAT);
    $price_per_unit = filter_input(INPUT_POST, 'price_per_unit', FILTER_VALIDATE_FLOAT);
    $harvest_date = trim($_POST['harvest_date']);
    $description = trim($_POST['description']);
    // The farmer's name is taken from the session, not the form, for security.
    $farmer_name_from_session = $session_farmer_name;
    
    // Validate inputs
    if (empty($crop_name)) $errors[] = "Crop name is required.";
    if ($quantity === false || $quantity <= 0) $errors[] = "Please enter a valid quantity.";
    if ($price_per_unit === false || $price_per_unit <= 0) $errors[] = "Please enter a valid price.";
    if (empty($harvest_date)) $errors[] = "Harvest date is required.";

    // Handle image upload logic
    $image_name = $_POST['current_image'] ?? 'default_crop.png';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/crops/';
        $file_info = pathinfo($_FILES['image']['name']);
        $file_ext = strtolower($file_info['extension']);
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $image_name = uniqid('crop_', true) . '.' . $file_ext;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name)) {
                $errors[] = "Failed to upload the new image.";
                $image_name = $_POST['current_image'] ?? 'default_crop.png'; // Revert to old image on failure
            }
        } else {
            $errors[] = "Invalid file type for image.";
        }
    }

    if (empty($errors)) {
        // --- UPDATE CROP ---
        if (isset($_POST['update_crop'])) {
            $crop_id_to_update = (int)$_POST['crop_id'];
            $sql = "UPDATE crops SET crop_name = ?, crop_type = ?, quantity = ?, price_per_unit = ?, harvest_date = ?, description = ?, image = ? WHERE id = ? AND farmer_name = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssddsssis", $crop_name, $crop_type, $quantity, $price_per_unit, $harvest_date, $description, $image_name, $crop_id_to_update, $farmer_name_from_session);
            if ($stmt->execute()) {
                $success = "Crop updated successfully!";
            } else {
                $errors[] = "Failed to update crop.";
            }
        // --- ADD NEW CROP ---
        } else {
            $sql = "INSERT INTO crops (farmer_name, crop_name, crop_type, quantity, price_per_unit, harvest_date, description, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssddsss", $farmer_name_from_session, $crop_name, $crop_type, $quantity, $price_per_unit, $harvest_date, $description, $image_name);
            if ($stmt->execute()) {
                $success = "Crop posted successfully!";
            } else {
                $errors[] = "Failed to post crop.";
            }
        }
        $stmt->close();
    }
}

// --- HANDLE EDIT REQUEST (to populate form) ---
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $crop_id_to_edit = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM crops WHERE id = ? AND farmer_name = ?");
    $stmt->bind_param("is", $crop_id_to_edit, $session_farmer_name);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $crop_to_edit = $result->fetch_assoc();
    } else {
        $edit_mode = false; // Crop not found or doesn't belong to user
        $errors[] = "The selected crop could not be found.";
    }
    $stmt->close();
}

// --- FETCH ALL OF THE FARMER'S CROPS FOR DISPLAY ---
$farmers_crops = [];
$stmt = $conn->prepare("SELECT * FROM crops WHERE farmer_name = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $session_farmer_name);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $farmers_crops[] = $row;
}
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Your Crops - Agromati</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #28a745; --primary-dark: #218838; --white: #fff; }
        body { font-family: 'Poppins', sans-serif; background-color: #f5f5f5; }
        .dashboard { display: flex; min-height: 100vh; }
        .sidebar { width: 280px; background: var(--primary); color: white; position: fixed; height: 100vh; }
        .sidebar-header { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h3 { color: white; font-weight: 600; }
        .sidebar-menu { padding: 20px 0; }
        .sidebar-menu a { display: flex; align-items: center; padding: 12px 25px; color: rgba(255,255,255,0.8); text-decoration: none; border-left: 3px solid transparent; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.1); color: white; border-left: 3px solid white; }
        .sidebar-menu a i { margin-right: 12px; width: 20px; text-align: center; }
        .main-content { flex: 1; margin-left: 280px; padding-top: 80px; }
        .top-navbar { padding: 15px 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: fixed; width: calc(100% - 280px); z-index: 999; background: var(--white); }
        .content-area { padding: 25px; }
        .form-card, .table-card { background: white; border-radius: 10px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .form-label { font-weight: 500; }
        .btn-main { background-color: var(--primary); color: var(--white); border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; }
        .btn-main:hover { background-color: var(--primary-dark); }
        .table img.crop-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3><?php echo htmlspecialchars($session_farmer_name); ?></h3>
                <p>Farmer</p>
            </div>
            <div class="sidebar-menu">
                <a href="farmer_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="post_for_sell.php" class="active"><i class="fas fa-plus-circle"></i> Manage Crops</a>
                <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="top-navbar">
                <h4>Manage Your Crop Listings</h4>
            </div>
            <div class="content-area">
                <!-- Add/Edit Form Card -->
                <div class="form-card">
                    <h4><?php echo $edit_mode ? 'Edit Crop Details' : 'Post a New Crop for Sale'; ?></h4>
                    <hr>
                    <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?><p class="mb-0"><?php echo $error; ?></p><?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="post_for_sell.php" enctype="multipart/form-data">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="crop_id" value="<?php echo $crop_to_edit['id']; ?>">
                            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($crop_to_edit['image']); ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Your Name (Read-only)</label>
                            <input type="text" name="farmer_name" class="form-control" value="<?php echo htmlspecialchars($session_farmer_name); ?>" readonly>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Crop Name *</label><input type="text" name="crop_name" class="form-control" value="<?php echo htmlspecialchars($crop_to_edit['crop_name'] ?? ''); ?>" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Crop Type</label><input type="text" name="crop_type" class="form-control" value="<?php echo htmlspecialchars($crop_to_edit['crop_type'] ?? ''); ?>"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3"><label class="form-label">Quantity (kg) *</label><input type="number" step="0.01" name="quantity" class="form-control" value="<?php echo htmlspecialchars($crop_to_edit['quantity'] ?? ''); ?>" required></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Price/Unit (৳) *</label><input type="number" step="0.01" name="price_per_unit" class="form-control" value="<?php echo htmlspecialchars($crop_to_edit['price_per_unit'] ?? ''); ?>" required></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Harvest Date *</label><input type="date" name="harvest_date" class="form-control" value="<?php echo htmlspecialchars($crop_to_edit['harvest_date'] ?? ''); ?>" required></div>
                        </div>
                        <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($crop_to_edit['description'] ?? ''); ?></textarea></div>
                        <div class="mb-3"><label class="form-label">Crop Image</label><input type="file" name="image" class="form-control" accept="image/*">
                        <?php if ($edit_mode && $crop_to_edit['image'] !== 'default_crop.png'): ?><small class="form-text text-muted">Current image: <?php echo htmlspecialchars($crop_to_edit['image']); ?>. Upload a new file to replace it.</small><?php endif; ?></div>
                        
                        <div class="text-end">
                            <?php if ($edit_mode): ?>
                                <a href="post_for_sell.php" class="btn btn-secondary">Cancel Edit</a>
                                <button type="submit" name="update_crop" class="btn btn-main"><i class="fas fa-save"></i> Update Crop</button>
                            <?php else: ?>
                                <button type="submit" name="add_crop" class="btn btn-main"><i class="fas fa-plus"></i> Add Crop</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Farmer's Crop Listings Table -->
                <div class="table-card">
                    <h4>My Listed Crops</h4>
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Image</th><th>Crop</th><th>Quantity</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php if (empty($farmers_crops)): ?>
                                    <tr><td colspan="6" class="text-center">You have not posted any crops for sale yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($farmers_crops as $crop): ?>
                                    <tr>
                                        <td><img src="uploads/crops/<?php echo htmlspecialchars($crop['image']); ?>" alt="<?php echo htmlspecialchars($crop['crop_name']); ?>" class="crop-thumb"></td>
                                        <td><strong><?php echo htmlspecialchars($crop['crop_name']); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($crop['crop_type']); ?></small></td>
                                        <td><?php echo htmlspecialchars($crop['quantity']); ?> kg</td>
                                        <td>৳<?php echo number_format($crop['price_per_unit'], 2); ?></td>
                                        <td><span class="badge bg-success"><?php echo ucfirst(htmlspecialchars($crop['status'])); ?></span></td>
                                        <td>
                                            <a href="post_for_sell.php?edit=<?php echo $crop['id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                            <form method="POST" action="post_for_sell.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this crop?');">
                                                <input type="hidden" name="crop_id" value="<?php echo $crop['id']; ?>">
                                                <button type="submit" name="delete_crop" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
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
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
