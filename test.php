<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard with Collapsible Navbar</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        /* Custom styles for collapsed navbar */
        .navbar-collapsed {
            width: 80px;
            transition: width 0.7s ease-in-out; /* Smooth transition */
        }

        /* Custom styles for expanded navbar */
        .navbar-expanded {
            width: 250px;
            transition: width 0.7s ease-in-out; /* Smooth transition */
        }

        /* Hide text when collapsed */
        .nav-item .nav-link span {
            display: none;
        }

        /* Show text when expanded */
        .navbar-expanded .nav-item .nav-link span {
            display: inline;
        }

        /* Adjust icon size when collapsed */
        .navbar-collapsed .nav-item .nav-link i {
            font-size: 24px;
        }

        /* Collapse button styles */
        .navbar-toggler {
            display: block;
            margin-left: auto;
        }

        /* Fixed Sidebar */
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh; /* Full height */
            z-index: 1030; /* Ensure it stays on top */
        }

        /* Header and Footer styles */
        header {
            background-color: #343a40;
            color: white;
            padding: 1rem;
            text-align: center;
        }

        footer {
            background-color: #343a40;
            color: white;
            padding: 1rem;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: 100%;
            z-index: 1020;
        }

        /* Ensure content is pushed to the right of the fixed navbar */
        .content {
            margin-left: 80px;
            transition: margin-left 0.7s ease-in-out;
        }

        /* Adjust when navbar is expanded */
        .navbar-expanded ~ .content {
            margin-left: 250px;
        }
    </style>
</head>
<body>

<!-- Header -->
<header>
    <h1>Dashboard Header</h1>
</header>

<div class="d-flex" id="toggleNavbar">
    <!-- Sidebar -->
    <nav id="sidebar" class="bg-dark text-white navbar-collapsed">
        <button class="navbar-toggler text-white" id="toggleNavbar">
            <i class="bi bi-list"></i>
        </button>
        <ul class="nav flex-column pt-3">
            <li class="nav-item">
                <a class="nav-link text-white" href="#">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="#">
                    <i class="bi bi-gear-fill"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="#">
                    <i class="bi bi-person-fill"></i>
                    <span>Profile</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="#">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main content -->
    <div class="container-fluid p-4 content">
        <h1>Main Content Area</h1>
        <p>This is where the dashboard's main content would go.</p>
    </div>
</div>

<!-- Footer -->
<footer>
    <p>Dashboard Footer &copy; 2024</p>
</footer>

<!-- Bootstrap 5 JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.getElementById('toggleNavbar').addEventListener('click', function() {
        var sidebar = document.getElementById('sidebar');
        var content = document.querySelector('.content');
        sidebar.classList.toggle('navbar-collapsed');
        sidebar.classList.toggle('navbar-expanded');
        content.classList.toggle('navbar-expanded');
    });
</script>

</body>
</html>
