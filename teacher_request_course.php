<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

if (!isset($_SESSION['u_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role_id'] !== 'Teacher') {
    header("Location: index.php");
    exit();
}

$u_id = intval($_SESSION['u_id']);

// Get teacher info
$teacherResult = $conn->query("
    SELECT * FROM teacher WHERE u_id = $u_id
");

if ($teacherResult->num_rows == 0) {
    die("Teacher profile not found.");
}

$teacher = $teacherResult->fetch_assoc();
$t_id = $teacher['t_id'];

// Handle course request
if (isset($_GET['request_course']) && isset($_GET['c_id'])) {
    $course_id = intval($_GET['c_id']);
    
    $check_sql = "SELECT * FROM assigned_course WHERE t_id = $t_id AND c_id = $course_id";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        $existing = $check_result->fetch_assoc();
        if ($existing['as_status'] == 'Rejected') {
            $update_sql = "UPDATE assigned_course SET as_status = 'Pending' WHERE t_id = $t_id AND c_id = $course_id";
            if ($conn->query($update_sql)) {
                $message = "Course request resent successfully!";
                $msg_type = "success";
            }
        } else {
            $message = "You have already requested this course!";
            $msg_type = "error";
        }
    } else {
        $insert_sql = "INSERT INTO assigned_course (as_status, c_id, t_id) VALUES ('Pending', $course_id, $t_id)";
        if ($conn->query($insert_sql)) {
            $message = "Course request sent successfully to Admin!";
            $msg_type = "success";
        }
    }
}

// Get teacher's requested courses
$my_requests_sql = "
    SELECT 
        assigned_course.as_id,
        assigned_course.as_status,
        assigned_course.created_at,
        course.c_id,
        course.c_name,
        course.c_des,
        course.c_image,
        course.c_price,
        categ.cat_name
    FROM assigned_course
    INNER JOIN course ON assigned_course.c_id = course.c_id
    INNER JOIN categ ON course.cat_id = categ.cat_id
    WHERE assigned_course.t_id = $t_id
    ORDER BY assigned_course.as_id DESC
";
$my_requests = $conn->query($my_requests_sql);

// Get available courses
$available_courses_sql = "
    SELECT 
        course.c_id,
        course.c_name,
        course.c_des,
        course.c_price,
        course.c_image,
        categ.cat_name
    FROM course
    INNER JOIN categ ON course.cat_id = categ.cat_id
    WHERE course.c_id NOT IN (
        SELECT c_id FROM assigned_course WHERE t_id = $t_id
    )
    ORDER BY course.c_id DESC
";
$available_courses = $conn->query($available_courses_sql);

// Stats
$pending_count = $conn->query("SELECT COUNT(*) AS total FROM assigned_course WHERE t_id = $t_id AND as_status = 'Pending'")->fetch_assoc()['total'];
$approved_count = $conn->query("SELECT COUNT(*) AS total FROM assigned_course WHERE t_id = $t_id AND as_status = 'Approved'")->fetch_assoc()['total'];
$rejected_count = $conn->query("SELECT COUNT(*) AS total FROM assigned_course WHERE t_id = $t_id AND as_status = 'Rejected'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Courses | Teacher Panel</title>
    <link rel="stylesheet" href="teacher_request_course.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="teacher-navbar">
    <div class="teacher-left">
        <div class="logo-box">🛠️</div>
        <div class="logo-text">
            <h2>Smart-LearnHub</h2>
            <p>Teacher Panel</p>
        </div>
    </div>

    <nav class="nav-links">
        <a href="teacher_home.php">Home</a>
        <a href="teacher_request_course.php" class="active">Request Courses</a>
        <a href="teacher_submissions.php">Submissions</a>
        <a href="teacher_course_assignment_submission.php">Assignment submission</a>
        <a href="teacher_final_grading.php">Final Grading Sheet</a>
    </nav>

    <div class="teacher-user">
        <span class="teacher-name">👤 <?php echo $_SESSION['user_name'] ?? 'Teacher'; ?></span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<!--  MAIN CONTENT  -->
<main class="request-main">
    <div class="request-container">
        
        <!-- Hero Section -->
        <div class="request-hero">
            <h1>Course Requests</h1>
            <p>Browse and request courses to teach from the admin</p>
        </div>

        <!-- Message Display -->
        <?php if(isset($message)): ?>
            <div class="message-box <?php echo $msg_type; ?>">
                <i class="fas <?php echo $msg_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon pending-icon"><i class="fa-regular fa-clock"></i></div>
                <div class="stat-info">
                    <h3><?php echo $pending_count; ?></h3>
                    <p>Pending Requests</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon approved-icon"><i class="fa-regular fa-circle-check"></i></div>
                <div class="stat-info">
                    <h3><?php echo $approved_count; ?></h3>
                    <p>Approved Courses</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon rejected-icon"><i class="fa-solid fa-xmark"></i></div>
                <div class="stat-info">
                    <h3><?php echo $rejected_count; ?></h3>
                    <p>Rejected Requests</p>
                </div>
            </div>
        </div>

        <!-- My Requests Section -->
        <div class="section-box">
            <h2><i class="fas fa-list-ul"></i> My Course Requests</h2>
            
            <?php if($my_requests->num_rows > 0): ?>
                <table class="requests-table">
                    <thead>
                        <tr><th>Course</th><th>Category</th><th>Price</th><th>Requested On</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php while($req = $my_requests->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="course-info-cell">
                                    <img src="<?php echo htmlspecialchars($req['c_image']); ?>" alt="">
                                    <div>
                                        <strong><?php echo htmlspecialchars($req['c_name']); ?></strong>
                                        <small><?php echo substr(htmlspecialchars($req['c_des']), 0, 50); ?>...</small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($req['cat_name']); ?></td>
                            <td><?php echo $req['c_price'] == 'Free' ? 'Free' : '$'.$req['c_price']; ?></td>
                            <td><?php echo date('d M Y', strtotime($req['created_at'])); ?></td>
                            <td><span class="status-badge <?php echo strtolower($req['as_status']); ?>"><?php echo $req['as_status']; ?></span></td>
                            <td>
                                <?php if($req['as_status'] == 'Rejected'): ?>
                                    <a href="?request_course=1&c_id=<?php echo $req['c_id']; ?>" class="action-btn retry-btn">Request Again</a>
                                <?php elseif($req['as_status'] == 'Approved'): ?>
                                    <a href="teacher_lessons.php?course_id=<?php echo $req['c_id']; ?>" class="action-btn manage-btn">Manage</a>
                                <?php else: ?>
                                    <span class="waiting-text">Waiting</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">No course requests yet.</div>
            <?php endif; ?>
        </div>

        <!-- Available Courses Section -->
        <div class="section-box">
            <h2><i class="fas fa-book-open"></i> Available Courses to Request</h2>
            
            <?php if($available_courses->num_rows > 0): ?>
                <div class="courses-grid">
                    <?php while($course = $available_courses->fetch_assoc()): ?>
                    <div class="course-card">
                        <img src="<?php echo htmlspecialchars($course['c_image']); ?>" alt="">
                        <div class="course-body">
                            <h3><?php echo htmlspecialchars($course['c_name']); ?></h3>
                            <p><?php echo substr(htmlspecialchars($course['c_des']), 0, 80); ?>...</p>
                            <div class="course-meta">
                                <span class="category-tag"><?php echo $course['cat_name']; ?></span>
                                <span class="price-tag"><?php echo $course['c_price'] == 'Free' ? 'Free' : '$'.$course['c_price']; ?></span>
                            </div>
                            <a href="?request_course=1&c_id=<?php echo $course['c_id']; ?>" class="request-teach-btn" onclick="return confirm('Request to teach this course?')">Request to Teach</a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">You have requested all available courses!</div>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php include 'footer.php'; ?>

</body>
</html>