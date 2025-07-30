<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Agromati</title>
    
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
        
        a {
            text-decoration: none;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
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
        
        .top-navbar .nav-toggle {
            display: none; /* Hidden on desktop */
            font-size: 1.5rem;
            color: var(--primary);
            cursor: pointer;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            border: 2px solid var(--primary);
        }

        .content-area {
            padding: 25px;
            flex-grow: 1;
        }
        
        /* Dashboard Components */
        .dashboard-card {
            background: var(--white);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        @media (hover: hover) {
            .dashboard-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            }
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: var(--white);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary);
        }
        
        .stat-card h3 {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-card p {
            color: var(--text-light);
            margin: 0;
        }
        
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: none;
            border: 1px solid #dee2e6;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            background: var(--primary);
            color: var(--white);
            font-weight: 600;
            border: none;
        }

        .table td, .table th {
            vertical-align: middle;
            padding: 12px 15px;
        }
        
        .badge.bg-success-light {
            background-color: rgba(40, 167, 69, 0.15);
            color: var(--primary-dark);
        }
        
        .badge.bg-warning-light {
            background-color: rgba(255, 193, 7, 0.15);
            color: #b98900;
        }

        .agro-theme-img {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: var(--shadow);
        }
        
        /* Activity Feed */
        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .activity-item:last-child { border-bottom: none; }
        
        .activity-item .icon {
            width: 40px;
            height: 40px;
            min-width: 40px;
            background: rgba(40, 167, 69, 0.1);
            border-radius: 50%;
            display: grid;
            place-items: center;
            color: var(--primary);
            margin-right: 15px;
            font-size: 1.2rem;
        }

        .activity-time {
            color: var(--text-light);
            font-size: 0.9rem;
            white-space: nowrap;
            margin-left: 15px;
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            :root { --sidebar-width: 220px; }
            
            .top-navbar .nav-toggle { display: block; }
            
            .sidebar {
                left: calc(-1 * var(--sidebar-width));
            }
            .sidebar.active {
                left: 0;
                box-shadow: 0 0 20px rgba(0,0,0,0.2);
            }

            .main-content {
                margin-left: 0;
            }
            
            .overlay {
                position: fixed;
                top: 0; left: 0;
                width: 100%; height: 100%;
                background: rgba(0,0,0,0.4);
                z-index: 999;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s ease;
            }
            .overlay.active {
                opacity: 1;
                visibility: visible;
            }
        }
        
        @media (max-width: 576px) {
            .content-area { padding: 15px; }
            .dashboard-card { padding: 15px; }
            
            .user-info span { display: none; } /* Hide admin name on small screens */

            .activity-item { flex-wrap: wrap; }
            .activity-text { flex-basis: 100%; margin-bottom: 5px; }
            .activity-time { margin-left: 0; }

            .table td, .table th { font-size: 0.9rem; padding: 10px 8px; }
            
            .top-navbar { padding: 10px 15px; }
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
                <a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="crop-prices.php"><i class="fas fa-seedling"></i> Manage Crops price</a>
                <a href="farmersList.php"><i class="fas fa-users"></i> Farmers List</a>
                <a href="buyersList.php"><i class="fas fa-store"></i> Buyers List</a>
                <a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>
        
        <div class="overlay"></div>

        <main class="main-content">
            <header class="top-navbar">
                <i class="fas fa-bars nav-toggle"></i>
                <h4 class="mb-0 d-none d-md-block">Admin Dashboard</h4>
                <div class="user-info">
                    <img src="https://i.pravatar.cc/150?u=admin" alt="Admin">
                    <span>Admin User</span>
                </div>
            </header>
            
            <div class="content-area">
                <img src="images/agro-theme-banner.png" alt="Agriculture Theme" class="agro-theme-img">
                
                <section class="stats-grid">
                    <div class="stat-card">
                        <h3>1,254</h3>
                        <p>Total Farmers</p>
                    </div>
                    <div class="stat-card">
                        <h3>586</h3>
                        <p>Registered Buyers</p>
                    </div>
                    <div class="stat-card">
                        <h3>24</h3>
                        <p>Crop Categories</p>
                    </div>
                    <div class="stat-card">
                        <h3>৳1.2M</h3>
                        <p>Monthly Transactions</p>
                    </div>
                </section>
                
                <section class="dashboard-card" id="crop-prices">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0"><i class="fas fa-seedling text-success"></i> Crop Prices Management</h4>
                        <button class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Add New Crop</button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Crop Name</th>
                                    <th>Price (৳/kg)</th>
                                    <th>Last Updated</th>
                                    <th>Trend</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><img src="https://via.placeholder.com/30x30/CCCCCC/FFFFFF?text=R" class="rounded-circle me-2" alt="rice"> Rice (Miniket)</td>
                                    <td>৳52</td>
                                    <td>Today, 10:30 AM</td>
                                    <td><i class="fas fa-arrow-up text-success"></i> 2.5%</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><img src="https://via.placeholder.com/30x30/CCCCCC/FFFFFF?text=P" class="rounded-circle me-2" alt="potato"> Potato</td>
                                    <td>৳28</td>
                                    <td>Yesterday, 3:45 PM</td>
                                    <td><i class="fas fa-arrow-down text-danger"></i> 1.2%</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><img src="https://via.placeholder.com/30x30/CCCCCC/FFFFFF?text=T" class="rounded-circle me-2" alt="tomato"> Tomato</td>
                                    <td>৳45</td>
                                    <td>Yesterday, 11:20 AM</td>
                                    <td><i class="fas fa-arrow-up text-success"></i> 5.7%</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
                
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <section class="dashboard-card h-100" id="farmers">
                            <h4 class="mb-3"><i class="fas fa-users text-success"></i> Farmers List</h4>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><img src="https://i.pravatar.cc/150?u=farmer1" width="30" height="30" class="rounded-circle me-2"> Rojina Begum</td>
                                            <td>Bogura</td>
                                            <td><span class="badge rounded-pill bg-success-light">Active</span></td>
                                            <td><button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button></td>
                                        </tr>
                                        <tr>
                                            <td><img src="https://i.pravatar.cc/150?u=farmer2" width="30" height="30" class="rounded-circle me-2"> Abdul Karim</td>
                                            <td>Rangpur</td>
                                            <td><span class="badge rounded-pill bg-success-light">Active</span></td>
                                            <td><button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button></td>
                                        </tr>
                                        <tr>
                                            <td><img src="https://i.pravatar.cc/150?u=farmer3" width="30" height="30" class="rounded-circle me-2"> Mohammad Ali</td>
                                            <td>Dinajpur</td>
                                            <td><span class="badge rounded-pill bg-warning-light">Pending</span></td>
                                            <td><button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <section class="dashboard-card h-100" id="buyers">
                            <h4 class="mb-3"><i class="fas fa-store text-success"></i> Buyers List</h4>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Business Name</th>
                                            <th>Location</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Dhaka Grocery Hub</td>
                                            <td>Dhaka</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-outline-info"><i class="fas fa-envelope"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Chittagong Food Supply</td>
                                            <td>Chittagong</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-outline-info"><i class="fas fa-envelope"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Khulna Fresh Market</td>
                                            <td>Khulna</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-outline-info"><i class="fas fa-envelope"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </div>
                
                <section class="dashboard-card" id="transactions">
                    <h4 class="mb-3"><i class="fas fa-history text-success"></i> Recent Activity</h4>
                    <div class="recent-activity">
                        <div class="activity-item">
                            <div class="icon"><i class="fas fa-seedling"></i></div>
                            <div class="activity-text"><strong>Rice price updated</strong> to ৳52 per kg</div>
                            <div class="activity-time">2 hours ago</div>
                        </div>
                        <div class="activity-item">
                            <div class="icon"><i class="fas fa-user-plus"></i></div>
                            <div class="activity-text"><strong>New farmer registered:</strong> Mohammad Ali</div>
                            <div class="activity-time">5 hours ago</div>
                        </div>
                        <div class="activity-item">
                            <div class="icon"><i class="fas fa-exchange-alt"></i></div>
                            <div class="activity-text"><strong>Transaction:</strong> 500kg Potato sold by A. Karim</div>
                            <div class="activity-time">1 day ago</div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navToggle = document.querySelector('.nav-toggle');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.overlay');
            const sidebarLinks = document.querySelectorAll('.sidebar-menu a');

            // Function to toggle the sidebar
            function toggleSidebar() {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            }

            // Event listeners for opening/closing the sidebar
            if (navToggle) {
                navToggle.addEventListener('click', toggleSidebar);
            }
            if (overlay) {
                overlay.addEventListener('click', toggleSidebar);
            }
            
            // --- Sidebar Functionality ---
            
            // 1. Set active link based on current section in view
            const sections = document.querySelectorAll('.content-area section');
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        sidebarLinks.forEach(link => {
                            link.classList.remove('active');
                            if (link.getAttribute('href') === `#${entry.target.id}`) {
                                link.classList.add('active');
                            }
                        });
                    }
                });
            }, { rootMargin: '-40% 0px -60% 0px' }); // Adjust margins to trigger in the middle of the viewport

            sections.forEach(section => {
                if (section.id) {
                    observer.observe(section);
                }
            });

            // 2. Smooth scrolling for sidebar links & close mobile menu on click
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    
                    // Handle internal links
                    if (href.startsWith('#')) {
                        e.preventDefault();
                        const targetEl = document.querySelector(href);
                        if (targetEl) {
                            const topOffset = document.querySelector('.top-navbar').offsetHeight + 15;
                            const elementPosition = targetEl.getBoundingClientRect().top;
                            const offsetPosition = elementPosition + window.pageYOffset - topOffset;
                      
                            window.scrollTo({
                                top: offsetPosition,
                                behavior: "smooth"
                            });
                        }
                        // Close sidebar on mobile after clicking a link
                        if (sidebar.classList.contains('active')) {
                            toggleSidebar();
                        }
                    }
                    // For external links like logout.php, the browser will navigate normally.
                });
            });
        });
    </script>
</body>
</html>