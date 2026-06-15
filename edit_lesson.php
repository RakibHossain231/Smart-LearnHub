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

$message = "";
$lesson_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$u_id = $_SESSION['u_id'];

$teacherStmt = $conn->prepare("SELECT t_id FROM teacher WHERE u_id = ?");
$teacherStmt->bind_param("i", $u_id);
$teacherStmt->execute();
$teacher = $teacherStmt->get_result()->fetch_assoc();

if (!$teacher) {
    die("Teacher not found.");
}

$t_id = $teacher['t_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lesson_title = trim($_POST['lesson_title']);
    $les_type = trim($_POST['les_type']);
    $duration = isset($_POST['duration']) ? trim($_POST['duration']) : "00:00:00";
    $url = trim($_POST['url']);

    if (empty($lesson_title) || empty($les_type) || empty($url)) {
        $message = "Please fill in all required fields.";
    } elseif ($les_type === "Video" && empty($duration)) {
        $message = "Duration is required for video lessons.";
    } else {
        if ($les_type !== "Video") {
            $duration = "00:00:00";
        }

        $updateStmt = $conn->prepare("
            UPDATE lesson 
            SET lesson_title = ?, les_type = ?, duration = ?, url = ?
            WHERE lesson_id = ? AND t_id = ?
        ");
        $updateStmt->bind_param("ssssii", $lesson_title, $les_type, $duration, $url, $lesson_id, $t_id);

        if ($updateStmt->execute()) {
            header("Location: teacher_view_lessons.php");
            exit();
        } else {
            $message = "Update failed.";
        }
    }
}

$lessonStmt = $conn->prepare("
    SELECT * FROM lesson 
    WHERE lesson_id = ? AND t_id = ?
");
$lessonStmt->bind_param("ii", $lesson_id, $t_id);
$lessonStmt->execute();
$lesson = $lessonStmt->get_result()->fetch_assoc();

if (!$lesson) {
    die("Lesson not found.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Lesson</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            padding: 40px;
        }

        .edit-box {
            max-width: 700px;
            margin: 80px auto;
            background: white;
            padding: 28px;
            border: 4px solid #4f46e5;
            border-radius: 18px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        h2 {
            margin-bottom: 20px;
            color: #111827;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
            color: #374151;
        }

        input,
        select {
            width: 100%;
            padding: 13px;
            margin-bottom: 16px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 15px;
            box-sizing: border-box;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79,70,229,0.12);
        }

        .alert {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .btn {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn:hover {
            background: #4338ca;
        }

        .cancel {
            text-decoration: none;
            background: #e5e7eb;
            color: #374151;
            padding: 12px 18px;
            border-radius: 10px;
            font-weight: bold;
            margin-left: 8px;
        }

        .cancel:hover {
            background: #d1d5db;
        }

        .actions {
            margin-top: 8px;
        }
    </style>
</head>

<body>

<div class="edit-box">
    <h2>Edit Lesson</h2>

    <?php if (!empty($message)): ?>
        <div class="alert"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Lesson Title</label>
        <input 
            type="text" 
            name="lesson_title" 
            value="<?php echo htmlspecialchars($lesson['lesson_title']); ?>" 
            required
        >

        <label>Lesson Type</label>
        <select name="les_type" id="lessonType" required>
            <option value="Video" <?php if ($lesson['les_type'] == "Video") echo "selected"; ?>>Video</option>
            <option value="PDF" <?php if ($lesson['les_type'] == "PDF") echo "selected"; ?>>PDF</option>
            <option value="Text" <?php if ($lesson['les_type'] == "Text") echo "selected"; ?>>Text</option>
        </select>

        <label id="durationLabel">Duration</label>
        <input 
            type="time" 
            name="duration" 
            id="durationField" 
            value="<?php echo htmlspecialchars($lesson['duration']); ?>"
        >

        <label>Lesson URL</label>
        <input 
            type="text" 
            name="url" 
            value="<?php echo htmlspecialchars($lesson['url']); ?>" 
            required
        >

        <div class="actions">
            <button type="submit" class="btn">Update Lesson</button>
            <a href="teacher_view_lessons.php" class="cancel">Cancel</a>
        </div>
    </form>
</div>

<script>
const lessonType = document.getElementById("lessonType");
const durationField = document.getElementById("durationField");
const durationLabel = document.getElementById("durationLabel");

function checkDuration() {
    if (lessonType.value === "Video") {
        durationField.style.display = "block";
        durationLabel.style.display = "block";
        durationField.required = true;
    } else {
        durationField.style.display = "none";
        durationLabel.style.display = "none";
        durationField.required = false;
        durationField.value = "00:00:00";
    }
}

lessonType.addEventListener("change", checkDuration);
checkDuration();
</script>

</body>
</html>