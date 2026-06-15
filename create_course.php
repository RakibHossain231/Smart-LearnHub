<?php
session_start();
include "db.php";

/* যদি login system এ admin id session এ থাকে */
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 3; // testing er jonno tomar admin id
}

$message = "";

/* Category load */
$category_query = "SELECT cat_id, cat_name FROM categ";
$category_result = mysqli_query($conn, $category_query);

/* Form submit */
if (isset($_POST['create_course'])) {

    $c_name = mysqli_real_escape_string($conn, $_POST['c_name']);
    $cat_id = mysqli_real_escape_string($conn, $_POST['cat_id']);
    $c_price = mysqli_real_escape_string($conn, $_POST['c_price']);
    $c_des = mysqli_real_escape_string($conn, $_POST['c_des']);
    $created_by = $_SESSION['user_id'];

    $image_name = $_FILES['c_image']['name'];
    $image_tmp = $_FILES['c_image']['tmp_name'];
    $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

    $allowed_ext = array("jpg", "jpeg", "png", "webp");

    if (!in_array($image_ext, $allowed_ext)) {
        $message = "Only JPG, JPEG, PNG, WEBP images are allowed.";
    } else {
        $new_image_name = time() . "_" . uniqid() . "." . $image_ext;

        /* create_course.php php folder e, Images folder ek level up */
        $upload_path = "Images/" . $new_image_name;
        $db_image_path = "Images/" . $new_image_name;

        if (move_uploaded_file($image_tmp, $upload_path)) {

            $insert_query = "INSERT INTO course 
                (c_name, c_des, c_price, cat_id, created_by, c_image)
                VALUES
                ('$c_name', '$c_des', '$c_price', '$cat_id', '$created_by', '$db_image_path')";

            if (mysqli_query($conn, $insert_query)) {
                $message = "Course created successfully!";
            } else {
                $message = "Database error: " . mysqli_error($conn);
            }

        } else {
            $message = "Image upload failed. Check Images folder.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Course - LearnHub</title>
    <link rel="stylesheet" href="create_course.css?v=100">
</head>
<body class="course-create-body">

    <div class="course-create-box">
        <h2>Create New Course</h2>

        <?php if ($message != "") { ?>
            <p class="course-message"><?php echo $message; ?></p>
        <?php } ?>

        <form action="" method="POST" enctype="multipart/form-data">

            <label>Course Title</label>
            <input type="text" name="c_name" placeholder="Enter course title" required>

            <label>Category</label>
            <select name="cat_id" required>
                <option value="">Select Category</option>

                <?php while ($cat = mysqli_fetch_assoc($category_result)) { ?>
                    <option value="<?php echo $cat['cat_id']; ?>">
                        <?php echo $cat['cat_name']; ?>
                    </option>
                <?php } ?>

            </select>

            <label>Price</label>
            <input type="text" name="c_price" placeholder="Example: $49.99 or Free" required>

            <label>Course Image</label>
            <input type="file" name="c_image" accept="image/*" required>

            <label>Description</label>
            <textarea name="c_des" placeholder="Enter course description" required></textarea>

            <div class="course-btn-group">
                <button type="submit" name="create_course">Create Course</button>
                <a href="admin_home.php">Cancel</a>
            </div>

        </form>
    </div>

</body>
</html>