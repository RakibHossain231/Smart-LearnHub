<?php
session_start();
include 'db.php';

if (!isset($_SESSION['u_id']) || $_SESSION['role_id'] !== 'Teacher') {
    header("Location: login.php");
    exit();
}

$u_id = intval($_SESSION['u_id']);

// Fetch Teacher Details
$teacherQuery = $conn->query("SELECT t_id FROM teacher WHERE u_id = $u_id");
$teacher = $teacherQuery->fetch_assoc();
if (!$teacher) {
    die("Teacher profile not found.");
}
$t_id = $teacher['t_id'];

// Filter parameters
$selected_course = isset($_GET['c_id']) ? intval($_GET['c_id']) : 0;
$selected_type = isset($_GET['test_type']) ? trim($_GET['test_type']) : '';

// Build Query to grab test results for this teacher's courses
$queryStr = "
    SELECT 
        tr.test_result_id,
        tr.test_result_mark,
        tr.test_status,
        tr.submitted_at,
        tr.is_late,
        t.test_type,
        t.test_mark as max_mark,
        t.test_question,
        c.c_name,
        s.s_name
    FROM test_result tr
    JOIN test t ON tr.test_id = t.test_id
    JOIN enrollment e ON tr.en_id = e.en_id
    JOIN student s ON e.s_id = s.s_id
    JOIN course c ON t.c_id = c.c_id
    WHERE t.t_id = $t_id
";

// Apply Filters
if ($selected_course > 0) {
    $queryStr .= " AND t.c_id = $selected_course";
}
if ($selected_type !== '') {
    $queryStr .= " AND t.test_type = '" . $conn->real_escape_string($selected_type) . "'";
}

$queryStr .= " ORDER BY tr.submitted_at DESC";
$submissions = $conn->query($queryStr);

// Fetch filter selections
$courses = $conn->query("
    SELECT c.c_id, c.c_name 
    FROM assigned_course ac 
    JOIN course c ON ac.c_id = c.c_id 
    WHERE ac.t_id = $t_id
");

$typesQuery = $conn->query("
    SELECT DISTINCT t.test_type 
    FROM test t 
    WHERE t.t_id = $t_id
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Submissions Panel | Smart-LearnHub</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            margin: 0;
            padding: 0;
        }

        /* Full widescreen wrapper dimensions layout */
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
            margin-top: 0;
            margin-bottom: 18px;
            font-size: 28px;
            color: #111827;
        }

        .filter-bar {
            margin-bottom: 25px;
        }

        .filter-form {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-group label {
            font-size: 13px;
            font-weight: 700;
            color: #4b5563;
        }

        .filter-select {
            height: 42px;
            padding: 0 14px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            min-width: 220px;
            font-size: 14px;
            background: #fafafa;
        }

        .filter-select:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px #e0e7ff;
        }

        .btn-clear {
            height: 42px;
            padding: 0 16px;
            background: #e5e7eb;
            color: #1f2937;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            align-self: flex-end;
        }

        .btn-clear:hover {
            background: #d1d5db;
        }

        .section-box table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }


        .section-box table th,
        .section-box table td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            font-size: 15px;
        }

        .section-box table thead th {
            background-color: #e6e6fa;
            color: #800080;
            font-weight: 700;
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

        .course-badge {
            display: inline-block;
            background: #eef2ff !important;
            color: #4f46e5 !important;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }


        .section-box .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        /* Light Blue for Submitted */
        .section-box .status-submitted {
            background-color: #e0f2fe;
            color: #0369a1;
        }


        .section-box .status-marked {
            background-color: #dcfce7;
            color: #166534;
        }


        .section-box .status-ontime {
            background-color: #ecfdf5;
            color: #047857;
        }


        .section-box .status-submitted.status-late,
        .section-box .status-late {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .btn-action {
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 13px;
            background: #4f46e5;
            color: white;
            display: inline-block;
        }

        .btn-action:hover {
            background: #4338ca;
        }
    </style>
</head>

<body>

    <?php include 'tnavbar.php'; ?>

    <div class="teacher-container">
        <section class="teacher-hero">
            <h1>Assessment Submissions</h1>
            <p>Review, track, and score student performance evaluations across your assigned classes.</p>
        </section>

        <section class="section-box">
            <h2>Student Submission Records</h2>

            <div class="filter-bar">
                <form method="GET" action="" class="filter-form">

                    <div class="filter-group">
                        <label for="c_id">Course</label>
                        <select name="c_id" id="c_id" class="filter-select" onchange="this.form.submit()">
                            <option value="">All Assigned Courses</option>
                            <?php while ($c = $courses->fetch_assoc()) { ?>
                                <option value="<?php echo $c['c_id']; ?>" <?php if ($selected_course == $c['c_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($c['c_name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="test_type">Assessment Type</label>
                        <select name="test_type" id="test_type" class="filter-select" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <?php while ($tRow = $typesQuery->fetch_assoc()) {
                                if (empty($tRow['test_type'])) continue;
                            ?>
                                <option value="<?php echo htmlspecialchars($tRow['test_type']); ?>" <?php if ($selected_type == $tRow['test_type']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($tRow['test_type']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <?php if ($selected_course > 0 || $selected_type !== '') { ?>
                        <a href="teacher_submissions.php" class="btn-clear">Clear Filters</a>
                    <?php } ?>

                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Course</th>
                        <th>Assessment Type</th>
                        <th>Submission Time</th>
                        <th>Timeline Status</th>
                        <th>Grading Status</th>
                        <th>Marks Obtained</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($submissions->num_rows > 0) {
                        while ($row = $submissions->fetch_assoc()) { ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['s_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['c_name']); ?></td>
                                <td><span class="course-badge"><?php echo htmlspecialchars($row['test_type']); ?></span></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($row['submitted_at'])); ?></td>
                                <td>
                                    <?php if ($row['is_late'] == 1) { ?>
                                        <span class="badge status-badge status-late">Late Submission</span>
                                    <?php } else { ?>
                                        <span class="badge status-badge status-ontime">On Time</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <span class="badge status-badge <?php echo ($row['test_status'] == 'Marked') ? 'status-marked' : 'status-submitted'; ?>">
                                        <?php echo htmlspecialchars($row['test_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo $row['test_result_mark']; ?></strong> / <?php echo $row['max_mark']; ?>
                                </td>
                                <td>
                                    <a href="teacher_grade_paper.php?id=<?php echo $row['test_result_id']; ?>" class="btn-action">
                                        <?php echo ($row['test_status'] == 'Marked') ? 'Review Grade' : 'Grade Paper'; ?>
                                    </a>
                                </td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #6b7280; padding: 30px;">No assessment submissions found for this configuration.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>

        <a href="teacher_home.php" style="display: block; text-align: center; margin-top: 20px; background: #e5e7eb; color: #111827; padding: 12px; border-radius: 12px; font-weight: bold; font-size: 14px; text-decoration: none; transition: 0.2s;" onmouseover="this.style.background='#d1d5db'" onmouseout="this.style.background='#e5e7eb'">
            ← Back to Dashboard
        </a>

    </div>
</body>

</html>