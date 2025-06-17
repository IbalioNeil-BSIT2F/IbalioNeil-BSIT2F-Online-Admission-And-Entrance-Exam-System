<?php
include("../php/connection.php");
$id = $_GET['id'];
$id1 = $_GET['id1'];
mysqli_query($conn,"DELETE FROM questions WHERE id = $id");
?>
<script type="text/javascript">
    window.location = "exam_question.php?exam_id=<?php echo $id1?>";
</script>