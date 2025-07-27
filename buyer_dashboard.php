<?php
session_start();
include('db.php');

// Check if user is logged in and is a farmer
if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, name, email, password, user_type FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // Redirect based on user type
                if ($user['user_type'] == 'buyer') {
                    header("Location: buyer_dashboard.php");
                } 
            } 
        } 
        $stmt->close();
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard - Agromati</title>
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

        /* Dashboard Layout */
        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: var(--primary);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header .buyer-avatar {
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

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left: 3px solid white;
        }

        .sidebar-menu a i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        /* Main Content Area */
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
            z-index: 999;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }

        .content-area {
            padding: 25px;
        }

        /* Dashboard Cards */
        .dashboard-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
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

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .stat-card p {
            color: var(--text-light);
            margin-bottom: 0;
        }

        .stat-card i {
            font-size: 2rem;
            color: var(--primary);
            margin-top: 15px;
            opacity: 0.2;
        }

        /* Mobile Navigation Toggle */
        .nav-toggle { display: none; background: transparent; border: none; width: 30px; height: 24px; position: relative; cursor: pointer; z-index: 1001; padding: 0; }
        .nav-toggle span { display: block; position: absolute; height: 3px; width: 100%; background: var(--primary); border-radius: 3px; opacity: 1; left: 0; transform: rotate(0deg); transition: all 0.3s ease; }
        .nav-toggle span:nth-child(1) { top: 0; }
        .nav-toggle span:nth-child(2),
        .nav-toggle span:nth-child(3) { top: 10px; }
        .nav-toggle span:nth-child(4) { top: 20px; }
        .nav-toggle.active span:nth-child(1),
        .nav-toggle.active span:nth-child(4) { top: 10px; width: 0%; left: 50%; }
        .nav-toggle.active span:nth-child(2) { transform: rotate(45deg); }
        .nav-toggle.active span:nth-child(3) { transform: rotate(-45deg); }

        /* Overlay */
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999; opacity: 0; visibility: hidden; transition: all 0.3s ease; }
        .overlay.active { opacity: 1; visibility: visible; }

        /* Responsive Styles */
        @media (max-width: 992px) { .sidebar { width: 250px; } .main-content { margin-left: 250px; } .top-navbar { width: calc(100% - 250px); } }
        @media (max-width: 768px) { .nav-toggle { display: block; } .sidebar { width: 280px; left: -280px; } .sidebar.active { left: 0; } .main-content { margin-left: 0; } .main-content.shift { margin-left: 280px; } .top-navbar { width: 100%; } }
        @media (max-width: 576px) { .content-area { padding: 15px; } .stats-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="images/buyers/default.jpg" class="buyer-avatar" alt="Buyer">
                <h3><?php echo htmlspecialchars($buyer_name); ?></h3>
                <p>Buyer Member</p>
            </div>

            <div class="sidebar-menu">
                <a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="main-content">
            <div class="top-navbar">
                <button class="nav-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <h4>Buyer Dashboard</h4>
                <div class="user-info">
                    <span><?php echo date('l, F j, Y'); ?></span>
                </div>
            </div>

            <div class="content-area">
                <div class="dashboard-card">
                    <h4><i class="fas fa-shopping-cart"></i> Welcome, <?php echo htmlspecialchars($buyer_name); ?>!</h4>
                    <p>Here's a summary of your account.</p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>4.9</h3>
                        <p>Your Rating</p>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="overlay"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navToggle = document.querySelector('.nav-toggle');
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            const overlay = document.querySelector('.overlay');

            // Toggle navigation
            navToggle.addEventListener('click', function() {
                this.classList.toggle('active');
                sidebar.classList.toggle('active');
                mainContent.classList.toggle('shift');
                overlay.classList.toggle('active');
            });

            // Close navigation when clicking overlay
            overlay.addEventListener('click', function() {
                navToggle.classList.remove('active');
                sidebar.classList.remove('active');
                mainContent.classList.remove('shift');
                this.classList.remove('active');
            });

            // Close navigation when clicking a menu item
            const navItems = document.querySelectorAll('.sidebar-menu a');
            navItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        if (sidebar.classList.contains('active')) {
                           navToggle.classList.remove('active');
                           sidebar.classList.remove('active');
                           mainContent.classList.remove('shift');
                           overlay.classList.remove('active');
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>