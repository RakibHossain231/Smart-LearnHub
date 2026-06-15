<?php
session_start();
include 'db.php';

if (!isset($_SESSION['u_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role_id'] !== 'Teacher') {
    header("Location: index.php");
    exit();
}

$u_id = $_SESSION['u_id'];

$teacherStmt = $conn->prepare("SELECT * FROM teacher WHERE u_id = ?");
$teacherStmt->bind_param("i", $u_id);
$teacherStmt->execute();
$teacher = $teacherStmt->get_result()->fetch_assoc();

if (!$teacher) {
    die("Teacher profile not found.");
}

$t_id = $teacher['t_id'];

$coursesStmt = $conn->prepare("
    SELECT c.c_id, c.c_name, c.c_des
    FROM course c
    INNER JOIN assigned_course ac ON c.c_id = ac.c_id
    WHERE ac.t_id = ?
    ORDER BY c.c_id DESC
");
$coursesStmt->bind_param("i", $t_id);
$coursesStmt->execute();
$courses = $coursesStmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Lesson Library | LMS</title>
    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="page-wrap">

 <?php include 'tnavbar.php'; ?>

    <section class="hero-box">
        <h1>My Lesson Library</h1>
        <p>All uploaded video, PDF, and text lessons are organized by course.</p>
    </section>

    <?php if ($courses->num_rows > 0): ?>
        <?php while ($course = $courses->fetch_assoc()): ?>

            <?php
            $lessonsStmt = $conn->prepare("
                SELECT *
                FROM lesson
                WHERE c_id = ? AND t_id = ?
                ORDER BY lesson_id DESC
            ");
            $lessonsStmt->bind_param("ii", $course['c_id'], $t_id);
            $lessonsStmt->execute();
            $lessons = $lessonsStmt->get_result();
            $totalLessons = $lessons->num_rows;
            ?>

            <div class="course-panel">
                <div class="course-title-row">
                    <div>
                        <h2><?php echo htmlspecialchars($course['c_name']); ?></h2>
                        <p><?php echo htmlspecialchars($course['c_des']); ?></p>
                    </div>

                    <span class="lesson-count">
                        <?php echo $totalLessons; ?> Lessons
                    </span>
                </div>

                <?php if ($totalLessons > 0): ?>
                    <div class="lesson-list">
                        <?php while ($lesson = $lessons->fetch_assoc()): ?>

                            <?php
                            $type = strtolower($lesson['les_type']);

                            if ($type == "video") {
                                $iconClass = "video-icon";
                                $iconText = "▶";
                                $buttonText = "Watch";
                            } elseif ($type == "pdf") {
                                $iconClass = "pdf-icon";
                                $iconText = "PDF";
                                $buttonText = "Open PDF";
                            } else {
                                $iconClass = "text-icon";
                                $iconText = "TXT";
                                $buttonText = "Read";
                            }
                            ?>

                            <div class="lesson-row">
                                <div class="lesson-icon <?php echo $iconClass; ?>">
                                    <?php echo $iconText; ?>
                                </div>

                                <div class="lesson-info">
                                    <h3><?php echo htmlspecialchars($lesson['lesson_title']); ?></h3>
                                    <p>
                                        <?php echo htmlspecialchars($course['c_name']); ?> lesson material
                                    </p>

                                    <div class="lesson-tags">
                                        <span class="tag"><?php echo htmlspecialchars($lesson['les_type']); ?></span>

                                        <span class="tag">
                                            <?php
                                            echo ($lesson['duration'] == "00:00:00")
                                                ? "No duration"
                                                : htmlspecialchars($lesson['duration']);
                                            ?>
                                        </span>
                                    </div>
                                </div>

                               <div style="display:flex; flex-direction:column; gap:8px;">

    <a href="<?php echo htmlspecialchars($lesson['url']); ?>" target="_blank" class="open-btn">
        <?php echo $buttonText; ?>
    </a>

    <a href="edit_lesson.php?id=<?php echo $lesson['lesson_id']; ?>" class="edit-btn">
        Edit
    </a>

    <a href="delete_lesson.php?id=<?php echo $lesson['lesson_id']; ?>" 
       class="delete-btn"
       onclick="return confirm('Delete this lesson?')">
        Delete
    </a>

</div>
                            </div>

                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-box">
                        No lessons uploaded for this course yet.
                    </div>
                <?php endif; ?>
            </div>

        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-box">No assigned courses found.</div>
    <?php endif; ?>

</div>

</body>
</html>