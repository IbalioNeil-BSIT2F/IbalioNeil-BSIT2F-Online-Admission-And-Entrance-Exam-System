<?php
    include('..\php\connection.php');
    session_start();

    if (!isset($_SESSION['user'])) {
        header("Location: ../login.php");
        exit();
    }

    $email = $_SESSION['user'];

    $stmt = $conn->prepare("SELECT * FROM check_status WHERE username = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $status = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Dashboard</title>
  <link rel="stylesheet" href="..\css\useradmission.css">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f8f9fa;
      margin: 0;
      padding: 0;
    }

    

    h2 {
      text-align: center;
      color: #333;
      margin-bottom: 40px;
    }

    .dashboard-container {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 30px;
    }

    .circle-box {
      display: flex;
      flex-direction: column;
      align-items: center;
      transition: transform 0.2s;
    }

    .circle-box:hover {
      transform: scale(1.05);
    }

    .circle {
      width: 90px;
      height: 90px;
      border-radius: 50%;
      background-color: #e0e0e0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      font-weight: bold;
      color: white;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      transition: background-color 0.3s;
    }

    .completed {
      background-color: #007bff; /* Modern blue */
    }

    .circle-label {
      margin-top: 12px;
      font-size: 14px;
      color: #555;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .topbar .right p {
      font-weight: 500;
    }

    .backbtn {
      background: none;
      border: none;
      color: #007bff;
      font-size: 16px;
      cursor: pointer;
    }

    .backbtn:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <div>
      <div class="logo">LOGO</div>
      <div class="nav-top">
        <button class="nav-btn" onclick="window.location.href='userdashboard.php'">Dashboard</button>
        <button class="nav-btn" onclick="window.location.href='useradmission.php'">Admission Overview</button>
        <button class="nav-btn" onclick="window.location.href='userprodandprog.php'">Procedures and Programs</button>
      </div>
    </div>
    <div class="nav-bottom">
      <button class="nav-btn">Settings</button>
      <button class="nav-btn">Help</button>
    </div>
  </div>

  <!-- Main -->
  <div class="main">
    <div class="topbar">
      <div class="left">
        <button class="backbtn">← Back</button>
      </div>
      <div class="center"></div>
      <div class="right">
        <p>Welcome, <span><?php echo htmlspecialchars($_SESSION['user']); ?></span></p>
        <a href="..\php\logout.php"><button class="btn font-weight-bold">Logout</button></a>
      </div>
    </div>

    <div class="container-box">
    <div class="content">
      <h2>Application Progress</h2>
      <div class="dashboard-container">
        <div class="circle-box">
          <div class="circle <?php echo $status['admission_info_completed'] ? 'completed' : ''; ?>">✓</div>
          <div class="circle-label">Admission</div>
        </div>
        <div class="circle-box">
          <div class="circle <?php echo $status['personal_info_completed'] ? 'completed' : ''; ?>">✓</div>
          <div class="circle-label">Personal</div>
        </div>
        <div class="circle-box">
          <div class="circle <?php echo $status['family_bg_completed'] ? 'completed' : ''; ?>">✓</div>
          <div class="circle-label">Family</div>
        </div>
        <div class="circle-box">
          <div class="circle <?php echo $status['education_bg_completed'] ? 'completed' : ''; ?>">✓</div>
          <div class="circle-label">Education</div>
        </div>
        <div class="circle-box">
          <div class="circle <?php echo $status['med_his_info_completed'] ? 'completed' : ''; ?>">✓</div>
          <div class="circle-label">Medical</div>
        </div>
        
      </div>
    </div>
  </div>
    <div class="content">
  <div class="card">
    <h2>Available courses</h2>
  </div>

  <div class="card">
    <h2>Available courses</h2>
  </div>
</div>

    
  </div>
  </div>

  

</body>
</html>
