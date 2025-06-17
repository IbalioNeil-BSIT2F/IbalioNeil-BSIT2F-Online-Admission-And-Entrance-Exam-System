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

    // Fetch exam schedule (if confirmed)
$sched_stmt = $conn->prepare("SELECT chosen_schedule FROM user_chosen_schedule WHERE email = ?");
$sched_stmt->bind_param("s", $email);
$sched_stmt->execute();
$sched_result = $sched_stmt->get_result();
$chosen_schedule = $sched_result->fetch_assoc()['chosen_schedule'] ?? 'Empty';

// Control number
$control_number = $status['control_number'] ?? 'Finish the Application';

// Application status: 0 = pending, 1 = accepted, 2 = rejected
$app_stmt = $conn->prepare("SELECT status FROM application_status WHERE username = ?");
$app_stmt->bind_param("s", $email);
$app_stmt->execute();
$app_result = $app_stmt->get_result();
$app_row = $app_result->fetch_assoc();
$status_code = $app_row['status'] ?? null;

if ($status_code === null) {
    $application_status = 'Not Available';
} else {
    $application_status = match ((int)$status_code) {
        0 => 'Pending',
        1 => 'Accepted',
        2 => 'Rejected',
        default => 'Unknown',
    };
}

?>
<?php include('../php/useradmissionheader.php'); ?>

    <!-- Main content container -->
    <div class="content">
      <div class="hero-text">
  <div class="card3">
    <h3>Welcome to Qupal University</h3>
    <p> You are formally submitting your application for admission to the university, signifying your intent to pursue academic advancement and personal development within our esteemed institution, where excellence, integrity, and innovation are the cornerstones of your educational journey.</p>
  </div>
</div>
      <!-- hero section -->
      <section class="hero">
        <div class="hero-text">
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
      </section>



      <!-- Dashboard Cards -->
      <div class="dashboard-cards">
  <div class="card">
    <h3>Exam Schedule</h3>
    <p><?php echo htmlspecialchars($chosen_schedule); ?></p>
  </div>
  <div class="card">
    <h3>Control Number</h3>
    <p><?php echo htmlspecialchars($control_number ?: 'Finish the Application'); ?></p>
  </div>
  <div class="card">
    <h3>Status</h3>
    <p><?php echo htmlspecialchars($application_status); ?></p>
  </div>
</div>
    </div>
  </div>

</body>
</html>