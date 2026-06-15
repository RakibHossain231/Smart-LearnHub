<?php
session_start();
include "db.php";

if (!isset($_GET['id'])) {
    header("Location: admin_courses_view.php");
    exit();
}

$c_id = mysqli_real_escape_string($conn, $_GET['id']);
$message = "";

$course_result = mysqli_query($conn, "SELECT * FROM course WHERE c_id='$c_id' LIMIT 1");
$course = mysqli_fetch_assoc($course_result);

if (!$course) {
    die("Course not found");
}

$category_result = mysqli_query($conn, "SELECT cat_id, cat_name FROM categ ORDER BY cat_name ASC");

if (isset($_POST['update_course'])) {
    $c_name = mysqli_real_escape_string($conn, $_POST['c_name']);
    $cat_id = mysqli_real_escape_string($conn, $_POST['cat_id']);
    $c_price = mysqli_real_escape_string($conn, $_POST['c_price']);
    $c_des = mysqli_real_escape_string($conn, $_POST['c_des']);

    $image_update = "";

    if (!empty($_FILES['c_image']['name'])) {
        $image_name = $_FILES['c_image']['name'];
        $image_tmp = $_FILES['c_image']['tmp_name'];
        $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        $allowed_ext = ["jpg", "jpeg", "png", "webp"];

        if (!in_array($image_ext, $allowed_ext)) {
            $message = "Only JPG, JPEG, PNG and WEBP images are allowed.";
        } else {
            $new_image = time() . "_" . uniqid() . "." . $image_ext;
            $upload_path = "Images/" . $new_image;
            $db_path = "Images/" . $new_image;

            if (move_uploaded_file($image_tmp, $upload_path)) {
                $image_update = ", c_image='$db_path'";
            } else {
                $message = "Image upload failed.";
            }
        }
    }

    if ($message == "") {
        $update_sql = "
            UPDATE course SET
                c_name='$c_name',
                c_des='$c_des',
                c_price='$c_price',
                cat_id='$cat_id'
                $image_update
            WHERE c_id='$c_id'
        ";

        if (mysqli_query($conn, $update_sql)) {
            header("Location: admin_courses_view.php");
            exit();
        } else {
            $message = "Update failed: " . mysqli_error($conn);
        }
    }
}

$current_image = !empty($course['c_image']) ? $course['c_image'] : "Images/default-course.jpg";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Course</title>
    <link rel="stylesheet" href="admin_courses_edit.css?v=2">
</head>
<body class="edit-course-body">

<div class="edit-course-box">
    <h2>Edit Course</h2>

    <?php if ($message != "") { ?>
        <p class="course-message"><?php echo $message; ?></p>
    <?php } ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Course Title</label>
        <input type="text" name="c_name" value="<?php echo htmlspecialchars($course['c_name']); ?>" required>

        <label>Category</label>
        <select name="cat_id" required>
            <?php while ($cat = mysqli_fetch_assoc($category_result)) { ?>
                <option value="<?php echo $cat['cat_id']; ?>"
                    <?php if ($cat['cat_id'] == $course['cat_id']) echo "selected"; ?>>
                    <?php echo htmlspecialchars($cat['cat_name']); ?>
                </option>
            <?php } ?>
        </select>

        <label>Price</label>
        <input type="text" name="c_price" value="<?php echo htmlspecialchars($course['c_price']); ?>" required>

        <label>Current Image</label>
        <img src="<?php echo htmlspecialchars($current_image); ?>" class="current-img">

        <label>Change Image</label>
        <input type="file" name="c_image" accept="image/*">

        <label>Description</label>
        <textarea name="c_des" required><?php echo htmlspecialchars($course['c_des']); ?></textarea>

        <div class="edit-course-btns">
            <button type="submit" name="update_course">Update Course</button>
            <a href="admin_courses_view.php">Cancel</a>
        </div>

    </form>
</div>

</body>
</html>