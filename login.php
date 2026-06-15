<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = trim($_POST['user_name']);
    $password = trim($_POST['password']);

    if (empty($user_name) || empty($password)) {
        $message = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM `user` WHERE user_name = ?");
        $stmt->bind_param("s", $user_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['u_id'] = $user['u_id'];
                $_SESSION['user_name'] = $user['user_name'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['email'] = $user['email'];

                if ($user['role_id'] === 'Teacher') {
                    header("Location: teacher_home.php");
                    exit();
                }  elseif ($user['role_id'] === 'Student') {
    header("Location: student_index.php");
    exit();
}elseif ($user['role_id'] === 'Admin') {
                    header("Location: admin_home.php");
                    exit();
                } else {
                    header("Location: index.php");
                    exit();
                }
            } else {
                $message = "Invalid username or password.";
            }
        } else {
            $message = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart-LearnHub| Log In</title>
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    </head>
<body class="auth-page login-bg">
    <div class="auth-overlay"></div>

    <div class="auth-wrapper">
        <div class="auth-left-panel">
            <span class="hero-badge auth-badge">Welcome Back</span>
            <h1>Continue your learning journey with LMS</h1>
            <p>
                Access your courses, quizzes, assignments, certificates,
                and learning dashboard in one beautiful platform.
            </p>
        </div>

        <div class="auth-card">
            <div class="auth-logo">
                <div class="auth-logo-icon">🎓</div>
                <div>
                    <h2>Smart-LearnHub</h2>
                    <p>Learning Management System</p>
                </div>
            </div>

            <h3 class="auth-title">Log In</h3>
            <p class="auth-subtitle">Sign in to access your account</p>

            <?php if (!empty($message)) : ?>
                <div class="alert-box"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="user_name" placeholder="Enter your username" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="auth-submit-btn">Log In</button>
            </form>

            <p class="auth-bottom-text">
                Don’t have an account?
                <a href="register.php">Sign Up</a>
            </p>

            <p class="auth-bottom-link">
                <a href="index.php">← Back to Home</a>
            </p>
        </div>
    </div>
</body>
</html>