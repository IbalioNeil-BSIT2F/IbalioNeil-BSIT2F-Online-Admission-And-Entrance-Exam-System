<?php
include("../php/connection.php");
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../php/logout.php");
    exit();
}

$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;

if (!$exam_id) {
    echo "<script>alert('Invalid exam ID.'); window.location='exam_category.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category']);
    $time_min = intval($_POST['time_min']);
    $schedule = $_POST['schedule'];
    $meeting_link = trim($_POST['meeting_link']);

    $stmt = $conn->prepare("UPDATE exam_category SET category = ?, time_min = ?, schedule = ?, meeting_link = ? WHERE exam_id = ?");
    $stmt->bind_param("sissi", $category, $time_min, $schedule, $meeting_link, $exam_id);

    if ($stmt->execute()) {
        echo "<script>alert('Exam updated successfully.'); window.location='exam_category.php';</script>";
        exit;
    } else {
        die("Error updating exam: " . $conn->error);
    }
}

$res = mysqli_query($conn, "SELECT * FROM exam_category WHERE exam_id = $exam_id");
if (!$res || mysqli_num_rows($res) === 0) {
    echo "<script>alert('Exam not found.'); window.location='exam_category.php';</script>";
    exit;
}

$row = mysqli_fetch_assoc($res);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit Exam</title>
  <link rel="stylesheet" href="../css/adminEC.css">
</head>
<body>

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

<div class="main">
  <div class="topbar">
    <div class="left">
      <button class="backbtn" onclick="history.back()">&larr; Back</button>
    </div>
    <div class="right">
      <p>Welcome, <span><?php echo htmlspecialchars($_SESSION['admin']); ?></span></p>
      <a href="../php/logout.php"><button class="btn font-weight-bold">Logout</button></a>
    </div>
  </div>

  <div class="subheader">
    <div>Edit Exam</div>
  </div>

  <div class="content">
    <form action="" method="post">
      <div class="exam-container">
        <div class="exam-form">
          <h2>Edit Exam Details</h2>

          <label>Category:</label><br />
          <input type="text" name="category" value="<?php echo htmlspecialchars($row['category']); ?>" required class="form-control" /><br />

          <label>Time (in minutes):</label><br />
          <input type="number" name="time_min" value="<?php echo intval($row['time_min']); ?>" required class="form-control" /><br />

          <label>Schedule (Date and Time):</label><br />
          <input type="datetime-local" name="schedule" value="<?php echo date('Y-m-d\TH:i', strtotime($row['schedule'])); ?>" required class="form-control" /><br />

          <label>Meeting Link (Zoom, Google Meet, etc.):</label><br />
          <input type="url" name="meeting_link" value="<?php echo htmlspecialchars($row['meeting_link']); ?>" placeholder="https://meet.google.com/abc-defg-hij" class="form-control" /><br />

          <div style="margin-top: 20px;">
            <button type="submit" class="submit-btn">Update Exam</button>
            <a href="exam_category.php" class="submit-btn" style="background-color: #ccc; color: #000; text-decoration: none; padding: 10px 20px; border-radius: 5px;">Cancel</a>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

</body>
</html>
