<link rel="stylesheet" href="courses_assign_popup.css?v=1">

<div class="assign-popup-overlay" id="assignPopup">
    <div class="assign-popup-box">
        <div class="assign-check-circle">✓</div>
        <h2 id="assignPopupTitle">Assign Teacher!</h2>
        <p id="assignPopupDate"></p>
        <button type="button" onclick="closeAssignPopup()">Ok</button>
    </div>
</div>

<script>
function approveAssign(id) {
    fetch('course_assign_action.php?id=' + id + '&status=Approved')
        .then(() => {
            showAssignPopup('Assign Teacher!');
        });
}

function rejectAssign(id) {
    fetch('course_assign_action.php?id=' + id + '&status=Rejected')
        .then(() => {
            showAssignPopup('Rejected!');
        });
}

function showAssignPopup(title) {
    const now = new Date();

    document.getElementById('assignPopupTitle').innerText = title;
    document.getElementById('assignPopupDate').innerText =
        now.toLocaleDateString('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        }) + ' at ' + now.toLocaleTimeString('en-US');

    document.getElementById('assignPopup').style.display = 'flex';
}

function closeAssignPopup() {
    document.getElementById('assignPopup').style.display = 'none';
    location.reload();
}
</script>