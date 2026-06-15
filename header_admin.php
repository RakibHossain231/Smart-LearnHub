<link rel="stylesheet" href="header_admin.css?v=4">

<header class="admin-navbar">

    <div class="admin-left">
        <div class="logo-box">🛠️</div>
        <div class="logo-text">
            <h2>Smart-LearnHub</h2>
            <p>Admin Panel</p>
        </div>
    </div>

    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    ?>

    <nav class="admin-menu">
        <a href="admin_home.php" 
        class="<?php if($current_page == 'admin_home.php') echo 'active'; ?>">
            Home
        </a>

        <a href="admin_courses_view.php" 
        class="<?php if(strpos($current_page, 'course') !== false) echo 'active'; ?>">
            Courses
        </a>

    

        <a href="category_management.php" 
        class="<?php if($current_page == 'category_management.php') echo 'active'; ?>">
            Dashboard
        </a>

    </nav>

    <div class="admin-user">
        <div class="profle">
            <span class="admin-name">
                👤 <?php echo $_SESSION['user_name'] ?? 'Admin'; ?>
            </span>
        </div>
        <div class="logout">
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        
    </div>

</header>