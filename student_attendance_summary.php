<?php
session_start();
include 'db.php';

if (!isset($_SESSION['u_id']) || $_SESSION['role_id'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$u_id = $_SESSION['u_id'];

$student = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM student WHERE u_id = $u_id"
));

if (!$student) {
    die("Student not found");
}

$s_id = $student['s_id'];

// enrolled courses
$courses = mysqli_query($conn,"
    SELECT c.*
    FROM enrollment e
    JOIN course c ON e.c_id = c.c_id
    WHERE e.s_id = $s_id
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Summary</title>

    <style>

        body{
            margin:0;
            font-family:Arial, sans-serif;
            background:#f5f7fb;
        }

        .container{
            padding:25px;
        }

        .course-box{
            background:white;
            padding:20px;
            border-radius:18px;
            margin-bottom:25px;
            box-shadow:0 6px 18px rgba(0,0,0,0.05);
        }

        .course-box h2{
            margin-top:0;
            color:#111827;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:15px;
        }

        table th{
            background:#4f46e5;
            color:white;
            padding:12px;
            text-align:left;
        }

        table td{
            padding:12px;
            border-bottom:1px solid #e5e7eb;
        }

        .completed{
            color:#16a34a;
            font-weight:bold;
        }

        .missed{
            color:#dc2626;
            font-weight:bold;
        }

        .btn{
            display:inline-block;
            margin-bottom:20px;
            background:#4f46e5;
            color:white;
            padding:8px 14px;
            border-radius:8px;
            text-decoration:none;
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

    </style>
</head>

<body>

<header class="admin-navbar">

    <div class="admin-left">
        <div class="logo-box">🛠️</div>
        <div class="logo-text">
            <h2>Smart-LearnHub</h2>
            <p>student Panel</p>
        </div>
    </div>

    


    <nav class="nav-links">
        <a href="student_index.php">Home</a>
        <a href="all_courses.php">Courses</a>
        <a href="student_home.php">Dashboard</a>
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

<div class="container">

    <a href="student_home.php" class="btn">
        ← Back to Dashboard
    </a>

    <h1>Attendance Summary</h1>

    <?php while($course = mysqli_fetch_assoc($courses)): ?>

        <div class="course-box">

            <h2>
                <?php echo htmlspecialchars($course['c_name']); ?>
            </h2>

            <?php

            $c_id = $course['c_id'];

            $lessons = mysqli_query($conn,"
                SELECT *
                FROM lesson
                WHERE c_id = $c_id
                ORDER BY lesson_id DESC
            ");

            ?>

            <?php if(mysqli_num_rows($lessons) > 0): ?>

                <table>

                    <tr>
                        <th>Lesson</th>
                        <th>Type</th>
                        <th>Status</th>
                    </tr>

                    <?php while($lesson = mysqli_fetch_assoc($lessons)): ?>

                        <?php

                        $lesson_id = $lesson['lesson_id'];

                        $att = mysqli_query($conn,"
                            SELECT *
                            FROM attendance
                            WHERE s_id = $s_id
                            AND lesson_id = $lesson_id
                            AND attendance = 1
                        ");

                       $completed = (bool)mysqli_num_rows($att);


                        ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($lesson['lesson_title']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($lesson['les_type']); ?>
                            </td>

                            <td>

                                <?php if($completed): ?>

                                    <span class="completed">
                                        Completed
                                    </span>

                                <?php else: ?>

                                    <span class="missed">
                                        Missed
                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                </table>

            <?php else: ?>

                <p>No lessons added yet.</p>

            <?php endif; ?>

        </div>

    <?php endwhile; ?>

</div>

</body>
</html>