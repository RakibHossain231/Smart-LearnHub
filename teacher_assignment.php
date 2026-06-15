<?php
session_start();
include "db.php";

if (!isset($_SESSION['u_id']) || $_SESSION['role_id'] !== 'Teacher') {
    header("Location: login.php");
    exit();
}

$msg = "";
$u_id = intval($_SESSION['u_id']);
$c_id = isset($_GET['c_id']) ? intval($_GET['c_id']) : 0;

if ($c_id <= 0) {
    die("No course selected.");
}

/* get teacher id */
$teacherQuery = $conn->query("SELECT t_id FROM teacher WHERE u_id = '$u_id'");
$teacher = $teacherQuery->fetch_assoc();
if (!$teacher) { die("Teacher profile not found."); }
$t_id = $teacher['t_id'];

if (isset($_POST['submit'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $deadline = $conn->real_escape_string($_POST['deadline']);

    $file_name = $_FILES['assignment_file']['name'];
    $tmp_name = $_FILES['assignment_file']['tmp_name'];
    
    if (!empty($file_name)) {
        if (!is_dir("uploads")) {
            mkdir("uploads", 0777, true);
        }
        move_uploaded_file($tmp_name, "uploads/" . $file_name);
    } else {
        $file_name = null;
    }

    $sql = "INSERT INTO assignment (assign_title, assign_descrip, deadline, assign_file, c_id, t_id) 
            VALUES ('$title', '$description', '$deadline', " . ($file_name ? "'$file_name'" : "NULL") . ", '$c_id', '$t_id')";

    if ($conn->query($sql)) {
        $msg = "Assignment Created Successfully!";
    } else {
        $msg = "Error : " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Assignment | Smart-LearnHub</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
        }

        .teacher-container {
            padding: 28px 30px;
            width: calc(100% - 60px);
            max-width: 100%;
            margin: 0 auto;
            box-sizing: border-box;
            display: flex;
            justify-content: center;
        }

        .box {
            width: 550px;
            background: white;
            padding: 35px;
            border-radius: 22px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
        }

        h2 {
            text-align: center;
            color: #111827;
            margin: 0 0 8px 0;
            font-size: 28px;
        }

        .subtitle {
            text-align: center;
            color: #6b7280;
            margin-bottom: 25px;
            font-size: 15px;
        }

        label {
            font-weight: bold;
            color: #374151;
            margin-top: 15px;
            display: block;
            font-size: 14px;
        }

        input, textarea {
            width: 100%;
            padding: 13px;
            margin-top: 8px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 15px;
            box-sizing: border-box;
            background: #fafafa;
        }

        textarea {
            height: 120px;
            resize: none;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px #e0e7ff;
            background: #fff;
        }

        button {
            width: 100%;
            padding: 14px;
            margin-top: 22px;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }

        button:hover {
            background: #4338ca;
        }

        .msg {
            text-align: center;
            background: #dcfce7;
            color: #166534;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-weight: bold;
            border: 1px solid #bbf7d0;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 18px;
            text-decoration: none;
            color: #4f46e5;
            font-weight: bold;
            font-size: 14px;
        }
        .back:hover {
            color: #3bd2f6;
        }
    </style>
</head>
<body>

    <?php include 'teacher_navbar.php'; ?>

    <div class="teacher-container">
        <div class="box">
            <h2>Create Assignment</h2>
            <p class="subtitle">Add a new assignment for this course path</p>

            <?php if ($msg != ""): ?>
                <div class="msg"><?php echo $msg; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <label>Assignment Title</label>
                <input type="text" name="title" placeholder="Enter assignment title" required>

                <label>Assignment Description</label>
                <textarea name="description" placeholder="Write assignment instructions details..."></textarea>

                <label>Assignment File Attachment (Optional)</label>
                <input type="file" name="assignment_file">

                <label>Submission Deadline</label>
                <input type="datetime-local" name="deadline" required>

                <button type="submit" name="submit">Create Assignment</button>
            </form>

            <a href="teacher_home.php" class="back">← Back to Dashboard</a>
        </div>
    </div>

</body>
</html>