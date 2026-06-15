<?php
session_start();
include "db.php";

/* Stats */
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM assigned_course WHERE as_status='Pending'"))['total'];
$approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM assigned_course WHERE as_status='Approved'"))['total'];
$rejected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM assigned_course WHERE as_status='Rejected'"))['total'];
$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM assigned_course"))['total'];

/* Filter */
$where = "";
$current_status = "";

if (isset($_GET['status']) && $_GET['status'] != "") {
    $current_status = mysqli_real_escape_string($conn, $_GET['status']);
    $where = "WHERE assigned_course.as_status = '$current_status'";
}

/* Main data */
$assign_sql = "
SELECT 
    assigned_course.as_id,
    assigned_course.as_status,
    teacher.t_name,
    teacher.experince,
    teacher.expert_at,
    user.email,
    course.c_name
FROM assigned_course
INNER JOIN teacher ON assigned_course.t_id = teacher.t_id
INNER JOIN user ON teacher.u_id = user.u_id
INNER JOIN course ON assigned_course.c_id = course.c_id
$where
ORDER BY assigned_course.as_id DESC
";

$assign_result = mysqli_query($conn, $assign_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Courses</title>

    <link rel="stylesheet" href="category_management.css?v=100">
    <link rel="stylesheet" href="courses_assign.css?v=101">
    <link rel="stylesheet" href="courses_assign_popup.css?v=100">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include "header_admin.php"; ?>

<main class="main-section">

    <div class="top-area">
        <div>
            <h1>Instructor Approvals</h1>
            <p>Review and approve instructor applications</p>
        </div>
    </div>

    <div class="approval-stats">
        <div class="approval-card">
            <div class="approval-head">
                <p>Pending Review</p>
                <span class="stat-icon"><i class="fa-regular fa-clock"></i></span>
            </div>
            <h2><?php echo $pending; ?></h2>
            <p>Awaiting approval</p>
        </div>

        <div class="approval-card">
            <div class="approval-head">
                <p>Approved</p>
                <span class="stat-icon"><i class="fa-regular fa-circle-check"></i></span>
            </div>
            <h2><?php echo $approved; ?></h2>
            <p>Active instructors</p>
        </div>

        <div class="approval-card">
            <div class="approval-head">
                <p>Rejected</p>
                <span class="stat-icon"><i class="fa-solid fa-xmark"></i></span>
            </div>
            <h2><?php echo $rejected; ?></h2>
            <p>Not approved</p>
        </div>

        <div class="approval-card">
            <div class="approval-head">
                <p>Total Applications</p>
                <span class="stat-icon"><i class="fa-regular fa-user"></i></span>
            </div>
            <h2><?php echo $total; ?></h2>
            <p>Total applications</p>
        </div>

    </div>

    <div class="tabs-row">
        <div class="left-tabs">
            <a href="admin_courses.php">All Courses</a>
            <a href="courses_assign.php" class="active">Assign Courses</a>
            <a href="category_management.php">Categories</a>
            <a href="certificate_management.php">Certificates</a>
        </div>

        <div class="right-tabs">
            <a href="courses_assign.php"
               class="<?php echo $current_status == '' ? 'active-small' : ''; ?>">
                All Instructors
            </a>

            <a href="courses_assign.php?status=Pending"
               class="<?php echo $current_status == 'Pending' ? 'active-small' : ''; ?>">
                Pending
            </a>

            <a href="courses_assign.php?status=Approved"
               class="<?php echo $current_status == 'Approved' ? 'active-small' : ''; ?>">
                Approved
            </a>

            <a href="courses_assign.php?status=Rejected"
               class="<?php echo $current_status == 'Rejected' ? 'active-small' : ''; ?>">
                Rejected
            </a>
        </div>
    </div>

    <section class="table-box">
        <h3>Instructor Applications</h3>

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Expertise</th>
                    <th>Experience</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($row = mysqli_fetch_assoc($assign_result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['t_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>

                        <td>
                            <span class="expertise-badge">
                                <?php echo htmlspecialchars($row['expert_at']); ?>
                            </span>
                        </td>

                        <td><?php echo htmlspecialchars($row['experince']); ?> years</td>

                        <td><?php echo htmlspecialchars($row['c_name']); ?></td>

                        <td>
                            <span class="status-badge <?php echo strtolower($row['as_status']); ?>">
                                <?php echo htmlspecialchars($row['as_status']); ?>
                            </span>
                        </td>

                        <td class="actions">
                            <a href="#">👁</a>

                            <?php if ($row['as_status'] == 'Pending') { ?>
                                <button class="approve-btn" onclick="approveAssign(<?php echo $row['as_id']; ?>)">✓</button>
                                <button class="reject-btn" onclick="rejectAssign(<?php echo $row['as_id']; ?>)">×</button>
                            <?php } else { ?>
                                <a class="edit-btn" href="course_assign_edit.php?id=<?php echo $row['as_id']; ?>">✎</a>
                            <?php } ?>

                            <button class="delete-btn" onclick="deleteAssign(<?php echo $row['as_id']; ?>)">🗑</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </section>

</main>

<?php include "footer.php"; ?>
<?php include "courses_assign_popup.php"; ?>

<script>
function deleteAssign(id) {
    if (confirm("Are you sure you want to delete this assignment?")) {
        window.location.href = "courses_assign_delete.php?id=" + id;
    }
}
</script>

</body>
</html>