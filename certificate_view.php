<?php
session_start();
include "db.php";

if (!isset($_GET['s_id']) || !isset($_GET['c_id'])) {
    die("Invalid certificate");
}

$s_id = mysqli_real_escape_string($conn, $_GET['s_id']);
$c_id = mysqli_real_escape_string($conn, $_GET['c_id']);

/* Admin full name */
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 3;
}

$admin_name = "Admin";

$admin_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);
$admin_q = mysqli_query($conn, "SELECT full_name FROM user WHERE u_id='$admin_id' LIMIT 1");

if ($admin_q && mysqli_num_rows($admin_q) > 0) {
    $admin = mysqli_fetch_assoc($admin_q);
    $admin_name = $admin['full_name'];
}

/* Certificate data with grade */
$sql = "
SELECT 
    certificate.cer_id,
    certificate.cer_type,
    certificate.status,
    certificate.issuded_date,
    certificate.s_id,
    certificate.t_id,
    certificate.c_id,
    certificate.grade,

    student.s_name,
    course.c_name,
    teacher.t_name

FROM certificate
LEFT JOIN student ON certificate.s_id = student.s_id
LEFT JOIN course ON certificate.c_id = course.c_id
LEFT JOIN teacher ON certificate.t_id = teacher.t_id

WHERE certificate.s_id = '$s_id'
AND certificate.c_id = '$c_id'
LIMIT 1
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$cert = mysqli_fetch_assoc($result);

if (!$cert) {
    die("Certificate not generated yet.");
}

$issuedDate = "Not issued";

if (!empty($cert['issuded_date']) && $cert['issuded_date'] != "0000-00-00 00:00:00") {
    $issuedDate = date("F d, Y", strtotime($cert['issuded_date']));
}

$certCode = "Not Generated";
if (!empty($cert['cer_id']) && $cert['cer_id'] > 0) {
    $certCode = "CERT-2026-" . str_pad($cert['cer_id'], 3, "0", STR_PAD_LEFT);
}

// Grade value
$gradeValue = !empty($cert['grade']) ? $cert['grade'] : $cert['cer_type'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Certificate - Smart-LearnHub</title>
    <link rel="stylesheet" href="certificate_view.css?v=50">
</head>
<body>

<div class="print-btn">
    <button onclick="window.print()">Download / Print PDF</button>
</div>

<div class="certificate">

    <div class="gold-dots"></div>
    <div class="corner top-left"></div>
    <div class="corner bottom-right"></div>

    <div class="seal">
        <div>
            SMART<br>LEARNHUB
            <span>★★★★★</span>
        </div>
    </div>

    <div class="brand">
        <div class="logo">🎓</div>
        <h2>SMART-LEARNHUB</h2>
        <p>Learn Smart, Achieve More</p>
    </div>

    <h1>CERTIFICATE</h1>
    <h3>OF COMPLETION</h3>

    <p class="certify">This is to certify that</p>

    <h2 class="student-name">
        <?php echo htmlspecialchars($cert['s_name']); ?>
    </h2>

    <p class="completed">has successfully completed the course</p>

    <h2 class="course-name">
        <?php echo htmlspecialchars($cert['c_name']); ?>
    </h2>

    <p class="statement">
        and has demonstrated dedication, discipline and excellence throughout the course.
    </p>

    <div class="details">
        <div>
            <span>INSTRUCTOR</span>
            <strong><?php echo htmlspecialchars($cert['t_name'] ?? 'N/A'); ?></strong>
        </div>

        <div>
            <span>ISSUED DATE</span>
            <strong><?php echo $issuedDate; ?></strong>
        </div>

        <div>
            <span>CERTIFICATE ID</span>
            <strong><?php echo $certCode; ?></strong>
        </div>

        <div>
            <span>COURSE GRADE</span>
            <strong><?php echo htmlspecialchars($gradeValue); ?></strong>
        </div>
    </div>

    <div class="signature-area">
        <div class="signature">
            <?php echo htmlspecialchars($admin_name); ?>
        </div>
        <div class="signature-line"></div>
        <div class="signature-label">Authorized Signature</div>
        <div class="signature-company">Smart-LearnHub</div>
    </div>

</div>

</body>
</html>