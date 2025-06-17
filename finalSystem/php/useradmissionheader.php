<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admission Overview</title>
  <link rel="stylesheet" href="..\css\useradmission.css">
</head>
<body>

  <div class="sidebar">
    <div>
<div class="logo">
  <img src="../image/logo.png" alt="Qupal University Logo" class="logo-img">
</div>
      <div class="nav-top">
        <button class="nav-btn" onclick="window.location.href='userdashboard.php'">Dashboard</button>
        <button class="nav-btn" onclick="window.location.href='useradmission.php'">Admission Overview</button>
        <button class="nav-btn" onclick="window.location.href='userprodandprog.php'">Procedures and Programs</button>
      </div>
    </div>
    <div class="nav-bottom">
      <button class="backbtn" onclick="history.back()">&larr; Back</button>
    </div>
  </div>

  <div class="main">
    <div class="topbar">
      <div class="left">
        <button class="backbtn">← Back</button>
      </div>
      <div class="right">
        <p>Welcome, <span><?php echo htmlspecialchars($_SESSION['user']); ?></span></p>
        <a href="..\php\logout.php"><button class="btn font-weight-bold">Logout</button></a>
      </div>
    </div>