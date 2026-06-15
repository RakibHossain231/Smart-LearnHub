<?php
session_start();
include "db.php";

if (!isset($_SESSION['u_id']) || $_SESSION['role_id'] !== 'Teacher') {
    header("Location: login.php");
    exit();
}

$u_id = intval($_SESSION['u_id']);
$message = "";

/* Get teacher info */
$teacher = $conn->query("SELECT * FROM teacher WHERE u_id='$u_id'");
$t = $teacher->fetch_assoc();
if (!$t) {
    die("Teacher profile not found.");
}
$t_id = $t['t_id'];

/* Process direct inline assignment evaluation */
if (isset($_POST['save_inline_mark'])) {
    $res_id = intval($_POST['assignment_rest_id']);
    $mark = floatval($_POST['ass_mark']);

    $en_id = intval($_POST['en_id']);
    $s_id = intval($_POST['s_id']);
    $c_id = intval($_POST['c_id']);

    // Update individual assignment score and status
    $updateRes = $conn->query("UPDATE assignmet_result SET ass_mark = $mark, status = 'Marked' WHERE assignment_rest_id = $res_id");

    // Sync components directly inside final course grades ledger
    $checkGrade = $conn->query("SELECT grade_id FROM grade WHERE en_id = $en_id");
    if ($checkGrade->num_rows > 0) {
        $conn->query("UPDATE grade SET assignmet_mark = $mark WHERE en_id = $en_id");
    } else {
        $conn->query("INSERT INTO grade (assignmet_mark, quizz_mark, mid_mark, final_mark, attenda_mark, total_mark, final_grade, s_id, c_id, en_id) 
                      VALUES ($mark, 0, 0, 0, 0, $mark, 'F', $s_id, $c_id, $en_id)");
    }

    if ($updateRes) {
        $message = "Assignment score recorded cleanly!";
    }
}

/* Get teacher courses */
$courses = $conn->query("
    SELECT c.*
    FROM assigned_course ac
    JOIN course c ON ac.c_id = c.c_id
    WHERE ac.t_id='$t_id'
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Course Wise Assignment Submissions | Smart-LearnHub</title>
    <link rel="stylesheet" href="style.css">
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

        .course-box {
            background: white;
            padding: 26px;
            margin-bottom: 30px;
            border-radius: 22px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            width: 100%;
            box-sizing: border-box;
        }

        .course-title {
            font-size: 26px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 18px;
        }


        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            padding: 14px !important;
            text-align: left !important;
            border-bottom: 1px solid #e5e7eb !important;
            font-size: 15px !important;
        }

        th {
            background-color: #fafafa !important;
            color: #374151 !important;
            font-weight: 700 !important;
        }

        /* Simple Alternating Row Background Colors using !important to force it */
        table tbody tr:nth-child(even) {
            background-color: #f8fafc !important;
        }

        table tbody tr:nth-child(odd) {
            background-color: #ffffff !important;
        }

        /* Highlight row on mouse hover */
        table tbody tr:hover {
            background-color: #f1f5f9 !important;
        }

        /* Color Badges */
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
        }

        .ontime {
            background: #dcfce7 !important;
            color: #166534 !important;
            border: 1px solid #86efac;
        }

        .late {
            background: #fee2e2 !important;
            color: #b91c1c !important;
            border: 1px solid #fca5a5;
        }

        .status-graded {
            background: #ecfdf5 !important;
            color: #047857 !important;
        }

        .status-pending {
            background: #fef3c7 !important;
            color: #d97706 !important;
        }

        .file {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            background: #eef2ff;
            color: #4f46e5;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: bold;
            border: 1px solid #c7d2fe;
        }

        .file:hover {
            background: #e0e7ff;
        }

        .grade-form-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .input-mark {
            width: 75px;
            height: 34px;
            padding: 0 8px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-weight: bold;
            text-align: center;
            font-size: 14px;
            background: white !important;
        }

        .btn-save-inline {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 13px;
        }

        .btn-save-inline:hover {
            background: #4338ca;
        }

        .empty {
            color: #6b7280;
            background: #fafafa;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }

        .toast-msg {
            background: #dcfce7;
            color: #166534;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #bbf7d0;
        }
    </style>
</head>

<body>

    <?php include 'tnavbar.php'; ?>

    <div class="teacher-container">
        <section class="teacher-hero">
            <h1>Course Assignment Submissions</h1>
            <p>Review descriptive implementation answers, download attached files, and award points out of 100.</p>
        </section>

        <?php if (!empty($message)) { ?>
            <div class="toast-msg"><?php echo $message; ?></div>
        <?php } ?>

        <?php while ($course = $courses->fetch_assoc()) { ?>
            <div class="course-box">
                <div class="course-title">
                    <?php echo htmlspecialchars($course['c_name']); ?>
                </div>

                <?php
                $c_id = $course['c_id'];
                $submissions = $conn->query("
                    SELECT 
                        ar.*,
                        a.assign_title,
                        a.deadline,
                        a.c_id,
                        s.s_name
                    FROM assignmet_result ar
                    JOIN assignment a ON ar.assignment_id = a.assignment_id
                    JOIN student s ON ar.s_id = s.s_id
                    WHERE a.c_id='$c_id' AND a.t_id='$t_id'
                    ORDER BY ar.submitted_at DESC
                ");
                ?>

                <?php if ($submissions->num_rows > 0) { ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Assignment Title</th>
                                <th>Submitted Answer Text</th>
                                <th>File</th>
                                <th>Deadline Status</th>
                                <th>Grading Status</th>
                                <th>Assign Grade (% )</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $submissions->fetch_assoc()) { ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['s_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['assign_title']); ?></td>
                                    <td>
                                        <div style="max-width: 300px; max-height: 70px; overflow-y: auto; background: #fff; padding: 6px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 14px;">
                                            <?php echo nl2br(htmlspecialchars($row['submitted_answer'] ?? 'No text description entered.')); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['submitted_file'])) { ?>
                                            <a class="file" href="uploads/<?php echo htmlspecialchars($row['submitted_file']); ?>" download>
                                                📥 Download
                                            </a>
                                        <?php } else {
                                            echo "No File";
                                        } ?>
                                    </td>
                                    <td>
                                        <?php if ($row['is_late'] == 1) { ?>
                                            <span class="badge late">Late Submission</span>
                                        <?php } else { ?>
                                            <span class="badge ontime">On Time</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo ($row['status'] == 'Marked') ? 'status-graded' : 'status-pending'; ?>">
                                            <?php echo ($row['status'] == 'Marked') ? 'Graded' : 'Pending'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="" class="grade-form-row">
                                            <input type="hidden" name="assignment_rest_id" value="<?php echo $row['assignment_rest_id']; ?>">
                                            <input type="hidden" name="en_id" value="<?php echo $row['en_id']; ?>">
                                            <input type="hidden" name="s_id" value="<?php echo $row['s_id']; ?>">
                                            <input type="hidden" name="c_id" value="<?php echo $row['c_id']; ?>">

                                            <input type="number" step="0.01" min="0" max="100" name="ass_mark" class="input-mark" placeholder="0-100" value="<?php echo $row['ass_mark']; ?>" required>
                                            <button type="submit" name="save_inline_mark" class="btn-save-inline">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <div class="empty">No student homework submissions found for this course panel.</div>
                <?php } ?>
            </div>
        <?php } ?>

        <a href="teacher_home.php" style="display: block; text-align: center; margin-top: 20px; background: #e5e7eb; color: #111827; padding: 12px; border-radius: 12px; font-weight: bold; font-size: 14px; text-decoration: none; transition: 0.2s;" onmouseover="this.style.background='#d1d5db'" onmouseout="this.style.background='#e5e7eb'">
            ← Back to Dashboard
        </a>

    </div>
</body>

</html>