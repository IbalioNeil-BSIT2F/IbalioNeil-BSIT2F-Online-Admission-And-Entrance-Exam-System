<?php
include('../php/connection.php');
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['user'];
$msg = '';

// Fetch user status
$stmt = $conn->prepare("SELECT * FROM check_status WHERE username = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: useradmission.php");
    exit();
}

$status = $result->fetch_assoc();
$current_stage = (int) ($status['current_stage'] ?? 1);
$control_number = $status['control_number'] ?? 'N/A';

// Redirect to next stage if already completed
if ($current_stage >= 4) {
    header("Location: useradmission4.php");
    exit();
}

// Get program from admission_info
$prog = $conn->query("SELECT program FROM admission_info WHERE username='$email'")->fetch_assoc();
$program = $prog['program'] ?? '';

// Load available schedules
$schedules = $conn->query("SELECT schedule, meeting_link FROM uploaded_exams WHERE category='$program'")
                  ->fetch_all(MYSQLI_ASSOC);

// Handle schedule confirmation
if (isset($_POST['confirm_schedule'])) {
    $sel = $_POST['selected_schedule'];
    $meet = $_POST['selected_link'];

    $exists = $conn->query("SELECT id FROM user_chosen_schedule WHERE email='$email'")->num_rows;
    if (!$exists) {
        $stmt1 = $conn->prepare("INSERT INTO user_chosen_schedule(email, chosen_schedule) VALUES (?, ?)");
        $stmt1->bind_param("ss", $email, $sel);
        $stmt1->execute();

        $stmt2 = $conn->prepare("INSERT INTO confirmed_exams(username, schedule, meeting_link) VALUES (?, ?, ?)");
        $stmt2->bind_param("sss", $email, $sel, $meet);
        $stmt2->execute();

        $msg = "Schedule confirmed!";
    } else {
        $msg = "You've already confirmed.";
    }
}

// Get confirmed schedule and meeting link
$chosen = $conn->query("SELECT * FROM user_chosen_schedule WHERE email='$email'")->fetch_assoc();
$confirmed_schedule = $chosen['chosen_schedule'] ?? '';
$meeting_data = $conn->query("SELECT meeting_link FROM uploaded_exams WHERE category='$program' AND schedule='$confirmed_schedule'")->fetch_assoc();
$meeting_link_final = $meeting_data['meeting_link'] ?? '';

$now = time();
$exam_time = strtotime($confirmed_schedule);
$is_ready = $exam_time && $now >= $exam_time;

if ($is_ready && isset($chosen) && !$chosen['button_activate']) {
    $conn->query("UPDATE user_chosen_schedule SET button_activate=1 WHERE email='$email'");
}

// Handle Start Now button
if (isset($_POST['start_exam']) && $is_ready) {
    $_SESSION['exam_started'] = true;
    $conn->query("UPDATE check_status SET current_stage = 4 WHERE username = '$email'");
    header("Location: userexam.php");
    exit();
}

function renderStepProgress($status) {
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

  <style>
    .upload-group input[type="file"] {
      border: 2px solid #aaa;
      border-radius: 5px;
      padding: 6px;
      background-color: #f9f9f9;
      width: 100%;
    }

    .button2:disabled {
      background-color: #ccc;
      cursor: not-allowed;
    }
    .message {
      padding: 10px;
      background-color: #f0f8ff;
      border: 1px solid #3399ff;
      color: #003366;
      border-radius: 5px;
      margin-bottom: 15px;
    }

    .control-card {
  text-align: center;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.control-card .label {
  font-size: 25px;
  color: #444;
  margin-bottom: 8px;
}

.control-card .control-number {
  font-size: 30px;
  font-weight: bold;
  color: #007bff;
  letter-spacing: 1px;
}

.requirement-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
}

.requirement-card {
  background-color: #ffffff;
  padding: 15px 20px;
  border: 1px solid #ddd;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.requirement-label {
  font-weight: 600;
  font-size: 14px;
  margin-bottom: 10px;
  color: #333;
}

.requirement-card input[type="file"] {
  border: 1px solid #ccc;
  padding: 8px;
  border-radius: 6px;
  background-color: #f8f9fa;
  transition: all 0.3s ease;
}

.requirement-card input[type="file"]:hover {
  background-color: #e9ecef;
  cursor: pointer;
}

#submitBtn {
  margin-top: 30px;
  padding: 10px 20px;
}

</style>

<?php include('../php/useradmissionheader.php'); ?>

  <div class="content">
      <?php renderStepProgress($status); ?>
<div class="dashboard-cards">
    <div class="card">
      <h3>Your Control Number</h3>
      <div class="control-number"><?php echo htmlspecialchars($control_number); ?></div>

      <?php if ($confirmed_schedule): ?>
        <p><strong>Confirmed Schedule:</strong> <?php echo date('F d, Y / h:i A', strtotime($confirmed_schedule)); ?></p>
      <?php else: ?>
        <form method="post">
          <select name="selected_schedule" required>
            <option value="">Choose Schedule – <?php echo htmlspecialchars($program); ?></option>
            <?php foreach ($schedules as $s): ?>
              <option value="<?php echo $s['schedule']; ?>"
                      data-link="<?php echo htmlspecialchars($s['meeting_link']); ?>">
                <?php echo date('F d, Y / h:i A', strtotime($s['schedule'])); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <input type="hidden" name="selected_link" id="selected_link">
          <button type="submit" name="confirm_schedule">Confirm Schedule</button>
        </form>
      <?php endif; ?>

      <p style="color: green;"><?php echo $msg; ?></p>
    </div>

    <div class="card">
      <b>MUST READ:</b><br>
      Answer honestly under proctor rules.<br><hr>

      <?php if ($meeting_link_final): ?>
        <a
          href="<?php echo $is_ready ? htmlspecialchars($meeting_link_final) : '#'; ?>"
          class="btn-link <?php echo $is_ready ? '' : 'disabled'; ?>"
          target="_blank"
          onclick="<?php echo $is_ready ? '' : 'return false;'; ?>">
          Join Meeting
        </a>
      <?php else: ?>
        <span>No meeting link yet</span>
      <?php endif; ?>

      <form method="post">
        <button class="btn" name="start_exam" <?php echo $is_ready ? '' : 'disabled'; ?>>Start Now</button>
      </form>
    </div>
  </div>
</div>

<script>
  const sel = document.querySelector('select[name="selected_schedule"]');
  sel?.addEventListener('change', () => {
    document.getElementById('selected_link').value =
      sel.options[sel.selectedIndex].dataset.link;
  });
</script>

</body>
</html>
