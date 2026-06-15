<?php
include 'db.php';

$id = $_GET['id'];

$conn->query("DELETE FROM lesson WHERE lesson_id = $id");

header("Location: teacher_view_lessons.php");
exit();