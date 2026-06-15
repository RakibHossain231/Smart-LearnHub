<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db.php';

//login check

if (!isset($_SESSION['u_id'])) {
    header("Location: login.php");
    exit();
}
//role check
if ($_SESSION['role_id'] !== 'Student') {
    header("Location: index.php");
    exit();
}

$u_id = $_SESSION['u_id'];

$u_id = intval($_SESSION['u_id']);

$student = $conn->query("
    SELECT *
    FROM student
    WHERE u_id = $u_id
")->fetch_assoc();

if (!$student) {
    die("Student profile not found.");
}

$s_id = $student['s_id'];
//unenroll logic
if (isset($_POST['unenroll'])) {
    $c_id = intval($_POST['c_id']);

    $enrollment = $conn->query("
        SELECT en_id 
        FROM enrollment 
        WHERE s_id = $s_id AND c_id = $c_id
    ")->fetch_assoc();
    //if recod exits contue delet

    if ($enrollment) {
        $en_id = $enrollment['en_id'];

        $results = $conn->query("
            SELECT test_result_id 
            FROM test_result 
            WHERE en_id = $en_id
        ");

        while ($r = $results->fetch_assoc()) {
            $test_result_id = $r['test_result_id'];

            $conn->query("
                DELETE FROM student_test_answer 
                WHERE test_result_id = $test_result_id
            ");
        }

        $conn->query("DELETE FROM test_result WHERE en_id = $en_id");
        $conn->query("DELETE FROM grade WHERE en_id = $en_id");
        $conn->query("DELETE FROM assignmet_result WHERE s_id = $s_id");
        $conn->query("DELETE FROM enrollment WHERE en_id = $en_id");

        header("Location: student_home.php");
        exit();
    }
}
//end of unrollenmt logic

$courseCount = $conn->query("SELECT COUNT(*) AS total FROM enrollment WHERE s_id = $s_id")->fetch_assoc()['total'];

$quizCount = $conn->query("
    SELECT COUNT(*) AS total
    FROM test t
    INNER JOIN enrollment e ON t.c_id = e.c_id
    WHERE e.s_id = $s_id AND t.is_published = 1
")->fetch_assoc()['total'];

$submittedCount = $conn->query("
    SELECT COUNT(*) AS total
    FROM test_result tr
    INNER JOIN enrollment e ON tr.en_id = e.en_id
    WHERE e.s_id = $s_id
")->fetch_assoc()['total'];

$courses = $conn->query("
    SELECT c.*
    FROM enrollment e
    INNER JOIN course c ON e.c_id = c.c_id
    WHERE e.s_id = $s_id
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard | LMS</title>
    <link rel="stylesheet" href="style.css">

   <style>
    body {
        background: #f5f7fb;
        font-family: Arial, sans-serif;
        margin: 0;
    }

    

    

    .container {
        padding: 28px 30px;
       
    }

    .hero {
        background: linear-gradient(135deg, #3b82f6, #6d28d9);
        color: white;
        border-radius: 18px;
        padding: 24px 24px;
        margin-bottom: 22px;
         height: 200px;
    }

    .hero h1 {
        font-size: 30px;
        margin: 0 0 6px;
    }

    .hero p {
        font-size: 20px;
        margin: 0;
        opacity: 0.92;
    }

    .stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 22px;
    }

    .stat-box {
        background: white;
        border-radius: 16px;
        padding: 18px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        text-decoration: none;
        color: #111827;
        height: 150px;
    }

    .stat-box h3 {
        font-size: 24px;
        margin: 0 0 4px;
    }

    .stat-box p {
        margin: 0;
        font-size: 14px;
        color: #6b7280;
    }

    .course-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
    }

    .course-card {
        background: white;
        border-radius: 18px;
        padding: 18px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.05);
    }

    .course-card h3 {
        font-size: 18px;
        margin-bottom: 8px;
    }

    .course-card p {
        color: #6b7280;
        font-size: 14px;
        margin-bottom: 12px;
    }

    .btn {
        display: inline-block;
        text-decoration: none;
        background: #4f46e5;
        color: white;
        padding: 9px 13px;
        border-radius: 9px;
        font-weight: bold;
        font-size: 14px;
    }

    @media(max-width:900px) {
        .stats,
        .course-grid {
            grid-template-columns: 1fr;
        }

        .container {
            padding: 18px 14px;
        }
    }
    .unenroll-btn {
    background: #dc2626;
    color: white;
    border: none;
    padding: 9px 13px;
    border-radius: 9px;
    font-weight: bold;
    font-size: 14px;
    cursor: pointer;
    margin-left: 8px;
}
</style>
</head>

<body>
    



  <?php include 'student_navbar.php'; ?>


<div class="container">
    <section class="hero">
        <h1>Welcome, <?php echo htmlspecialchars($student['s_name']); ?></h1>
        <p>View your enrolled courses, participate in quizzes, and submit your answers.</p>
    </section>

    <section class="stats">
        <a href="#" class="stat-box">
            <h3><?php echo $courseCount; ?></h3>
            <p>Enrolled Courses</p>
        </a>

        <a href="student_quizzes.php" class="stat-box">
            <h3><?php echo $quizCount; ?></h3>
            <p>Available Quizzes</p>
        </a>

        <a href="student_quizzes.php" class="stat-box">
            <h3><?php echo $submittedCount; ?></h3>
            <p>Submitted Quizzes</p>
        </a>
    </section>

    <h2>My Courses</h2>
    <br>

    <?php if ($courses->num_rows > 0): ?>
        <div class="course-grid">
            <?php while($course = $courses->fetch_assoc()): ?>
                <div class="course-card">
                    <h3><?php echo htmlspecialchars($course['c_name']); ?></h3>
                    <p><?php echo htmlspecialchars($course['c_des']); ?></p>
                  <a href="take_lesson.php?c_id=<?php echo $course['c_id']; ?>" class="btn">
    View course
</a>

<form method="POST" style="display:inline;">
   
    <button type="submit" name="unenroll" class="unenroll-btn"
        onclick="return confirm('Are you sure you want to unenroll from this course? All related data will be deleted.');">
        Unenroll
    </button>
</form>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No enrolled courses found.</p>
    <?php endif; ?>
</div>

</body>
</html>