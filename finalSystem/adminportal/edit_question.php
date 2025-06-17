<?php
include('../php/connection.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../php/logout.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;

if (!$id || !$exam_id) {
    echo "<script>alert('Invalid parameters.'); window.location.href='exam_category.php';</script>";
    exit;
}

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $q = mysqli_real_escape_string($conn, $_POST['question']);
    $o1 = mysqli_real_escape_string($conn, $_POST['opt1']);
    $o2 = mysqli_real_escape_string($conn, $_POST['opt2']);
    $o3 = mysqli_real_escape_string($conn, $_POST['opt3']);
    $o4 = mysqli_real_escape_string($conn, $_POST['opt4']);
    $ans = mysqli_real_escape_string($conn, $_POST['answer']);
    
    $update = "UPDATE questions SET
        question = '$q', opt1 = '$o1', opt2 = '$o2',
        opt3 = '$o3', opt4 = '$o4', answer = '$ans'
        WHERE id = $id";
    
    if (mysqli_query($conn, $update)) {
        echo "<script>alert('Question updated successfully.'); window.location='exam_question.php?exam_id=$exam_id';</script>";
        exit;
    } else {
        die("Error updating record: " . mysqli_error($conn));
    }
}

// Fetch question details
$res = mysqli_query($conn, "SELECT * FROM questions WHERE id = $id");
if (!$res || mysqli_num_rows($res) === 0) {
    echo "<script>alert('Question not found.'); window.location='exam_question.php?exam_id=$exam_id';</script>";
    exit;
}
$row = mysqli_fetch_assoc($res);
$question = $row['question'];
$opt1 = $row['opt1'];
$opt2 = $row['opt2'];
$opt3 = $row['opt3'];
$opt4 = $row['opt4'];
$answer = $row['answer'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit Question</title>
  <link rel="stylesheet" href="..\css\adminEC.css">
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <div>
      <div class="logo">ADMIN PANEL</div>
      <div class="nav-top">
        <button class="nav-btn" onclick="window.location.href='admindashboard.php'">Dashboard</button>
        <button class="nav-btn" onclick="window.location.href='adminadmission.php'">Manage Admission</button>
        <button class="nav-btn" onclick="window.location.href='admingrading.php'">Manage Grade</button>
        <button class="nav-btn" onclick="window.location.href='exam_category.php'">Manage Exam</button>
      </div>
    </div>
  </div>

  </div>

  <!-- Main area -->
  <div class="main">
    <!-- Topbar -->
    <div class="topbar">
      <div class="left">
        <button class="backbtn" onclick="history.back()">&larr; Back</button>
      </div>
      <div class="center"></div>
      <div class="right">
        <p>Welcome, <span><?php echo $_SESSION['admin']; ?></span></p>
        <a href="..\php\logout.php"><button class="btn font-weight-bold">Logout</button></a>
      </div>
    </div>

    <div class="subheader">
        <div>Edit Question</div>
    </div>

    <div class="content">
        <form action="" method="post">
            <div class="exam-container">
                <div class="exam-form">
                    <h3>Update Question</h3>
                    
                    <label>Question</label><br />
                    <input type="text" name="question" class="form-control" value="<?php echo htmlspecialchars($question); ?>" required />

                    <label>Option 1</label><br />
                    <input type="text" name="opt1" class="form-control" value="<?php echo htmlspecialchars($opt1); ?>" required />

                    <label>Option 2</label><br />
                    <input type="text" name="opt2" class="form-control" value="<?php echo htmlspecialchars($opt2); ?>" required />

                    <label>Option 3</label><br />
                    <input type="text" name="opt3" class="form-control" value="<?php echo htmlspecialchars($opt3); ?>" required />

                    <label>Option 4</label><br />
                    <input type="text" name="opt4" class="form-control" value="<?php echo htmlspecialchars($opt4); ?>" required />

                    <label>Answer</label><br />
                    <input type="text" name="answer" class="form-control" value="<?php echo htmlspecialchars($answer); ?>" required />

                    <input type="submit" value="Update Question" class="submit-btn" />
                </div>
            </div>
        </form>
    </div>
  </div>
</body>
</html>
