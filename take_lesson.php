<?php
session_start();

if (!isset($_SESSION['u_id']) || $_SESSION['role_id'] !== 'Student') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$u_id = $_SESSION['u_id'];
$c_id = isset($_GET['c_id']) ? intval($_GET['c_id']) : 0;

if ($c_id <= 0) {
    header("Location: student_home.php");
    exit();
}

/* Get student */
$student_q = $conn->query("SELECT s_id FROM student WHERE u_id = $u_id");

if (!$student_q || $student_q->num_rows == 0) {
    die("Student not found.");
}

$student = $student_q->fetch_assoc();
$s_id = $student['s_id'];

/* Check enrollment */
$enroll_q = $conn->query("
    SELECT * FROM enrollment 
    WHERE s_id = $s_id AND c_id = $c_id
");

if (!$enroll_q || $enroll_q->num_rows == 0) {
    header("Location: enrollmentpage.php?c_id=$c_id");
    exit();
}

/* Get course */
$course_q = $conn->query("
    SELECT course.*, categ.cat_name
    FROM course
    LEFT JOIN categ ON course.cat_id = categ.cat_id
    WHERE course.c_id = $c_id
");

if (!$course_q || $course_q->num_rows == 0) {
    die("Course not found.");
}

$course = $course_q->fetch_assoc();

$course_name = $course['c_name'];
$course_desc = $course['c_des'];
$category = $course['cat_name'] ?? 'Course';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Continue Learning</title>

<style>
* {
    margin: 0;
    padding: 0;
    
    font-family: Arial, sans-serif;
}

body {
    background: #f5f6fa;
    color: #111827;
}

.learning-wrapper {
    max-width: 1150px;
    margin: 35px auto;
    padding: 0 20px;
}

.back-link {
    display: inline-block;
    margin-bottom: 18px;
    color: #6b7280;
    text-decoration: none;
    font-size: 14px;
}

.course-header {
    background: linear-gradient(135deg, #111827, #4f46e5);
    color: white;
    padding: 35px;
    border-radius: 18px;
    margin-bottom: 28px;
}

.course-header span {
    background: rgba(255,255,255,0.18);
    padding: 7px 14px;
    border-radius: 20px;
    font-size: 13px;
    display: inline-block;
    margin-bottom: 14px;
}

.course-header h1 {
    font-size: 34px;
    margin-bottom: 10px;
}

.course-header p {
    color: #e5e7eb;
    max-width: 720px;
    line-height: 1.6;
}

.learning-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 22px;
}

.learning-box {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    text-decoration: none;
    color: #111827;
    box-shadow: 0 8px 22px rgba(0,0,0,0.08);
    transition: 0.3s;
}

.learning-box:hover {
    transform: translateY(-6px);
    box-shadow: 0 14px 30px rgba(0,0,0,0.12);
}

.box-top {
    height: 110px;
    background: linear-gradient(135deg, #7c3aed, #c026d3);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 38px;
}

.box-top.lesson {
    background: linear-gradient(135deg, #2563eb, #06b6d4);
}

.box-top.assignment {
    background: linear-gradient(135deg, #f97316, #facc15);
}

.box-top.exam {
    background: linear-gradient(135deg, #dc2626, #f43f5e);
}

.box-top.result {
    background: linear-gradient(135deg, #16a34a, #22c55e);
}

.box-body {
    padding: 20px;
}

.box-body h3 {
    font-size: 20px;
    margin-bottom: 8px;
}

.box-body p {
    font-size: 14px;
    color: #6b7280;
    line-height: 1.5;
    margin-bottom: 15px;
}

.box-btn {
    display: inline-block;
    background: #111827;
    color: white;
    padding: 9px 14px;
    border-radius: 8px;
    font-size: 13px;
}

.footer-note {
    margin-top: 35px;
    background: white;
    border-radius: 14px;
    padding: 22px;
    color: #6b7280;
    font-size: 14px;
}

@media(max-width: 1000px) {
    .learning-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media(max-width: 600px) {
    .learning-grid {
        grid-template-columns: 1fr;
    }

    .course-header h1 {
        font-size: 26px;
    }
}

.admin-navbar {
    width: calc(100% - 60px);
    margin: 15px auto;
    height: 85px;
    background: #fff;
    border-radius: 20px;
    border: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 25px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
}

/* LEFT */
.admin-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.logo-box {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #3b82f6, #7c2df2);
    color: white;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}

.logo-text h2 {
    font-size: 22px;
    margin: 0;
}

.logo-text p {
    font-size: 12px;
    color: #64748b;
}

/* MENU */
.admin-menu {
    display: flex;
    gap: 18px;
}

.admin-menu a {
    text-decoration: none;
    color: #475569;
    font-weight: 600;
    padding: 8px 14px;
    border-radius: 8px;
    transition: 0.2s;
}

.admin-menu a:hover {
    background: #f1f5f9;
}

.admin-menu a.active {
    background: #4f46e5;
    color: #fff;
}

/* USER */
.admin-user {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #f1f5f9;
    padding: 8px 10px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
}

.admin-name {
    color: #111827;
}

.logout-btn {
    text-decoration: none;
    background: #4f46e5;
    color: #fff;
    padding: 7px 13px;
    border-radius: 8px;
    font-size: 13px;
    transition: 0.2s;
}

.logout-btn:hover {
    background: #e48343;
}


.notification-box {
    background: white;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
}

.notification-box h3 {
    margin-bottom: 12px;
    font-size: 18px;
}

.notification-item {
    background: #f8fafc;
    padding: 10px 12px;
    border-radius: 10px;
    margin-bottom: 8px;
    font-size: 14px;
    color: #374151;
}

.admin-navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* MENU CENTER */
.admin-menu {
    display: flex;
    gap: 18px;
}

/* NOTIFICATIONS */
.admin-notifications {
    background: #f1f5f9;
    padding: 8px 14px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    cursor: pointer;
    transition: 0.2s;
    margin-left: auto;
    margin-right: 15px;
}

.admin-notifications:hover {
    background: #e2e8f0;
}
.nav-links a{
      text-decoration: none;
    color: #475569;
    font-weight: 600;
    padding: 8px 14px;
    border-radius: 8px;
    transition: 0.2s;
}
</style>
</head>

<body>

<header class="admin-navbar">

    <div class="admin-left">
        <div class="logo-box">🛠️</div>
        <div class="logo-text">
            <h2>Smart-LearnHub</h2>
            <p>student Panel</p>
        </div>
    </div>

    


    <nav class="nav-links">
        <a href="student_index.php">Home</a>
        <a href="all_courses.php">Courses</a>
        <a href="student_home.php">Dashboard</a>
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

<div class="learning-wrapper">

    <a href="student_home.php" class="back-link">← Back to Dashboard</a>

    <div class="course-header">
        <span><?php echo htmlspecialchars($category); ?></span>
        <h1><?php echo htmlspecialchars($course_name); ?></h1>
        <p><?php echo htmlspecialchars($course_desc); ?></p>
    </div>

    <div class="notification-box">
    <h3>🔔 Notifications</h3>

    <?php
    $now = date("Y-m-d H:i:s");
    $notifications = [];

    /*  Exam Notifications */
    $testQuery = $conn->query("
        SELECT t.test_type, t.start_time, t.end_time, tr.test_result_id
        FROM enrollment e
        JOIN test t ON e.c_id = t.c_id
        LEFT JOIN test_result tr ON tr.test_id = t.test_id AND tr.en_id = e.en_id
        WHERE e.s_id = $s_id AND e.c_id = $c_id   
    ");

    if ($testQuery && $testQuery->num_rows > 0) {
        while ($t = $testQuery->fetch_assoc()) {
            $not_started = ($now < $t['start_time']);
            $ended = ($now > $t['end_time']);
            
            if ($not_started) {
                $msg =   htmlspecialchars($t['test_type']) . " will start at " . $t['start_time'];
            } elseif ($ended && empty($t['test_result_id'])) {
                $msg = " You missed the " . htmlspecialchars($t['test_type']);
            } elseif (!empty($t['test_result_id'])) {
                $msg = " You submitted the " . htmlspecialchars($t['test_type']);
            } else {
                $msg =  htmlspecialchars($t['test_type']) . " is ongoing";
            }
            
            $notifications[$t['start_time'] . '_test'] = $msg;
        }
    }

    
    $assignQuery = $conn->query("
        SELECT a.assignment_id, a.assign_title, a.deadline, a.created_at, ar.assignment_rest_id
        FROM assignment a
        LEFT JOIN assignmet_result ar ON a.assignment_id = ar.assignment_id AND ar.s_id = $s_id
        WHERE a.c_id = $c_id
    ");

    if ($assignQuery && $assignQuery->num_rows > 0) {
        while ($a = $assignQuery->fetch_assoc()) {
           
            $deadline_time = $a['deadline'] . " 23:59:59"; 
            $overdue = ($now > $deadline_time);
            
            if (!empty($a['assignment_rest_id'])) {
                $msg = " Assignment Submitted: " . htmlspecialchars($a['assign_title']);
            } elseif ($overdue) {
                $msg = " Missed Assignment: " . htmlspecialchars($a['assign_title']);
            } else {
                $msg = " New Assignment Posted: \"" . htmlspecialchars($a['assign_title']) . "\" (Due: " . $a['deadline'] . ")";
            }
            
            $notifications[$a['created_at'] . '_assign'] = $msg;
        }
    }

    // Sort notifications latest
    krsort($notifications);
    
    //  displaying to top 5 combined records
    $display_notifications = array_slice($notifications, 0, 5);

    if (!empty($display_notifications)):
        foreach ($display_notifications as $notif):
    ?>
        <div class="notification-item"><?php echo $notif; ?></div>
    <?php 
        endforeach; 
    else: 
    ?>
        <div class="notification-item">No notifications for this course.</div>
    <?php endif; ?>
</div>
    
</div>
  <div class="learning-grid">

    <a href="student_courses.php?c_id=<?php echo $c_id; ?>" class="learning-box">
        <div class="box-top lesson">Lesson</div>
        <div class="box-body">
            <h3>Lessons</h3>
            <p>Watch video lectures and open PDF materials for this course.</p>
            <span class="box-btn">Open Lessons</span>
        </div>
    </a>

    <a href="assignment.php?c_id=<?php echo $c_id; ?>" class="learning-box">
        <div class="box-top assignment">Assignment</div>
        <div class="box-body">
            <h3>Assignments</h3>
            <p>View and submit your course assignments before the deadline.</p>
            <span class="box-btn">View Assignments</span>
        </div>
    </a>

          <a href="student_quizzes.php?c_id=<?php echo $c_id; ?>" class="learning-box">
        <div class="box-top exam">Quiz / Exam</div>
        <div class="box-body">
            <h3>Quizzes</h3>
            <p>Take quizzes and exams assigned for this course.</p>
            <span class="box-btn">Start Quiz</span>
        </div>
    </a>

    <a href="student_results.php?c_id=<?php echo $c_id; ?>" class="learning-box">
        <div class="box-top result">Result</div>
        <div class="box-body">
            <h3>Assignment / Quiz Result</h3>
            <p>Check your marks, grades, and performance for this course.</p>
            <span class="box-btn">View Results</span>
        </div>
    </a>
    

</div>

    <div class="footer-note">
        Continue your learning journey from one place. Choose Lessons, Assignments, Exams, or Result History.
    </div>

</div>

</body>
</html>