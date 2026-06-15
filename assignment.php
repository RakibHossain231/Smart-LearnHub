<?php
session_start();
include "db.php";

$msg = "";

$c_id = $_GET['c_id'];
$u_id = $_SESSION['u_id'];


$student = $conn->query("SELECT * FROM student WHERE u_id='$u_id'");
$s = $student->fetch_assoc();
$s_id = $s['s_id'];


$enroll = $conn->query("SELECT en_id FROM enrollment WHERE s_id='$s_id' AND c_id='$c_id'");
$e = $enroll->fetch_assoc();
$en_id = $e['en_id'];

/*   latest assignment  */
$assignment = $conn->query("SELECT * FROM assignment WHERE c_id='$c_id' ORDER BY assignment_id DESC LIMIT 1");
$row = $assignment->fetch_assoc();


if (isset($_POST['submit'])) {
    $assignment_id = $_POST['assignment_id'];
    $answer = $_POST['answer'];

    $file_name = $_FILES['file']['name'];
    $tmp_name = $_FILES['file']['tmp_name'];

    if (!empty($file_name)) {
        move_uploaded_file($tmp_name, "uploads/" . $file_name);
    }

    /*  deadline for late submission */
    $getDeadline = $conn->query("SELECT deadline FROM assignment WHERE assignment_id='$assignment_id'");
    $d = $getDeadline->fetch_assoc();

    $today = date("Y-m-d H:i:s");
    $is_late = ($today > $d['deadline']) ? 1 : 0;

    /* Insert into database */
    $sql = "INSERT INTO assignmet_result 
            (submitted_answer, submitted_file, submitted_at, is_late, s_id, assignment_id, en_id) 
            VALUES 
            ('$answer', '$file_name', NOW(), '$is_late', '$s_id', '$assignment_id', '$en_id')";

    if ($conn->query($sql)) {
        if ($is_late == 1) {
            $msg = "Late Submission";
        } else {
            $msg = "Assignment Submitted";
        }
    } else {
        $msg = "Error : " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Student Assignment</title>
    <style>
        * {
            box-sizing: border-box;

        }

        body {
            margin: 0;
            font-family: Arial,
                sans-serif;
            background: #f3f4f6;
            color: #111827;
        }

        .container {
            width: 850px;
            margin: 40px auto;
        }

        h2 {
            text-align: center;
            font-size: 32px;
            margin-bottom: 25px;
            color: #1f2937;
        }

        .card {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 18px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid #e5e7eb;
        }

        .card h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #111827;
        }

        .card p {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .download-btn {
            display: inline-block;
            text-decoration: none;
            background: #eef2ff;
            color: #4f46e5;
            padding: 9px 14px;
            border-radius: 10px;
            font-weight: bold;
            margin: 8px 0 18px;
        }

        .download-btn:hover {
            background: #e0e7ff;
        }

        textarea,
        input[type="file"] {
            width: 100%;
            padding: 13px;
            margin-top: 10px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            font-size: 15px;
            background: #f9fafb;
        }

        textarea {
            height: 110px;
            resize: none;
        }

        textarea:focus,
        input[type="file"]:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px #e0e7ff;
            background: white;
        }

        button {
            width: 100%;
            padding: 13px;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 12px;
            margin-top: 15px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #4338ca;
        }

        .msg {
            background: #dcfce7;
            color: #166534;
            padding: 13px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .back-btn {
            display: block;
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
            background: #e5e7eb;
            color: #111827;
            padding: 12px;
            border-radius: 12px;
            font-weight: bold;
        }

        .back-btn:hover {
            background: #d1d5db;
        }
        .des{
            color: black;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="container">

        <h2>Assignments</h2>

        <?php if ($msg != ""): ?>
            <div class="msg">
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <?php
        // teacher posted assignment
        if ($row):
            $current_assignment_id = $row['assignment_id'];

            /* Check  student has already submitted  assignment */
            $check_submission = $conn->query("SELECT * FROM assignmet_result WHERE s_id='$s_id' AND assignment_id='$current_assignment_id'");

            if ($check_submission->num_rows > 0):
                /*  Student submitted it,  */
        ?>
                <div class="card" style="text-align:center; color:#6b7280;">
                    <p>no assignment available</p>
                </div>
            <?php else: 
                /*  Teacher posted an assignment and student hasn't submitted it yet */
            ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($row['assign_title']); ?></h3>
                   <?php if (!empty($row['assign_file'])): ?>

    <a href="uploads/<?php echo urlencode($row['assign_file']); ?>" 
       class="download-btn" 
       download>
        Download Assignment File
    </a>

<?php else: ?>

    <p class="des"> 
        Assignment Description:
        <?php echo htmlspecialchars($row['assign_descrip']); ?>
    </p>

<?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="assignment_id" value="<?php echo htmlspecialchars($row['assignment_id']); ?>">

                        <textarea name="answer" placeholder="Write your answer here..."></textarea>
                        <input type="file" name="file">

                        <button type="submit" name="submit">Submit Assignment</button>
                    </form>
                </div>
            <?php endif; ?>
        <?php else: 
            /* Scenario 3: Teacher has not posted any assignment at all */
            ?>
            <div class="card" style="text-align:center; color:#6b7280;">
                <p>no assignment available</p>
            </div>
        <?php endif; ?>

        <a href="student_home.php" class="back-btn">← Back to Dashboard</a>

    </div>

</body>

</html>
