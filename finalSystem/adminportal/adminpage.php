<?php
    include('..\php\connection.php');
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Web Layout</title>
  <link rel="stylesheet" href="..\css\admin.css">
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <div>
      <div class="logo">LOGO</div>
      <div class="nav-top">
        <button class="nav-btn" onclick="window.location.href='admindashboard.php'">Dashboard</button>
        <button class="nav-btn" onclick="window.location.href='adminadmission.php'">Manage Admission</button>
        <button class="nav-btn" onclick="window.location.href='admingrading.php'">Manage Grade</button>
        <button class="nav-btn" onclick="window.location.href='exam_category.php'">Manage Exam</button>
      </div>
    </div>
    <div class="nav-bottom">
      <button class="nav-btn">Settings</button>
      <button class="nav-btn">Help</button>
    </div>
  </div>

  <!-- Main area -->
  <div class="main">
    <!-- Topbar -->
    <div class="topbar">
      <div class="left">
        <button class="backbtn">← Back</button>
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
        <div>Sub-header or Navigation</div>
    </div>

        <!-- Main content container -->
        <div class="content">

        <div class="maincontainer">
            
        </div>
    </div>

  </div>

</body>
</html>