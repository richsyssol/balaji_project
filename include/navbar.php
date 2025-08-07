<?php include 'header.php'; ?>



<!-- navbar.php -->
<nav id="sidebar" class="text-white navbar-collapsed neumorphic">

    <img src="asset/image/Ujjwal_pingale-SIP.png" width="80px" class="pt-5">

    <ul class="nav flex-column mt-5">

            <li class="nav-item nav-name">
                <a class="nav-link text-white" href="todo">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <span>TODO Manager</span>
                </a>
            </li>
        
        <!-- Admin-only sections -->
            <li class="nav-item nav-name">
                <a class="nav-link text-white" href="index">
                    <i class="fa-solid fa-house"></i>
                    <span>Dashboard</span>
                </a>
            </li>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') : ?>
            
            
            <li class="nav-item nav-name">
                <a class="nav-link text-white" href="client">
                    <i class="fa-solid fa-user"></i>
                    <span>Add Client</span>
                </a>
            </li>
            
            <!-- Full Register Entry menu for admin -->
            <li class="nav-item nav-name dropdown">
                <a class="nav-link text-white dropdown-toggle" href="#" id="dropdownMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-table"></i>
                    <span>Register Entry</span>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item text-dark" href="gic">GIC</a></li>
                    <li><a class="dropdown-item text-dark" href="lic">LIC</a></li>
                    <li><a class="dropdown-item text-dark" href="rto">R/JOBS</a></li>
                    <li><a class="dropdown-item text-dark" href="bmds">BMDS</a></li>
                    <li><a class="dropdown-item text-dark" href="mf">MF</a></li>
                </ul>
            </li>

            <!-- Admin-only sections like Expenses, Reports, User management -->
            <li class="nav-item nav-name">
                <a class="nav-link text-white" href="expense">
                    <i class="fa-solid fa-money-bill"></i>
                    <span>Expenses</span>
                </a>
            </li>

            

            <li class="nav-item nav-name">
                <a class="nav-link text-white" href="report">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span>Reports</span>
                </a>
            </li>

            <li class="nav-item nav-name">
                <a class="nav-link text-white" href="messages">
                    <i class="fa-solid fa-message"></i>
                    <span>Send Msg</span>
                </a>
            </li>
            
            <li class="nav-item nav-name">
                <a class="nav-link text-white" href="letter">
                    <i class="fa-solid fa-envelope"></i>
                    <span>Letter Generator</span>
                </a>
            </li>

            
            <li class="nav-item nav-name">
                <a class="nav-link text-white" href="trash">
                    <i class="fa-solid fa-trash"></i>
                    <span>Trash Bin</span>
                </a>
            </li>
            
            <li class="nav-item nav-name">
                <a class="nav-link text-white" href="user">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>User</span>
                </a>
            </li>

            
            <li class="nav-item nav-name">
                <a class="nav-link text-white" href="goal">
                    <i class="fa-solid fa-signal"></i>
                    <span>Set Goal</span>
                </a>
            </li>
            
            <li class="nav-item nav-name">
                <a class="nav-link text-white" href="thought">
                    <i class="fa-solid fa-comments"></i>
                    <span>Thought</span>
                </a>
            </li>

            <li class="nav-item nav-name">
                <a class="nav-link text-white" href="store">
                    <i class="fa-solid fa-images"></i>
                    <span>Image Store</span>
                </a>
            </li>

            <li class="nav-item nav-name">
                <a class="nav-link text-white" href="billing_system">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span>Reports</span>
                </a>
            </li>
        <?php endif; ?>

        <!-- Fields visible to both admin and user -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'user') : ?>

            <li class="nav-item nav-name">
                <a class="nav-link text-white" href="client">
                    <i class="fa-solid fa-user"></i>
                    <span>Add Client</span>
                </a>
            </li>

            <!-- Add Register Entry (visible only user) -->
            <li class="nav-item nav-name dropdown">
                <a class="nav-link text-white dropdown-toggle" href="#" id="dropdownMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-address-card"></i>
                    <span>Add Register Entry</span>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item text-dark" href="gic"> GIC</a></li>
                    <li><a class="dropdown-item text-dark" href="lic"> LIC</a></li>
                    <li><a class="dropdown-item text-dark" href="rto"> R/JOBS</a></li>
                    <li><a class="dropdown-item text-dark" href="bmds"> BMDS</a></li>
                    <li><a class="dropdown-item text-dark" href="mf"> MF</a></li>
                </ul>
            </li>

            <!-- Add Expenses (visible only user user) -->
            <li class="nav-item nav-name">
                <a class="nav-link text-white" href="expense">
                    <i class="fa-solid fa-money-bill"></i>
                    <span> Expenses</span>
                </a>
            </li>

            <li class="nav-item nav-name">
                <a class="nav-link text-white" href="report">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span>Reports</span>
                </a>
            </li>

            

        <?php endif; ?>

        <!-- Logout option for both admin and user -->
        <!-- <li class="nav-item nav-name">
            <a class="nav-link text-white" href="logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </li> -->
    </ul>
</nav>




<?php include 'header1.php'; ?>