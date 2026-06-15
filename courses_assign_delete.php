<?php
include "db.php";

if (isset($_GET['id'])) {
    $as_id = mysqli_real_escape_string($conn, $_GET['id']);

    $sql = "DELETE FROM assigned_course WHERE as_id='$as_id'";

    if (mysqli_query($conn, $sql)) {
        header("Location: courses_assign.php");
        exit();
    } else {
        echo "Delete failed: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request";
}
?>