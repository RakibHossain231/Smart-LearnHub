<?php
session_start();
include "db.php";

/* testing er jonno */
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 3; // tomar admin id
}

$message = "";

if (isset($_POST['create_category'])) {
    $cat_name = mysqli_real_escape_string($conn, $_POST['cat_name']);
    $cat_des = mysqli_real_escape_string($conn, $_POST['cat_des']);
    $created_by = $_SESSION['user_id'];

    $check_sql = "SELECT * FROM categ WHERE cat_name = '$cat_name'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $message = "This category already exists.";
    } else {
        $insert_sql = "INSERT INTO categ (cat_name, cat_des, created_by)
                       VALUES ('$cat_name', '$cat_des', '$created_by')";

        if (mysqli_query($conn, $insert_sql)) {
            $message = "Category created successfully!";
            header("Location: category_management.php");
            exit();
        } else {
            $message = "Database error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Category - LearnHub</title>
    <link rel="stylesheet" href="create_course.css?v=101">
</head>
<body class="course-create-body">

    <div class="course-create-box">
        <h2>Create New Category</h2>

        <?php if ($message != "") { ?>
            <p class="course-message"><?php echo $message; ?></p>
        <?php } ?>

        <form action="" method="POST">

            <label>Category Name</label>
            <input type="text" name="cat_name" placeholder="Enter category name" required>

            <label>Description</label>
            <textarea name="cat_des" placeholder="Enter category description" required></textarea>

            <div class="course-btn-group">
                <button type="submit" name="create_category">Create Category</button>
                <a href="category_management.php">Cancel</a>
            </div>

        </form>
    </div>

</body>
</html>