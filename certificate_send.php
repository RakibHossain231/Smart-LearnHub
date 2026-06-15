<?php
session_start();
include "db.php";

if (!isset($_GET['en_id'])) {
    header("Location: certificate_management.php");
    exit();
}

$en_id = mysqli_real_escape_string($conn, $_GET['en_id']);

/* 1. Enrollment fetch */
$en_q = mysqli_query($conn, "
    SELECT * 
    FROM enrollment 
    WHERE en_id='$en_id' 
    LIMIT 1
");

if (!$en_q || mysqli_num_rows($en_q) == 0) {
    echo "<script>
        alert('Enrollment not found.');
        window.location.href='certificate_management.php';
    </script>";
    exit();
}

$en = mysqli_fetch_assoc($en_q);

$s_id = $en['s_id'];
$c_id = $en['c_id'];

/* 2. Grade check by en_id */
$grade_q = mysqli_query($conn, "
    SELECT final_grade 
    FROM grade 
    WHERE en_id='$en_id'
    LIMIT 1
");

if (!$grade_q || mysqli_num_rows($grade_q) == 0) {
    echo "<script>
        alert('Grade not found. Cannot generate certificate.');
        window.location.href='certificate_management.php';
    </script>";
    exit();
}

$grade = mysqli_fetch_assoc($grade_q);
$final_grade = mysqli_real_escape_string($conn, $grade['final_grade']);

/* 3. Teacher check */
$teacher_q = mysqli_query($conn, "
    SELECT t_id 
    FROM assigned_course 
    WHERE c_id='$c_id'
    LIMIT 1
");

if (!$teacher_q || mysqli_num_rows($teacher_q) == 0) {
    echo "<script>
        alert('Teacher not assigned for this course. Cannot generate certificate.');
        window.location.href='certificate_management.php';
    </script>";
    exit();
}

$teacher = mysqli_fetch_assoc($teacher_q);
$teacher_id = mysqli_real_escape_string($conn, $teacher['t_id']);

/* 4. Issue date */
if (!empty($en['completed_at']) && $en['completed_at'] != "0000-00-00") {
    $completed_at = mysqli_real_escape_string($conn, $en['completed_at']);
    $issue_date = "'$completed_at'";
} else {
    $issue_date = "NOW()";
}

/* 5. Certificate exists check */
$cert_q = mysqli_query($conn, "
    SELECT cer_id 
    FROM certificate 
    WHERE s_id='$s_id' AND c_id='$c_id'
    LIMIT 1
");

if ($cert_q && mysqli_num_rows($cert_q) > 0) {

    $cert = mysqli_fetch_assoc($cert_q);
    $cer_id = $cert['cer_id'];

    $update = mysqli_query($conn, "
        UPDATE certificate
        SET 
            cer_type = 'Course Completion',
            grade = '$final_grade',
            status = 'Sent',
            issuded_date = $issue_date,
            t_id = '$teacher_id'
        WHERE cer_id = '$cer_id'
    ");

    if (!$update) {
        die("Certificate update failed: " . mysqli_error($conn));
    }

} else {

    $insert = mysqli_query($conn, "
        INSERT INTO certificate 
        (cer_type, grade, c_id, issuded_date, status, s_id, t_id)
        VALUES 
        ('Course Completion', '$final_grade', '$c_id', $issue_date, 'Sent', '$s_id', '$teacher_id')
    ");


    if (!$insert) {
        die("Certificate insert failed: " . mysqli_error($conn));
    }

    $cer_id = mysqli_insert_id($conn);
}

/* 6. Download/view or back */
if (isset($_GET['download'])) {
    if (!empty($s_id) && !empty($c_id)) {
        header("Location: certificate_view.php?s_id=" . $s_id . "&c_id=" . $c_id);
        exit();
    } else {
        echo "<script>
            alert('Certificate generated but student or course ID missing.');
            window.location.href='certificate_management.php';
        </script>";
        exit();
    }
}

header("Location: certificate_management.php");
exit();
?>