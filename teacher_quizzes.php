<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db.php';

if (!isset($_SESSION['u_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role_id'] !== 'Teacher') {
    header("Location: index.php");
    exit();
}

$message = ""; 
$u_id = $_SESSION['u_id'];

/* Get logged-in teacher */
$teacherResult = $conn->query("SELECT t_id FROM teacher WHERE u_id = $u_id");
$teacher = $teacherResult->fetch_assoc();
if (!$teacher) die("Teacher not found.");
$t_id = intval($teacher['t_id']);

/* Course selected from Create Quiz button */
$selectedCourseId = isset($_GET['c_id']) ? intval($_GET['c_id']) : 0;
$selectedCourse = null;

if ($selectedCourseId > 0) {
    $courseCheckResult = $conn->query("
        SELECT c.c_id, c.c_name
        FROM course c
        INNER JOIN assigned_course ac ON c.c_id = ac.c_id
        WHERE ac.t_id = $t_id AND c.c_id = $selectedCourseId
        LIMIT 1
    ");
    $selectedCourse = $courseCheckResult->fetch_assoc();
    if (!$selectedCourse) {
        die("Course not found or not assigned to you.");
    }
} else {
    die("No course selected.");
}

/* Edit Mode Initialization Parameters */
$edit_mode = false;
$edit_q_id = 0;
$edit_q_text = "";
$edit_q_type = "MCQ";
$edit_q_marks = "";
$edit_options = ["", "", "", ""];
$edit_correct = 0;

if (isset($_GET['edit_question_id'])) {
    $edit_mode = true;
    $edit_q_id = intval($_GET['edit_question_id']);
    
    $q_edit_res = $conn->query("SELECT * FROM test_question WHERE question_id = $edit_q_id");
    if ($q_edit_row = $q_edit_res->fetch_assoc()) {
        $edit_q_text = $q_edit_row['question_text'];
        $edit_q_type = $q_edit_row['question_type'];
        $edit_q_marks = $q_edit_row['marks'];
        
        if ($edit_q_type === "MCQ") {
            $opt_res = $conn->query("SELECT * FROM test_option WHERE question_id = $edit_q_id ORDER BY option_id ASC");
            $idx = 0;
            while ($opt_row = $opt_res->fetch_assoc()) {
                if ($idx < 4) {
                    $edit_options[$idx] = $opt_row['option_text'];
                    if ($opt_row['is_correct'] == 1) {
                        $edit_correct = $idx;
                    }
                    $idx++;
                }
            }
        }
    }
}

/* Delete Question Action */
if (isset($_GET['delete_question_id']) && isset($_GET['test_id'])) {
    $del_id = intval($_GET['delete_question_id']);
    $t_id_param = intval($_GET['test_id']);
    
    $conn->query("DELETE FROM test_option WHERE question_id = $del_id");
    $conn->query("DELETE FROM test_question WHERE question_id = $del_id");
    
    $_SESSION['success'] = "Question removed cleanly from paper layout.";
    header("Location: teacher_quizzes.php?test_id=$t_id_param&c_id=" . $selectedCourseId);
    exit();
}

/* Create quiz/test */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_test'])) {
    $test_type = trim($_POST['test_type']);
    $test_mark = floatval($_POST['test_mark']);
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $c_id = intval($_POST['c_id']);
    $test_question = ($test_type === "Quiz") ? "Multiple questions" : "Theory Exam Paper";

    $validCourseResult = $conn->query("SELECT as_id FROM assigned_course WHERE t_id = $t_id AND c_id = $c_id LIMIT 1");
    $validCourse = $validCourseResult->num_rows > 0;

    if (empty($test_type) || $test_mark <= 0 || empty($start_time) || empty($end_time) || $c_id <= 0 || !$validCourse) {
        $message = "Please fill all fields correctly.";
    } else {
        $insertResult = $conn->query("
            INSERT INTO test 
            (test_type, test_mark, test_question, dealine, c_id, t_id, start_time, end_time, is_published)
            VALUES 
            ('$test_type', $test_mark, '$test_question', '$end_time', $c_id, $t_id, '$start_time', '$end_time', 0)
        ");

        if ($insertResult) {
            $last_id = $conn->insert_id;
            $_SESSION['success'] = "Exam paper created. Now configure your content.";
            header("Location: teacher_quizzes.php?test_id=" . $last_id . "&c_id=" . $c_id);
            exit();
        } else {
            $message = "Quiz creation failed.";
        }
    }
}

/* Add or Update question content matrix */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_question'])) {
    $test_id = intval($_POST['test_id']);
    $question_type = isset($_POST['question_type']) ? trim($_POST['question_type']) : 'Theory';
    $marks = floatval($_POST['marks']);
    $question_text = "";
    
    $is_updating = isset($_POST['is_updating_mode']) && intval($_POST['is_updating_mode']) === 1;
    $target_q_id = intval($_POST['target_question_id'] ?? 0);

    $testCheckResult = $conn->query("SELECT test_id, c_id, test_type FROM test WHERE test_id = $test_id AND t_id = $t_id LIMIT 1");
    $testCheck = $testCheckResult->fetch_assoc();

    if (!$testCheck) {
        $message = "Invalid quiz selected.";
    } else {
        if ($testCheck['test_type'] === "Mid" || $testCheck['test_type'] === "Final") {
            $question_type = "Theory";
            
            if (!empty($_FILES['exam_pdf']['name'])) {
                $file_name = time() . '_' . $_FILES['exam_pdf']['name'];
                $tmp_name = $_FILES['exam_pdf']['tmp_name'];
                if (!is_dir("uploads")) mkdir("uploads", 0777, true);
                
                if (move_uploaded_file($tmp_name, "uploads/" . $file_name)) {
                    $question_text = "uploads/" . $file_name;
                } else {
                    $message = "Failed to upload PDF file.";
                }
            } else {
                /* 🛡️ FIXED: Unified target variable storage name from fallback fields */
                $question_text = trim($_POST['question_text_fallback']);
            }
        } else {
            $question_text = trim($_POST['question_text']);
        }

        if (empty($question_text) && empty($message)) {
            $message = "Please provide a question description or document upload.";
        } elseif ($marks <= 0) {
            $message = "Please enter valid marks.";
        } else {
            if ($is_updating && $target_q_id > 0) {
                $conn->query("UPDATE test_question SET question_text = '$question_text', question_type = '$question_type', marks = $marks WHERE question_id = $target_q_id");
                $conn->query("DELETE FROM test_option WHERE question_id = $target_q_id");
                $question_id = $target_q_id;
            } else {
                $conn->query("INSERT INTO test_question (test_id, question_text, question_type, marks) VALUES ($test_id, '$question_text', '$question_type', $marks)");
                $question_id = $conn->insert_id;
            }

            if ($question_type == "MCQ") {
                $options = $_POST['options'] ?? [];
                $correct = intval($_POST['correct_option'] ?? 0);

                foreach ($options as $key => $option_text) {
                    $option_text = trim($option_text);
                    if ($option_text != "") {
                        $is_correct = ($key == $correct) ? 1 : 0;
                        $conn->query("INSERT INTO test_option (question_id, option_text, is_correct) VALUES ($question_id, '$option_text', $is_correct)");
                    }
                }
            }

            $_SESSION['success'] = $is_updating ? "Question entry successfully updated." : "Question successfully saved.";
            header("Location: teacher_quizzes.php?test_id=$test_id&c_id=" . $testCheck['c_id']);
            exit();
        }
    }
}

/* Fetch selected test */
$selectedTest = null;
$questions = null;

if (isset($_GET['test_id'])) {
    $test_id = intval($_GET['test_id']);
    $testResult = $conn->query("SELECT t.*, c.c_name FROM test t INNER JOIN course c ON t.c_id = c.c_id WHERE t.test_id = $test_id AND t.t_id = $t_id LIMIT 1");
    $selectedTest = $testResult->fetch_assoc();

    if ($selectedTest) {
        $selectedCourseId = $selectedTest['c_id'];
        $selectedCourse = ['c_id' => $selectedTest['c_id'], 'c_name' => $selectedTest['c_name']];
        $questionsResult = $conn->query("SELECT * FROM test_question WHERE test_id = $test_id ORDER BY question_id ASC");
        $questions = $questionsResult;
    }
}

if ($selectedCourseId > 0) {
    $allQuizStmt = $conn->query("
        SELECT t.*, c.c_name,
        (SELECT COUNT(*) FROM test_question tq WHERE tq.test_id = t.test_id) AS total_questions
        FROM test t
        INNER JOIN course c ON t.c_id = c.c_id
        WHERE t.t_id = $t_id AND t.c_id = $selectedCourseId
        ORDER BY t.test_type ASC, t.test_id ASC
    ");
}
$allQuizzes = $allQuizStmt;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Quiz Manager | LMS</title>
    <link rel="stylesheet" href="teacher_quiz.css?v=4">
</head>
<body class="teacher-page">
    <?php include 'teacher_navbar.php'; ?>

    <div class="hero">
        <h1>Quiz / Mid / Final Management</h1>
        <p>Create real-time exam papers with structural editing properties.</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <div class="grid">
        <div>
            <div class="box">
                <h2>1. Create New Quiz Paper</h2>
                <form method="POST">
                    <label>Course</label>
                    <?php if ($selectedCourse) { ?>
                        <input type="hidden" name="c_id" value="<?php echo $selectedCourse['c_id']; ?>">
                        <div class="course-display"><?php echo $selectedCourse['c_name']; ?></div>
                    <?php } ?>

                    <label>Test Type</label>
                    <select name="test_type" required>
                        <option value="">Choose type</option>
                        <option value="Quiz">Quiz</option>
                        <option value="Mid">Mid</option>
                        <option value="Final">Final</option>
                    </select>

                    <label>Total Marks</label>
                    <input type="number" step="0.01" name="test_mark" required>

                    <label>Start Time</label>
                    <input type="datetime-local" name="start_time" required>

                    <label>Deadline / End Time</label>
                    <input type="datetime-local" name="end_time" required>

                    <button type="submit" name="create_test" class="btn">Create Quiz</button>
                    <a href="teacher_home.php" class="cancel">Cancel</a>
                </form>
            </div>

            <?php if ($selectedTest): ?>
                <div class="box" id="form-content-container">
                    <h2><?php echo $edit_mode ? '📝 Modify Content Parameters' : '2. Configure Examination Content'; ?></h2>
                    <p><span class="badge">Type Selected: <?php echo htmlspecialchars($selectedTest['test_type']); ?></span></p>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="test_id" value="<?php echo intval($selectedTest['test_id']); ?>">
                        <input type="hidden" name="is_updating_mode" value="<?php echo $edit_mode ? '1' : '0'; ?>">
                        <input type="hidden" name="target_question_id" value="<?php echo $edit_q_id; ?>">

                        <?php if ($selectedTest['test_type'] === "Quiz") { ?>
                            <label>Question Type</label>
                            <select name="question_type" id="questionType" required onchange="toggleMCQ()">
                                <option value="MCQ" <?php if($edit_q_type === 'MCQ') echo 'selected'; ?>>MCQ</option>
                                <option value="Theory" <?php if($edit_q_type === 'Theory') echo 'selected'; ?>>Theory</option>
                            </select>

                            <label>Question Text</label>
                            <textarea name="question_text" required placeholder="Enter question description..."><?php echo htmlspecialchars($edit_q_text); ?></textarea>

                            <div class="mcq-options" id="mcqOptions" style="display: <?php echo ($edit_q_type === 'MCQ') ? 'block' : 'none'; ?>;">
                                <label>Option A.</label>
                                <input type="text" name="options[]" value="<?php echo htmlspecialchars($edit_options[0]); ?>">
                                <label>Option B.</label>
                                <input type="text" name="options[]" value="<?php echo htmlspecialchars($edit_options[1]); ?>">
                                <label>Option C.</label>
                                <input type="text" name="options[]" value="<?php echo htmlspecialchars($edit_options[2]); ?>">
                                <label>Option D.</label>
                                <input type="text" name="options[]" value="<?php echo htmlspecialchars($edit_options[3]); ?>">

                                <label>Correct Option Match</label>
                                <select name="correct_option">
                                    <option value="0" <?php if($edit_correct == 0) echo 'selected'; ?>>Option A</option>
                                    <option value="1" <?php if($edit_correct == 1) echo 'selected'; ?>>Option B</option>
                                    <option value="2" <?php if($edit_correct == 2) echo 'selected'; ?>>Option C</option>
                                    <option value="3" <?php if($edit_correct == 3) echo 'selected'; ?>>Option D</option>
                                </select>
                            </div>
                        <?php } else { ?>
                            <input type="hidden" name="question_type" value="Theory">
                            
                            <?php if ($edit_mode && strpos($edit_q_text, 'uploads/') !== false): ?>
                                <p style="font-size:14px; background:#f0fdf4; padding:8px; border-radius:6px; color:#166534;">
                                    Current File: <a href="<?php echo $edit_q_text; ?>" target="_blank">View File Document</a>
                                </p>
                            <?php endif; ?>

                            <label style="color:#4f46e5;">📤 Upload Exam Paper PDF Document</label>
                            <input type="file" name="exam_pdf" accept="application/pdf">
                            
                            <label>Or Write Theory Questions Text Here</label>
                            <textarea name="question_text_fallback" placeholder="Write text if not uploading a PDF file..."><?php echo (strpos($edit_q_text, 'uploads/') === false) ? htmlspecialchars($edit_q_text) : ''; ?></textarea>
                        <?php } ?>

                        <label>Assigned Points/Marks</label>
                        <input type="number" step="0.01" name="marks" value="<?php echo htmlspecialchars($edit_mode ? $edit_q_marks : $selectedTest['test_mark']); ?>" required>

                        <button type="submit" name="add_question" class="btn"><?php echo $edit_mode ? 'Update Question Content' : 'Save to Paper'; ?></button>
                        <?php if($edit_mode): ?>
                            <a href="teacher_quizzes.php?test_id=<?php echo $selectedTest['test_id']; ?>&c_id=<?php echo $selectedCourseId; ?>" class="cancel" style="padding:10px 14px; font-size:14px;">Cancel Edit</a>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <?php if ($selectedTest) { ?>
                <div class="box">
                    <h2>Questions in This Paper</h2>
                    <?php if ($questions && $questions->num_rows > 0) { 
                        $q_counter = 1;
                        while($q = $questions->fetch_assoc()) { 
                    ?>
                            <div class="question-box" style="position:relative;">
                                <p>
                                    <strong><?php echo $q_counter . ") "; ?></strong>
                                    <?php 
                                    if (strpos($q['question_text'], 'uploads/') !== false) {
                                        echo '<a href="'.htmlspecialchars($q['question_text']).'" target="_blank" style="color:#4f46e5; font-weight:bold;">📄 View Attached Exam PDF Document</a>';
                                    } else {
                                        echo nl2br(htmlspecialchars($q['question_text']));
                                    }
                                    ?>
                                </p>
                                <p>
                                    <span class="badge"><?php echo $q['question_type']; ?></span>
                                    <span class="badge"><?php echo $q['marks']; ?> marks</span>
                                </p>

                                <?php if ($q['question_type'] == "MCQ") { 
                                    $options = $conn->query("SELECT * FROM test_option WHERE question_id = ".$q['question_id']);
                                    $labels = ['A', 'B', 'C', 'D'];
                                    $index = 0;
                                    while($op = $options->fetch_assoc()) { 
                                ?>
                                        <div class="option-box">
                                            <?php echo $labels[$index] . ". " . htmlspecialchars($op['option_text']); ?>
                                            <?php if ($op['is_correct'] == 1) { echo " ✅"; } ?>
                                        </div>
                                <?php 
                                        $index++;
                                    } 
                                } ?>

                                <div style="margin-top:12px; display:flex; gap:10px; border-top:1px solid #e5e7eb; padding-top:8px;">
                                    <a href="teacher_quizzes.php?test_id=<?php echo $selectedTest['test_id']; ?>&c_id=<?php echo $selectedCourseId; ?>&edit_question_id=<?php echo $q['question_id']; ?>#form-content-container" style="color:#4f46e5; font-weight:bold; text-decoration:none; font-size:13px;">✏️ Modify/Edit</a>
                                    <a href="teacher_quizzes.php?test_id=<?php echo $selectedTest['test_id']; ?>&c_id=<?php echo $selectedCourseId; ?>&delete_question_id=<?php echo $q['question_id']; ?>" onclick="return confirm('Remove this question entirely?')" style="color:#b91c1c; font-weight:bold; text-decoration:none; font-size:13px;">❌ Delete</a>
                                </div>
                            </div>
                    <?php 
                            $q_counter++;
                        } 
                    } else { ?>
                        <p>No questions added yet.</p>
                    <?php } ?>
                </div>
            <?php } ?>

            <div class="box">
                <h2><?php echo htmlspecialchars($selectedCourse['c_name'] ?? 'Course'); ?> Active Uploaded Papers</h2>
                <?php if ($allQuizzes->num_rows > 0) { 
                    $typeCounter = [];
                    while ($quiz = $allQuizzes->fetch_assoc()) {
                        $type = $quiz['test_type'];
                        if (!isset($typeCounter[$type])) {
                            $typeCounter[$type] = 1;
                        } else {
                            $typeCounter[$type]++;
                        }
                        $displayName = $type . '-' . $typeCounter[$type];
                ?>
                        <div class="quiz-card">
                            <h3><?php echo htmlspecialchars($displayName); ?></h3>
                            <p>
                                <span class="badge"><?php echo htmlspecialchars($quiz['test_mark']); ?> Marks</span>
                                <span class="badge"><?php echo htmlspecialchars($quiz['total_questions']); ?> Entries</span>
                            </p>
                            
                            <div style="margin-top: 10px; display: flex; align-items: center; gap: 12px;">
                                <a href="teacher_quizzes.php?test_id=<?php echo intval($quiz['test_id']); ?>&c_id=<?php echo intval($quiz['c_id']); ?>" class="btn" style="padding: 8px 12px; font-size: 13px;">
                                    Modify Content Configuration
                                </a>
                                <?php if (intval($quiz['is_published']) == 0): ?>
                                    <a href="publish_quiz.php?id=<?php echo intval($quiz['test_id']); ?>&c_id=<?php echo intval($quiz['c_id']); ?>" class="publish-btn" style="margin-top:0; padding: 8px 12px; font-size: 13px;">
                                        🚀 Go Live Online
                                    </a>
                                <?php else: ?>
                                    <span class="published" style="margin-top:0; color:#16a34a; font-weight:bold;">✔ Published Online</span>
                                <?php endif; ?>
                            </div>
                        </div>
                <?php } 
                } else { ?>
                    <p>No active exams or quizzes deployed yet.</p>
                <?php } ?>
            </div>
        </div>
    </div>

    <script>
    function toggleMCQ() {
        const questionType = document.getElementById("questionType");
        const mcqOptions = document.getElementById("mcqOptions");
        if (questionType && mcqOptions) {
            mcqOptions.style.display = questionType.value === "MCQ" ? "block" : "none";
        }
    }
    </script>
</body>
</html>