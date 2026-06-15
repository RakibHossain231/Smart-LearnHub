<?php
session_start();

if (!isset($_SESSION['u_id']) || $_SESSION['role_id'] !== 'Student') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$u_id = $_SESSION['u_id'];
$c_id = isset($_GET['c_id']) ? intval($_GET['c_id']) : 0;

if ($c_id <= 0) {
    header("Location: student_index.php");
    exit();
}

$student_q = $conn->query("SELECT s_id FROM student WHERE u_id = $u_id");

if (!$student_q || $student_q->num_rows == 0) {
    die("Student not found.");
}

$student = $student_q->fetch_assoc();
$s_id = $student['s_id'];

$check = $conn->query("SELECT * FROM enrollment WHERE s_id = $s_id AND c_id = $c_id");

if ($check && $check->num_rows == 0) {
    $conn->query("
        INSERT INTO enrollment (process, completed_at, c_id, s_id)
        VALUES (0, NOW(), $c_id, $s_id)
    ");
}

header("Location: student_home.php");
exit();
?>