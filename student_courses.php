<?php
session_start();
include 'db.php';

// check login
if (!isset($_SESSION['u_id']) || $_SESSION['role_id'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$u_id = $_SESSION['u_id'];

// get student
$student = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM student WHERE u_id = $u_id"
));

if (!$student) {
    die("Student not found");
}

$s_id = $student['s_id'];

// get course id
$c_id = isset($_GET['c_id']) ? (int)$_GET['c_id'] : 0;

if ($c_id == 0) {
    header("Location: student_home.php");
    exit();
}

// mark lesson completed
if(isset($_POST['complete_lesson']))
{
    $lesson_id = (int)$_POST['lesson_id'];

    // check already completed
    $check = mysqli_query($conn,"
        SELECT *
        FROM attendance
        WHERE s_id = $s_id
        AND lesson_id = $lesson_id
    ");

    if(mysqli_num_rows($check) == 0)
    {
        mysqli_query($conn,"
            INSERT INTO attendance(s_id, lesson_id, attendance)
            VALUES($s_id, $lesson_id, 1)
        ");
    }
}

// get enrolled course
$course = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT c.*
    FROM enrollment e
    JOIN course c ON e.c_id = c.c_id
    WHERE e.s_id = $s_id
    AND c.c_id = $c_id
"));
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Courses | LMS</title>
    <link rel="stylesheet" href="style.css">

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
        }

        .container {
            padding: 22px 24px;
        }

        .hero {
            background: linear-gradient(135deg, #3b82f6, #6d28d9);
            color: white;
            border-radius: 18px;
            padding: 24px 22px;
            margin-bottom: 20px;
        }

        .hero h1 {
            font-size: 24px;
            margin: 0 0 6px;
        }

        .hero p {
            font-size: 14px;
            margin: 0;
            opacity: 0.9;
        }

        .course-box {
            background: white;
            border-radius: 20px;
            padding: 22px;
            margin-bottom: 24px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }

        .course-header {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        .course-header h2 {
            margin: 0 0 6px;
            color: #111827;
            font-size: 22px;
        }

        .course-header p {
            color: #6b7280;
            margin: 0;
            font-size: 14px;
        }

        .section-title {
            margin: 18px 0 12px;
            font-size: 18px;
            color: #111827;
        }

        .item-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }

        .item-card {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 16px;
        }

        .item-card h3 {
            font-size: 16px;
            margin: 0 0 8px;
            color: #111827;
        }

        .item-card p {
            margin: 0 0 8px;
            color: #6b7280;
            font-size: 14px;
        }

        .badge {
            display: inline-block;
            background: #eef2ff;
            color: #4f46e5;
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            margin-right: 5px;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            background: #4f46e5;
            color: white;
            padding: 8px 12px;
            border-radius: 9px;
            font-weight: bold;
            font-size: 13px;
            margin-top: 6px;
        }

        .btn:hover {
            background: #4338ca;
        }

        .complete-btn{
            background: #16a34a;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 9px;
            margin-top: 10px;
            cursor: pointer;
            font-weight: bold;
        }

        .complete-btn:hover{
            background: #15803d;
        }

        .completed-btn{
            background: #9ca3af;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 9px;
            margin-top: 10px;
            font-weight: bold;
        }

        .empty {
            color: #6b7280;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 14px;
            border-radius: 14px;
            font-size: 14px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 18px;
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
        }

        @media(max-width:900px) {

            .item-grid {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 18px 14px;
            }
        }

    </style>
</head>

<body>

<?php include 'student_navbar.php'; ?>

<div class="container">

    <a href="student_home.php" class="back-link">
        ← Back to course
    </a>

    <section class="hero">
        <h1>My Course Materials</h1>
        <p>Each course shows lessons, quizzes, assignments, and marks in one place.</p>
    </section>

<?php if ($course): ?>

    <div class="course-box">

        <div class="course-header">
            <h2><?php echo htmlspecialchars($course['c_name']); ?></h2>
            <p><?php echo htmlspecialchars($course['c_des']); ?></p>
        </div>

        <!-- LESSONS -->
        <h3 class="section-title">Lessons</h3>

        <?php

        $lessons = mysqli_query($conn, "
            SELECT *
            FROM lesson
            WHERE c_id = $c_id
            ORDER BY lesson_id DESC
        ");

        ?>

        <?php if (mysqli_num_rows($lessons) > 0): ?>

            <div class="item-grid">

                <?php while($lesson = mysqli_fetch_assoc($lessons)): ?>

                    <?php

                    $lesson_id = $lesson['lesson_id'];

                    $att = mysqli_query($conn,"
                        SELECT *
                        FROM attendance
                        WHERE s_id = $s_id
                        AND lesson_id = $lesson_id
                    ");

                    $completed = (bool)mysqli_num_rows($att);


                    ?>

                    <div class="item-card">

                        <h3>
                            <?php echo htmlspecialchars($lesson['lesson_title']); ?>
                        </h3>

                        <p>

                            <span class="badge">
                                <?php echo htmlspecialchars($lesson['les_type']); ?>
                            </span>

                            <span class="badge">

                                <?php
                                echo ($lesson['duration'] == "00:00:00")
                                ? "No duration"
                                : htmlspecialchars($lesson['duration']);
                                ?>

                            </span>

                        </p>

                        <a href="<?php echo htmlspecialchars($lesson['url']); ?>"
                          
                           class="btn">

                            Open Lesson

                        </a>

                        <br>

                        <?php if($completed): ?>

                            <button class="completed-btn" disabled>
                                Completed
                            </button>

                        <?php else: ?>

                            <form method="POST">

                                <input type="hidden"
                                       name="lesson_id"
                                       value="<?php echo $lesson_id; ?>">

                                <button type="submit"
                                        name="complete_lesson"
                                        class="complete-btn">

                                    Mark as Completed

                                </button>

                            </form>

                        <?php endif; ?>

                    </div>

                <?php endwhile; ?>

            </div>

        <?php else: ?>

            <div class="empty">
                No lessons uploaded yet.
            </div>

        <?php endif; ?>

    </div>

<?php else: ?>

    <div class="empty">
        Course not found or not enrolled.
    </div>

<?php endif; ?>

</div>

</body>
</html>