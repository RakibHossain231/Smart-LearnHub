

<?php
session_start();
include "db.php";

$total_cat_sql = "SELECT COUNT(*) AS total_categories FROM categ";
$total_cat_result = mysqli_query($conn, $total_cat_sql);
$total_cat = mysqli_fetch_assoc($total_cat_result)['total_categories'];

$total_course_sql = "SELECT COUNT(*) AS total_courses FROM course";
$total_course_result = mysqli_query($conn, $total_course_sql);
$total_course = mysqli_fetch_assoc($total_course_result)['total_courses'];

$average = ($total_cat > 0) ? round($total_course / $total_cat) : 0;

$cat_sql = "
    SELECT 
        categ.cat_id,
        categ.cat_name,
        categ.cat_des,
        COUNT(course.c_id) AS total_course
    FROM categ
    LEFT JOIN course ON categ.cat_id = course.cat_id
    GROUP BY categ.cat_id
    ORDER BY categ.cat_id ASC
";

$cat_result = mysqli_query($conn, $cat_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Category Management - LearnHub</title>
    <link rel="stylesheet" href="./category_management.css?v=11">
</head>
<body>
    
<script src="https://unpkg.com/lucide@latest"></script>
<?php include "header_admin.php"; ?>

<main class="main-section">

    <div class="top-area">
        <div>
            <h1>Category Management</h1>
            <p>Organize courses by creating and managing categories</p>
        </div>

        <a href="create_category.php" class="add-btn">＋ Add Category</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-head">
                <p>Categories</p>
                <span class="stat-icon">
                    <i data-lucide="layers"></i>
                </span>
            </div>
            <h2><?php echo $total_cat; ?></h2>
            <p>Total categories</p>
        </div>

        <div class="stat-card">
            <div class="stat-head">
                <p>Total Courses</p>
                <span class="stat-icon">
                    <i data-lucide="book-open"></i>
                </span>
            </div>
            <h2><?php echo $total_course; ?></h2>
            <p>Across all categories</p>
        </div>

        <div class="stat-card">
            <div class="stat-head">
                <p>Average</p>
                <span class="stat-icon">
                    <i data-lucide="bar-chart-3"></i>
                </span>
            </div>
            <h2><?php echo $average; ?></h2>
            <p>Courses per category</p>
        </div>
    </div>

    <div class="tabs-row">
        <div class="left-tabs">
            <a href="admin_courses.php">All Courses</a>
            <a href="courses_assign.php">Assign Courses</a>
            <a href="#" class="active">Categories</a>
            <a href="certificate_management.php">Certificates</a>
        </div>

        <!-- <div class="right-tabs">
            <a href="#" class="active-small">All Categories</a>
            <a href="#">Active</a>
            <a href="#">Archived</a>
        </div> -->
    </div>

    <section class="table-box">
        <h3>All Categories</h3>

        <table>
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th>Courses</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($cat = mysqli_fetch_assoc($cat_result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cat['cat_name']); ?></td>
                        <td><?php echo htmlspecialchars($cat['cat_des']); ?></td>
                        <td><strong><?php echo $cat['total_course']; ?></strong></td>
                        <td class="actions">
                            <a href="category_edit.php?id=<?php echo $cat['cat_id']; ?>">✎</a>
                            <a href="category_delete.php?id=<?php echo $cat['cat_id']; ?>" onclick="return confirm('Are you sure you want to delete this category?')">🗑</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </section>
    <script>
    lucide.createIcons();
</script>

</main>

<?php include "footer.php"; ?>

</body>
</html>