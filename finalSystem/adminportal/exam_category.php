<?php
include('../php/connection.php');
session_start();

// Redirect to login if admin session not set
if (!isset($_SESSION['admin'])) {
    header("Location: ../php/logout.php");
    exit();
}

$msg = '';

// Fetch already uploaded exam IDs first
$already_uploaded_ids = [];
$uploaded_res = mysqli_query($conn, "SELECT exam_id FROM uploaded_exams");
while ($row = mysqli_fetch_assoc($uploaded_res)) {
    $already_uploaded_ids[] = $row['exam_id'];
}

// Add Exam Logic
if (isset($_POST['addexambtn'])) {
    $examname = trim($_POST['examname']);
    $examtime = trim($_POST['examtime']);
    $schedule = $_POST['schedule'];
    $video_link = trim($_POST['meeting_link']);

    if (!empty($examname) && !empty($examtime) && is_numeric($examtime) && !empty($schedule)) {
        $stmt = $conn->prepare("INSERT INTO exam_category (category, time_min, schedule, meeting_link) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $examname, $examtime, $schedule, $video_link);
        $stmt->execute();
        $msg = "Exam added successfully.";
    } else {
        $msg = "Please fill in all fields correctly.";
    }
}

// Upload Exam Logic
if (isset($_GET['upload_exam_id'])) {
    $upload_id = intval($_GET['upload_exam_id']);
    $exam_query = mysqli_query($conn, "SELECT * FROM exam_category WHERE exam_id = $upload_id");

    if ($exam_query && mysqli_num_rows($exam_query) > 0) {
        $exam = mysqli_fetch_assoc($exam_query);

        // Check if already uploaded
        $check_uploaded = mysqli_query($conn, "SELECT * FROM uploaded_exams WHERE exam_id = $upload_id");
        if (mysqli_num_rows($check_uploaded) > 0) {
            $msg = "This exam has already been uploaded.";
        } else {
            $stmt = $conn->prepare("INSERT INTO uploaded_exams (exam_id, category, time_min, schedule, meeting_link, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("isiss", $exam['exam_id'], $exam['category'], $exam['time_min'], $exam['schedule'], $exam['meeting_link']);

            if ($stmt->execute()) {
                header("Location: exam_category.php?uploaded=1");
                exit();
            } else {
                $msg = "Failed to upload exam.";
            }
        }
    } else {
        $msg = "Exam not found.";
    }
}

// Unupload Exam Logic
if (isset($_GET['unupload_exam_id'])) {
    $unupload_id = intval($_GET['unupload_exam_id']);
    $stmt = $conn->prepare("DELETE FROM uploaded_exams WHERE exam_id = ?");
    $stmt->bind_param("i", $unupload_id);
    if ($stmt->execute()) {
        header("Location: exam_category.php?unuploaded=1");
        exit();
    } else {
        $msg = "Failed to unupload exam.";
    }
}

// Duplicate Exam Logic
if (isset($_GET['duplicate_exam_id'])) {
    $dup_id = intval($_GET['duplicate_exam_id']);

    $exam_query = mysqli_query($conn, "SELECT * FROM exam_category WHERE exam_id = $dup_id");
    if ($exam_query && mysqli_num_rows($exam_query) > 0) {
        $exam = mysqli_fetch_assoc($exam_query);

        $new_category = $exam['category'];
        $time_min = $exam['time_min'];
        $schedule = $exam['schedule'];
        $video_link = $exam['meeting_link'];

        $stmt = $conn->prepare("INSERT INTO exam_category (category, time_min, schedule, meeting_link) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $new_category, $time_min, $schedule, $video_link);
        if ($stmt->execute()) {
            $new_exam_id = $stmt->insert_id;

            $question_query = mysqli_query($conn, "SELECT * FROM questions WHERE exam_id = $dup_id");
            while ($question = mysqli_fetch_assoc($question_query)) {
                $q_stmt = $conn->prepare("INSERT INTO questions (exam_id, question, opt1, opt2, opt3, opt4, answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $q_stmt->bind_param(
                    "issssss",
                    $new_exam_id,
                    $question['question'],
                    $question['opt1'],
                    $question['opt2'],
                    $question['opt3'],
                    $question['opt4'],
                    $question['answer']
                );
                $q_stmt->execute();
            }
            $msg = "Exam and questions duplicated successfully.";
        } else {
            $msg = "Failed to duplicate exam.";
        }
    } else {
        $msg = "Original exam not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Manage Exam</title>
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
    <div class="center"></div>
    <div class="right">
      <p>Welcome, <span><?php echo htmlspecialchars($_SESSION['admin']); ?></span></p>
      <a href="../php/logout.php"><button class="btn font-weight-bold">Logout</button></a>
    </div>
  </div>

  <div class="subheader">
    <h2>Manage Exam</h2>
  </div>

  <div class="content">
    <form method="post" action="">
      <div class="exam-container">

        <!-- ADD EXAM FORM -->
        <div class="exam-form">
          <h3>Add Exam</h3>

          <label for="examname">New Exam Category</label><br />
          <select name="examname" class="form-control" required>
            <option value="">Select Course</option>
            <option value="BSBM">BSBM</option>
            <option value="BSCS">BSCS</option>
            <option value="BSCE">BSCE</option>
            <option value="BSIT">BSIT</option>
          </select>

          <label for="examtime">Exam Time In Minutes</label><br />
          <input type="number" name="examtime" step="10" min="10" max="120"
                placeholder="Exam Time In Minutes" class="form-control" required />

          <label for="schedule">Schedule (Future Date)</label><br />
          <input type="datetime-local" name="schedule" class="form-control"
                required min="<?php echo date('Y-m-d\TH:i'); ?>" />

          <label for="meeting_link">Video Call Link (Optional)</label><br />
          <input type="url" name="meeting_link" placeholder="Google Meet / Zoom URL"
                class="form-control" />

          <input type="submit" name="addexambtn" value="Add Exam" class="submit-btn" />
          <p class="msg"><?php echo $msg; ?></p>
        </div>

        <!-- EXISTING EXAM TABLE -->
        <div class="exam-table">
          <h2>Exam Categories</h2>
          <table border="1" cellpadding="10">
            <tr>
              <th>Exam ID</th>
              <th>Category</th>
              <th>Time (min)</th>
              <th>Schedule</th>
              <th>Video Link</th>
              <th>Actions</th>
            </tr>
            <?php
            $res = mysqli_query($conn, "SELECT * FROM exam_category ORDER BY exam_id DESC");
            while ($row = mysqli_fetch_assoc($res)) {
                $exam_id = $row['exam_id'];
                $category = htmlspecialchars($row['category']);
                $time_min = $row['time_min'];
                $schedule = $row['schedule'];
                $video_link = $row['meeting_link'];
                $is_uploaded = in_array($exam_id, $already_uploaded_ids);
            ?>
            <tr>
              <td><?php echo $exam_id; ?></td>
              <td><?php echo $category; ?></td>
              <td><?php echo $time_min; ?></td>
              <td><?php echo $schedule; ?></td>
              <td><?php echo htmlspecialchars($video_link); ?></td>
              <td>
                <?php if (!$is_uploaded) { ?>
                  <a href="edit_exam.php?exam_id=<?php echo $exam_id; ?>">Edit</a> |
                  <a href="../php/delete.php?exam_id=<?php echo $exam_id; ?>"
                    onclick="return confirm('Delete this exam and all its questions?');">Delete</a> |
                  <a href="exam_category.php?upload_exam_id=<?php echo $exam_id; ?>"
                    onclick="return confirm('Upload this exam and remove from editable list?');">Upload</a> |
                  <a href="exam_category.php?duplicate_exam_id=<?php echo $exam_id; ?>"
                    onclick="return confirm('Duplicate this exam and its questions?');">Duplicate</a>
                <?php } else { ?>
                  <span style="color: gray;">Uploaded</span>
                <?php } ?>
                | <a href="exam_question.php?exam_id=<?php echo $exam_id; ?>">Manage Questions</a>
              </td>
            </tr>
            <?php } ?>
          </table>
        </div>

        <!-- UPLOADED EXAM TABLE -->
        <div class="exam-table">
          <h2>Uploaded Exams</h2>
          <table border="1" cellpadding="10">
            <tr>
              <th>Upload ID</th>
              <th>Exam ID</th>
              <th>Category</th>
              <th>Time (min)</th>
              <th>Schedule</th>
              <th>Video Link</th>
              <th>Uploaded At</th>
              <th>Action</th>
            </tr>
            <?php
            $res_uploaded = mysqli_query($conn, "SELECT * FROM uploaded_exams ORDER BY upload_id DESC");
            while ($row = mysqli_fetch_assoc($res_uploaded)) {
                $upload_id = $row['upload_id'];
                $exam_id = $row['exam_id'];
                $category = htmlspecialchars($row['category']);
                $time_min = $row['time_min'];
                $schedule = $row['schedule'];
                $video_link = $row['meeting_link'];
                $uploaded_at = $row['uploaded_at'];
            ?>
            <tr>
              <td><?php echo $upload_id; ?></td>
              <td><?php echo $exam_id; ?></td>
              <td><?php echo $category; ?></td>
              <td><?php echo $time_min; ?></td>
              <td><?php echo $schedule; ?></td>
              <td><?php echo htmlspecialchars($video_link); ?></td>
              <td><?php echo $uploaded_at; ?></td>
              <td>
                <a href="exam_category.php?unupload_exam_id=<?php echo $exam_id; ?>" onclick="return confirm('Unupload this exam and make it editable again?');">Unupload</a>
              </td>
            </tr>
            <?php } ?>
          </table>
        </div>

      </div>
    </form>
  </div>

</div>

</body>
</html>
