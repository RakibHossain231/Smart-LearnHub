<?php
session_start();
include "db.php";

/* Admin testing */
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 3;
}

$search = "";
$category_id = "";
$where = "WHERE 1";

if (isset($_GET['search']) && $_GET['search'] != "") {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND course.c_name LIKE '%$search%'";
}

if (isset($_GET['category']) && $_GET['category'] != "") {
    $category_id = mysqli_real_escape_string($conn, $_GET['category']);
    $where .= " AND course.cat_id = '$category_id'";
}

/* Categories */
$cat_result = mysqli_query($conn, "SELECT cat_id, cat_name FROM categ ORDER BY cat_name ASC");

/* Courses with dynamic stats */
$course_sql = "
SELECT 
    course.*,
    categ.cat_name,

    COUNT(DISTINCT enrollment.s_id) AS total_students,

    COUNT(DISTINCT CASE 
        WHEN lesson.les_type = 'VIDEO' THEN lesson.lesson_id 
    END) AS total_videos,

    COUNT(DISTINCT CASE 
        WHEN lesson.les_type = 'PDF' THEN lesson.lesson_id 
    END) AS total_pdfs,

    SEC_TO_TIME(SUM(
        CASE 
            WHEN lesson.les_type = 'VIDEO' THEN TIME_TO_SEC(lesson.duration)
            ELSE 0
        END
    )) AS total_video_duration

FROM course
LEFT JOIN categ ON course.cat_id = categ.cat_id
LEFT JOIN enrollment ON course.c_id = enrollment.c_id
LEFT JOIN lesson ON course.c_id = lesson.c_id

$where

GROUP BY course.c_id
ORDER BY course.c_id DESC
";

$course_result = mysqli_query($conn, $course_sql);

if (!$course_result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Courses - Smart LearnHub</title>

    <link rel="stylesheet" href="admin_courses_view.css?v=6">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>

<?php include "header_admin.php"; ?>

<main class="admin-course-main">

    <section class="course-hero">
        <div>
            <h1>All Courses</h1>
            <p>Manage, edit, and organize all courses</p>
        </div>

        <a href="create_course.php" class="create-btn">
            <i class="fa-solid fa-plus"></i> Create Course
        </a>
    </section>

    <section class="filter-box">

        <form method="GET" class="search-form">
            <div class="search-input">
                <i class="fa-solid fa-magnifying-glass"></i>

                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search courses..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                >

                <?php if ($category_id != "") { ?>
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_id); ?>">
                <?php } ?>

                <button type="submit">Search</button>
            </div>
        </form>

        <div class="category-tabs">

            <a href="admin_courses_view.php"
               class="<?php echo $category_id == '' ? 'active' : ''; ?>">
                All Categories
            </a>

            <?php while ($cat = mysqli_fetch_assoc($cat_result)) { ?>
                <a href="admin_courses_view.php?category=<?php echo $cat['cat_id']; ?>"
                   class="<?php echo $category_id == $cat['cat_id'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat['cat_name']); ?>
                </a>
            <?php } ?>

        </div>

    </section>

    <section class="course-grid">

        <?php if (mysqli_num_rows($course_result) > 0) { ?>

            <?php while ($course = mysqli_fetch_assoc($course_result)) { ?>

                <?php
                    $image = !empty($course['c_image']) ? $course['c_image'] : "Images/default-course.jpg";

                    if (strtolower($course['c_price']) == "free" || $course['c_price'] == "0") {
                        $price = "Free";
                    } else {
                        $price = "$" . $course['c_price'];
                    }

                    $total_students = $course['total_students'] ?? 0;
                    $total_videos = $course['total_videos'] ?? 0;
                    $total_pdfs = $course['total_pdfs'] ?? 0;

                    $total_duration = $course['total_video_duration'] ?? '00:00:00';
                    $time_parts = explode(':', $total_duration);

                    $hours = isset($time_parts[0]) ? (int)$time_parts[0] : 0;
                    $minutes = isset($time_parts[1]) ? (int)$time_parts[1] : 0;

                    if ($hours > 0) {
                        $duration_text = $hours . "h " . $minutes . "m";
                    } else {
                        $duration_text = $minutes . "m";
                    }
                ?>

                <div class="course-card">

                    <div class="course-img">
                        <img src="<?php echo htmlspecialchars($image); ?>" alt="Course Image">
                    </div>

                    <div class="course-content">

                        <div class="course-title-row">
                            <h3><?php echo htmlspecialchars($course['c_name']); ?></h3>

                            <span>
                                <?php echo htmlspecialchars($course['cat_name'] ?? 'No Category'); ?>
                            </span>
                        </div>

                        <p class="course-desc">
                            <?php echo htmlspecialchars(substr($course['c_des'], 0, 120)); ?>...
                        </p>

                        <div class="course-meta">
                            <span title="Rating">
                                <i class="fa-solid fa-star"></i> 4.8
                            </span>

                            <span title="Students">
                                <i class="fa-solid fa-users"></i> <?php echo $total_students; ?>
                            </span>

                            <span title="Videos">
                                <i class="fa-solid fa-video"></i> <?php echo $total_videos; ?>
                            </span>

                            <span title="Video Duration">
                                <i class="fa-regular fa-clock"></i> <?php echo $duration_text; ?>
                            </span>

                            <span title="PDF Lessons">
                                <i class="fa-regular fa-file-pdf"></i> <?php echo $total_pdfs; ?>
                            </span>
                        </div>

                        <div class="course-bottom">
                            <h2><?php echo htmlspecialchars($price); ?></h2>

                            <div class="admin-actions">
                                <a href="admin_course_details.php?id=<?php echo $course['c_id']; ?>" class="view-btn">
                                    View
                                </a>

                                <a href="admin_courses_edit.php?id=<?php echo $course['c_id']; ?>" class="edit-btn">
                                    Edit
                                </a>

                                <a href="admin_course_delete.php?id=<?php echo $course['c_id']; ?>"
                                   class="delete-btn"
                                   onclick="return confirm('Are you sure you want to delete this course?')">
                                    Delete
                                </a>
                            </div>
                        </div>

                    </div>

                </div>

            <?php } ?>

        <?php } else { ?>

            <p class="no-course">No courses found.</p>

        <?php } ?>

    </section>

</main>

<?php include "footer.php"; ?>

</body>
</html>