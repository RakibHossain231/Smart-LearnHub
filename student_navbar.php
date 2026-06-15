<link rel="stylesheet" href="student_navbar.css?v=4">

<header class="admin-navbar">

    <div class="admin-left">
        <div class="logo-box">🛠️</div>
        <div class="logo-text">
            <h2>Smart-LearnHub</h2>
            <p>student Panel</p>
        </div>
    </div>

    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    ?>


    <nav class="nav-links">
        <a href="student_index.php">Home</a>
        <a href="all_courses.php">Courses</a>
        <a href="student_home.php">Dashboard</a>
        <a href="student_attendance_summary.php">Attendeance</a>

    </nav>

    <div class="admin-user">
        <div class="profle">
            <span class="admin-name">
                👤 <?php echo $_SESSION['user_name'] ?? 'student'; ?>
            </span>
        </div>
        <div class="logout">
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        
    </div>

</header>