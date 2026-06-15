<link rel="stylesheet" href="teacher_navbar.css?v=6">

<header class="teacher-navbar">

    <div class="teacher-left">
        <div class="logo-box">🛠️</div>
        <div class="logo-text">
            <h2>Smart-LearnHub</h2>
            <p>Teacher Panel</p>
        </div>
    </div>

    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    ?>

    <nav class="teacher-menu">
        <a href="teacher_home.php" class="<?php echo ($current_page == 'teacher_home.php') ? 'active' : ''; ?>">Home</a>
        
        <a href="teacher_submissions.php" class="<?php echo ($current_page == 'teacher_submissions.php') ? 'active' : ''; ?>">Submissions</a>
        <a href="teacher_course_assignment_submission.php" class="<?php echo ($current_page == 'teacher_course_assignment_submission.php') ? 'active' : ''; ?>">Assignment submisson</a>
        <a href="teacher_final_grading.php" class="<?php echo ($current_page == 'teacher_final_grading.php') ? 'active' : ''; ?>">Final Grading Sheet</a>
        
    </nav>

    <div class="teacher-user">
        <div class="profle">
            <span class="teacher-name">
                👤 <?php echo $_SESSION['user_name'] ?? 'student'; ?>
            </span>
        </div>
        <div class="logout">
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

</header>