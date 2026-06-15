<?php
session_start();
include 'db.php';

if (!isset($_SESSION['u_id']) || $_SESSION['role_id'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$u_id = intval($_SESSION['u_id']);
$c_id = isset($_GET['c_id']) ? intval($_GET['c_id']) : 0;

if ($c_id <= 0) {
    header("Location: student_home.php");
    exit();
}


$student_q = $conn->query("SELECT s_id, s_name FROM student WHERE u_id = $u_id");
if (!$student_q || $student_q->num_rows == 0) {
    die("Student profile records not found.");
}
$student = $student_q->fetch_assoc();
$s_id = $student['s_id'];


$course_q = $conn->query("
    SELECT 
        c.c_name,
        g.assignmet_mark, 
        g.quizz_mark, 
        g.mid_mark, 
        g.final_mark, 
        g.attenda_mark,
        g.total_mark,
        g.final_grade,
        cert.status as cert_status,
        tr.submitted_answer as instructor_feedback
    FROM enrollment e
    JOIN course c ON e.c_id = c.c_id
    LEFT JOIN grade g ON e.en_id = g.en_id
    LEFT JOIN certificate cert ON (cert.s_id = e.s_id AND cert.c_id = e.c_id)
    LEFT JOIN test_result tr ON tr.en_id = e.en_id
    WHERE e.s_id = $s_id AND e.c_id = $c_id
    GROUP BY c.c_id
");

if (!$course_q || $course_q->num_rows == 0) {
    die("You are not currently enrolled in this specific course module.");
}

$data = $course_q->fetch_assoc();

// Checking if Admin has approved the grade 
$is_admin_approved = ($data['cert_status'] === 'Sent' || $data['cert_status'] === 'Generated');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Performance Results | Smart-LearnHub</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 0;
            color: #111827;
        }

        
        .results-wrapper {
            padding: 28px 30px;
            width: calc(100% - 60px);
            max-width: 100%;
            margin: 0 auto;
            box-sizing: border-box;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 18px;
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link:hover {
            color: #4f46e5;
        }

        .results-hero {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: white;
            padding: 34px 30px;
            border-radius: 24px;
            margin-bottom: 24px;
            width: 100%;
            box-sizing: border-box;
        }

        .results-hero h1 {
            font-size: 34px;
            margin: 0 0 8px 0;
        }

        .results-hero p {
            margin: 0;
            color: #e5e7eb;
            font-size: 15px;
            opacity: 0.92;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 22px;
            margin-bottom: 30px;
            width: 100%;
        }

        .metric-card {
            background: white;
            padding: 22px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
            border: 1px solid #e5e7eb;
            box-sizing: border-box;
        }

        .metric-card h3 {
            font-size: 14px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 8px;
           
        }

        .metric-card .score {
            font-size: 28px;
            font-weight: bold;
            color: #111827;
        }

        .section-box {
            background: white;
            border-radius: 22px;
            padding: 26px;
            margin-bottom: 25px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            width: 100%;
            box-sizing: border-box;
        }

        .section-box h2 {

            font-size: 24px;
            margin: 0 0 15px 0;
            color: #111827;

        }

        table {
            width: 100%;
            border-collapse: collapse;

        }

        table th,
        table td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            font-size: 15px;

        }


        table thead th {

            background-color: #e6e6fa;
            color: #800080;
            font-weight: bold;


        }

        table tbody tr:nth-child(even) {
            background-color: #f8fafc;

        }

        table tbody tr:hover {
            background-color: #f1f5f9;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }

        .status-released {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .status-locked {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fcd34d;
            font-style: italic;
        }

        .final-grade-display {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
        }

        .instructor-feedback {
            font-size: 14px;
            color: #4b5563;
            font-style: italic;
            background: #f8fafc;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #4f46e5;
            margin-top: 10px;
        }


        .student-navbar {
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
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            margin-top: 0px;
            margin-bottom: 30px;
            box-sizing: border-box;
        }

        .nav-links a {
            text-decoration: none;
            color: #475569;
            font-weight: 600;
            padding: 8px 14px;
            border-radius: 8px;
        }

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

        .logout-btn {
            text-decoration: none;
            background: #4f46e5;
            color: #fff;
            padding: 7px 13px;
            border-radius: 8px;
            font-size: 13px;
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

        @media(max-width: 1000px) {
            .metrics-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width: 600px) {
            .metrics-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <header class="student-navbar">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div class="logo-box">🛠️</div>
            <div>
                <h2 style="font-size: 22px; margin: 0;">Smart-LearnHub</h2>
                <p style="font-size: 12px; color: #64748b; margin: 0;">Student Panel</p>
            </div>
        </div>
        <nav class="nav-links">
            <a href="student_index.php">Home</a>
            <a href="all_courses.php">Courses</a>
            <a href="student_home.php">Dashboard</a>
        </nav>
        <div class="admin-user">
            <span>👤 <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Student'); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="results-wrapper">
        <a href="take_lesson.php?c_id=<?php echo $c_id; ?>" class="back-link">← Back to Learning Room</a>

        <section class="results-hero">
            <h1>Performance Report</h1>
            <p>Course Module: <strong><?php echo htmlspecialchars($data['c_name']); ?></strong></p>
        </section>

        <div class="metrics-grid">
            <div class="metric-card">
                <h3>Assignments Mark</h3>
                <div class="score" style="color: #f97316;"><?php echo $data['assignmet_mark'] ?? 0; ?></div>
            </div>
            <div class="metric-card">
                <h3>Quizzes Mark</h3>
                <div class="score" style="color: #2563eb;"><?php echo $data['quizz_mark'] ?? 0; ?></div>
            </div>
            <div class="metric-card">
                <h3>Midterm Exam</h3>
                <div class="score" style="color: #dc2626;"><?php echo $data['mid_mark'] ?? 0; ?></div>
            </div>
            <div class="metric-card">
                <h3>Attendance Mark</h3>
                <div class="score" style="color: #16a34a;"><?php echo $data['attenda_mark'] ?? 0; ?> <span style="font-size:14px; color:#6b7280;">pts</span></div>
            </div>
        </div>

        <div class="section-box">
            <h2>Official Academic Grade Report</h2>
            <table>
                <thead>
                    <tr>
                        <th>Verification Status</th>
                        <th>Cumulative Total Score</th>
                        <th>Final Letter Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <?php if ($is_admin_approved): ?>
                                <span class="status-badge status-released">Approved & Published</span>
                            <?php else: ?>
                                <span class="status-badge status-locked">Pending Admin Approval</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="final-grade-display">
                                <?php echo $is_admin_approved ? ($data['total_mark'] ?? '0') . ' / 100' : '—'; ?>
                            </span>
                        </td>
                        <td>
                            <span class="final-grade-display" style="color: #b91c1c;">
                                <?php echo $is_admin_approved ? ($data['final_grade'] ?? 'Not Set') : ' Final Grade Pending'; ?>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php if ($is_admin_approved && !empty($data['instructor_feedback'])): ?>
                <div style="margin-top: 25px;">
                    <strong style="font-size: 15px; color:#374151;">Instructor Feedback Remarks:</strong>
                    <div class="instructor-feedback">
                        "<?php echo htmlspecialchars($data['instructor_feedback']); ?>"
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>