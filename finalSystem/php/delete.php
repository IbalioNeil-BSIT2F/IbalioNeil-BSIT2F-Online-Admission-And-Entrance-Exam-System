<?php
include("connection.php");
$msg = '';

if (isset($_GET['exam_id'])) {
    $exam_id = intval($_GET['exam_id']);
    mysqli_query($conn, "DELETE FROM exam_category WHERE exam_id = $exam_id");
    $msg = "Exam and related questions deleted successfully.";
} else {
    $msg = "No exam ID provided.";
}
?>
<script>
alert("<?php echo $msg; ?>");
window.location="../adminportal/exam_category.php";
</script>
