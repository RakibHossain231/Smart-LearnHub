<?php
session_start();
include "db.php";

/* Dynamic stats */
$total_courses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM course"))['total'];

$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM student"))['total'];

$total_certificates = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM certificate WHERE status='Sent'"))['total'];

/* Featured courses */
$course_sql = "
SELECT 
    course.c_id,
    course.c_name,
    course.c_des,
    course.c_price,
    course.c_image,
    categ.cat_name,

    COUNT(DISTINCT enrollment.s_id) AS total_students,

    COUNT(DISTINCT CASE 
        WHEN lesson.les_type = 'VIDEO' THEN lesson.lesson_id 
    END) AS total_videos,

    SEC_TO_TIME(SUM(
        CASE 
            WHEN lesson.les_type = 'VIDEO' THEN TIME_TO_SEC(lesson.duration)
            ELSE 0
        END
    )) AS total_duration

FROM course

LEFT JOIN categ ON course.cat_id = categ.cat_id
LEFT JOIN enrollment ON course.c_id = enrollment.c_id
LEFT JOIN lesson ON course.c_id = lesson.c_id

GROUP BY course.c_id
ORDER BY course.c_id DESC
LIMIT 3
";

$course_result = mysqli_query($conn, $course_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart-LearnHub</title>
    <link rel="stylesheet" href="admin_home.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>

<?php include "header_admin.php"; ?>

<section class="hero-section">
    <div class="hero-content">
        <h1>Learn Without Limits</h1>
        <p>
            Access thousands of courses from world-class instructors. 
            Start learning today and advance your career.
        </p>

        <div class="hero-buttons">
            <a href="admin_courses_view.php" class="primary-btn">Explore Courses</a>
            <a href="register.php" class="secondary-btn">Get Started Free</a>
        </div>
    </div>
</section>

<section class="stats-section">

    <div class="stat-box">
        <div class="stat-icon blue">
            <i class="fa-regular fa-book-open"></i>
        </div>
        <h2><?php echo $total_courses; ?>+</h2>
        <p>Courses Available</p>
    </div>

    <div class="stat-box">
        <div class="stat-icon purple">
            <i class="fa-solid fa-users"></i>
        </div>
        <h2><?php echo $total_students; ?>+</h2>
        <p>Active Students</p>
    </div>

    <div class="stat-box">
        <div class="stat-icon green">
            <i class="fa-solid fa-award"></i>
        </div>
        <h2><?php echo $total_certificates; ?>+</h2>
        <p>Certificates Issued</p>
    </div>

    <div class="stat-box">
        <div class="stat-icon orange">
            <i class="fa-solid fa-arrow-trend-up"></i>
        </div>
        <h2>95%</h2>
        <p>Success Rate</p>
    </div>

</section>

<section class="featured-section">

    <div class="section-head">
        <div>
            <h2>Featured Courses</h2>
            <p>Most popular courses chosen by our students</p>
        </div>

        <a href="admin_courses_view.php" class="view-all-btn">View All</a>
    </div>

    <div class="course-grid">

        <?php while ($course = mysqli_fetch_assoc($course_result)) { ?>

            <?php
                $image = !empty($course['c_image']) ? $course['c_image'] : "Images/default-course.jpg";

                if (strtolower($course['c_price']) == "free" || $course['c_price'] == "0") {
                    $price = "Free";
                } else {
                    $price = "$" . $course['c_price'];
                }

                $total_students_course = $course['total_students'] ?? 0;

                $duration = $course['total_duration'] ?? "00:00:00";
                $parts = explode(":", $duration);

                $hours = isset($parts[0]) ? (int)$parts[0] : 0;
                $minutes = isset($parts[1]) ? (int)$parts[1] : 0;

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

                <div class="course-body">
                    <div class="course-title-row">
                        <h3><?php echo htmlspecialchars($course['c_name']); ?></h3>
                        <span><?php echo htmlspecialchars($course['cat_name'] ?? 'No Category'); ?></span>
                    </div>

                    <p class="course-desc">
                        <?php echo htmlspecialchars(substr($course['c_des'], 0, 115)); ?>...
                    </p>

                    <div class="course-meta">
                        <span><i class="fa-solid fa-star"></i> 4.8</span>
                        <span><i class="fa-solid fa-users"></i> <?php echo $total_students_course; ?></span>
                        <span><i class="fa-regular fa-clock"></i> <?php echo $duration_text; ?></span>
                    </div>

                    <div class="course-bottom">
                        <h2><?php echo htmlspecialchars($price); ?></h2>
                        <a href="admin_course_details.php?id=<?php echo $course['c_id']; ?>">View Course</a>
                    </div>
                </div>
            </div>

        <?php } ?>

    </div>

</section>

<section class="why-section">

    <h2>Why Choose LearnHub?</h2>

    <div class="why-grid">

        <div class="why-card">
            <div class="why-icon blue">
                <i class="fa-regular fa-book-open"></i>
            </div>
            <h3>Expert Instructors</h3>
            <p>Learn from industry professionals with years of real-world experience.</p>
        </div>

        <div class="why-card">
            <div class="why-icon purple">
                <i class="fa-solid fa-users"></i>
            </div>
            <h3>Learn at Your Pace</h3>
            <p>Access courses anytime, anywhere. Learn on your schedule, at your own pace.</p>
        </div>

        <div class="why-card">
            <div class="why-icon green">
                <i class="fa-solid fa-award"></i>
            </div>
            <h3>Get Certified</h3>
            <p>Earn certificates upon course completion to showcase your achievements.</p>
        </div>

    </div>

</section>

<?php include "footer.php"; ?>

</body>
</html>