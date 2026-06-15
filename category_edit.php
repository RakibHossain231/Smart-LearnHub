<?php
session_start();
include "db.php";

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$cat_id = $_GET['id'];

/* category fetch */
$sql = "SELECT * FROM categ WHERE cat_id = '$cat_id'";
$result = mysqli_query($conn, $sql);
$cat = mysqli_fetch_assoc($result);

if (!$cat) {
    die("Category not found");
}

$message = "";

/* update */
if (isset($_POST['update_category'])) {

    $cat_name = mysqli_real_escape_string($conn, $_POST['cat_name']);
    $cat_des = mysqli_real_escape_string($conn, $_POST['cat_des']);

    $update_sql = "UPDATE categ 
                   SET cat_name='$cat_name', cat_des='$cat_des' 
                   WHERE cat_id='$cat_id'";

    if (mysqli_query($conn, $update_sql)) {
        header("Location: category_management.php");
        exit();
    } else {
        $message = "Update failed";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Category</title>
    <link rel="stylesheet" href="category_edit.css">
</head>

<body class="course-create-body">

<div class="course-create-box">
    <h2>Edit Category</h2>

    <?php if ($message != "") { ?>
        <p class="course-message"><?php echo $message; ?></p>
    <?php } ?>

    <form method="POST">

        <label>Category Name</label>
        <input type="text" name="cat_name" value="<?php echo $cat['cat_name']; ?>" required>

        <label>Description</label>
        <textarea name="cat_des" required><?php echo $cat['cat_des']; ?></textarea>

        <div class="course-btn-group">
            <button type="submit" name="update_category">Update Category</button>
            <a href="category_management.php">Cancel</a>
        </div>

    </form>
</div>

</body>
</html>