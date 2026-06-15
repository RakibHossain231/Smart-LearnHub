<?php
session_start();
include "db.php";

if (!isset($_GET['id'])) {
    header("Location: admin_courses.php");
    exit();
}

$c_id = mysqli_real_escape_string($conn, $_GET['id']);
$message = "";

/* course fetch */
$course_q = mysqli_query($conn, "
    SELECT course.*, categ.cat_name
    FROM course
    LEFT JOIN categ ON course.cat_id = categ.cat_id
    WHERE course.c_id='$c_id'
    LIMIT 1
");

$course = mysqli_fetch_assoc($course_q);

if (!$course) {
    die("Course not found");
}

/* teachers */
$teacher_result = mysqli_query($conn, "
    SELECT t_id, t_name, expert_at 
    FROM teacher 
    ORDER BY t_name ASC
");

/* current assigned teacher */
$assigned_q = mysqli_query($conn, "
    SELECT * FROM assigned_course
    WHERE c_id='$c_id'
    LIMIT 1
");

$assigned = mysqli_fetch_assoc($assigned_q);

/* update assign */
if (isset($_POST['assign_teacher'])) {

    $t_id = mysqli_real_escape_string($conn, $_POST['t_id']);
    $status = mysqli_real_escape_string($conn, $_POST['as_status']);

    if ($assigned) {
        $update = mysqli_query($conn, "
            UPDATE assigned_course
            SET t_id='$t_id', as_status='$status'
            WHERE c_id='$c_id'
        ");
    } else {
        $update = mysqli_query($conn, "
            INSERT INTO assigned_course (as_status, c_id, t_id)
            VALUES ('$status', '$c_id', '$t_id')
        ");
    }

    if ($update) {
        header("Location: admin_courses.php");
        exit();
    } else {
        $message = "Assign failed: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Teacher</title>
    <link rel="stylesheet" href="admin_course_assign_teacher.css?v=1">
</head>
<body class="assign-body">

<div class="assign-box">

    <h2>Assign Teacher</h2>

    <?php if ($message != "") { ?>
        <p class="message"><?php echo $message; ?></p>
    <?php } ?>

    <div class="course-info">
        <p class="label">Course</p>
        <h3><?php echo htmlspecialchars($course['c_name']); ?></h3>

        <div class="info-row">
            <span>Category</span>
            <strong><?php echo htmlspecialchars($course['cat_name'] ?? 'No Category'); ?></strong>
        </div>

        <div class="info-row">
            <span>Price</span>
            <strong><?php echo htmlspecialchars($course['c_price']); ?></strong>
        </div>

        <p class="desc">
            <?php echo htmlspecialchars($course['c_des']); ?>
        </p>
    </div>

    <form method="POST">

        <label>Teacher</label>
        <select name="t_id" required>
            <option value="">Select Teacher</option>

            <?php while ($teacher = mysqli_fetch_assoc($teacher_result)) { ?>
                <option value="<?php echo $teacher['t_id']; ?>"
                    <?php 
                        if ($assigned && $assigned['t_id'] == $teacher['t_id']) {
                            echo "selected";
                        }
                    ?>>
                    <?php echo htmlspecialchars($teacher['t_name']); ?>
                    <?php if (!empty($teacher['expert_at'])) { ?>
                        - <?php echo htmlspecialchars($teacher['expert_at']); ?>
                    <?php } ?>
                </option>
            <?php } ?>
        </select>

        <label>Status</label>
        <select name="as_status" required>
            <option value="Pending" <?php if ($assigned && $assigned['as_status'] == 'Pending') echo 'selected'; ?>>
                Pending
            </option>

            <option value="Approved" <?php if ($assigned && $assigned['as_status'] == 'Approved') echo 'selected'; ?>>
                Approved
            </option>

            <option value="Rejected" <?php if ($assigned && $assigned['as_status'] == 'Rejected') echo 'selected'; ?>>
                Rejected
            </option>
        </select>

        <div class="assign-btns">
            <button type="submit" name="assign_teacher">Update Assign</button>
            <a href="admin_courses.php">Cancel</a>
        </div>

    </form>

</div>

</body>
</html>