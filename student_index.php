
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['u_id']) || $_SESSION['role_id'] !== 'Student') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$user_id = $_SESSION['u_id'];
$user_name = $_SESSION['user_name'];
$full_name = $_SESSION['full_name'];
$email = $_SESSION['email'];

// fetch the courses
$courses = $conn->query("
    SELECT course.*, categ.cat_name
    FROM course
    JOIN categ ON course.cat_id = categ.cat_id
    ORDER BY course.c_id DESC
");

// count total course
$total_courses_query = $conn->query("
    SELECT COUNT(*) AS total 
    FROM course
");
$total_courses = $total_courses_query->fetch_assoc()['total'];

// count toal studnet
$total_students_query = $conn->query("
    SELECT COUNT(*) AS total 
    FROM student
");
$total_students = $total_students_query->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'student_navbar.php'; ?>

<section class="hero">
    <div class="hero-content">
        <div class="hero-badge">Modern Online Learning Platform</div>
        <h1>Learn Without Limits</h1>
        <p>
            Access courses from expert instructors. Build your skills,
            complete lessons, attempt quizzes, and grow your future with LMS.
        </p>

        

        <div class="hero-buttons">
            <a href="all_courses.php" class="btn-light">Explore Courses</a>
            <a href="student_home.php" class="btn-transparent">My Dashboard</a>
        </div>
    </div>
</section>

<section class="stats-section">
    <div class="stat-card">
        <div class="stat-icon blue">📘</div>
        <h3><?php echo $total_courses; ?>+</h3>
        <p>Courses Available</p>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple">👨‍🎓</div>
        <h3><?php echo $total_students; ?>+</h3>
        <p>Active Students</p>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">🏅</div>
        <h3>10K+</h3>
        <p>Certificates Issued</p>
    </div>

    <div class="stat-card">
        <div class="stat-icon orange">📈</div>
        <h3>95%</h3>
        <p>Success Rate</p>
    </div>
</section>

<section class="courses-section" id="courses">
    <div class="section-header">
        <div>
            <h2>Featured Courses</h2>
            <p>Popular and high-quality courses for students, teachers, and professionals.</p>
        </div>
        <a href="student_index.php" class="view-all">View All</a>
    </div>

    <div class="courses-grid">
        <?php if ($courses && $courses->num_rows > 0): ?>
            <?php while ($course = $courses->fetch_assoc()): ?>

                <?php
                $course_id = $course['c_id'];
                $course_name = $course['c_name'];
                $course_des = $course['c_des'];
                $course_price = $course['c_price'];
                $category = $course['cat_name'] ?? 'Course';

                if (!empty($course['c_image'])) {
                    $course_image = $course['c_image'];
                } else {
                    $course_image = "https://images.unsplash.com/photo-1515879218367-8466d910aaa4?auto=format&fit=crop&w=800&q=80";
                }

                $is_free = strtolower($course_price) === "free" || $course_price == "0" || $course_price == "0.00";
                ?>

                <div class="course-card">
                    <img 
                        src="<?php echo htmlspecialchars($course_image); ?>" 
                        alt="<?php echo htmlspecialchars($course_name); ?>"
                    >

                    <div class="course-body">
                        <div class="course-top">
                            <span class="course-tag">
                                <?php echo htmlspecialchars($category); ?>
                            </span>

                            <?php if ($is_free): ?>
                                <span class="price free">Free</span>
                            <?php else: ?>
                                <span class="price paid">
                                    $<?php echo htmlspecialchars($course_price); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <h3><?php echo htmlspecialchars($course_name); ?></h3>

                        <p>
                            <?php echo htmlspecialchars($course_des); ?>
                        </p>

                        <div class="course-meta">
                            <span>⭐ 4.8</span>
                            <span>👥 Students</span>
                        </div>

                        <a href="enrollmentpage.php?c_id=<?php echo $course_id; ?>" class="course-btn">
                            <?php echo $is_free ? "Start Free" : "View Course"; ?>
                        </a>
                    </div>
                </div>

            <?php endwhile; ?>
         <?php else: ?> 

             <!-- if no course find -->
            
            
            <div style="width: 100%; background: #fff; padding: 30px; border-radius: 12px; text-align: center;">
    <h3>No courses available</h3>
    <p>Please check back later.</p>
</div>
            
        <?php endif; ?>
    </div>
</section>

<section class="why-section">
    <div class="section-title-center">
        <h2>Why Choose LMS?</h2>
        <p>A simple, powerful, and flexible platform for better learning.</p>
    </div>

    <div class="why-grid">
        <div class="why-card">
            <div class="why-icon">👨‍🏫</div>
            <h3>Expert Instructors</h3>
            <p>Learn from qualified teachers and industry professionals with real experience.</p>
        </div>

        <div class="why-card">
            <div class="why-icon">⏰</div>
            <h3>Learn at Your Pace</h3>
            <p>Study anytime, anywhere, with flexible lessons that fit your routine.</p>
        </div>

        <div class="why-card">
            <div class="why-icon">📜</div>
            <h3>Get Certified</h3>
            <p>Earn certificates after course completion and showcase your achievements.</p>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="footer-grid">
        <div>
            <h3>LMS</h3>
            <p>
                Learning Management System for modern education, course delivery,
                and student success.
            </p>
        </div>

        <div>
            <h4>Platform</h4>
            <ul>
                <li><a href="#">About Us</a></li>
                <li><a href="#">Courses</a></li>
                <li><a href="#">Teachers</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </div>

        <div>
            <h4>Resources</h4>
            <ul>
                <li><a href="#">Help Center</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms & Conditions</a></li>
            </ul>
        </div>

        <div>
            <h4>Community</h4>
            <ul>
                <li><a href="#">Student Forum</a></li>
                <li><a href="#">Success Stories</a></li>
                <li><a href="#">Programs</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p>© 2026 LMS. All rights reserved.</p>
    </div>
</footer>

</body>
</html>