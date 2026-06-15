<?php
session_start();
include "db.php";

$total_courses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM course"))['total'];
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM user WHERE role_id='Student'"))['total'];
$total_teachers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM teacher"))['total'];
$total_categories = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM categ"))['total'];

$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM assigned_course WHERE as_status='Pending'"))['total'];

$course_sql = "
SELECT 
    course.c_id,
    course.c_name,
    categ.cat_name,
    assigned_course.as_status,
    teacher.t_name,
    COUNT(DISTINCT enrollment.s_id) AS total_students
FROM course
LEFT JOIN categ ON course.cat_id = categ.cat_id
LEFT JOIN assigned_course ON course.c_id = assigned_course.c_id
LEFT JOIN teacher ON assigned_course.t_id = teacher.t_id
LEFT JOIN enrollment ON course.c_id = enrollment.c_id
GROUP BY course.c_id, course.c_name, categ.cat_name, assigned_course.as_status, teacher.t_name
ORDER BY course.c_id DESC
";

$course_result = mysqli_query($conn, $course_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="category_management.css?v=80">
    <link rel="stylesheet" href="admin_courses.css?v=83">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<?php include "header_admin.php"; ?>

<main class="main-section">

    <div class="top-area">
        <div>
            <h1>Admin Dashboard</h1>
            <p>Manage platform content and users</p>
        </div>

        <a href="create_course.php" class="add-btn">＋ Create Course</a>
    </div>

    <div class="dashboard-stats">
        <div class="dash-card">
            <div class="dash-head">
                <p>Total Courses</p>
                <i data-lucide="book-open"></i>
            </div>
            <h2><?php echo $total_courses; ?></h2>
            <p><?php echo $pending; ?> pending approval</p>
        </div>

        <div class="dash-card">
            <div class="dash-head">
                <p>Total Students</p>
                <i data-lucide="users"></i>
            </div>
            <h2><?php echo $total_students; ?></h2>
        </div>

        <div class="dash-card">
            <div class="dash-head">
                <p>Teachers</p>
                <i data-lucide="user-check"></i>
            </div>
            <h2><?php echo $total_teachers; ?></h2>
            <p><?php echo $pending; ?> pending approval</p>
        </div>

        <div class="dash-card">
            <div class="dash-head">
                <p>Categories</p>
                <i data-lucide="layers"></i>
            </div>
            <h2><?php echo $total_categories; ?></h2>
        </div>
    </div>

    <div class="tabs-row">
        <div class="left-tabs">
            <a href="#" class="active">All Courses</a>
            <a href="courses_assign.php">Assign Courses</a>
            <a href="category_management.php">Categories</a>
            <a href="certificate_management.php">Certificates</a>
        </div>
    </div>

    <section class="table-box">
        <h3>All Courses</h3>

        <table>
            <thead>
                <tr>
                    <th>Course Title</th>
                    <th>Category</th>
                    <th>Instructor</th>
                    <th>Students</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($course = mysqli_fetch_assoc($course_result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($course['c_name']); ?></td>

                        <td>
                            <span class="category-badge">
                                <?php echo htmlspecialchars($course['cat_name'] ?? 'No Category'); ?>
                            </span>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($course['t_name'] ?? 'Not Assigned'); ?>
                        </td>

                        <td><?php echo $course['total_students']; ?></td>

                        <td>
                            <?php
                                if (empty($course['t_name'])) {
                                    $status = "Not Assigned";
                                } else {
                                    $status = $course['as_status'];
                                }

                                $status_class = strtolower(str_replace(" ", "-", $status));
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo $status; ?>
                            </span>
                        </td>

                        <td class="actions">
                            <a href="#">👁</a>
                            <a href="admin_course_assign_teacher.php?id=<?php echo $course['c_id']; ?>" class="edit-btn">
                                ✎
                            </a>
                            <a href="admin_course_delete.php?id=<?php echo $course['c_id']; ?>" 
                            onclick="return confirm('Are you sure you want to delete this course?')">🗑</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </section>

</main>

<?php include "footer.php"; ?>

<script>
    lucide.createIcons();
</script>

</body>
</html>