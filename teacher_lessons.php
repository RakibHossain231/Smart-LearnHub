<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

if (!isset($_SESSION['u_id'])) {
    die("Session missing. Please login again.");
}

if ($_SESSION['role_id'] !== 'Teacher') {
    die("You are not logged in as Teacher.");
}

$u_id = $_SESSION['u_id'];
$message = "";

/* Get teacher info */
$teacherQuery = $conn->prepare("SELECT * FROM teacher WHERE u_id = ?");
$teacherQuery->bind_param("i", $u_id);
$teacherQuery->execute();
$teacherResult = $teacherQuery->get_result();

if ($teacherResult->num_rows == 0) {
    die("Teacher profile not found.");
}

$teacher = $teacherResult->fetch_assoc();
$t_id = intval($teacher['t_id']);

/* Get selected course */
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if ($course_id <= 0) {
    die("No course selected.");
}

$courseResult = $conn->query("
    SELECT c.c_id, c.c_name
    FROM course c
    INNER JOIN assigned_course ac ON c.c_id = ac.c_id
    WHERE c.c_id = $course_id AND ac.t_id = $t_id
    LIMIT 1
");

if ($courseResult->num_rows == 0) {
    die("Invalid course or not assigned to you.");
}

$selectedCourse = $courseResult->fetch_assoc();

/* Upload lesson */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $lesson_title = trim($_POST['lesson_title']);
    $les_type = trim($_POST['les_type']);
    $duration = isset($_POST['duration']) ? trim($_POST['duration']) : "";
    $url = trim($_POST['url']);
    $c_id = intval($_POST['c_id']);

    if (empty($lesson_title) || empty($les_type) || empty($url) || empty($c_id)) {
        $message = "Please fill in all required fields.";
    }

    elseif ($les_type === "Video" && empty($duration)) {
        $message = "Duration is required for video lessons.";
    }

    else {

        if ($les_type !== "Video") {
            $duration = "00:00:00";
        }

        /* Check course belongs to teacher */
        $checkResult = $conn->query("
            SELECT as_id 
            FROM assigned_course 
            WHERE c_id = $c_id AND t_id = $t_id
        ");

        if ($checkResult->num_rows == 0) {
            $message = "Invalid course selected.";
        }

        else {

            $insertResult = $conn->query("
                INSERT INTO lesson 
                (lesson_title, les_type, duration, url, c_id, t_id)
                VALUES 
                ('$lesson_title', '$les_type', '$duration', '$url', $c_id, $t_id)
            ");

            if ($insertResult) {
                $_SESSION['success'] = "You have uploaded a new lesson ($les_type)";
                header("Location: teacher_lessons.php?course_id=" . $c_id);
                exit();
            } else {
                $message = "Failed to upload lesson.";
            }
        }
    }
}

/* Fetch lessons for selected course only */
$lessonsResult = $conn->query("
    SELECT l.*, c.c_name
    FROM lesson l
    INNER JOIN course c ON l.c_id = c.c_id
    WHERE l.t_id = $t_id AND l.c_id = {$selectedCourse['c_id']}
    ORDER BY l.lesson_id DESC
");

$lessons = $lessonsResult;
$lesson_type_selected = isset($_POST['les_type']) ? $_POST['les_type'] : "";
$les_type = $_POST['les_type'] ?? "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Lessons | LMS</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="teacher_lesson.css">
    <style>
        .lesson-table-wrap {
            overflow-x: auto;
        }

        .lesson-table {
            width: 100%;
            border-collapse: collapse;
        }

        .lesson-table th,
        .lesson-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            font-size: 14px;
        }

        /* 💜 DYNAMIC STRATEGIC OVERRIDE PATH - NO IMPORTANT TAGS 💜 */
        html body .teacher-container .lesson-table-wrap table.lesson-table thead tr th {
            background: #e6e6fa; /* Lavender Background */
            color: #800080;      /* Purple Column Text */
            font-weight: 700;
        }

        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            background: #eef2ff;
            color: #4f46e5;
            font-weight: 700;
            font-size: 12px;
        }

        .lesson-link {
            color: #4f46e5;
            font-weight: 700;
            text-decoration: none;
        }

        .empty-text {
            color: #6b7280;
        }

        .highlight-box {
            animation: highlightFade 2s ease;
        }
    </style>
</head>

<body class="teacher-page">

     <?php include 'tnavbar.php'; ?>

    <div class="teacher-container">

        <section class="teacher-hero">
            <h1>Manage Lessons - <?php echo htmlspecialchars($selectedCourse['c_name']); ?></h1>
            <p>Upload lessons for this selected course only.</p>
        </section>

        <div class="teacher-grid">

            <div class="section-box">
                <h2>Upload Lesson</h2>

                <?php if (!empty($message)): ?>
                    <div class="alert"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="success-msg">
                        <?php 
                            echo $_SESSION['success']; 
                            unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Course</label>
                        <input type="text" value="<?php echo htmlspecialchars($selectedCourse['c_name']); ?>" disabled>
                        <input type="hidden" name="c_id" value="<?php echo $selectedCourse['c_id']; ?>">
                    </div>

                    <div class="form-group">
                        <label>Lesson Title</label>
                        <input type="text" name="lesson_title" placeholder="Example: Introduction to HTML" required>
                    </div>

                    <div class="form-group">
                        <label>Lesson Type</label>
                        <select name="les_type" id="lessonType" onchange="handleDuration()">
                            <option value="">Choose type</option>
                            <option value="Video">Video</option>
                            <option value="PDF">PDF</option>
                            <option value="Text">Text</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Lesson URL / PDF Link / Text Link</label>
                        <input type="text" name="url" placeholder="Paste video, PDF, or text link" required>
                    </div>

                    <div class="form-group" id="durationBox" style="display:none;">
                        <label>Duration</label>
                        <input type="text" name="duration" id="durationField" placeholder="HH:MM:SS (e.g. 00:20:19)">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="submit-btn">Upload Lesson</button>
                        <a href="#" class="cancel-btn" onclick="return confirmCancel()">Cancel Upload</a>
                    </div>
                </form>
            </div>

            <div class="section-box">
                <h2 class="lesson-title">Uploaded Lessons for <?php echo htmlspecialchars($selectedCourse['c_name']); ?></h2>

                <?php if ($lessons->num_rows > 0): ?>
                    <div class="lesson-table-wrap">
                        <table class="lesson-table">
                            <thead>
                                <tr>
                                    <th>Lesson</th>
                                    <th>Course</th>
                                    <th>Type</th>
                                    <th>Duration</th>
                                    <th>Link</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($lesson = $lessons->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($lesson['lesson_title']); ?></td>
                                        <td><?php echo htmlspecialchars($lesson['c_name']); ?></td>
                                        <td>
                                            <span class="badge">
                                                <?php echo htmlspecialchars($lesson['les_type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                                echo ($lesson['duration'] == "00:00:00" || empty($lesson['duration'])) 
                                                ? "Not needed" 
                                                : htmlspecialchars($lesson['duration']); 
                                            ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo htmlspecialchars($lesson['url']); ?>" target="_blank" class="lesson-link">
                                                Open
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="empty-text">No lessons uploaded for this course yet.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <script>
    function handleDuration() {
        let type = document.getElementById("lessonType").value;
        let box = document.getElementById("durationBox");
        let input = document.getElementById("durationField");

        if (type === "Video") {
            box.style.display = "block";
            input.required = true;
        } else {
            box.style.display = "none";
            input.required = false;
            input.value = "";
        }
    }
    </script>

    <script>
    function confirmCancel() {
        return confirm("Are you sure you want to cancel this upload?");
    }
    </script>
</body>
</html>