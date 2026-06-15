<?php
session_start();
include "db.php";

$current_status = "";
$where_status = "";
if (isset($_GET['status']) && $_GET['status'] != "") {
    $current_status = mysqli_real_escape_string($conn, $_GET['status']);
    $where_status = "WHERE COALESCE(cert.status, 'Pending') = '$current_status'";
}

$total = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(DISTINCT e.en_id) AS total 
    FROM enrollment e
    INNER JOIN grade g ON e.en_id = g.en_id
    WHERE g.final_grade IS NOT NULL AND g.final_grade != ''
"))['total'];

$pending = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM enrollment e
    LEFT JOIN certificate cert ON e.s_id = cert.s_id AND e.c_id = cert.c_id
    WHERE cert.status IS NULL OR cert.status='Pending'
"))['total'];

$sent = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM certificate WHERE status='Sent'
"))['total'];

$generated = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM certificate WHERE status='Generated'
"))['total'];

$sql = "
SELECT 
    e.en_id,
    e.s_id,
    e.c_id,
    e.completed_at,

    s.s_name,
    u.email,
    c.c_name,

    g.final_grade,

    cert.cer_id,
    cert.cer_type,
    cert.status,
    cert.issuded_date

FROM enrollment e
LEFT JOIN student s ON e.s_id = s.s_id
LEFT JOIN user u ON s.u_id = u.u_id
LEFT JOIN course c ON e.c_id = c.c_id
LEFT JOIN grade g ON e.en_id = g.en_id
LEFT JOIN certificate cert ON e.s_id = cert.s_id AND e.c_id = cert.c_id

$where_status

ORDER BY e.en_id DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate Management</title>

    <link rel="stylesheet" href="category_management.css?v=10">
    <link rel="stylesheet" href="certificate_management.css?v=31">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include "header_admin.php"; ?>

<main class="main-section">

    <div class="top-area">
        <div>
            <h1>Certificate Management</h1>
            <p>Generate and manage course completion certificates</p>
        </div>
    </div>

    <div class="certificate-stats">

        <div class="cert-card">
            <div class="cert-head">
                <p>Total Certificates</p>
                <i class="fa-solid fa-award stat-icon"></i>
            </div>
            <h2><?php echo $total; ?></h2>
            <p>All certificates</p>
        </div>

        <div class="cert-card">
            <div class="cert-head">
                <p>Pending</p>
                <i class="fa-solid fa-award stat-icon"></i>
            </div>
            <h2><?php echo $pending; ?></h2>
            <p>To be generated</p>
        </div>

        <div class="cert-card">
            <div class="cert-head">
                <p>Generated</p>
                <i class="fa-solid fa-award stat-icon"></i>
            </div>
            <h2><?php echo $generated; ?></h2>
            <p>Ready to send</p>
        </div>

        <div class="cert-card">
            <div class="cert-head">
                <p>Sent</p>
                <i class="fa-solid fa-award stat-icon"></i>
            </div>
            <h2><?php echo $sent; ?></h2>
            <p>Delivered to students</p>
        </div>

    </div>

    <div class="tabs-row">
        <div class="left-tabs">
            <a href="admin_courses.php">All Courses</a>
            <a href="courses_assign.php">Assign Courses</a>
            <a href="category_management.php">Categories</a>
            <a href="certificate_management.php" class="active">Certificates</a>
        </div>

        <div class="right-tabs">
            <a href="certificate_management.php" class="<?php echo $current_status == '' ? 'active-small' : ''; ?>">All Certificates</a>
            <a href="certificate_management.php?status=Pending" class="<?php echo $current_status == 'Pending' ? 'active-small' : ''; ?>">Pending</a>
            <a href="certificate_management.php?status=Sent" class="<?php echo $current_status == 'Sent' ? 'active-small' : ''; ?>">Sent</a>
        </div>
    </div>

    <section class="table-box">
        <h3>All Certificates</h3>

        <table>
            <thead>
                <tr>
                    <th>Certificate ID</th>
                    <th>Student Name</th>
                    <th>Course</th>
                    <th>Completion Date</th>
                    <th>Grade</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <?php
                        $status = $row['status'] ?? 'Pending';
                        $grade = $row['final_grade'] ?? 'N/A';

                        if (!empty($row['issuded_date']) && $row['issuded_date'] != "0000-00-00 00:00:00") {
                            $date = date("M d, Y", strtotime($row['issuded_date']));
                        } else {
                            $date = "Not issued";
                        }
                        $current_year = date('Y');
                        $cert_code = !empty($row['cer_id'])
                            ? "CERT-" . $current_year . "-" . str_pad($row['cer_id'], 3, "0", STR_PAD_LEFT)
                            : "Not generated";
                    ?>

                    <tr>
                        <td><?php echo $cert_code; ?></td>

                        <td>
                            <strong><?php echo htmlspecialchars($row['s_name'] ?? 'Unknown'); ?></strong><br>
                            <span class="student-email"><?php echo htmlspecialchars($row['email'] ?? 'No email'); ?></span>
                        </td>

                        <td><?php echo htmlspecialchars($row['c_name'] ?? 'Unknown Course'); ?></td>

                        <td><?php echo $date; ?></td>

                        <td>
                            <span class="grade-badge">
                                <?php echo htmlspecialchars($grade); ?>
                            </span>
                        </td>

                        <td>
                            <span class="cert-status <?php echo strtolower($status); ?>">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </td>

                        <td class="cert-actions">
                            <?php if ($status != 'Sent') { ?>
                                <a href="certificate_send.php?en_id=<?php echo $row['en_id']; ?>">
                                    <i class="fa-regular fa-paper-plane"></i>
                                </a>
                            <?php } ?>

                            <a href="certificate_send.php?en_id=<?php echo $row['en_id']; ?>&download=1" target="_blank">
                                <i class="fa-solid fa-download"></i>
                            </a>
                        </td>
                    </tr>

                <?php } ?>
            </tbody>
        </table>
    </section>

</main>

<?php include "footer.php"; ?>

</body>
</html>