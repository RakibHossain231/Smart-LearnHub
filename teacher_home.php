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

$u_id = $_SESSION['u_id'];

$u_id = intval($_SESSION['u_id']);

/* Get teacher info */
$teacherResult = $conn->query("
    SELECT * 
    FROM teacher 
    WHERE u_id = $u_id
");

if ($teacherResult->num_rows == 0) {
    die("Teacher profile not found.");
}

$teacher = $teacherResult->fetch_assoc();
$t_id = $teacher['t_id'];




/* Dashboard counts */
$countCourses = 0;
$countLessons = 0;
$countAssignments = 0;
$countTests = 0;

$courseCountResult = $conn->query("
    SELECT COUNT(*) AS total_courses 
    FROM assigned_course 
    WHERE t_id = $t_id
")->fetch_assoc();

$countCourses = $courseCountResult['total_courses'];

$lessonCountResult = $conn->query("
    SELECT COUNT(*) AS total_lessons 
    FROM lesson 
    WHERE t_id = $t_id
")->fetch_assoc();

$countLessons = $lessonCountResult['total_lessons'];


$assignmentCountResult = $conn->query("
    SELECT COUNT(*) AS total_assignments 
    FROM assignment 
    WHERE t_id = $t_id
")->fetch_assoc();

$countAssignments = $assignmentCountResult['total_assignments'];


$testCountResult = $conn->query("
    SELECT COUNT(*) AS total_submissions
    FROM test_result tr
    INNER JOIN test t ON tr.test_id = t.test_id
    WHERE t.t_id = $t_id
")->fetch_assoc();

$countTests = $testCountResult['total_submissions'];

/* Assigned courses */
$assignedCourses = $conn->query("
    SELECT 
        course.c_id,
        course.c_name,
        course.c_des,
        course.c_price,
        course.c_image,
        assigned_course.as_status
    FROM assigned_course
    INNER JOIN course ON assigned_course.c_id = course.c_id
    WHERE assigned_course.t_id = $t_id and assigned_course.as_status= 'Approved'
    ORDER BY course.c_id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
   
    <title>Teacher Dashboard | LMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .teacher-page {
            background: #f5f7fb;
            min-height: 100vh;
        }

        .teacher-container {
            padding: 28px 30px;

        }

        .teacher-hero {
             background: linear-gradient(135deg, #3b82f6, #6d28d9);
            color: white;
            border-radius: 24px;
            padding: 34px 30px;
            margin-bottom: 24px;
        }

        .teacher-hero h1 {
            margin-bottom: 8px;
            font-size: 34px;
        }

        .teacher-hero p {
            margin: 0;
            opacity: 0.92;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
          
            margin-bottom: 24px;
        }

     .stat-box {
    background: white;
    border-radius: 18px;
    padding: 22px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);

    /* IMPORTANT FIX */
    width: 100%;
    height: 100%;
    box-sizing: border-box;
}

        .stat-box h3 {
            font-size: 28px;
            margin-bottom: 4px;
            color: #111827;
        }

        .stat-box p {
            color: #6b7280;
            font-size: 14px;
        }

        .section-box {
            background: white;
            border-radius: 22px;
            padding: 26px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }

        .section-box h2 {
            margin-bottom: 18px;
            font-size: 28px;
            color: #111827;
        }

        .course-list {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }


.course-img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 12px;
    margin-bottom: 12px;
}
        .course-card {
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 18px;
            background: #fafafa;
        }

        .course-card h3 {
            margin-bottom: 10px;
            font-size: 20px;
            color: #111827;
        }

        .course-card p {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .course-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 14px;
        }

        .course-badge {
            display: inline-block;
            background: #eef2ff;
            color: #4f46e5;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .course-price {
            background: #dcfce7;
            color: #166534;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .course-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .course-actions a {
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
        }

        .btn-primary-small {
            background: #4f46e5;
            color: white;
        }

        .btn-primary-small1 {
            background: gray;
            color: white;
        }


        .btn-light-small {
            background: #eef2ff;
            color: #4f46e5;
        }

        .empty-text {
            color: #6b7280;
            font-size: 15px;
        }

.stat-link {
    text-decoration: none;
    color: inherit;
    display: block;
    width: 100%;
    height: 100%;
    transition: 0.3s;
}

.stat-link:hover {
    transform: translateY(-5px);
    background: lightskyblue;
}
    </style>

</head>
<body class="teacher-page">

    <!-- <link rel="stylesheet" href="teacher_navbar.css?v=4"> -->
 
 <?php include 'tnavbar.php'; ?>

    <div class="teacher-container">
        <section class="teacher-hero">
            <h1>Welcome, <?php echo htmlspecialchars($teacher['t_name']); ?></h1>
            <p>
                Manage your assigned courses, upload lesson materials, create quizzes and assignments,
                and review student submissions.
            </p>
        </section>

   <section class="stats-grid">

    <a href="#courses" class="stat-box stat-link">
        <h3><?php echo $countCourses; ?></h3>
        <p>Assigned Courses</p>

    </a>

    <a href="teacher_view_lessons.php" class="stat-box stat-link">
        <h3><?php echo $countLessons; ?></h3>
        <p>Uploaded Lessons</p>
    </a>

    <a href="#" class="stat-box stat-link">
        <h3><?php echo $countAssignments; ?></h3>
        <p>Assignments</p>
    </a>

    <a href="teacher_submissions.php" class="stat-box stat-link">
        <h3><?php echo $countTests; ?></h3>
        <p>Student Submission Papers</p>
    </a>

</section>
</div>

       <section class="section-box" id="courses">
            <h2>My Courses</h2>

            <?php if ($assignedCourses->num_rows > 0): ?>
                <div class="course-list">
                    <?php while ($course = $assignedCourses->fetch_assoc()): ?>
                      <div class="course-card">


                            <?php
$img = !empty($course['c_image']) 
    ? $course['c_image'] 
    : "https://via.placeholder.com/800x400";
?>

    <img src="<?php echo $img; ?>" class="course-img">



    <h3><?php echo htmlspecialchars($course['c_name']); ?></h3>
    <p><?php echo htmlspecialchars($course['c_des']); ?></p>

    <div class="course-meta">
        <span class="course-badge">
            Status: <?php echo htmlspecialchars($course['as_status']); ?>
        </span>
        <span class="course-price">
            Price: <?php echo htmlspecialchars($course['c_price']); ?>
        </span>
    </div>

    <div class="course-actions">
       <a href="teacher_lessons.php?course_id=<?php echo $course['c_id']; ?>" class="btn-primary-small">
    Upload Content
</a>
    <a href="teacher_quizzes.php?c_id=<?php echo $course['c_id']; ?>" class="btn-primary-small1">Create Quiz</a>
        <a href="teacher_assignment.php?c_id=<?php echo $course['c_id']; ?>" class="btn-light-small">
    Create Assignment
</a>
    </div>

</div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="empty-text">No courses are assigned to this teacher yet.</p>
            <?php endif; ?>
        </section>
    </div>

</body>
</html>




























































<!-- 



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

$u_id = $_SESSION['u_id'];

$u_id = intval($_SESSION['u_id']);

/* Get teacher info */
$teacherResult = $conn->query("
    SELECT * 
    FROM teacher 
    WHERE u_id = $u_id
");

if ($teacherResult->num_rows == 0) {
    die("Teacher profile not found.");
}

$teacher = $teacherResult->fetch_assoc();
$t_id = $teacher['t_id'];




/* Dashboard counts */
$countCourses = 0;
$countLessons = 0;
$countAssignments = 0;
$countTests = 0;

$courseCountResult = $conn->query("
    SELECT COUNT(*) AS total_courses 
    FROM assigned_course 
    WHERE t_id = $t_id
")->fetch_assoc();

$countCourses = $courseCountResult['total_courses'];

$lessonCountResult = $conn->query("
    SELECT COUNT(*) AS total_lessons 
    FROM lesson 
    WHERE t_id = $t_id
")->fetch_assoc();

$countLessons = $lessonCountResult['total_lessons'];


$assignmentCountResult = $conn->query("
    SELECT COUNT(*) AS total_assignments 
    FROM assignment 
    WHERE t_id = $t_id
")->fetch_assoc();

$countAssignments = $assignmentCountResult['total_assignments'];


$testCountResult = $conn->query("
    SELECT COUNT(*) AS total_submissions
    FROM test_result tr
    INNER JOIN test t ON tr.test_id = t.test_id
    WHERE t.t_id = $t_id
")->fetch_assoc();

$countTests = $testCountResult['total_submissions'];

/* Assigned courses */
$assignedCourses = $conn->query("
    SELECT 
        course.c_id,
        course.c_name,
        course.c_des,
        course.c_price,
        course.c_image,
        assigned_course.as_status
    FROM assigned_course
    INNER JOIN course ON assigned_course.c_id = course.c_id
    WHERE assigned_course.t_id = $t_id
    ORDER BY course.c_id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
   
    <title>Teacher Dashboard | LMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .teacher-page {
            background: #f5f7fb;
            min-height: 100vh;
        }

        .teacher-container {
            padding: 28px 30px;

        }

        .teacher-hero {
             background: linear-gradient(135deg, #3b82f6, #6d28d9);
            color: white;
            border-radius: 24px;
            padding: 34px 30px;
            margin-bottom: 24px;
        }

        .teacher-hero h1 {
            margin-bottom: 8px;
            font-size: 34px;
        }

        .teacher-hero p {
            margin: 0;
            opacity: 0.92;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
          
            margin-bottom: 24px;
        }

     .stat-box {
    background: white;
    border-radius: 18px;
    padding: 22px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);

    /* IMPORTANT FIX */
    width: 100%;
    height: 100%;
    box-sizing: border-box;
}

        .stat-box h3 {
            font-size: 28px;
            margin-bottom: 4px;
            color: #111827;
        }

        .stat-box p {
            color: #6b7280;
            font-size: 14px;
        }

        .section-box {
            background: white;
            border-radius: 22px;
            padding: 26px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }

        .section-box h2 {
            margin-bottom: 18px;
            font-size: 28px;
            color: #111827;
        }

        .course-list {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }


.course-img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 12px;
    margin-bottom: 12px;
}
        .course-card {
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 18px;
            background: #fafafa;
        }

        .course-card h3 {
            margin-bottom: 10px;
            font-size: 20px;
            color: #111827;
        }

        .course-card p {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .course-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 14px;
        }

        .course-badge {
            display: inline-block;
            background: #eef2ff;
            color: #4f46e5;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .course-price {
            background: #dcfce7;
            color: #166534;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .course-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .course-actions a {
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
        }

        .btn-primary-small {
            background: #4f46e5;
            color: white;
        }

        .btn-primary-small1 {
            background: gray;
            color: white;
        }


        .btn-light-small {
            background: #eef2ff;
            color: #4f46e5;
        }

        .empty-text {
            color: #6b7280;
            font-size: 15px;
        }

.stat-link {
    text-decoration: none;
    color: inherit;
    display: block;
    width: 100%;
    height: 100%;
    transition: 0.3s;
}

.stat-link:hover {
    transform: translateY(-5px);
    background: lightskyblue;
}
    </style>

</head>
<body class="teacher-page">

    <link rel="stylesheet" href="teacher_navbar.css?v=4">
<header class="teacher-navbar">

    <div class="teacher-left">
        <div class="logo-box">🛠️</div>
        <div class="logo-text">
            <h2>Smart-LearnHub</h2>
            <p>Teacher Panel</p>
        </div>
    </div>

    <nav class="teacher-menu">
        <a href="teacher_home.php" class="active">Home</a>
        <a href="teacher_submissions.php">Submissions</a>
    </nav>

    <div class="teacher-user">
        <span class="teacher-name">
            👤 <?php echo $_SESSION['user_name'] ?? 'Teacher'; ?>
        </span>

        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

</header>

    <div class="teacher-container">
        <section class="teacher-hero">
            <h1>Welcome, <?php echo htmlspecialchars($teacher['t_name']); ?></h1>
            <p>
                Manage your assigned courses, upload lesson materials, create quizzes and assignments,
                and review student submissions.
            </p>
        </section>

   <section class="stats-grid">

    <a href="#courses" class="stat-box stat-link">
        <h3><?php echo $countCourses; ?></h3>
        <p>Assigned Courses</p>

    </a>

    <a href="teacher_view_lessons.php" class="stat-box stat-link">
        <h3><?php echo $countLessons; ?></h3>
        <p>Uploaded Lessons</p>
    </a>

    <a href="#" class="stat-box stat-link">
        <h3><?php echo $countAssignments; ?></h3>
        <p>Assignments</p>
    </a>

    <a href="teacher_quiz_submissions.php" class="stat-box stat-link">
        <h3><?php echo $countTests; ?></h3>
        <p>Student Submission Papers</p>
    </a>

</section>
</div>

       <section class="section-box" id="courses">
            <h2>My Courses</h2>

            <?php if ($assignedCourses->num_rows > 0): ?>
                <div class="course-list">
                    <?php while ($course = $assignedCourses->fetch_assoc()): ?>
                      <div class="course-card">


                            <?php
$img = !empty($course['c_image']) 
    ? $course['c_image'] 
    : "https://via.placeholder.com/800x400";
?>

    <img src="<?php echo $img; ?>" class="course-img">



    <h3><?php echo htmlspecialchars($course['c_name']); ?></h3>
    <p><?php echo htmlspecialchars($course['c_des']); ?></p>

    <div class="course-meta">
        <span class="course-badge">
            Status: <?php echo htmlspecialchars($course['as_status']); ?>
        </span>
        <span class="course-price">
            Price: <?php echo htmlspecialchars($course['c_price']); ?>
        </span>
    </div>

    <div class="course-actions">
       <a href="teacher_lessons.php?course_id=<?php echo $course['c_id']; ?>" class="btn-primary-small">
    Upload Content
</a>
    <a href="teacher_quizzes.php?c_id=<?php echo $course['c_id']; ?>" class="btn-primary-small1">Create Quiz</a>
        <a href="#" class="btn-light-small">Create Assignment</a>
    </div>

</div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="empty-text">No courses are assigned to this teacher yet.</p>
            <?php endif; ?>
        </section>
    </div>

</body>
</html> -->