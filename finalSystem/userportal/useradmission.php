<?php
include('..\php\connection.php');
include('..\php\check_status_helper.php');
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['user'];

// Handle control number button click
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_cn'])) {
    $stmt = $conn->prepare("UPDATE check_status SET control_number_click = 1 WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    // Refresh page to trigger generation
    header("Location: useradmission.php");
    exit();
}

// Fetch current check_status
$stmt = $conn->prepare("SELECT * FROM check_status WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$control_number = '';
$control_number_click = 0;
$allCompleted = false;
$progress = 0;
$status = null;

if ($row = $result->fetch_assoc()) {
    $status = $row;
    $control_number = $row['control_number'];
    $control_number_click = $row['control_number_click'];
    $filled = [
        'Admission Information' => $row['admission_info_completed'],
        'Personal Information' => $row['personal_info_completed'],
        'Family Background' => $row['family_bg_completed'],
        'Educational Background' => $row['education_bg_completed'],
        'Medical History Information' => $row['med_his_info_completed']
    ];
    $progress = array_sum($filled);
    $allCompleted = $progress === 5;
} else {
    $filled = [
        'Admission Information' => 0,
        'Personal Information' => 0,
        'Family Background' => 0,
        'Educational Background' => 0,
        'Medical History Information' => 0
    ];
}

// Only generate control number if click is made and all forms are done
checkAndGenerateControlNumber($conn, $username);

// Re-fetch in case control_number just generated
$stmt = $conn->prepare("SELECT control_number FROM check_status WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$control_result = $stmt->get_result();
if ($control = $control_result->fetch_assoc()) {
    if (!empty($control['control_number'])) {
        header("Location: useradmission2.php");
        exit();
    }
}

// Fetch user info
$stmt = $conn->prepare("SELECT * FROM accounts WHERE email = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();

if (!$status) {
    $status = [
        'admission_info_completed' => 0,
        'personal_info_completed' => 0,
        'family_bg_completed' => 0,
        'education_bg_completed' => 0,
        'med_his_info_completed' => 0,
        'control_number_click' => 0,
        'control_number' => '',
        'current_stage' => 0
    ];
}

function renderStepProgress($status) {
    if (!$status) return;

    $step1Complete = (
        $status['admission_info_completed'] &&
        $status['personal_info_completed'] &&
        $status['family_bg_completed'] &&
        $status['education_bg_completed'] &&
        $status['med_his_info_completed']
    );
    $step2Complete = ($status['control_number_click'] == 1 && !empty($status['control_number']));
    $step3Complete = ($status['current_stage'] >= 3);
    $step4Complete = ($status['current_stage'] >= 4);

    $steps = [
        ['label' => 'Applicant Information', 'complete' => $step1Complete],
        ['label' => 'Requirements', 'complete' => $step2Complete],
        ['label' => 'Entrance Exam', 'complete' => $step3Complete],
        ['label' => 'Exam Results', 'complete' => $step4Complete],
    ];

    echo '<section class="hero"><div class="center-wrapper"><div class="dashboard-container">';
    foreach ($steps as $i => $step) {
        $circleClass = $step['complete'] ? 'circle completed' : 'circle';
        echo '<div class="circle-box">';
        echo '<div class="' . $circleClass . '">' . ($i + 1) . '</div>';
        echo '<div class="circle-label">' . htmlspecialchars($step['label']) . '</div>';
        echo '</div>';
    }
    echo '</div></div></section>';
}
?>

<?php include('../php/useradmissionheader.php'); ?>


    <div class="content">
      <?php renderStepProgress($status); ?>

      <div class="dashboard-cards">
        <div class="card">
          <h3>Applicant Data</h3>
          <p><a href="admission_info.php">Admission Information</a>
          <?php if ($filled['Admission Information']) echo ' ✅'; ?>
        </p>
          <p>
    <a href="personal_info.php">Personal Information</a>
    <?php if ($filled['Personal Information']) echo ' ✅'; ?>
  </p>
  <p>
    <a href="family_bg.php">Family Background</a>
    <?php if ($filled['Family Background']) echo ' ✅'; ?>
  </p>
  <p>
    <a href="education_bg.php">Educational Background</a>
    <?php if ($filled['Educational Background']) echo ' ✅'; ?>
  </p>
  <p>
    <a href="med_his_info.php">Medical History Information</a>
    <?php if ($filled['Medical History Information']) echo ' ✅'; ?>
  </p>
</div>

        <div class="card card2">
          <p>You can't modify your application form once you get your control number</p>

          <?php if ($allCompleted && !$control_number_click): ?>
            <form method="POST">
              <button class="button2" type="submit" name="generate_cn">Get Control Number</button>
            </form>
          <?php elseif ($control_number_click): ?>
            <p style="color: green;">Control number is being generated. Redirecting...</p>
          <?php else: ?>
            <button class="button2" disabled>Get Control Number</button>
            <p style="color: red;">Complete all 5 forms to unlock your Control Number.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
