<?php
session_start();
include 'db.php';

if (!isset($_SESSION['u_id']) || $_SESSION['role_id'] !== 'Teacher') {
    header("Location: login.php");
    exit();
}

$test_result_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($test_result_id <= 0) { 
    die("Invalid Submission ID."); 
}

$msg = "";

// Process grading updates when the teacher saves marks
if (isset($_POST['submit_grades'])) {
    $calculated_total = 0.0;
    
    if (isset($_POST['marks'])) {
        foreach ($_POST['marks'] as $answer_id => $mark_value) {
            $ans_id = intval($answer_id);
            $assigned_mark = floatval($mark_value);
            $calculated_total += $assigned_mark;

            // Update individual question points
            $conn->query("UPDATE student_test_answer SET marks_obtained = $assigned_mark WHERE answer_id = $ans_id");
        }
    }

    // Determine Letter Grade based on the percentage score
    $max_test_mark = floatval($_POST['max_test_mark']);
    $percentage = ($max_test_mark > 0) ? ($calculated_total / $max_test_mark) * 100 : 0;
    
    if ($percentage >= 90) $final_grade = "A";
    elseif ($percentage >= 80) $final_grade = "B";
    elseif ($percentage >= 70) $final_grade = "C";
    elseif ($percentage >= 60) $final_grade = "D";
    else $final_grade = "F";

    // Append optional feedback to our submission notes status
    $feedback = isset($_POST['feedback']) ? $conn->real_escape_string(trim($_POST['feedback'])) : "";
    $status_text = "Marked";
    if(!empty($feedback)) {
        $status_text = "Marked - Feedback Provided";
    }

    // Update master test execution header record
    $updateMaster = $conn->query("
        UPDATE test_result 
        SET test_result_mark = $calculated_total, 
            test_status = '$status_text',
            submitted_answer = IF('$feedback' != '', '$feedback', submitted_answer)
        WHERE test_result_id = $test_result_id
    ");

    // Upsert into main structural 'grade' registry table
    $en_id = intval($_POST['en_id']);
    $s_id = intval($_POST['s_id']);
    $c_id = intval($_POST['c_id']);

    $checkGradeExists = $conn->query("SELECT grade_id FROM grade WHERE en_id = $en_id AND c_id = $c_id");
    if($checkGradeExists->num_rows > 0) {
        $conn->query("UPDATE grade SET quizz_mark = $calculated_total, total_mark = $calculated_total, final_grade = '$final_grade' WHERE en_id = $en_id AND c_id = $c_id");
    } else {
        $conn->query("INSERT INTO grade (quizz_mark, assignmet_mark, mid_mark, final_mark, attenda_mark, total_mark, final_grade, s_id, c_id, en_id) VALUES ($calculated_total, 0, 0, 0, 0, $calculated_total, '$final_grade', $s_id, $c_id, $en_id)");
    }

    if ($updateMaster) {
        $msg = "Grades processed, letters mapped to ($final_grade) and saved successfully!";
    } else {
        $msg = "Error finalizing evaluation logs: " . $conn->error;
    }
}

// Fetch general test metadata and student details
$metadata = $conn->query("
    SELECT tr.*, t.test_type, t.test_mark, t.c_id, e.s_id, tr.en_id, c.c_name, s.s_name
    FROM test_result tr
    JOIN test t ON tr.test_id = t.test_id
    JOIN enrollment e ON tr.en_id = e.en_id
    JOIN student s ON e.s_id = s.s_id
    JOIN course c ON t.c_id = c.c_id
    WHERE tr.test_result_id = $test_result_id
")->fetch_assoc();

if (!$metadata) { 
    die("Submission record not found."); 
}

// Pull list of student answers alongside actual questions
$answers_query = $conn->query("
    SELECT 
        sta.answer_id,
        sta.theory_answer,
        sta.marks_obtained,
        tq.question_id,
        tq.question_text,
        tq.question_type,
        tq.marks as question_max_mark,
        to_sel.option_text as student_chosen_option,
        to_sel.is_correct as is_choice_correct
    FROM student_test_answer sta
    JOIN test_question tq ON sta.question_id = tq.question_id
    LEFT JOIN test_option to_sel ON sta.selected_option_id = to_sel.option_id
    WHERE sta.test_result_id = $test_result_id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grade Assessment Paper | Smart-LearnHub</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            margin: 0;
            padding: 0;
        }
        /* UPDATED: Dynamic full width screen length container with box sizing constraint corrections */
        .teacher-container {
            padding: 28px 30px;
            width: calc(100% - 60px);
            max-width: 100%;
            margin: 0 auto;
            box-sizing: border-box;
        }
        .back-btn {
            display: inline-block;
            text-decoration: none;
            color: #4f46e5;
            font-weight: 700;
            margin-bottom: 20px;
            font-size: 15px;
        }
        
        /* UPDATED: Two-column layout grid expands seamlessly to the edges of wide displays */
        .workspace-layout {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 25px;
            align-items: start;
            width: 100%;
        }

        .teacher-hero {
            background: linear-gradient(135deg, #3b82f6, #6d28d9);
            color: white;
            border-radius: 24px;
            padding: 34px 30px;
            margin-bottom: 24px;
            width: 100%;
            box-sizing: border-box;
        }
        .teacher-hero h1 { margin: 0 0 8px 0; font-size: 34px; }
        .teacher-hero p { margin: 0; opacity: 0.92; }

        .section-box {
            background: white;
            border-radius: 22px;
            padding: 26px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
            margin-bottom: 25px;
            width: 100%;
            box-sizing: border-box;
        }
        .section-box h2 { margin-top: 0; margin-bottom: 18px; font-size: 24px; color: #111827; }

        .question-card {
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 20px;
            background: #fafafa;
            margin-bottom: 20px;
            width: 100%;
            box-sizing: border-box;
        }
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .question-title { font-weight: 700; font-size: 16px; color: #111827; }
        .max-badge {
            background: #eef2ff;
            color: #4f46e5;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }
        .student-response-box {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 15px;
            margin: 12px 0;
        }
        .badge-ui { display: inline-block; padding: 5px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; margin-top: 8px; }
        .badge-correct { background: #dcfce7; color: #166534; }
        .badge-incorrect { background: #fee2e2; color: #b91c1c; }

        .grading-action { display: flex; align-items: center; gap: 10px; margin-top: 15px; }
        .mark-input {
            width: 90px;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            background: #fff;
        }
        .mark-input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px #e0e7ff;
        }

        /* STICKY SIDEBAR PANEL CONTAINER */
        .sticky-sidebar {
            position: sticky;
            top: 25px;
            background: white;
            border-radius: 22px;
            padding: 24px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
            box-sizing: border-box;
            width: 100%;
        }
        .sidebar-title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 20px;
        }
        .sidebar-group {
            margin-bottom: 20px;
        }
        .sidebar-label {
            font-size: 13px;
            font-weight: bold;
            color: #6b7280;
            margin-bottom: 8px;
            display: block;
        }
        .pill-status {
            display: inline-block;
            background: #fef3c7;
            color: #d97706;
            padding: 6px 14px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
        }
        .sidebar-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            font-size: 14px;
            box-sizing: border-box;
            background: #fafafa;
        }
        .sidebar-input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px #e0e7ff;
        }
        .sidebar-textarea {
            height: 120px;
            resize: none;
        }
        .btn-save-grade {
            width: 100%;
            padding: 13px;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }
        .btn-save-grade:hover { background: #4338ca; }
        .btn-cancel-grade {
            display: block;
            text-align: center;
            width: 100%;
            padding: 12px;
            background: #f3f4f6;
            color: #1f2937;
            text-decoration: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: bold;
            box-sizing: border-box;
        }
        .btn-cancel-grade:hover { background: #e5e7eb; }

        /* GUIDELINES BOX GRAPHICS */
        .guidelines-box {
            margin-top: 25px;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        .guideline-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 8px;
        }
        .g-ex { color: #10b981; font-weight: bold; }
        .g-gd { color: #3b82f6; font-weight: bold; }
        .g-av { color: #f59e0b; font-weight: bold; }
        .g-ba { color: #ef4444; font-weight: bold; }
        .g-fl { color: #b91c1c; font-weight: bold; }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .meta-item { font-size: 15px; color: #4b5563; }
        .meta-item strong { color: #111827; }
        .msg-toast {
            background: #dcfce7;
            color: #166534;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #bbf7d0;
        }
    </style>
</head>
<body>

     <?php include 'tnavbar.php'; ?>

    <div class="teacher-container">
        <a href="teacher_submissions.php" class="back-btn">← Back to Submissions</a>

        <section class="teacher-hero">
            <h1>Evaluate Answer Script</h1>
            <p>Review questions, automated output values, and evaluate theory descriptions.</p>
        </section>

        <?php if($msg != "") { ?>
            <div class="msg-toast"><?php echo $msg; ?></div>
        <?php } ?>

        <form method="POST" action="">
            
            <input type="hidden" name="max_test_mark" value="<?php echo $metadata['test_mark']; ?>">
            <input type="hidden" name="en_id" value="<?php echo $metadata['en_id']; ?>">
            <input type="hidden" name="s_id" value="<?php echo $metadata['s_id']; ?>">
            <input type="hidden" name="c_id" value="<?php echo $metadata['c_id']; ?>">

            <div class="workspace-layout">
                
                <div class="questions-column">
                    <section class="section-box">
                        <h2>Submission Overview</h2>
                        <div class="meta-grid">
                            <div class="meta-item"><strong>Student Name:</strong> <?php echo htmlspecialchars($metadata['s_name']); ?></div>
                            <div class="meta-item"><strong>Course:</strong> <?php echo htmlspecialchars($metadata['c_name']); ?></div>
                            <div class="meta-item"><strong>Exam Type:</strong> <?php echo htmlspecialchars($metadata['test_type']); ?></div>
                            <div class="meta-item"><strong>Current Score:</strong> <span style="color:#4f46e5; font-weight:bold;"><?php echo $metadata['test_result_mark']; ?> / <?php echo $metadata['test_mark']; ?></span> Total Marks</div>
                        </div>
                    </section>

                    <section class="section-box">
                        <h2>Questions Matrix</h2>

                        <?php 
                        $count = 1;
                        while($row = $answers_query->fetch_assoc()) { 
                        ?>
                            <div class="question-card">
                                <div class="question-header">
                                    <span class="question-title">Question <?php echo $count++; ?></span>
                                    <span class="max-badge">Max Marks: <?php echo $row['question_max_mark']; ?></span>
                                </div>

                                <p style="font-size: 15px; color:#111827; margin:0 0 10px 0;">
                                    <strong>Question:</strong> <?php echo htmlspecialchars($row['question_text']); ?>
                                </p>

                                <?php if($row['question_type'] == 'MCQ') { ?>
                                    <div class="student-response-box">
                                        <p style="margin:0; font-size:14px; color:#4b5563;">
                                            <strong>Selected Option:</strong> <?php echo htmlspecialchars($row['student_chosen_option'] ?? 'No option selected'); ?>
                                        </p>
                                        <?php if($row['is_choice_correct'] == 1) { ?>
                                            <span class="badge-ui badge-correct">✓ Correct Option Match</span>
                                        <?php } else { ?>
                                            <span class="badge-ui badge-incorrect">✗ Incorrect Option Match</span>
                                        <?php } ?>
                                    </div>
                                <?php } else { ?>
                                    <div class="student-response-box">
                                        <p style="margin:0 0 6px 0; font-size:14px; color:#4b5563;"><strong>Student Answer:</strong></p>
                                        <p style="margin:0; font-size:14px; color:#111827; line-height:1.5;">
                                            <?php echo nl2br(htmlspecialchars($row['theory_answer'] ?? 'No answer script body found.')); ?>
                                        </p>
                                    </div>
                                <?php } ?>

                                <div class="grading-action">
                                    <label>Assigned Points:</label>
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        max="<?php echo $row['question_max_mark']; ?>" 
                                        name="marks[<?php echo $row['answer_id']; ?>]" 
                                        class="mark-input" 
                                        value="<?php echo $row['marks_obtained']; ?>" 
                                        required
                                    >
                                    <span style="color: #6b7280; font-size: 13px;">/ <?php echo $row['question_max_mark']; ?> Marks</span>
                                </div>
                            </div>
                        <?php } ?>
                    </section>
                </div>

                <div class="sticky-sidebar">
                    <div class="sidebar-title">Grading</div>
                    
                    <div class="sidebar-group">
                        <span class="sidebar-label">Current Status</span>
                        <span class="pill-status">
                            <?php echo ($metadata['test_status'] == 'Marked' || strpos($metadata['test_status'], 'Marked') !== false) ? 'Graded' : 'Pending Review'; ?>
                        </span>
                    </div>

                    <div class="sidebar-group">
                        <span class="sidebar-label">Total Exam Score Summary</span>
                        <div style="font-size:18px; font-weight:bold; color:#111827;">
                            <?php echo $metadata['test_result_mark']; ?> / <?php echo $metadata['test_mark']; ?> Max Marks
                        </div>
                    </div>

                    <div class="sidebar-group">
                        <span class="sidebar-label">Feedback (Optional)</span>
                        <textarea 
                            name="feedback" 
                            class="sidebar-input sidebar-textarea" 
                            placeholder="Provide feedback to the student..."
                        ></textarea>
                    </div>

                    <button type="submit" name="submit_grades" class="sidebar-group btn-save-grade">
                        Save Grade
                    </button>

                    <a href="teacher_submissions.php" class="btn-cancel-grade">Cancel</a>

                    <div class="guidelines-box">
                        <span class="sidebar-label" style="margin-bottom: 12px;">Grading Guidelines</span>
                        
                        <div class="guideline-row"><span>A (90-100)</span><span class="g-ex">Excellent</span></div>
                        <div class="guideline-row"><span>B (80-89)</span><span class="g-gd">Good</span></div>
                        <div class="guideline-row"><span>C (70-79)</span><span class="g-av">Average</span></div>
                        <div class="guideline-row"><span>D (60-69)</span><span class="g-ba">Below Average</span></div>
                        <div class="guideline-row"><span>F (0-59)</span><span class="g-fl">Fail</span></div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</body>
</html>