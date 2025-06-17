<?php
include('../php/connection.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../php/logout.php");
    exit();
}

if (!isset($_GET['exam_id']) || !is_numeric($_GET['exam_id'])) {
    echo "<script>alert('Invalid or missing exam ID.'); window.location.href='exam_category.php';</script>";
    exit();
}

$exam_id = intval($_GET['exam_id']);

// Fetch exam category
$res = mysqli_query($conn, "SELECT category, time_min FROM exam_category WHERE exam_id = $exam_id");
if (!$res || mysqli_num_rows($res) == 0) {
    echo "<script>alert('Exam not found.'); window.location.href='exam_category.php';</script>";
    exit();
}
$row = mysqli_fetch_assoc($res);
$exam_category = $row['category'];

// Add question handler
if (isset($_POST['addexambtn'])) {
    $question = mysqli_real_escape_string($conn, $_POST['question']);
    $opt1 = mysqli_real_escape_string($conn, $_POST['opt1']);
    $opt2 = mysqli_real_escape_string($conn, $_POST['opt2']);
    $opt3 = mysqli_real_escape_string($conn, $_POST['opt3']);
    $opt4 = mysqli_real_escape_string($conn, $_POST['opt4']);
    $answer = mysqli_real_escape_string($conn, $_POST['answer']);

    // Renumber
    $res1 = mysqli_query($conn, "SELECT id FROM questions WHERE exam_id = $exam_id ORDER BY question_no ASC");
    $loop = 0;
    while ($row1 = mysqli_fetch_assoc($res1)) {
        mysqli_query($conn, "UPDATE questions SET question_no = " . (++$loop) . " WHERE id = " . $row1['id']);
    }
    $loop++;

    $insert = "INSERT INTO questions (question_no, question, opt1, opt2, opt3, opt4, answer, exam_id) 
               VALUES ($loop, '$question', '$opt1', '$opt2', '$opt3', '$opt4', '$answer', $exam_id)";
    mysqli_query($conn, $insert) or die(mysqli_error($conn));

    echo "<script>alert('Question Added Successfully'); window.location.href = window.location.href;</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Manage Exam</title>
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
      <div class="center">
        <!-- Empty space -->
      </div>
      <div class="right">
        <p>Welcome, <span><?php echo $_SESSION['admin']; ?></span></p>
        <a href="..\php\logout.php"><button class="btn font-weight-bold">Logout</button></a>
      </div>
    </div>
    <div class="subheader">
        <div>Manage Exam</div>
    </div>

        <!-- Main content container -->
        <div class="content">
            <form action="" method="post">
                <div class="exam-container">
                    <!-- Add Exam Form -->
                    <div class="exam-form">
                        <h3>Add New Question inside <font color='red'><?php echo $exam_category;?></font></h3>
                        <label for="examname">Question</label><br />
                        <input type="text" name="question" placeholder="Add Question" class="form-control"/>

                        <label for="examname">Add Option 1</label><br />
                        <input type="text" name="opt1" placeholder="Add Option 1" class="form-control"/>

                        <label for="examname">Add Option 2</label><br />
                        <input type="text" name="opt2" placeholder="Add Option 2" class="form-control"/>

                        <label for="examname">Add Option 3</label><br />
                        <input type="text" name="opt3" placeholder="Add Option 3" class="form-control"/>

                        <label for="examname">Add Option 4</label><br />
                        <input type="text" name="opt4" placeholder="Add Option 4" class="form-control"/>

                        <label for="examname">Add Answer</label><br />
                        <input type="text" name="answer" placeholder="Add Answer" class="form-control"/>

                        <input type="submit" name="addexambtn" value="Add Question" class="submit-btn" />
                    </div>
                </div>
            </form>

            <!-- Edit Exam Forms -->
            <div class="exam-container edit-exam-section">
                <div class="exam-table">
                    <h3>Edit Exam Forms</h3>
                    <table>
                    <thead>
                        <tr>
                        <th>No.</th>
                        <th>Questions</th>
                        <th>Option1</th>
                        <th>Option2</th>
                        <th>Option3</th>
                        <th>Option4</th>
                        <th>Answer</th>
                        <th>Edit</th>
                        <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $res = mysqli_query($conn, "SELECT * FROM questions WHERE exam_id = $exam_id ORDER BY question_no ASC");
                        while ($row = mysqli_fetch_array($res)) {
                        ?>
                        <tr>
                            <td><?php echo $row['question_no']; ?></td>
                            <td><?php echo $row['question']; ?></td>
                            <td><?php echo $row['opt1']; ?></td>
                            <td><?php echo $row['opt2']; ?></td>
                            <td><?php echo $row['opt3']; ?></td>
                            <td><?php echo $row['opt4']; ?></td>
                            <td><?php echo $row['answer']; ?></td>
                            <td><a href="edit_question.php?id=<?php echo $row['id'];?>&exam_id=<?php echo $exam_id;?>">Select</a></td>
                            <td><a href="delete_question.php?id=<?php echo $row['id']; ?>&exam_id=<?php echo $exam_id;?>">Delete</a></td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

  </div>

</body>
</html>