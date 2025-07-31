<?php
session_start();

// Check if farmer is logged in
if (!isset($_SESSION['logged_in'])) {
    header("Location: farmer_Login.php");
    exit();
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

// Get farmer details
$stmt = $conn->prepare("SELECT farmer_name, farmer_image FROM farmers WHERE farmer_id = ?");
$stmt->bind_param("i", $_SESSION['farmer_id']);
$stmt->execute();
$result = $stmt->get_result();
$farmer = $result->fetch_assoc();
$stmt->close();

// Initialize variables
$errors = [];
$success = false;
$action = isset($_GET['action']) ? $_GET['action'] : '';
$crop_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle delete action
if ($action === 'delete' && $crop_id > 0) {
    $stmt = $conn->prepare("DELETE FROM crops WHERE crop_id = ? AND farmer_id = ?");
    $stmt->bind_param("ii", $crop_id, $_SESSION['farmer_id']);
    if ($stmt->execute()) {
        $success = "Crop post deleted successfully!";
    } else {
        $errors['database'] = "Error deleting crop post: " . $stmt->error;
    }
    $stmt->close();
}

// Process form submission for add/edit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $crop_name = trim($_POST['crop_name']);
    $crop_type = trim($_POST['crop_type']);
    $quantity = trim($_POST['quantity']);
    $price_per_kg = trim($_POST['price_per_kg']);
    $harvest_date = trim($_POST['harvest_date']);
    $status = 'available'; // Default status
    $farmer_id = $_SESSION['farmer_id'];
    
    // Handle image upload
    $crop_image = null;
    if (isset($_FILES['crop_image']) && $_FILES['crop_image']['error'] === UPLOAD_ERR_OK) {
        $check = getimagesize($_FILES['crop_image']['tmp_name']);
        if ($check !== false) {
            $crop_image = file_get_contents($_FILES['crop_image']['tmp_name']);
        } else {
            $errors['crop_image'] = "File is not an image.";
        }
    } elseif ($action === 'edit' && empty($_FILES['crop_image']['name'])) {
        // Keep existing image if editing and no new image uploaded
        $crop_image = null; // We'll handle this in the update query
    } else {
        $errors['crop_image'] = "Please upload an image of your crop.";
    }
    
    // Validation
    if (empty($crop_name)) {
        $errors['crop_name'] = "Crop name is required";
    }
    
    if (empty($crop_type)) {
        $errors['crop_type'] = "Crop type is required";
    }
    
    if (empty($quantity) || !is_numeric($quantity) || $quantity <= 0) {
        $errors['quantity'] = "Please enter a valid quantity";
    }
    
    if (empty($price_per_kg) || !is_numeric($price_per_kg) || $price_per_kg <= 0) {
        $errors['price_per_kg'] = "Please enter a valid price";
    }
    
    if (empty($harvest_date)) {
        $errors['harvest_date'] = "Harvest date is required";
    } elseif (strtotime($harvest_date) > strtotime('today')) {
        $errors['harvest_date'] = "Harvest date cannot be in the future";
    }
    
    // If no errors, insert/update into database
    if (empty($errors)) {
        if ($action === 'edit' && $crop_id > 0) {
            // Update existing crop
            if ($crop_image !== null) {
                $stmt = $conn->prepare("UPDATE crops SET crop_name=?, crop_type=?, quantity=?, price_per_kg=?, harvest_date=?, crop_image=?, status=? WHERE crop_id=? AND farmer_id=?");
                $stmt->bind_param("ssddsssii", $crop_name, $crop_type, $quantity, $price_per_kg, $harvest_date, $crop_image, $status, $crop_id, $farmer_id);
            } else {
                $stmt = $conn->prepare("UPDATE crops SET crop_name=?, crop_type=?, quantity=?, price_per_kg=?, harvest_date=?, status=? WHERE crop_id=? AND farmer_id=?");
                $stmt->bind_param("ssddssii", $crop_name, $crop_type, $quantity, $price_per_kg, $harvest_date, $status, $crop_id, $farmer_id);
            }
            
            if ($stmt->execute()) {
                $success = "Crop post updated successfully!";
            } else {
                $errors['database'] = "Error updating crop post: " . $stmt->error;
            }
        } else {
            // Insert new crop
            $stmt = $conn->prepare("INSERT INTO crops (crop_name, crop_type, quantity, price_per_kg, harvest_date, crop_image, status, farmer_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssddsssi", $crop_name, $crop_type, $quantity, $price_per_kg, $harvest_date, $crop_image, $status, $farmer_id);
            
            if ($stmt->execute()) {
                $success = "Crop post created successfully!";
                // Clear form for new entry
                $crop_name = $crop_type = $quantity = $price_per_kg = $harvest_date = '';
            } else {
                $errors['database'] = "Error creating crop post: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}

// Get farmer's existing crop posts
$stmt = $conn->prepare("SELECT * FROM crops WHERE farmer_id = ? ORDER BY harvest_date DESC");
$stmt->bind_param("i", $_SESSION['farmer_id']);
$stmt->execute();
$crop_posts = $stmt->get_result();
$stmt->close();

// Get data for edit
$edit_data = [];
if ($action === 'edit' && $crop_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM crops WHERE crop_id = ? AND farmer_id = ?");
    $stmt->bind_param("ii", $crop_id, $_SESSION['farmer_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Sell Post | Agromati</title>
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

        /* Form Styles */
        .sell-form-container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 30px;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
        }

        .form-control {
            border-radius: 5px;
            padding: 10px 15px;
            border: 1px solid #ddd;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
        }

        .btn-submit {
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .image-preview {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px dashed #ddd;
            display: block;
            margin: 15px auto;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875em;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
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



        .posts-container {
            max-width: 1000px;
            margin: 0 auto;
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 30px;
        }

        .posts-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .posts-header h2 {
            color: var(--primary);
            margin: 0;
        }

        .post-card {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .post-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .post-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary);
            margin: 0;
        }

        .post-type {
            display: inline-block;
            background: #e9f7ef;
            color: var(--primary);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .post-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .post-details {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }

        .post-detail {
            display: flex;
            align-items: center;
        }

        .post-detail i {
            margin-right: 8px;
            color: var(--primary);
        }

        .post-actions {
            display: flex;
            gap: 10px;
        }

        .btn-edit {
            background: var(--secondary);
            color: var(--dark);
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-edit:hover, .btn-delete:hover {
            opacity: 0.9;
        }

        .no-posts {
            text-align: center;
            padding: 30px;
            color: var(--text-light);
        }

        /* Responsive */
        @media (max-width: 768px) {            
            .post-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .post-actions {
                margin-top: 10px;
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
            
            .sell-form-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
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
                <a href="sell_post.php" class="active">
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
                <a href="farmer_price_list.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Price List</span>
                </a>
                <a href="farmer_logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
        
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
                <a href="sell_post.php" class="active">
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
                <a href="farmer_price_list.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Price List</span>
                </a>
                <a href="farmer_logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="page-title">
                    <h1><i class="fas fa-plus-circle"></i> <?php echo $action === 'edit' ? 'Edit' : 'Create'; ?> Sell Post</h1>
                </div>
            </div>
            
            <!-- Form Container -->
            <div class="sell-form-container">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($errors['database'])): ?>
                    <div class="alert alert-danger"><?php echo $errors['database']; ?></div>
                <?php endif; ?>
                
                <form action="sell_post.php<?php echo $action === 'edit' ? '?action=edit&id='.$crop_id : ''; ?>" method="post" enctype="multipart/form-data">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="crop_name" class="form-label">Crop Name</label>
                                <input type="text" class="form-control <?php echo isset($errors['crop_name']) ? 'is-invalid' : ''; ?>" 
                                       id="crop_name" name="crop_name" 
                                       value="<?php echo htmlspecialchars($edit_data['crop_name'] ?? $crop_name ?? ''); ?>">
                                <?php if (isset($errors['crop_name'])): ?>
                                    <div class="error-message"><?php echo $errors['crop_name']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="crop_type" class="form-label">Crop Type</label>
                                <select class="form-control <?php echo isset($errors['crop_type']) ? 'is-invalid' : ''; ?>" 
                                        id="crop_type" name="crop_type">
                                    <option value="">Select Crop Type</option>
                                    <option value="Cereal" <?php echo (isset($edit_data['crop_type']) ? $edit_data['crop_type'] : (isset($crop_type) ? $crop_type : '')) == 'Cereal' ? 'selected' : ''; ?>>Cereal</option>
                                    <option value="Vegetable" <?php echo (isset($edit_data['crop_type']) ? $edit_data['crop_type'] : (isset($crop_type) ? $crop_type : '')) == 'Vegetable' ? 'selected' : ''; ?>>Vegetable</option>
                                    <option value="Fruit" <?php echo (isset($edit_data['crop_type']) ? $edit_data['crop_type'] : (isset($crop_type) ? $crop_type : '')) == 'Fruit' ? 'selected' : ''; ?>>Fruit</option>
                                    <option value="Pulse" <?php echo (isset($edit_data['crop_type']) ? $edit_data['crop_type'] : (isset($crop_type) ? $crop_type : '')) == 'Pulse' ? 'selected' : ''; ?>>Pulse</option>
                                    <option value="Oilseed" <?php echo (isset($edit_data['crop_type']) ? $edit_data['crop_type'] : (isset($crop_type) ? $crop_type : '')) == 'Oilseed' ? 'selected' : ''; ?>>Oilseed</option>
                                    <option value="Spice" <?php echo (isset($edit_data['crop_type']) ? $edit_data['crop_type'] : (isset($crop_type) ? $crop_type : '')) == 'Spice' ? 'selected' : ''; ?>>Spice</option>
                                </select>
                                <?php if (isset($errors['crop_type'])): ?>
                                    <div class="error-message"><?php echo $errors['crop_type']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity (kg)</label>
                                <input type="number" step="0.01" class="form-control <?php echo isset($errors['quantity']) ? 'is-invalid' : ''; ?>" 
                                       id="quantity" name="quantity" 
                                       value="<?php echo htmlspecialchars($edit_data['quantity'] ?? $quantity ?? ''); ?>">
                                <?php if (isset($errors['quantity'])): ?>
                                    <div class="error-message"><?php echo $errors['quantity']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price_per_kg" class="form-label">Price Per Kg (৳)</label>
                                <input type="number" step="0.01" class="form-control <?php echo isset($errors['price_per_kg']) ? 'is-invalid' : ''; ?>" 
                                       id="price_per_kg" name="price_per_kg" 
                                       value="<?php echo htmlspecialchars($edit_data['price_per_kg'] ?? $price_per_kg ?? ''); ?>">
                                <?php if (isset($errors['price_per_kg'])): ?>
                                    <div class="error-message"><?php echo $errors['price_per_kg']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="harvest_date" class="form-label">Harvest Date</label>
                                <input type="date" class="form-control <?php echo isset($errors['harvest_date']) ? 'is-invalid' : ''; ?>" 
                                       id="harvest_date" name="harvest_date" 
                                       value="<?php echo htmlspecialchars($edit_data['harvest_date'] ?? $harvest_date ?? ''); ?>">
                                <?php if (isset($errors['harvest_date'])): ?>
                                    <div class="error-message"><?php echo $errors['harvest_date']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="crop_image" class="form-label">Crop Image</label>
                                <input type="file" class="form-control <?php echo isset($errors['crop_image']) ? 'is-invalid' : ''; ?>" 
                                       id="crop_image" name="crop_image" accept="image/*">
                                <?php if (isset($errors['crop_image'])): ?>
                                    <div class="error-message"><?php echo $errors['crop_image']; ?></div>
                                <?php endif; ?>
                                <?php if ($action === 'edit' && !empty($edit_data['crop_image'])): ?>
                                    <div class="mt-2">
                                        <small>Current Image:</small><br>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($edit_data['crop_image']); ?>" alt="Current Crop Image" style="max-width: 100px; max-height: 100px;" class="mt-2">
                                    </div>
                                <?php endif; ?>
                                <img id="imagePreview" src="#" alt="Preview" class="image-preview" style="display: none;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-submit">
                            <i class="fas fa-save"></i> <?php echo $action === 'edit' ? 'Update' : 'Create'; ?> Post
                        </button>
                        <?php if ($action === 'edit'): ?>
                            <a href="sell_post.php" class="btn btn-secondary ms-2">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- Existing Posts Container -->
            <div class="posts-container">
                <div class="posts-header">
                    <h2><i class="fas fa-list"></i> Your Crop Posts</h2>
                    <div>
                        <span class="badge bg-primary"><?php echo $crop_posts->num_rows; ?> Posts</span>
                    </div>
                </div>
                
                <?php if ($crop_posts->num_rows > 0): ?>
                    <?php while ($post = $crop_posts->fetch_assoc()): ?>
                        <div class="post-card">
                            <div class="post-header">
                                <div>
                                    <h3 class="post-title"><?php echo htmlspecialchars($post['crop_name']); ?></h3>
                                    <span class="post-type"><?php echo htmlspecialchars($post['crop_type']); ?></span>
                                </div>
                                <div class="post-actions">
                                    <a href="sell_post.php?action=edit&id=<?php echo $post['crop_id']; ?>" class="btn btn-sm btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="sell_post.php?action=delete&id=<?php echo $post['crop_id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this post?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                            
                            <?php if (!empty($post['crop_image'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($post['crop_image']); ?>" alt="<?php echo htmlspecialchars($post['crop_name']); ?>" class="post-image">
                            <?php endif; ?>
                            
                            <div class="post-details">
                                <div class="post-detail">
                                    <i class="fas fa-weight-hanging"></i>
                                    <span><?php echo number_format($post['quantity'], 2); ?> kg available</span>
                                </div>
                                <div class="post-detail">
                                    <i class="fas fa-tag"></i>
                                    <span>৳<?php echo number_format($post['price_per_kg'], 2); ?> per kg</span>
                                </div>
                                <div class="post-detail">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Harvested on <?php echo date('M j, Y', strtotime($post['harvest_date'])); ?></span>
                                </div>
                                <div class="post-detail">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Status: 
                                        <span class="badge <?php echo $post['status'] === 'available' ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo ucfirst($post['status']); ?>
                                        </span>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="post-meta">
                                <small class="text-muted">Posted on <?php echo date('M j, Y \a\t g:i a', strtotime($post['created_at'])); ?></small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-posts">
                        <i class="fas fa-info-circle fa-3x mb-3" style="color: #ddd;"></i>
                        <h4>No Crop Posts Yet</h4>
                        <p>You haven't created any crop posts yet. Use the form above to create your first post!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image preview functionality
        document.getElementById('crop_image').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        // Set max date to today for harvest date
        document.getElementById('harvest_date').max = new Date().toISOString().split("T")[0];
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                new bootstrap.Alert(alert).close();
            });
        }, 5000);
    </script>
</body>
</html>