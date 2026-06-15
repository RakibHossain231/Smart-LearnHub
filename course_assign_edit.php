<?php
session_start();
include "db.php";

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$as_id = mysqli_real_escape_string($conn, $_GET['id']);

$assign_sql = "SELECT * FROM assigned_course WHERE as_id='$as_id'";
$assign_result = mysqli_query($conn, $assign_sql);
$assign = mysqli_fetch_assoc($assign_result);

if (!$assign) {
    die("Assign data not found");
}

$course_result = mysqli_query($conn, "SELECT c_id, c_name FROM course ORDER BY c_name ASC");
$teacher_result = mysqli_query($conn, "SELECT t_id, t_name FROM teacher ORDER BY t_name ASC");

if (isset($_POST['update_assign'])) {
    $c_id = mysqli_real_escape_string($conn, $_POST['c_id']);
    $t_id = mysqli_real_escape_string($conn, $_POST['t_id']);
    $as_status = mysqli_real_escape_string($conn, $_POST['as_status']);

    $update_sql = "UPDATE assigned_course 
                   SET c_id='$c_id', t_id='$t_id', as_status='$as_status'
                   WHERE as_id='$as_id'";

    if (mysqli_query($conn, $update_sql)) {
        header("Location: courses_assign.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Assigned Course</title>
    <link rel="stylesheet" href="course_assign_edit.css?v=1">
</head>
<body class="assign-edit-body">

<div class="assign-edit-box">
    <h2>Edit Assigned Course</h2>

    <form method="POST">

        <label>Course</label>
        <select name="c_id" required>
            <?php while ($course = mysqli_fetch_assoc($course_result)) { ?>
                <option value="<?php echo $course['c_id']; ?>"
                    <?php if ($course['c_id'] == $assign['c_id']) echo "selected"; ?>>
                    <?php echo htmlspecialchars($course['c_name']); ?>
                </option>
            <?php } ?>
        </select>

        <label>Teacher</label>
        <select name="t_id" required>
            <?php while ($teacher = mysqli_fetch_assoc($teacher_result)) { ?>
                <option value="<?php echo $teacher['t_id']; ?>"
                    <?php if ($teacher['t_id'] == $assign['t_id']) echo "selected"; ?>>
                    <?php echo htmlspecialchars($teacher['t_name']); ?>
                </option>
            <?php } ?>
        </select>

        <label>Status</label>
        <select name="as_status" required>
            <option value="Pending" <?php if ($assign['as_status'] == 'Pending') echo "selected"; ?>>Pending</option>
            <option value="Approved" <?php if ($assign['as_status'] == 'Approved') echo "selected"; ?>>Approved</option>
            <option value="Rejected" <?php if ($assign['as_status'] == 'Rejected') echo "selected"; ?>>Rejected</option>
        </select>

        <div class="assign-edit-btns">
            <button type="submit" name="update_assign">Update Assign</button>
            <a href="courses_assign.php">Cancel</a>
        </div>

    </form>
</div>

</body>
</html>