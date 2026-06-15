
<?php
session_start();


if (!isset($_SESSION['u_id']) || $_SESSION['role_id'] !== 'Student') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$c_id = isset($_GET['c_id']) ? intval($_GET['c_id']) : 0;

if ($c_id <= 0) {
    header("Location: student_index.php");
    exit();
}

/* get course */
$course_result = $conn->query("
    SELECT course.*, categ.cat_name
    FROM course
     JOIN categ ON course.cat_id = categ.cat_id
    WHERE course.c_id = $c_id
");

if (!$course_result || $course_result->num_rows == 0) {
    die("Course not found.");
}

$course = $course_result->fetch_assoc();

/* lesson*/
$lessons = $conn->query("
    SELECT lesson_title, les_type, duration
    FROM lesson
    WHERE c_id = $c_id
    ORDER BY lesson_id ASC
");

$lesson_count = $lessons ? $lessons->num_rows : 0;


$course_name = $course['c_name'];
$course_desc = $course['c_des'];
$course_price = $course['c_price'];
$category = $course['cat_name'] ?? 'Course';

$is_free = (
    $course_price == 0 ||
    $course_price == "0.00" ||
    strtolower((string)$course_price) == "free"
);

$u_id = $_SESSION['u_id'];

$student_q = $conn->query("SELECT s_id FROM student WHERE u_id = $u_id");
$student = $student_q->fetch_assoc();
$s_id = $student['s_id']; 
// if i didnot convert to arrat i iwoo not use it 

$enroll_check = $conn->query("
    SELECT * FROM enrollment 
    WHERE s_id = $s_id AND c_id = $c_id
");

$is_enrolled = ($enroll_check && $enroll_check->num_rows > 0);
?>
<!DOCTYPE html>
<html lang="en">

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    .admin-navbar {
    width: calc(100% - 60px);
    margin: 15px auto;
    height: 85px;
    background: #fff;
    border-radius: 20px;
    border: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 25px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
}

/* LEFT */
.admin-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.logo-box {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #3b82f6, #7c2df2);
    color: white;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}

.logo-text h2 {
    font-size: 22px;
    margin: 0;
}

.logo-text p {
    font-size: 12px;
    color: #64748b;
}


.admin-menu {
    display: flex;
    gap: 18px;
}

.admin-menu a {
    text-decoration: none;
    color: #475569;
    font-weight: 600;
    padding: 8px 14px;
    border-radius: 8px;
    transition: 0.2s;
}

.admin-menu a:hover {
    background: #f1f5f9;
}

.admin-menu a.active {
    background: #4f46e5;
    color: #fff;
}


.admin-user {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #f1f5f9;
    padding: 8px 10px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
}

.admin-name {
    color: #111827;
}

.logout-btn {
    text-decoration: none;
    background: #4f46e5;
    color: #fff;
    padding: 7px 13px;
    border-radius: 8px;
    font-size: 13px;
    transition: 0.2s;
}

.logout-btn:hover {
    background: #e48343;
}
.nav-links a{
      text-decoration: none;
    color: #475569;
    font-weight: 600;
    padding: 8px 14px;
    border-radius: 8px;
    transition: 0.2s;
}
</style>

<link rel="stylesheet" href="enrollment.css">






<body>
<header class="admin-navbar">

    <div class="admin-left">
        <div class="logo-box">🛠️</div>
        <div class="logo-text">
            <h2>Smart-LearnHub</h2>
            <p>student Panel</p>
        </div>
    </div>
    
    <nav class="nav-links">
        <a href="student_index.php">Home</a>
        <a href="all_courses.php">Courses</a>
        <a href="student_home.php">Dashboard</a>
    </nav>

    <div class="admin-user">
        <div class="profle">
            <span class="admin-name">
                👤 <?php echo $_SESSION['user_name'] ?? 'Admin'; ?>
            </span>
        </div>
        <div class="logout">
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        
    </div>

</header>


<main class="container">

<section class="hero">

<div class="hero-left">
<span class="tag"><?php echo htmlspecialchars($category); ?></span>

<h1><?php echo htmlspecialchars($course_name); ?></h1>

<p>
<?php echo htmlspecialchars($course_desc); ?>
</p>

<div class="stats">
⭐ 4.8 &nbsp; 👥 Students &nbsp; 📘 <?php echo $lesson_count; ?> lessons
</div>
</div>

<div class="price-card">
<h2>
<?php echo $is_free ? "Free" : "$" . htmlspecialchars($course_price); ?>
</h2>


<?php if ($is_enrolled): ?>

    <a href="student_home.php?c_id=<?php echo $c_id; ?>" id="enrollBtn" class="detail-btn" style="background:#16a34a;">
        Continue Learning
    </a>

<?php elseif ($is_free): ?>

  <a href="free_enroll.php?c_id=<?php echo $c_id; ?>" 
       id="enrollBtn" 
       class="detail-btn"
       onclick="return confirm('Are you sure you want to enroll in this course?');">
        Enroll Now
    </a>
  
<?php else: ?>

    <a href="payment.php?c_id=<?php echo $c_id; ?>" id="enrollBtn" class="detail-btn">
        Enroll Now
    </a>

<?php endif; ?>

<ul>
<li>Lifetime access</li>
<li>Certificate of completion</li>
</ul>
</div>

</section>

<section class="card">
<h3>What You'll Learn</h3>

<div class="learn-grid">
<p>Master the fundamentals of <?php echo htmlspecialchars($course_name); ?></p>
<p>Complete <?php echo $lesson_count; ?> comprehensive lessons</p>
<p>Build real-world projects and portfolio pieces</p>
<p>Earn a certificate of completion</p>
<p>Access course materials and lesson resources</p>
</div>
</section>

<section class="card">
<h3>Course Curriculum</h3>

<?php if ($lesson_count > 0): ?>
    <?php 
    $count = 1;
    while ($lesson = $lessons->fetch_assoc()): 
    ?>
        <div class="lesson">
            <div class="lesson-title-only">
                <span>
                    <?php echo $count . ". " . htmlspecialchars($lesson['lesson_title']); ?>
                </span>

                <span>
                    <?php echo htmlspecialchars($lesson['les_type']); ?>
                    <?php if (!empty($lesson['duration'])): ?>
                        • <?php echo htmlspecialchars($lesson['duration']); ?>
                    <?php endif; ?>
                </span>
            </div>
        </div>
    <?php 
    $count++;
    endwhile; 
    ?>
<?php else: ?>
    <div class="lesson">
        No lessons added yet.
    </div>
<?php endif; ?>

</section>

</main>

<footer class="footer">

<div>
<h4>LearnHub</h4>
<p>Empowering learners worldwide with quality education.</p>
</div>

<div>
<h4>Platform</h4>
<p>About Us<br>Careers<br>Blog</p>
</div>

<div>
<h4>Resources</h4>
<p>Help Center<br>Privacy Policy<br>Terms</p>
</div>

<div>
<h4>Community</h4>
<p>Student Forum<br>Become a Partner</p>
</div>

</footer>

</body>
</html>