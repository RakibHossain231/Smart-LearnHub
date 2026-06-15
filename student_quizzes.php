<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db.php';
date_default_timezone_set('Asia/Dhaka');

if (!isset($_SESSION['u_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role_id'] !== 'Student') {
    header("Location: index.php");
    exit();
}

$message = "";
$u_id = intval($_SESSION['u_id']);

$studentQuery = "SELECT * FROM student WHERE u_id = $u_id";
$studentResult = $conn->query($studentQuery);
$student = $studentResult->fetch_assoc();

if (!$student) {
    die("Student not found.");
}

$s_id = intval($student['s_id']);
$c_id = isset($_GET['c_id']) ? intval($_GET['c_id']) : 0;
$view_test_id = isset($_GET['view']) ? intval($_GET['view']) : 0;


/* Submit Quiz */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_quiz'])) {

    $test_id = intval($_POST['test_id']);
    $en_id   = intval($_POST['en_id']);

    $testQuery = "SELECT * FROM test WHERE test_id = $test_id";
    $testResult = $conn->query($testQuery);
    $test = $testResult->fetch_assoc();

    $now = date("Y-m-d H:i:s");
    $is_late = ($now > $test['end_time']) ? 1 : 0;

    $checkQuery = "
        SELECT test_result_id 
        FROM test_result 
        WHERE test_id = $test_id 
        AND en_id = $en_id
    ";
    $already = $conn->query($checkQuery);

    if ($already->num_rows > 0) {
        $message = "You already submitted this quiz.";
    } else {

        $status = $is_late ? "Late Submitted" : "Submitted";
        $status = mysqli_real_escape_string($conn, $status);
        $nowSafe = mysqli_real_escape_string($conn, $now);

        $totalMark = 0;

        $insertQuery = "
            INSERT INTO test_result
            (test_result_mark, test_status, test_id, en_id, submitted_at, is_late)
            VALUES
            ($totalMark, '$status', $test_id, $en_id, '$nowSafe', $is_late)
        ";

        $conn->query($insertQuery);
        $test_result_id = $conn->insert_id;

        if (isset($_POST['answer'])) {

            foreach ($_POST['answer'] as $question_id => $answerValue) {

                $question_id = intval($question_id);

                $qQuery = "SELECT * FROM test_question WHERE question_id = $question_id";
                $qResult = $conn->query($qQuery);
                $question = $qResult->fetch_assoc();

                if ($question['question_type'] == "MCQ") {

                    $selected_option_id = intval($answerValue);
                    $marks = 0;

                    $opQuery = "SELECT is_correct FROM test_option WHERE option_id = $selected_option_id";
                    $opResult = $conn->query($opQuery);
                    $option = $opResult->fetch_assoc();

                    if ($option && $option['is_correct'] == 1) {
                        $marks = floatval($question['marks']);
                        $totalMark += $marks;
                    }

                    $ansQuery = "
                        INSERT INTO student_test_answer
                        (test_result_id, question_id, selected_option_id, theory_answer, marks_obtained)
                        VALUES
                        ($test_result_id, $question_id, $selected_option_id, NULL, $marks)
                    ";

                    $conn->query($ansQuery);

                } else {

                    $selected_option_id = "NULL";
                    $theory_answer = mysqli_real_escape_string($conn, trim($answerValue));
                    $marks = 0;

                    $ansQuery = "
                        INSERT INTO student_test_answer
                        (test_result_id, question_id, selected_option_id, theory_answer, marks_obtained)
                        VALUES
                        ($test_result_id, $question_id, NULL, '$theory_answer', $marks)
                    ";

                    $conn->query($ansQuery);
                }
            }
        }

        $updateQuery = "
            UPDATE test_result
            SET test_result_mark = $totalMark
            WHERE test_result_id = $test_result_id
        ";

        $conn->query($updateQuery);

        $message = "Quiz submitted successfully.";
    }
}


/* Quiz List */
if ($c_id > 0) {
    $quizQuery = "
        SELECT t.*, c.c_name, e.en_id,
               tr.test_result_id, tr.test_status,
               tr.test_result_mark, tr.is_late
        FROM enrollment e
        JOIN course c ON e.c_id = c.c_id
        JOIN test t ON c.c_id = t.c_id
        LEFT JOIN test_result tr
        ON tr.test_id = t.test_id AND tr.en_id = e.en_id
        WHERE e.s_id = $s_id
        AND c.c_id = $c_id
        AND t.is_published = 1
        ORDER BY t.test_id DESC
    ";
} else {
    $quizQuery = "
        SELECT t.*, c.c_name, e.en_id,
               tr.test_result_id, tr.test_status,
               tr.test_result_mark, tr.is_late
        FROM enrollment e
        JOIN course c ON e.c_id = c.c_id
        JOIN test t ON c.c_id = t.c_id
        LEFT JOIN test_result tr
        ON tr.test_id = t.test_id AND tr.en_id = e.en_id
        WHERE e.s_id = $s_id
        AND t.is_published = 1
        ORDER BY t.test_id DESC
    ";
}

$quizzes = $conn->query($quizQuery);
?>
<!DOCTYPE html>
<html>
<head>
<title>Student Quizzes</title>

<style>
body{
    margin:0;
    background:#f5f7fb;
    font-family:Arial;
}
.container{
    padding:30px;
}
.quiz-card{
    background:white;
    padding:25px;
    border-radius:16px;
    margin-bottom:20px;
    box-shadow:0 8px 20px rgba(0,0,0,.06);
}
.badge{
    background:#eef2ff;
    color:#4338ca;
    padding:6px 10px;
    border-radius:20px;
    font-size:12px;
    display:inline-block;
    margin:4px;
}
.closed{
    background:#fee2e2;
    color:#991b1b;
    padding:12px;
    border-radius:10px;
}
.btn{
    background:#4338ca;
    color:white;
    border:none;
    padding:10px 15px;
    border-radius:10px;
    cursor:pointer;
    text-decoration:none;
    display:inline-block;
}
.option{
    display:block;
    margin:8px 0;
}
textarea{
    width:100%;
    height:100px;
}
.message{
    background:#dcfce7;
    padding:10px;
    border-radius:10px;
    margin-bottom:15px;
}
.correct{
    background:#dcfce7;
    padding:8px;
    border-radius:8px;
    margin:5px 0;
}
.wrong{
    background:#fee2e2;
    padding:8px;
    border-radius:8px;
    margin:5px 0;
}
.normal{
    background:#f8fafc;
    padding:8px;
    border-radius:8px;
    margin:5px 0;
}

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

/* MENU */
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

/* USER */
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
.back-link {
    display: inline-block;
    margin-bottom: 18px;
    color: #6b7280;
    text-decoration: none;
    font-size: 14px;
}


.pdf-download-container {
    background: #f0f7ff;
    padding: 16px;
    border-radius: 12px;
    border: 1px solid #bae6fd;
    margin-top: 10px;
    margin-bottom: 15px;
}
.pdf-btn {
    display: inline-flex;
    align-items: center;
    background: #2563eb;
    color: white;
    text-decoration: none;
    padding: 10px 16px;
    border-radius: 8px;
    font-weight: bold;
    font-size: 14px;
    transition: 0.2s;
}
.pdf-btn:hover {
    background: #1d4ed8;
}
</style>
</head>

<body>

<header class="admin-navbar">

    <div class="admin-left">
        <div class="logo-box">🛠️</div>
        <div class="logo-text">
            <h2>Smart-LearnHub</h2>
            <p>student Panel</p>
        </div>
    </div>

    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    ?>

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

<div class="container">
    <a href="student_home.php" class="back-link">← Back to course</a>

<h1>My Quizzes</h1>

<?php if($message!=""): ?>
<div class="message"><?php echo $message; ?></div>
<?php endif; ?>

<?php if($quizzes->num_rows > 0): ?>

<?php while($quiz = $quizzes->fetch_assoc()): ?>

<?php
$now = date("Y-m-d H:i:s");
$not_started = ($now < $quiz['start_time']);
$ended = ($now > $quiz['end_time']);
?>

<div class="quiz-card">

<h2><?php echo $quiz['test_type']; ?> - <?php echo $quiz['c_name']; ?></h2>

<p>
<span class="badge">Start: <?php echo $quiz['start_time']; ?></span>
<span class="badge">End: <?php echo $quiz['end_time']; ?></span>
</p>

<?php if(!empty($quiz['test_result_id'])): ?>

<p>
<span class="badge"><?php echo $quiz['test_status']; ?></span>
<span class="badge">Mark: <?php echo $quiz['test_result_mark']; ?></span>
</p>

<a class="btn" href="student_quizzes.php?c_id=<?php echo $c_id; ?>&view=<?php echo $quiz['test_id']; ?>">
    View Details
</a>

<?php elseif($not_started): ?>

<div class="closed">Quiz has not started yet.</div>

<?php elseif($ended): ?>

<div class="closed">Quiz deadline is over.</div>

<?php else: ?>

<?php
$quiz_test_id = intval($quiz['test_id']);
$qQuery = "SELECT * FROM test_question WHERE test_id = $quiz_test_id";
$questions = $conn->query($qQuery);
?>

<form method="POST">

<input type="hidden" name="test_id" value="<?php echo $quiz['test_id']; ?>">
<input type="hidden" name="en_id" value="<?php echo $quiz['en_id']; ?>">

<?php while($question = $questions->fetch_assoc()): ?>

<div style="margin-bottom:20px;">

<?php if (strpos($question['question_text'], 'uploads/') !== false): ?>
    <div class="pdf-download-container">
        <p style="margin: 0 0 8px 0; font-weight: bold; color: #1e40af;">📄 Examination Question Paper Document Attached:</p>
        <a href="<?php echo htmlspecialchars($question['question_text']); ?>" download class="pdf-btn">
            📥 Download Question Paper PDF
        </a>
    </div>
<?php else: ?>
    <h3><?php echo htmlspecialchars($question['question_text']); ?></h3>
<?php endif; ?>

<p><span class="badge"><?php echo $question['marks']; ?> Marks</span></p>

<?php if($question['question_type']=="MCQ"): ?>

<?php
$question_id = intval($question['question_id']);
$opQuery = "SELECT * FROM test_option WHERE question_id = $question_id";
$options = $conn->query($opQuery);
?>

<?php while($op = $options->fetch_assoc()): ?>

<label class="option">
<input type="radio"
name="answer[<?php echo $question['question_id']; ?>]"
value="<?php echo $op['option_id']; ?>" required>
<?php echo htmlspecialchars($op['option_text']); ?>
</label>

<?php endwhile; ?>

<?php else: ?>

<textarea
name="answer[<?php echo $question['question_id']; ?>]"
required></textarea>

<?php endif; ?>

</div>

<?php endwhile; ?>

<button type="submit" name="submit_quiz" class="btn">
Submit Quiz
</button>

</form>

<?php endif; ?>

</div>

<?php if($view_test_id == $quiz['test_id'] && !empty($quiz['test_result_id'])): ?>

<div class="quiz-card">

<h2>Quiz Details</h2>

<?php
$test_result_id = intval($quiz['test_result_id']);
$quiz_test_id = intval($quiz['test_id']);

$detailQuery = "
    SELECT q.*, a.selected_option_id, a.theory_answer, a.marks_obtained
    FROM test_question q
    LEFT JOIN student_test_answer a
    ON q.question_id = a.question_id
    AND a.test_result_id = $test_result_id
    WHERE q.test_id = $quiz_test_id
";

$details = $conn->query($detailQuery);

?>

<p><span class="badge">Total Mark: <?php echo $quiz['test_result_mark']; ?></span></p>

<?php while($row = $details->fetch_assoc()): ?>

<div style="border:1px solid #eee;padding:15px;border-radius:12px;margin-bottom:15px;">

<?php if (strpos($row['question_text'], 'uploads/') !== false): ?>
    <div class="pdf-download-container">
        <p style="margin: 0 0 8px 0; font-weight: bold; color: #1e40af;">📄 Attached Exam Paper PDF File:</p>
        <a href="<?php echo htmlspecialchars($row['question_text']); ?>" download class="pdf-btn">
            📥 Download Question Paper PDF
        </a>
    </div>
<?php else: ?>
    <h3><?php echo htmlspecialchars($row['question_text']); ?></h3>
<?php endif; ?>

<?php if($row['question_type']=="MCQ"): ?>

<?php
$row_question_id = intval($row['question_id']);
$opsQuery = "SELECT * FROM test_option WHERE question_id = $row_question_id";
$optList = $conn->query($opsQuery);
?>

<?php while($opt = $optList->fetch_assoc()): ?>

<div class="<?php
if($opt['is_correct']==1) echo 'correct';
elseif($row['selected_option_id']==$opt['option_id']) echo 'wrong';
else echo 'normal';
?>">

<?php echo htmlspecialchars($opt['option_text']); ?>

<?php if($opt['is_correct']==1): ?>
 ✔ Correct
<?php endif; ?>

<?php if($row['selected_option_id']==$opt['option_id'] && $opt['is_correct']==0): ?>
 ✘ Your Answer
<?php endif; ?>

</div>

<?php endwhile; ?>

<?php else: ?>

<p><strong>Your Answer:</strong></p>
<div class="normal">
<?php echo nl2br(htmlspecialchars($row['theory_answer'] ?? '')); ?>
</div>

<?php endif; ?>

<p>
<span class="badge">Obtained: <?php echo $row['marks_obtained']; ?></span>
</p>

</div>

<?php endwhile; ?>

</div>

<?php endif; ?>

<?php endwhile; ?>

<?php else: ?>

<p>No available quiz found.</p>

<?php endif; ?>

</div>

</body>
</html>