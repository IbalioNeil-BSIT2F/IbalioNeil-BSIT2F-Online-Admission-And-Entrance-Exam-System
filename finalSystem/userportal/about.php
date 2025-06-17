<?php
    include('..\php\connection.php');
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>About Us - Qupal University</title>
  <link rel="stylesheet" href="..\css\user.css">
  <style>
    .content {
      padding: 40px;
      color: #333;
      line-height: 1.8;
    }
    h1, h2 {
      color: #004aad;
    }
    .section {
      margin-bottom: 40px;
    }
    .info-box {
      background-color: #f4f8fc;
      padding: 20px;
      border-radius: 10px;
    }
    .contact p, .admin p {
      margin: 5px 0;
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

  <!-- Main area -->
  <div class="main">
    <!-- Topbar -->
    <div class="topbar">
      <div class="left">
        <button class="backbtn" onclick="history.back()">← Back</button>
      </div>
      <div class="center"></div>
      <div class="right">
        <p>Welcome, <span><?php echo $_SESSION['user']; ?></span></p>
        <a href="..\php\logout.php"><button class="btn font-weight-bold">Logout</button></a>
      </div>
    </div>

    <!-- Main content -->
    <div class="content">
      <div class="section">
        <h1>About Qupal University</h1>
        <div class="info-box">
          <p>Qupal University is committed to academic excellence and student success. We offer a diverse range of programs designed to equip students with the skills and knowledge needed to thrive in today's competitive world.</p>
          <p>Our programs include:</p>
          <ul>
            <li>Bachelor of Science in Computer Science</li>
            <li>Bachelor of Science in Information Technology</li>
            <li>Bachelor of Secondary Education</li>
            <li>Bachelor of Elementary Education</li>
            <li>Bachelor of Arts in Journalism</li>
          </ul>
          <p>Our experienced faculty and supportive staff are dedicated to creating a nurturing and stimulating learning environment, providing students with the foundation to succeed academically and professionally.</p>
        </div>
      </div>

      <div class="section">
        <h2>Mission</h2>
        <div class="info-box">
          <p>To provide world-class education in Computer Science while nurturing a community grounded in the knowledge, wisdom, and reverence of God.</p>
        </div>
      </div>

      <div class="section">
        <h2>Vision</h2>
        <div class="info-box">
          <p>To become a leading institution where innovation in technology meets faith, producing ethical leaders who glorify God through excellence in computing and teaching.</p>
        </div>
      </div>

      <div class="section contact">
        <h2>Contact Us</h2>
        <div class="info-box">
          <p><strong>Phone:</strong> 09123456789</p>
          <p><strong>Email:</strong> QupalUniversityCollege@gmail.com</p>
          <p><strong>Website:</strong> <a href="http://www.qupaluniversity.edu.ph" target="_blank">www.qupaluniversity.edu.ph</a></p>
        </div>
      </div>

      <div class="section admin">
        <h2>University Administration</h2>
        <div class="info-box">
          <p><strong>Founder:</strong> Neil Vincent D. Ibalio</p>
          <p><strong>President:</strong> Elijah Rafayel Robles</p>
          <p><strong>Vice President:</strong> Nicole Madlangbayan</p>
          <p><strong>Dean:</strong> Vash Aaron Kei D. Manguerra</p>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
