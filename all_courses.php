<?php
session_start();
include 'db.php';

if (!isset($_SESSION['u_id']) || $_SESSION['role_id'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$u_id = $_SESSION['u_id'];

$student_q = $conn->query("SELECT s_id FROM student WHERE u_id = $u_id");
$student = $student_q->fetch_assoc();
$s_id = $student['s_id'];

//search fillter
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
//category fillter
$cat_id = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;

$where = "WHERE 1";

if ($search !== "") {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $where .= " AND (course.c_name LIKE '%$search_safe%' 
                OR course.c_des LIKE '%$search_safe%' 
                OR categ.cat_name LIKE '%$search_safe%')";
}

//run is category is 

if ($cat_id > 0) {
    $where .= " AND course.cat_id = $cat_id";
}
//to get the selected items checking enrolled to change the btton later
$courses = $conn->query("
    SELECT course.*, categ.cat_name,
    enrollment.s_id AS enrolled_student
    FROM course
    LEFT JOIN categ ON course.cat_id = categ.cat_id
    LEFT JOIN enrollment 
        ON course.c_id = enrollment.c_id 
        AND enrollment.s_id = $s_id
    $where
    ORDER BY course.c_id DESC
");
//for catergory lists
$categories = $conn->query("SELECT * FROM categ ORDER BY cat_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>All Courses</title>
<link rel="stylesheet" href="style.css">

<style>
body {
    background: #f5f5f5;
    margin: 0;
    font-family: Arial, sans-serif;
}

.page-wrap {
    max-width: 1200px;
    margin: 0 auto;
    padding: 25px;
}

.page-header h1 {
    font-size: 30px;
    margin-bottom: 6px;
}

.page-header p {
    color: #6b7280;
    font-size: 14px;
}

.filter-box {
    background: #fff;
    border-radius: 12px;
    padding: 18px;
    margin: 25px 0;
}

.search-input {
    width: 100%;
    padding: 13px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin-bottom: 14px;
}

.category-list {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.category-list a {
    text-decoration: none;
    padding: 7px 12px;
    border-radius: 20px;
    background: #eef2ff;
    color: #4338ca;
    font-size: 12px;
    font-weight: 600;
}

.category-list a.active {
    background: #111827;
    color: #fff;
}

.courses-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 22px;
}

.course-card {
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 8px 18px rgba(0,0,0,0.08);
}

.course-card img {
    width: 100%;
    height: 210px;
    object-fit: cover;
}

.course-body {
    padding: 18px;
}

.course-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.course-tag {
    background: #eef2ff;
    color: #4f46e5;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}

.course-body h3 {
    font-size: 18px;
    margin-bottom: 8px;
}

.course-body p {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.5;
    min-height: 40px;
}

.course-meta {
    display: flex;
    justify-content: space-between;
    color: #6b7280;
    font-size: 12px;
    margin: 15px 0;
}

.course-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.price {
    font-size: 18px;
    font-weight: 700;
    color: #2563eb;
    
}



.course-btn {
    background: #111827;
    color: #fff;
    text-decoration: none;
    padding: 10px 16px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
}

.continue-btn {
    background: skyblue;
    color: black;
    margin-left: 10px;
}

.no-course {
    grid-column: 1 / -1;
    background: #fff;
    padding: 30px;
    text-align: center;
    border-radius: 12px;
}

@media(max-width: 900px) {
    .courses-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media(max-width: 600px) {
    .courses-grid {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>

<?php include 'student_navbar.php'; ?>

<div class="page-wrap">

    <div class="page-header">
        <h1>All Courses</h1>
        <p>Explore our wide range of courses</p>
    </div>

    <div class="filter-box">
        <form method="GET" action="all_courses.php">
            <input 
                type="text" 
                name="search" 
                class="search-input" 
                placeholder="Search courses..."

                value="<?php echo htmlspecialchars($search); ?>"
            >

            <!-- filltee and searcg work togethrer -->

            <?php if ($cat_id > 0): ?>
                <input type="hidden" name="cat_id" value="<?php echo $cat_id; ?>">
            <?php endif; ?>
        </form>

        <div class="category-list">
            <a href="all_courses.php" class="<?php echo $cat_id == 0 ? 'active' : ''; ?>">
                All Categories
            </a>

            <?php while ($cat = $categories->fetch_assoc()): ?>
                <a 
                    href="all_courses.php?cat_id=<?php echo $cat['cat_id']; ?>"
                    class="<?php echo $cat_id == $cat['cat_id'] ? 'active' : ''; ?>"
                >
                <!-- //cateogy name -->
                    <?php echo htmlspecialchars($cat['cat_name']); ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="courses-grid">

        <?php if ($courses && $courses->num_rows > 0): ?>
            <?php while ($course = $courses->fetch_assoc()): ?>

                <?php
                $course_id = $course['c_id'];
                $course_name = $course['c_name'];
                $course_desc = $course['c_des'];
                $course_price = $course['c_price'];
                $category = $course['cat_name'] ?? 'Course';

                $course_image = !empty($course['c_image'])
                    ? $course['c_image']
                    : "https://via.placeholder.com/800x450";

                $is_free = (
                    $course_price == 0 ||
                    $course_price == "0.00" ||
                    strtolower((string)$course_price) == "free"
                );

                $is_enrolled = !empty($course['enrolled_student']);
                ?>

                <div class="course-card">
                    


                    <div class="course-body">
                        <div class="course-top">
                            <span class="course-tag">
                                <?php echo htmlspecialchars($category); ?>
                            </span>
                        </div>

                        <h3><?php echo htmlspecialchars($course_name); ?></h3>

                        <p><?php echo htmlspecialchars($course_desc); ?></p>

                        <div class="course-meta">
                            <span>⭐ 4.8</span>
                            <span>👥 Students</span>
                        </div>

                        <div class="course-bottom">
                            <span class="price">
                                <?php echo $is_free ? "Free" : "$" . htmlspecialchars($course_price); ?>
                            </span>

                            <?php if ($is_enrolled): ?>
                                <a href="student_home.php?c_id=<?php echo $course_id; ?>" class="course-btn continue-btn">
                                    Continue Learning
                                </a>
                            <?php else: ?>
                                <a href="enrollmentpage.php?c_id=<?php echo $course_id; ?>" class="course-btn">
                                    View Course
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php endwhile; ?>

        <?php else: ?>
            <div class="no-course">
                <h3>No courses found</h3>
                <p>Try another search or category.</p>
            </div>
        <?php endif; ?>

    </div>

</div>

<footer class="footer">
    <div class="footer-grid">
        <div>
            <h3>LearnHub</h3>
            <p>Empowering learners worldwide with quality education and professional development.</p>
        </div>

        <div>
            <h4>Platform</h4>
            <ul>
                <li><a href="#">About Us</a></li>
                <li><a href="all_courses.php">Courses</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </div>

        <div>
            <h4>Resources</h4>
            <ul>
                <li><a href="#">Help Center</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
            </ul>
        </div>

        <div>
            <h4>Community</h4>
            <ul>
                <li><a href="#">Student Forum</a></li>
                <li><a href="#">Teach on LearnHub</a></li>
                <li><a href="#">Affiliate Program</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p>© 2026 LearnHub. All rights reserved.</p>
    </div>
</footer>

</body>
</html>