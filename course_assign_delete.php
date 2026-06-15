<?php
include "db.php";

if (isset($_GET['id'])) {
    $as_id = mysqli_real_escape_string($conn, $_GET['id']);

    $sql = "DELETE FROM assigned_course WHERE as_id='$as_id'";
    mysqli_query($conn, $sql);
}

header("Location: course_assign.php");
exit();
?>