<?php
include('../php/connection.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['username'])) {
    echo "Invalid request.";
    exit();
}

$username = $_GET['username'];

$admission = $conn->query("SELECT * FROM admission_info WHERE username = '$username'")->fetch_assoc();
$personal = $conn->query("SELECT * FROM personal_info WHERE username = '$username'")->fetch_assoc();
$family = $conn->query("SELECT * FROM family_bg WHERE username = '$username'")->fetch_assoc();
$education = $conn->query("SELECT * FROM education_bg WHERE username = '$username'")->fetch_assoc();
$medical = $conn->query("SELECT * FROM med_his_info WHERE username = '$username'")->fetch_assoc();
$status = $conn->query("SELECT * FROM application_status WHERE username = '$username'")->fetch_assoc();
$control = $conn->query("SELECT control_number FROM check_status WHERE username = '$username'")->fetch_assoc();

$entry = $admission['entry'] ?? '';
$control_number = $control['control_number'] ?? 'N/A';

if ($entry === 'New') {
    $files = $conn->query("SELECT * FROM freshmen_files WHERE username = '$username'")->fetch_assoc();
} elseif ($entry === 'Transferee') {
    $files = $conn->query("SELECT * FROM transferee_files WHERE username = '$username'")->fetch_assoc();
} elseif ($entry === 'Second Courser') {
    $files = $conn->query("SELECT * FROM second_courser_files WHERE username = '$username'")->fetch_assoc();
} else {
    $files = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = isset($_POST['accept']) ? 1 : (isset($_POST['reject']) ? 3 : 0);
    $stmt = $conn->prepare("INSERT INTO application_status (username, status) VALUES (?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)");
    $stmt->bind_param("si", $username, $new_status);
    $stmt->execute();
    $stmt->close();

    header("Location: adminadmission.php");
    exit();
}

function formatLabel($key) {
    $map = [
        'gurdianname' => "Guardian's Name",
        'gnumber' => "Guardian's Number",
        'goccupation' => "Guardian's Occupation",
        'fathername' => "Father's Name",
        'fnumber' => "Father's Number",
        'foccupation' => "Father's Occupation",
        'mothername' => "Mother's Name",
        'mnumber' => "Mother's Number",
        'moccupation' => "Mother's Occupation",
        'fam_month_inc' => 'Family Monthly Income',
        'numsibling' => 'Number of Siblings',
        'birthorder' => 'Birth Order',
        'soloparent' => 'Solo Parent',
        'fam_work_abroad' => 'Family Working Abroad',
        'elemname' => 'Elementary School Name',
        'elemaddress' => 'Elementary Address',
        'elemyear' => 'Elementary Year Graduated',
        'elemtype' => 'Elementary Type',
        'midname' => 'Junior High School Name',
        'midaddress' => 'Junior High Address',
        'midyear' => 'Junior High Year Graduated',
        'midtype' => 'Junior High Type',
        'seniorname' => 'Senior High School Name',
        'senioraddress' => 'Senior High Address',
        'senioryear' => 'Senior High Year Graduated',
        'seniortype' => 'Senior High Type',
        'vocname' => 'Vocational School Name',
        'vocaddress' => 'Vocational Address',
        'vocyear' => 'Vocational Year Graduated',
        'voctype' => 'Vocational Type'
    ];
    return $map[$key] ?? ucfirst(str_replace('_', ' ', $key));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Profile</title>
  <link rel="stylesheet" href="../css/adminEC.css">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f5f6fa;
    }
    .content {
      background: #fff;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
      margin: 2rem;
    }
    .content h2 {
      margin-bottom: 1rem;
      color: #2c3e50;
    }
    .content h3 {
      margin-top: 2rem;
      margin-bottom: 1rem;
      color: #34495e;
      border-left: 4px solid #3498db;
      padding-left: 10px;
    }
    .grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }
    .grid-2 p {
      background: #f0f3f5;
      padding: 10px;
      border-radius: 5px;
    }
    .grid-2 p strong {
      display: block;
      margin-bottom: 5px;
      color: #2c3e50;
    }
    .status {
      font-weight: bold;
      padding: 5px 10px;
      border-radius: 5px;
      display: inline-block;
    }
    .status.pending { background: #f1c40f33; color: #f39c12; }
    .status.accepted { background: #2ecc7133; color: #27ae60; }
    .status.rejected { background: #e74c3c33; color: #c0392b; }
    form button {
      padding: 10px 20px;
      border: none;
      background: #3498db;
      color: white;
      border-radius: 5px;
      cursor: pointer;
      margin-top: 10px;
      margin-right: 10px;
    }
    form button[name="reject"] {
      background: #e74c3c;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <div class="logo">ADMIN PANEL</div>
    <div class="nav-top">
        <button class="nav-btn" onclick="window.location.href='admindashboard.php'">Dashboard</button>
        <button class="nav-btn" onclick="window.location.href='adminadmission.php'">Manage Admission</button>
        <button class="nav-btn" onclick="window.location.href='admingrading.php'">Manage Grade</button>
        <button class="nav-btn" onclick="window.location.href='exam_category.php'">Manage Exam</button>
    </div>
  </div>

  <div class="main">
    <div class="topbar">
      <button class="backbtn" onclick="history.back()">&larr; Back</button>
      <div class="right">
        <p>Welcome, <span><?php echo $_SESSION['admin']; ?></span></p>
        <a href="../php/logout.php"><button>Logout</button></a>
      </div>
    </div>

    <div class="content">
      <h2>Applicant Profile</h2>
      <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
      <p><strong>Control Number:</strong> <?php echo htmlspecialchars($control_number); ?></p>

      <h3>Personal Information</h3>
      <div class="grid-2">
      <?php foreach ($personal as $k => $v) echo "<p><strong>" . formatLabel($k) . ":</strong> " . htmlspecialchars($v) . "</p>"; ?>
      </div>

      <h3>Admission Information</h3>
      <div class="grid-2">
      <?php foreach ($admission as $k => $v) echo "<p><strong>" . formatLabel($k) . ":</strong> " . htmlspecialchars($v) . "</p>"; ?>
      </div>

      <h3>Family Background</h3>
      <div class="grid-2">
      <?php foreach ($family as $k => $v) echo "<p><strong>" . formatLabel($k) . ":</strong> " . htmlspecialchars($v) . "</p>"; ?>
      </div>

      <h3>Educational Background</h3>
      <div class="grid-2">
      <?php foreach ($education as $k => $v) echo "<p><strong>" . formatLabel($k) . ":</strong> " . htmlspecialchars($v) . "</p>"; ?>
      </div>

      <h3>Medical History</h3>
      <div class="grid-2">
      <?php foreach ($medical as $k => $v) echo "<p><strong>" . formatLabel($k) . ":</strong> " . htmlspecialchars($v) . "</p>"; ?>
      </div>

      <h3>Uploaded Files</h3>
      <div class="grid-2">
      <?php if ($files): foreach ($files as $label => $file): 
        if (!in_array($label, ['id', 'submitted_at', 'username', 'control_number']) && $file): ?>
          <p><strong><?php echo formatLabel($label); ?>:</strong> <a href="../uploads/<?php echo htmlspecialchars($file); ?>" target="_blank">View</a></p>
      <?php endif; endforeach; else: echo "<p>No files found.</p>"; endif; ?>
      </div>

      <h3>Application Status</h3>
      <?php
        $stat = $status['status'] ?? 0;
        $statText = $stat == 1 ? 'Accepted' : ($stat == 3 ? 'Rejected' : 'Pending');
        $statClass = $stat == 1 ? 'accepted' : ($stat == 3 ? 'rejected' : 'pending');
      ?>
      <p>Status: <span class="status <?php echo $statClass; ?>"><?php echo $statText; ?></span></p>

      <?php if (!isset($status['status']) || $status['status'] == 0): ?>
      <form method="POST">
        <button type="submit" name="accept">Accept</button>
        <button type="submit" name="reject">Reject</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
