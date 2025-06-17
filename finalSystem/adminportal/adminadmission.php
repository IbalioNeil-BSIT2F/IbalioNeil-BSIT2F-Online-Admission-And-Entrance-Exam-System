<?php
include('../php/connection.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

function fetch_students_by_status($conn, $status) {
    $stmt = $conn->prepare("SELECT a.username, c.control_number, pi.firstname, pi.lastname, ai.entry, ai.type FROM application_status a
                            JOIN check_status c ON a.username = c.username
                            JOIN personal_info pi ON a.username = pi.username
                            JOIN admission_info ai ON a.username = ai.username
                            WHERE a.status = ?");
    $stmt->bind_param("i", $status);
    $stmt->execute();
    return $stmt->get_result();
}

$pending_students = fetch_students_by_status($conn, 0);
$accepted_students = fetch_students_by_status($conn, 1);
$rejected_students = fetch_students_by_status($conn, 3);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Admissions</title>
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
      <h2>Manage Admission</h2>
    </div>
    <div class="content">
      <div class="exam-container">
        <!-- Exam List (Pending, Accepted, Rejected Applicants) -->
        <div class="exam-table">
          <h2>Pending Applicants</h2>
          <table>
            <thead>
              <tr>
                <th>Control No.</th>
                <th>Name</th>
                <th>Entry</th>
                <th>Type</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $pending_students->fetch_assoc()): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['control_number']); ?></td>
                  <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                  <td><?php echo htmlspecialchars($row['entry']); ?></td>
                  <td><?php echo htmlspecialchars($row['type']); ?></td>
                  <td><a href="view_profile.php?username=<?php echo urlencode($row['username']); ?>">View</a></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <div class="exam-table">
          <h2>Accepted Applicants</h2>
          <table>
            <thead>
              <tr>
                <th>Control No.</th>
                <th>Name</th>
                <th>Entry</th>
                <th>Type</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $accepted_students->fetch_assoc()): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['control_number']); ?></td>
                  <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                  <td><?php echo htmlspecialchars($row['entry']); ?></td>
                  <td><?php echo htmlspecialchars($row['type']); ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <div class="exam-table">
          <h2>Rejected Applicants</h2>
          <table>
            <thead>
              <tr>
                <th>Control No.</th>
                <th>Name</th>
                <th>Entry</th>
                <th>Type</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $rejected_students->fetch_assoc()): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['control_number']); ?></td>
                  <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                  <td><?php echo htmlspecialchars($row['entry']); ?></td>
                  <td><?php echo htmlspecialchars($row['type']); ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</body>
</html>
