<?php
session_start();
include 'db.php';

if (!isset($_SESSION['u_id']) || $_SESSION['role_id'] !== 'Teacher') {
    header("Location: login.php");
    exit();
}

$u_id = intval($_SESSION['u_id']);
$selected_course = isset($_GET['c_id']) ? intval($_GET['c_id']) : 0;

$teacher = $conn->query("SELECT t_id FROM teacher WHERE u_id = $u_id")->fetch_assoc();
if (!$teacher) {
    die("Teacher profile not found.");
}
$t_id = $teacher['t_id'];

$msg = "";

if (isset($_POST['submit_final_course_grade'])) {
    $s_id = intval($_POST['s_id']);
    $en_id = intval($_POST['en_id']);
    $c_id = intval($_POST['c_id']);

    $assign_mark = floatval($_POST['assignment_mark']);
    $quiz_mark = floatval($_POST['quiz_mark']);
    $mid_mark = floatval($_POST['mid_mark']);
    $final_exam_mark = floatval($_POST['final_mark']);
    $att_mark = floatval($_POST['attendance_mark']);

    // Sum total points including the calculated attendance
    $total_mark = $assign_mark + $quiz_mark + $mid_mark + $final_exam_mark + $att_mark;

    // Assign proper alphanumeric code
    if ($total_mark >= 90) $final_grade = "A";
    elseif ($total_mark >= 80) $final_grade = "B";
    elseif ($total_mark >= 70) $final_grade = "C";
    elseif ($total_mark >= 60) $final_grade = "D";
    else $final_grade = "F";

    // Manage upsert transaction directly into main student grade repository
    $check = $conn->query("SELECT grade_id FROM grade WHERE en_id = $en_id");
    if ($check->num_rows > 0) {
        $conn->query("UPDATE grade SET 
            assignmet_mark = $assign_mark, quizz_mark = $quiz_mark, mid_mark = $mid_mark, 
            final_mark = $final_exam_mark, attenda_mark = $att_mark, total_mark = $total_mark, 
            final_grade = '$final_grade' WHERE en_id = $en_id");
    } else {
        $conn->query("INSERT INTO grade (assignmet_mark, quizz_mark, mid_mark, final_mark, attenda_mark, total_mark, final_grade, s_id, c_id, en_id) 
            VALUES ($assign_mark, $quiz_mark, $mid_mark, $final_exam_mark, $att_mark, $total_mark, '$final_grade', $s_id, $c_id, $en_id)");
    }

    // Sync review text comments to test logs if added by the teacher
    if (!empty($_POST['teacher_feedback_text'])) {
        $feedback = $conn->real_escape_string(trim($_POST['teacher_feedback_text']));
        $conn->query("UPDATE test_result SET submitted_answer = '$feedback' WHERE en_id = $en_id LIMIT 1");
    }

    $msg = "Final grade computed ($final_grade) and synchronized successfully! Manifest queued for Admin verification.";
}

$courses = $conn->query("SELECT c.c_id, c.c_name FROM assigned_course ac JOIN course c ON ac.c_id = c.c_id WHERE ac.t_id = $t_id");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Final Course Grading | Smart-LearnHub</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            margin: 0;
            padding: 0;
        }

        .teacher-container {
            padding: 28px 30px;
            width: calc(100% - 60px);
            max-width: 100%;
            margin: 0 auto;
            box-sizing: border-box;
        }

        .teacher-hero {
            background: linear-gradient(135deg, #3b82f6, #6d28d9);
            color: white;
            border-radius: 24px;
            padding: 34px 30px;
            margin-bottom: 24px;
            width: 100%;
            box-sizing: border-box;
        }

        .teacher-hero h1 {
            margin: 0 0 8px 0;
            font-size: 34px;
        }

        .teacher-hero p {
            margin: 0;
            opacity: 0.92;
        }

        .section-box {
            background: white;
            border-radius: 22px;
            padding: 26px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            width: 100%;
            box-sizing: border-box;
        }

        .section-box h2 {
            margin: 0 0 15px 0;
            font-size: 24px;
            color: #111827;
        }

        .filter-select {
            height: 42px;
            padding: 0 14px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            min-width: 250px;
            background: #fafafa;
        }

        .section-box table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .section-box table th,
        .section-box table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }

        .section-box table thead th {
            background-color: #e6e6fa;
            color: #800080;
            font-weight: bold;
        }

        .section-box table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .section-box table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .section-box table tbody tr:hover {
            background-color: #f1f5f9;
        }

        .input-score {

            width: 65px;
            height: 32px;
            padding: 0 6px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            text-align: center;
            box-sizing: border-box;
            background: white;
        }

        .input-text-area {
            width: 150px;
            height: 32px;
            padding: 0 8px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 13px;
            box-sizing: border-box;
            background: white;
        }

        .btn-submit {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-submit:hover {
            background: #4338ca;
        }

        .section-box .status-badge {
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }

        .section-box .status-approved {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .section-box .status-pending {
            background: #fef3c7;
            color: #d97706;
            border: 1px solid #fcd34d;
        }

        .msg-alert {
            background: #dcfce7;
            color: #166534;
            padding: 15px;
            border-radius: 12px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
            border: 1px solid #bbf7d0;
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
    </style>
</head>

<body>

   

<header class="admin-navbar">

    <div class="admin-left">
        <div class="logo-box">🛠️</div>
        <div class="logo-text">
            <h2>Smart-LearnHub</h2>
            <p>Teacher Panel</p>
        </div>
    </div>

    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    ?>


    <nav class="nav-links">
        <a href="teacher_home.php" class="<?php echo ($current_page == 'teacher_home.php') ? 'active' : ''; ?>">Home</a>
        <a href="teacher_request_course.php" class="<?php echo ($current_page == 'teacher_request_courses.php') ? 'active' : ''; ?>">Request Courses</a>
        <a href="teacher_submissions.php" class="<?php echo ($current_page == 'teacher_submissions.php') ? 'active' : ''; ?>">Submissions</a>
        <a href="teacher_course_assignment_submission.php" class="<?php echo ($current_page == 'teacher_course_assignment_submission.php') ? 'active' : ''; ?>">Assignment submisson</a>
        <a href="teacher_final_grading.php" class="<?php echo ($current_page == 'teacher_final_grading.php') ? 'active' : ''; ?>">Final Grading Sheet</a>

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

    <div class="teacher-container">

        <section class="teacher-hero">
            <h1>Final Performance Grade Panel</h1>
            <p>Compile accumulated student grade logs, aggregate course component criteria points, and release marks layout profiles.</p>
        </section>

        <?php if (!empty($msg)) { ?><div class="msg-alert"><?php echo $msg; ?></div><?php } ?>

        <div class="section-box">
            <h2>Select Course </h2>
            <form method="GET" action="">
                <select name="c_id" class="filter-select" onchange="this.form.submit()">
                    <option value="">Choose Course for Evaluation </option>
                    <?php while ($c = $courses->fetch_assoc()) { ?>
                        <option value="<?php echo $c['c_id']; ?>" <?php if ($selected_course == $c['c_id']) echo 'selected'; ?>><?php echo htmlspecialchars($c['c_name']); ?></option>
                    <?php } ?>
                </select>
            </form>

            <?php if ($selected_course > 0) {
                // Fetch student details along with current marks
                $students = $conn->query("
                    SELECT 
                        e.en_id, e.c_id, s.s_id, s.s_name, 
                        g.assignmet_mark, g.quizz_mark, g.mid_mark, g.final_mark, g.attenda_mark, g.final_grade,
                        cert.status as cert_status,
                        tr.submitted_answer as existing_feedback
                    FROM enrollment e
                    JOIN student s ON e.s_id = s.s_id
                    LEFT JOIN grade g ON e.en_id = g.en_id
                    LEFT JOIN certificate cert ON (cert.s_id = s.s_id AND cert.c_id = e.c_id)
                    LEFT JOIN test_result tr ON tr.en_id = e.en_id
                    WHERE e.c_id = $selected_course
                    GROUP BY e.en_id
                ");
            ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Assignments</th>
                            <th>Quizzes</th>
                            <th>Midterm</th>
                            <th>Final Exam</th>
                            <th>Attendance</th>
                            <th>Review Comments (Optional)</th>
                            <th>Calculated Grade</th>
                            <th>Visibility Release</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $students->fetch_assoc()) {
                            $student_id = intval($row['s_id']);

                            // Check how many unique lessons the student has completed
                            $completedQuery = $conn->query("
                                SELECT COUNT(DISTINCT a.lesson_id) as completed 
                                FROM attendance a
                                JOIN lesson l ON a.lesson_id = l.lesson_id
                                WHERE a.s_id = $student_id 
                                AND l.c_id = $selected_course 
                                AND a.attendance = 1
                            ");
                            $completedRow = $completedQuery->fetch_assoc();
                            $completedCount = intval($completedRow['completed']);

                            // New Tiered Marking Rules Implementation logic
                            if ($completedCount >= 5) {
                                $auto_attendance = 5;
                            } elseif ($completedCount >= 3) {
                                $auto_attendance = 3;
                            } elseif ($completedCount == 2) {
                                $auto_attendance = 2;
                            } else {
                                // Default back to whatever was saved prior, or 0
                                $auto_attendance = isset($row['attenda_mark']) ? $row['attenda_mark'] : 0;
                            }
                        ?>
                            <form method="POST" action="">
                                <input type="hidden" name="s_id" value="<?php echo $row['s_id']; ?>">
                                <input type="hidden" name="en_id" value="<?php echo $row['en_id']; ?>">
                                <input type="hidden" name="c_id" value="<?php echo $selected_course; ?>">
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['s_name']); ?></strong></td>
                                    <td><input type="text" name="assignment_mark" class="input-score" value="<?php echo $row['assignmet_mark'] ?? 0; ?>"></td>
                                    <td><input type="text" name="quiz_mark" class="input-score" value="<?php echo $row['quizz_mark'] ?? 0; ?>"></td>
                                    <td><input type="text" name="mid_mark" class="input-score" value="<?php echo $row['mid_mark'] ?? 0; ?>"></td>
                                    <td><input type="text" name="final_mark" class="input-score" value="<?php echo $row['final_mark'] ?? 0; ?>"></td>

                                    <td><input type="text" name="attendance_mark" class="input-score" value="<?php echo $auto_attendance; ?>" style="background:#f0fdf4; font-weight:bold; color:#166534; border-color:#bbf7d0;"></td>

                                    <td><input type="text" name="teacher_feedback_text" class="input-text-area" placeholder="Add comments..." value="<?php echo htmlspecialchars($row['existing_feedback'] ?? ''); ?>"></td>
                                    <td><span style="font-weight:bold; color:#4f46e5; font-size:16px;"><?php echo $row['final_grade'] ?? 'Not Set'; ?></span></td>
                                    <td>
                                        <?php if ($row['cert_status'] === 'Sent' || $row['cert_status'] === 'Generated') { ?>
                                            <span class="status-badge status-approved">Published</span>
                                        <?php } else { ?>
                                            <span class="status-badge status-pending">Awaiting Admin</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <button type="submit" name="submit_final_course_grade" class="btn-submit">Publish & Sync</button>
                                    </td>
                                </tr>
                            </form>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p style="margin-top: 25px; color: #6b7280; font-style: italic;">Please select a course to view enrolled student rows.</p>
            <?php } ?>
        </div>

        <a href="teacher_home.php" style="display: block; text-align: center; margin-top: 25px; background: #e5e7eb; color: #111827; padding: 12px; border-radius: 12px; font-weight: bold; font-size: 14px; text-decoration: none; transition: 0.2s; width: 100%; box-sizing: border-box;" onmouseover="this.style.background='#d1d5db'" onmouseout="this.style.background='#e5e7eb'">
            ← Back to Dashboard
        </a>
    </div>

</body>

</html>