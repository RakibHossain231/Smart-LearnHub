<?php
session_start();

if (!isset($_SESSION['u_id']) || $_SESSION['role_id'] !== 'Student') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$u_id = $_SESSION['u_id'];
$c_id = isset($_GET['c_id']) ? intval($_GET['c_id']) : 0;

if ($c_id <= 0) {
    header("Location: student_index.php");
    exit();
}

/* Get student */
$student_q = $conn->query("SELECT * FROM student WHERE u_id = $u_id");

if (!$student_q || $student_q->num_rows == 0) {
    die("Student profile not found.");
}

$student = $student_q->fetch_assoc();
$s_id = $student['s_id'];

/* Get course */
$course_q = $conn->query("
    SELECT course.*, categ.cat_name
    FROM course
    LEFT JOIN categ ON course.cat_id = categ.cat_id
    WHERE course.c_id = $c_id
");

if (!$course_q || $course_q->num_rows == 0) {
    die("Course not found.");
}

$course = $course_q->fetch_assoc();

/* Check already enrolled */
$check_enroll = $conn->query("
    SELECT * FROM enrollment 
    WHERE c_id = $c_id AND s_id = $s_id
");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pay_now'])) {

    if ($check_enroll && $check_enroll->num_rows > 0) {
        header("Location: student_home.php");
        exit();
    }

    $insert = $conn->query("
        INSERT INTO enrollment (process, completed_at, c_id, s_id)
        VALUES (0, '0000-00-00', $c_id, $s_id)
    ");

    if ($insert) {
        header("Location: student_home.php");
        exit();
    } else {
        die("Enrollment failed: " . $conn->error);
    }
}

$course_name = $course['c_name'];
$course_price = $course['c_price'];
$category = $course['cat_name'] ?? 'Course';

$is_free = (
    $course_price == 0 ||
    $course_price == "0.00" ||
    strtolower((string)$course_price) == "free"
);

$display_price = $is_free ? "Free" : "$" . $course_price;
$pay_text = $is_free ? "Enroll Free" : "Pay $" . $course_price;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Page</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, sans-serif;
}

body{
    background:#f5f5f5;
    padding:30px;
}

.container{
    max-width:1100px;
    margin:auto;
}

.back{
    color:#555;
    margin-bottom:20px;
}

.back a{
    color:#555;
    text-decoration:none;
}

.main-box{
    display:flex;
    gap:25px;
    align-items:flex-start;
}

.payment-box{
    flex:2;
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
}

.summary-box{
    flex:1;
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
}

h2{
    margin-bottom:8px;
}

.small{
    color:#777;
    font-size:14px;
    margin-bottom:20px;
}

.methods{
    display:flex;
    gap:15px;
    margin-bottom:25px;
}

.method{
    flex:1;
    border:2px solid #ddd;
    padding:20px;
    text-align:center;
    border-radius:10px;
    cursor:pointer;
    transition:0.3s;
}

.method.active{
    border-color:#2563ff;
    background:#eef4ff;
}

.method:hover{
    border-color:#2563ff;
}

.form-group{
    margin-bottom:15px;
}

label{
    display:block;
    margin-bottom:6px;
    font-size:14px;
}

input{
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:8px;
}

.row{
    display:flex;
    gap:15px;
}

button{
    width:100%;
    padding:14px;
    border:none;
    border-radius:8px;
    background:linear-gradient(to right,#7b2cff,#c100ff);
    color:white;
    font-size:16px;
    cursor:pointer;
    margin-top:10px;
}

button:hover{
    opacity:0.9;
}

.summary-box h3{
    margin-bottom:15px;
}

.summary-line{
    display:flex;
    justify-content:space-between;
    margin:10px 0;
    color:#555;
}

.total{
    font-size:22px;
    color:#2563ff;
    font-weight:bold;
}

ul{
    margin-top:15px;
    padding-left:18px;
    color:green;
    line-height:1.8;
    font-size:14px;
}

.popup{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.5);
    display:none;
    justify-content:center;
    align-items:center;
}

.popup-box{
    background:#fff;
    padding:30px;
    border-radius:12px;
    width:350px;
    text-align:center;
}

.popup-box h3{
    margin-bottom:15px;
}

.popup-box p{
    color:#555;
    margin-bottom:20px;
}

.popup-btns{
    display:flex;
    gap:10px;
}

.popup-btns button{
    margin:0;
}

.cancel{
    background:#ccc;
    color:#000;
}

@media(max-width:800px){
    .main-box{
        flex-direction:column;
    }
}
</style>
</head>

<body>

<div class="container">

<div class="back">
    <a href="student_index.php?c_id=<?php echo $c_id; ?>">← Back to Course</a>
</div>

<div class="main-box">

<div class="payment-box">

<h2>Complete Your Purchase</h2>
<p class="small">Choose your preferred payment method</p>

<form method="POST" id="paymentForm">

<div class="methods">
<div class="method active" onclick="selectMethod(this,'card')">
💳<br><br>Payment Card
</div>

<div class="method" onclick="selectMethod(this,'mobile')">
📱<br><br>Mobile Banking
</div>
</div>

<!-- CARD -->
<div id="cardFields">
<div class="form-group">
<label>Card Number</label>
<input type="text" placeholder="1234 5678 9012 3456">
</div>

<div class="form-group">
<label>Cardholder Name</label>
<input type="text" placeholder="labiba zoha">
</div>

<div class="row">
<div class="form-group">
<label>Expiry Date</label>
<input type="text" placeholder="MM/YY">
</div>

<div class="form-group">
<label>CVV</label>
<input type="text" placeholder="123">
</div>
</div>
</div>

<!-- MOBILE -->
<div id="mobileFields" style="display:none;">
<div class="form-group">
<label>Mobile Number</label>
<input type="text" placeholder="01XXXXXXXXX">
</div>

<div class="form-group">
<label>Transaction ID</label>
<input type="text" placeholder="Enter transaction ID">
</div>
</div>



<input type="hidden" name="pay_now" value="1">

<button type="button" onclick="openConfirm()">
    <?php echo htmlspecialchars($pay_text); ?>
</button>

</form>

</div>

<div class="summary-box">

<h3>Order Summary</h3>

<p><b><?php echo htmlspecialchars($course_name); ?></b></p>
<p class="small"><?php echo htmlspecialchars($category); ?> Course</p>

<div class="summary-line">
<span>Course Price</span>
<span><?php echo htmlspecialchars($display_price); ?></span>
</div>

<div class="summary-line">
<span>Tax</span>
<span>$0.00</span>
</div>

<hr><br>

<div class="summary-line">
<span>Total</span>
<span class="total"><?php echo htmlspecialchars($display_price); ?></span>
</div>

<ul>
<li>Secure payment processing</li>
<li>Lifetime access included</li>
<li>Certificate of completion</li>
</ul>

</div>

</div>
</div>

<div class="popup" id="confirmPopup">
<div class="popup-box">
<h3>Confirm Enrollment</h3>
<p>Do you really want to enroll in this course?</p>

<div class="popup-btns">
<button type="button" class="cancel" onclick="closePopup()">
    Cancel
</button>

<button type="button" onclick="submitPayment()">
    Yes
</button>
</div>
</div>
</div>

<script>
function selectMethod(el, type){
    document.querySelectorAll('.method').forEach(box=>{
        box.classList.remove('active');
    });

    el.classList.add('active');

    if(type === 'card'){
        document.getElementById('cardFields').style.display = 'block';
        document.getElementById('mobileFields').style.display = 'none';
    } else {
        document.getElementById('cardFields').style.display = 'none';
        document.getElementById('mobileFields').style.display = 'block';
    }
}

function openConfirm(){
    document.getElementById("confirmPopup").style.display="flex";
}

function closePopup(){
    document.getElementById("confirmPopup").style.display="none";
}

function submitPayment(){
    document.getElementById("paymentForm").submit();
}
</script>
</body>
</html>