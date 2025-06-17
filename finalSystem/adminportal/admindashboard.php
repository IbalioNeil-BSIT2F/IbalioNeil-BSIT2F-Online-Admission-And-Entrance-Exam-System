<?php
include('..\php\connection.php');
session_start();

// Fetch counts
$pendingAdmission = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM application_status WHERE status = 0"));
$pendingGrades = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM application_status WHERE grade_status = 0"));
$passed = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM application_status WHERE grade_status = 1"));
$failed = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM application_status WHERE grade_status = 2"));
$uploadedExams = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM uploaded_exams"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="..\css\admin.css">
  <style>
    .dashboard-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      padding: 30px;
    }
    .dashboard-card {
      background-color: #f1f5fb;
      border-left: 6px solid #007bff;
      padding: 20px;
      border-radius: 10px;
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .dashboard-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 15px rgba(0,0,0,0.1);
    }
    .dashboard-title {
      font-size: 18px;
      font-weight: bold;
      color: #333;
    }
    .dashboard-number {
      font-size: 32px;
      color: #007bff;
      margin-top: 10px;
    }
  </style>
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

<!-- Main area -->
<div class="main">
  <!-- Topbar -->
  <div class="topbar">
    <div class="left"></div>
    <div class="center"></div>
    <div class="right">
      <p>Welcome, <span><?php echo $_SESSION['admin']; ?></span></p>
      <a href="..\php\logout.php"><button class="btn font-weight-bold">Logout</button></a>
    </div>
  </div>

  <!-- Dashboard Cards -->
  <div class="content">
    <div class="dashboard-container">
      <div class="dashboard-card" onclick="window.location.href='adminadmission.php'">
        <div class="dashboard-title">Pending Admission Applicants</div>
        <div class="dashboard-number"><?php echo $pendingAdmission; ?></div>
      </div>
      
      <div class="dashboard-card" onclick="window.location.href='admingrading.php'">
        <div class="dashboard-title">Exam Grade Pending</div>
        <div class="dashboard-number"><?php echo $pendingGrades; ?></div>
      </div>

      <div class="dashboard-card" onclick="window.location.href='exam_category.php'">
        <div class="dashboard-title">Uploaded Exams</div>
        <div class="dashboard-number"><?php echo $uploadedExams; ?></div>
      </div>

      <div class="dashboard-card" onclick="window.location.href='admingrading.php'">
        <div class="dashboard-title">Passed Applicants</div>
        <div class="dashboard-number"><?php echo $passed; ?></div>
      </div>

      <div class="dashboard-card" onclick="window.location.href='admingrading.php'">
        <div class="dashboard-title">Failed Applicants</div>
        <div class="dashboard-number"><?php echo $failed; ?></div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
