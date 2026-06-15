<?php
include "db.php";

if (isset($_GET['id']) && isset($_GET['status'])) {
    $as_id = mysqli_real_escape_string($conn, $_GET['id']);
    $status = mysqli_real_escape_string($conn, $_GET['status']);

    if ($status == "Approved" || $status == "Rejected" || $status == "Pending") {
        $sql = "UPDATE assigned_course SET as_status='$status' WHERE as_id='$as_id'";
        mysqli_query($conn, $sql);
    }
}
?>