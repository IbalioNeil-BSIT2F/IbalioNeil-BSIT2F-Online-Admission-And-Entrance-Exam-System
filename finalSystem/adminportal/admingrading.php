<?php
include('..\php\connection.php');
session_start();

// Handle grade status update
if (isset($_GET['confirm']) && isset($_GET['username']) && isset($_GET['grade_status'])) {
    $username = $_GET['username'];
    $grade_status = intval($_GET['grade_status']);

    $update = $conn->prepare("UPDATE application_status SET grade_status=? WHERE username=?");
    $update->bind_param("is", $grade_status, $username);
    $update->execute();
    header("Location: admingrading.php");
    exit();
}

// Fetch Pending Users (only submitted exams)
$pending_stmt = $conn->prepare("
    SELECT a.username, 
           CONCAT(p.lastname, ', ', p.firstname, ' ', COALESCE(p.middlename, '')) AS fullname,
           ad.entry, ad.program
    FROM application_status a
    JOIN exam_attempts e ON a.username = e.email
    JOIN personal_info p ON a.username = p.username
    JOIN admission_info ad ON a.username = ad.username
    WHERE a.grade_status = 0 AND e.is_submitted = 1
    GROUP BY a.username
");
$pending_stmt->execute();
$pending_users = $pending_stmt->get_result();

// Fetch Passed Users
$passed_users = fetchUsersByStatus($conn, 1);

// Fetch Failed Users
$failed_users = fetchUsersByStatus($conn, 2);

// Function to fetch Passed/Failed users with full details
function fetchUsersByStatus($conn, $status) {
    $stmt = $conn->prepare("
        SELECT a.username, 
               CONCAT(p.lastname, ', ', p.firstname, ' ', COALESCE(p.middlename, '')) AS fullname,
               ad.entry, ad.program
        FROM application_status a
        JOIN personal_info p ON a.username = p.username
        JOIN admission_info ad ON a.username = ad.username
        WHERE a.grade_status = ?
    ");
    $stmt->bind_param("i", $status);
    $stmt->execute();
    return $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Grading Management</title>
  <link rel="stylesheet" href="..\css\adminEC.css">
  <style>
    table { width: 100%; border-collapse: collapse; margin-bottom: 2rem; }
    th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
    th { background: #f2f2f2; }
    .check-link { color: blue; text-decoration: underline; cursor: pointer; }
  </style>
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
    <button class="backbtn" onclick="history.back()">&larr; Back</button>
    <div class="center"></div>
    <div class="right">
      <p>Welcome, <span><?php echo $_SESSION['admin']; ?></span></p>
      <a href="..\php\logout.php"><button class="btn font-weight-bold">Logout</button></a>
    </div>
  </div>
  <div class="subheader">
    <h2>Grading Management</h2>
  </div>

  <div class="content">
    <div class="exam-container">
    <div class="exam-table">
        <h2>Pending Applicants</h2>
        <table>
        <tr><th>Username</th><th>Full Name</th><th>Entry</th><th>Program</th><th>Action</th></tr>
        <?php while($row = $pending_users->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
            <td><?php echo htmlspecialchars($row['entry']); ?></td>
            <td><?php echo htmlspecialchars($row['program']); ?></td>
            <td><a class="check-link" href="view_exam.php?username=<?php echo urlencode($row['username']); ?>">Confirm</a></td>
        </tr>
        <?php endwhile; ?>
        </table>
    </div>
    <div class="exam-table">
        <h2>Passed Applicants</h2>
        <table>
        <tr><th>Username</th><th>Full Name</th><th>Entry</th><th>Program</th></tr>
        <?php while($row = $passed_users->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
            <td><?php echo htmlspecialchars($row['entry']); ?></td>
            <td><?php echo htmlspecialchars($row['program']); ?></td>
        </tr>
        <?php endwhile; ?>
        </table>
    </div>
    <div class="exam-table">
        <h2>Failed Applicants</h2>
        <table>
        <tr><th>Username</th><th>Full Name</th><th>Entry</th><th>Program</th></tr>
        <?php while($row = $failed_users->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
            <td><?php echo htmlspecialchars($row['entry']); ?></td>
            <td><?php echo htmlspecialchars($row['program']); ?></td>
        </tr>
        <?php endwhile; ?>
        </table>
    </div>

    </div>
  </div>
</div>

</body>
</html>
