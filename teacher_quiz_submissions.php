<?php
if (session_status() == PHP_SESSION_NONE) session_start();
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
$message = "";

/* Get teacher */
$teacherResult = $conn->query("SELECT * FROM teacher WHERE u_id = $u_id");
$teacher = $teacherResult->fetch_assoc();

if (!$teacher) {
    die("Teacher not found.");
}
$t_id = $teacher['t_id'];

/* Selected course */
$selectedCourseId = isset($_GET['c_id']) ? (int)$_GET['c_id'] : 0;
$selectedCourse = null;

if ($selectedCourseId > 0) {
    $courseResult = $conn->query("
        SELECT c.c_id, c.c_name
        FROM course c
        INNER JOIN assigned_course ac ON c.c_id = ac.c_id
        WHERE ac.t_id = $t_id AND c.c_id = $selectedCourseId
        LIMIT 1
    ");

    $selectedCourse = $courseResult->fetch_assoc();

    if (!$selectedCourse) {
        die("Course not found or not assigned to you.");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $test_result_id = (int)$_POST['test_result_id'];
    $mark = (float)$_POST['mark'];

    $update = $conn->query("
        UPDATE test_result
        SET test_result_mark = $mark,
            test_status = 'Marked'
        WHERE test_result_id = $test_result_id
    ");

    if ($update) {
        $message = "Quiz paper marked successfully.";
    } else {
        $message = "Failed to update mark.";
    }
}

if ($selectedCourseId > 0) {
    $resultStmt = $conn->query("
        SELECT 
            tr.*,
            s.s_id,
            t.test_question,
            t.test_mark,
            t.test_type,
            c.c_name,
            s.s_name
        FROM test_result tr
        INNER JOIN test t ON tr.test_id = t.test_id
        INNER JOIN enrollment e ON tr.en_id = e.en_id
        INNER JOIN student s ON e.s_id = s.s_id
        INNER JOIN course c ON t.c_id = c.c_id
        WHERE t.t_id = $t_id AND t.c_id = $selectedCourseId
        ORDER BY tr.test_result_id DESC
    ");
} else {
    $resultStmt = $conn->query("
        SELECT 
            tr.*,
            s.s_id,
            t.test_question,
            t.test_mark,
            t.test_type,
            c.c_name,
            s.s_name
        FROM test_result tr
        INNER JOIN test t ON tr.test_id = t.test_id
        INNER JOIN enrollment e ON tr.en_id = e.en_id
        INNER JOIN student s ON e.s_id = s.s_id
        INNER JOIN course c ON t.c_id = c.c_id
        WHERE t.t_id = $t_id
        ORDER BY tr.test_result_id DESC
    ");
}
$submissions = $resultStmt;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Submissions | LMS</title>
    <style>
        body { font-family: Arial; background: #f5f7fb; margin: 0; padding: 30px; }
        .hero { background: linear-gradient(135deg, #3b82f6, #6d28d9); color: white; padding: 30px; border-radius: 22px; margin-bottom: 25px; }
        .paper { background: white; padding: 22px; border-radius: 18px; margin-bottom: 18px; box-shadow: 0 8px 20px rgba(0,0,0,0.06); }
        .answer { background: #f8fafc; border: 1px solid #e5e7eb; padding: 14px; border-radius: 12px; margin: 12px 0; }
        input { padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; }
        .btn { background: #4f46e5; color: white; border: none; padding: 10px 14px; border-radius: 8px; font-weight: bold; cursor: pointer; }
        .success { background: #dcfce7; color: #166534; padding: 12px; border-radius: 10px; margin-bottom: 12px; font-weight: bold; }
        .alert { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 10px; margin-bottom: 12px; font-weight: bold; }
        .badge { display: inline-block; background: #eef2ff; color: #4f46e5; padding: 7px 11px; border-radius: 999px; font-size: 13px; font-weight: bold; margin-top: 8px; }
        .filter-bar { background: white; padding: 12px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .filter-form { display: flex; gap: 10px; align-items: center; }
        .filter-select { height: 42px; padding: 0 10px; border-radius: 10px; border: 1px solid #d1d5db; min-width: 200px; }
        .filter-input { height: 42px; padding: 0 10px; border-radius: 10px; border: 1px solid #d1d5db; flex: 1; }
        .filter-btn { height: 42px; padding: 0 16px; border: none; background: #4f46e5; color: white; border-radius: 10px; cursor: pointer; font-weight: bold; }
        .pdf-link-btn { background: #10b981; color: white; text-decoration: none; padding: 8px 12px; border-radius: 6px; font-weight: bold; display: inline-block; margin-top: 5px; }
    </style>
</head>
<body>
    <?php include 'teacher_navbar.php'; ?>

    <div class="filter-bar">
        <form method="GET" class="filter-form">
            <select name="c_id" class="filter-select" onchange="this.form.submit()">
                <option value="">All Courses</option>
                <?php
                $courseList = $conn->query("SELECT c.c_id, c.c_name FROM course c INNER JOIN assigned_course ac ON c.c_id = ac.c_id WHERE ac.t_id = $t_id");
                while ($c = $courseList->fetch_assoc()) {
                    $selected = ($selectedCourseId == $c['c_id']) ? "selected" : "";
                    echo "<option value='{$c['c_id']}' $selected>{$c['c_name']}</option>";
                }
                ?>
            </select>
            <input type="text" name="search" placeholder="Search student..." class="filter-input">
            <button type="submit" class="filter-btn">Search</button>
        </form>
    </div>

    <div class="hero">
        <h1>Student Quiz Papers</h1>
        <?php if ($selectedCourse): ?>
            <p>Submitted papers for:</p>
            <span class="badge"><?php echo htmlspecialchars($selectedCourse['c_name']); ?></span>
        <?php else: ?>
            <p>Please select a course to view submissions.</p>
        <?php endif; ?>
    </div>

    <?php if(!empty($message)): ?>
        <div class="<?php echo ($message == 'Quiz paper marked successfully.') ? 'success' : 'alert'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if($submissions->num_rows > 0): 
        $submission_counter = 1; // Tracks 1), 2), 3)... numbers automatically
        while($s = $submissions->fetch_assoc()): 
    ?>
            <div class="paper">
                <h2><?php echo $submission_counter . ") " . htmlspecialchars($s['s_name']); ?></h2>
                <p><strong>Course:</strong> <?php echo htmlspecialchars($s['c_name']); ?></p>
                <p><strong>Quiz Type:</strong> <?php echo htmlspecialchars($s['test_type']); ?></p>
                <p><strong>Question Framework:</strong> <?php echo htmlspecialchars($s['test_question']); ?></p>

                <div class="answer">
                    <strong>Student Answer Script / URL Document:</strong><br>
                    <?php 
                    $student_ans = trim($s['submitted_answer']);
                    // If the answer looks like a link, show an "Open PDF" button automatically
                    if (filter_var($student_ans, FILTER_VALIDATE_URL) || substr($student_ans, 0, 4) === 'http') {
                        echo '<a href="'.htmlspecialchars($student_ans).'" target="_blank" class="pdf-link-btn">📄 Open Attached PDF Link</a>';
                    } else {
                        echo nl2br(htmlspecialchars($s['submitted_answer']));
                    }
                    ?>
                </div>

                <p><strong>Total Marks:</strong> <?php echo htmlspecialchars($s['test_mark']); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($s['test_status']); ?></p>

                <form method="POST" action="teacher_quiz_submissions.php<?php echo $selectedCourseId > 0 ? '?c_id=' . $selectedCourseId : ''; ?>">
                    <input type="hidden" name="test_result_id" value="<?php echo $s['test_result_id']; ?>">
                    <input type="number" step="0.01" name="mark" value="<?php echo htmlspecialchars($s['test_result_mark']); ?>" required>
                    <button type="submit" class="btn">Save Mark</button>
                </form>
            </div>
    <?php 
            $submission_counter++;
        endwhile; 
    ?>
    <?php else: ?>
        <p>No submitted papers found yet.</p>
    <?php endif; ?>
</body>
</html>