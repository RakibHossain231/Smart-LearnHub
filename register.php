<?php
include 'db.php';

$message = "";
$role_id = isset($_POST['role_id']) ? trim($_POST['role_id']) : "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_account'])) {
    $full_name = trim($_POST['full_name']);
    $user_name = trim($_POST['user_name']);
    $email = trim($_POST['email']);
    $phone_no = trim($_POST['phone_no']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    $institution = isset($_POST['institution']) ? trim($_POST['institution']) : "";
    $specialization = isset($_POST['specialization']) ? trim($_POST['specialization']) : "";
    $experience = isset($_POST['experience']) ? trim($_POST['experience']) : "";

    if (
        empty($role_id) || empty($full_name) || empty($user_name) || empty($email) ||
        empty($phone_no) || empty($password) || empty($confirm_password)
    ) {
        $message = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        if ($role_id === "Student" && empty($institution)) {
            $message = "Institution is required for Student.";
        } elseif ($role_id === "Teacher" && (empty($specialization) || $experience === "")) {
            $message = "Specialization and Experience are required for Teacher.";
        } 
        else {

    // Check if email or username already exists
    $check_result = $conn->query("
        SELECT u_id 
        FROM user 
        WHERE email = '$email' OR user_name = '$user_name'
    ");

    if ($check_result->num_rows > 0) {

        $message = "Email or username already exists.";

    } else {

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into user table
        $insert_user = $conn->query("
            INSERT INTO user 
            (full_name, user_name, password, phone_no, role_id, email) 
            VALUES 
            ('$full_name', '$user_name', '$hashed_password', '$phone_no', '$role_id', '$email')
        ");

        if ($insert_user) {

            $u_id = $conn->insert_id;

            // If user is Student
            if ($role_id == "Student") {

                $conn->query("
                    INSERT INTO student 
                    (s_name, school_name, u_id) 
                    VALUES 
                    ('$full_name', '$institution', $u_id)
                ");

            }

            // If user is Teacher
            if ($role_id == "Teacher") {

                $teacher_school = "Not Provided";

                $conn->query("
                    INSERT INTO teacher 
                    (t_name, t_school, experince, expert_at, u_id) 
                    VALUES 
                    ('$full_name', '$teacher_school', $experience, '$specialization', $u_id)
                ");

            }

            header("Location: login.php");
            exit();

        } else {

            $message = "Registration failed. Please try again.";

        }
    }
}

        }
    }
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS | Sign Up</title>
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet"  href="style.css">

</head>
<body class="auth-page register-bg">
    <div class="auth-overlay"></div>

    <div class="auth-wrapper">
        <div class="auth-left-panel">
            <span class="hero-badge auth-badge">Create Account</span>
            <h1>Join LMS and start learning today</h1>
            <p>
                Select your role first, then fill in only the fields you need.
            </p>
        </div>

        <div class="auth-card auth-card-large">
            <div class="auth-logo">
                <div class="auth-logo-icon">🎓</div>
                <div>
                    <h2>Smart-LearnHub</h2>
                    <p>Learning Management System</p>
                </div>
            </div>

            <h3 class="auth-title">Sign Up</h3>
            <p class="auth-subtitle">Create your new LMS account</p>

            <?php if (!empty($message)) : ?>
                <div class="alert-box"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if (empty($role_id) && !isset($_POST['create_account'])) : ?>
                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label>Select Role</label>
                        <select name="role_id" required>
                            <option value="">Choose role</option>
                            <option value="Student">Student</option>
                            <option value="Teacher">Teacher</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>

                    <button type="submit" class="auth-submit-btn">Continue</button>
                </form>
            <?php else: ?>
                <form method="POST" class="auth-form">
                    <input type="hidden" name="role_id" value="<?php echo htmlspecialchars($role_id); ?>">

                    <div class="selected-role-box">
                        Role Selected: <strong><?php echo htmlspecialchars($role_id); ?></strong>
                        <a href="register.php" class="change-role-link">Change</a>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="full_name" placeholder="Enter your full name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="user_name" placeholder="Choose a username" value="<?php echo isset($_POST['user_name']) ? htmlspecialchars($_POST['user_name']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone_no" placeholder="Enter your phone number" value="<?php echo isset($_POST['phone_no']) ? htmlspecialchars($_POST['phone_no']) : ''; ?>" required>
                        </div>

                        <?php if ($role_id === "Student") : ?>
                            <div class="form-group form-group-full">
                                <label>Institution</label>
                                <input type="text" name="institution" placeholder="Enter your school, college, or university" value="<?php echo isset($_POST['institution']) ? htmlspecialchars($_POST['institution']) : ''; ?>" required>
                            </div>
                        <?php endif; ?>

                        <?php if ($role_id === "Teacher") : ?>
                            <div class="form-group">
                                <label>Specialization</label>
                                <input type="text" name="specialization" placeholder="Example: Web Development, Mathematics" value="<?php echo isset($_POST['specialization']) ? htmlspecialchars($_POST['specialization']) : ''; ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Experience (Years)</label>
                                <input type="number" name="experience" placeholder="Enter years of experience" min="0" value="<?php echo isset($_POST['experience']) ? htmlspecialchars($_POST['experience']) : ''; ?>" required>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" placeholder="Create password" required>
                        </div>

                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" placeholder="Confirm password" required>
                        </div>
                    </div>

                    <button type="submit" name="create_account" value="1" class="auth-submit-btn">Create Account</button>
                </form>
            <?php endif; ?>

            <p class="auth-bottom-text">
                Already have an account?
                <a href="login.php">Log In</a>
            </p>

            <p class="auth-bottom-link">
                <a href="index.php">← Back to Home</a>
            </p>
        </div>
    </div>
</body>
</html>