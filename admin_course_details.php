<?php
session_start();
include "db.php";

if (!isset($_GET['id'])) {
    header("Location: admin_courses_view.php");
    exit();
}

$c_id = mysqli_real_escape_string($conn, $_GET['id']);

$sql = "
SELECT 
    course.*,
    categ.cat_name,
    user.full_name AS admin_name
FROM course
LEFT JOIN categ ON course.cat_id = categ.cat_id
LEFT JOIN user ON course.created_by = user.u_id
WHERE course.c_id='$c_id'
LIMIT 1
";

$result = mysqli_query($conn, $sql);
$course = mysqli_fetch_assoc($result);

if (!$course) {
    die("Course not found");
}

$student_count = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM enrollment WHERE c_id='$c_id'
"))['total'];

$teacher_q = mysqli_query($conn, "
    SELECT teacher.t_name
    FROM assigned_course
    LEFT JOIN teacher ON assigned_course.t_id = teacher.t_id
    WHERE assigned_course.c_id='$c_id'
    LIMIT 1
");

$teacher = mysqli_fetch_assoc($teacher_q);
$teacher_name = $teacher['t_name'] ?? 'Not Assigned';

$image = !empty($course['c_image']) ? $course['c_image'] : "Images/default-course.jpg";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Course Details</title>
    <link rel="stylesheet" href="admin_course_details.css?v=1">
</head>
<body>

<?php include "header_admin.php"; ?>

<main class="details-main">

    <div class="details-card">
        <img src="<?php echo htmlspecialchars($image); ?>" class="course-cover">

        <div class="details-content">
            <span class="category"><?php echo htmlspecialchars($course['cat_name'] ?? 'No Category'); ?></span>

            <h1><?php echo htmlspecialchars($course['c_name']); ?></h1>

            <p class="desc"><?php echo nl2br(htmlspecialchars($course['c_des'])); ?></p>

            <div class="info-grid">
                <div>
                    <strong>Price</strong>
                    <p>$<?php echo htmlspecialchars($course['c_price']); ?></p>
                </div>

                <div>
                    <strong>Instructor</strong>
                    <p><?php echo htmlspecialchars($teacher_name); ?></p>
                </div>

                <div>
                    <strong>Students</strong>
                    <p><?php echo $student_count; ?></p>
                </div>

                <div>
                    <strong>Created By</strong>
                    <p><?php echo htmlspecialchars($course['admin_name'] ?? 'Admin'); ?></p>
                </div>
            </div>

            <div class="details-actions">
                <a href="admin_courses_edit.php?id=<?php echo $course['c_id']; ?>" class="edit-btn">Edit Course</a>
                <a href="admin_courses_view.php" class="back-btn">Back</a>
            </div>
        </div>
    </div>

</main>

<?php include "footer.php"; ?>

</body>
</html>