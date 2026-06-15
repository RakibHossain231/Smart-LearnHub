<?php
session_start();
include 'db.php';

if (!isset($_SESSION['u_id'])) {
    header("Location: login.php");
    exit();
}

$id = intval($_GET['id']);
$c_id = isset($_GET['c_id']) ? intval($_GET['c_id']) : 0;

/* publish quiz */
$stmt = $conn->prepare("UPDATE test SET is_published = 1 WHERE test_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

/* redirect back to SAME course page */
if ($c_id > 0) {
    header("Location: teacher_quizzes.php?c_id=" . $c_id);
} else {
    header("Location: teacher_quizzes.php");
}

exit();
?>