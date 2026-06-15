<?php
include "db.php";

if (!isset($_GET['id'])) {
    header("Location: admin_courses.php");
    exit();
}

$c_id = mysqli_real_escape_string($conn, $_GET['id']);

/* Check enrollment */
$check_enroll = mysqli_query($conn, "SELECT * FROM enrollment WHERE c_id='$c_id'");

if (mysqli_num_rows($check_enroll) > 0) {
    echo "<script>
        alert('Cannot delete! This course has enrolled students.');
        window.location.href='admin_courses.php';
    </script>";
    exit();
}

/* Check assigned teacher */
$check_assign = mysqli_query($conn, "SELECT * FROM assigned_course WHERE c_id='$c_id'");

if (mysqli_num_rows($check_assign) > 0) {
    echo "<script>
        alert('Cannot delete! This course has assigned teacher records.');
        window.location.href='admin_courses.php';
    </script>";
    exit();
}

/* Delete course */
$delete = mysqli_query($conn, "DELETE FROM course WHERE c_id='$c_id'");

if ($delete) {
    header("Location: admin_courses.php");
    exit();
} else {
    echo "Delete failed: " . mysqli_error($conn);
}
?>