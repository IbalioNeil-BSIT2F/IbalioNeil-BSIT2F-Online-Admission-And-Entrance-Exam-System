<?php
include('../php/connection.php');
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['user'];

// ✅ Check application_status and redirect
$stmt = $conn->prepare("SELECT status FROM application_status WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$app_result = $stmt->get_result();
$app_status = $app_result->fetch_assoc();

$is_pending = false;
$read_only_attr = '';

if ($app_status) {
    $status_code = (int)$app_status['status'];
    if ($status_code === 1) {
        header("Location: useradmission3.php");
        exit();
    } elseif ($status_code === 3) {
        header("Location: sorry_message.php");
        exit();
    } elseif ($status_code === 0) {
        $is_pending = true;
        $read_only_attr = 'disabled';
    }
}

// Fetch user status
$stmt = $conn->prepare("SELECT * FROM check_status WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: useradmission.php");
    exit();
}

$status = $result->fetch_assoc();

if ((int)$status['current_stage'] > 2) {
    header("Location: useradmission3.php");
    exit();
}

if ((int)$status['current_stage'] < 2 || empty($status['control_number'])) {
    header("Location: useradmission.php");
    exit();
}

$control_number = $status['control_number'];

// Get user info and admission type
$stmt = $conn->prepare("SELECT firstname, lastname FROM personal_info WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc() ?: ['firstname' => '', 'lastname' => ''];

$stmt = $conn->prepare("SELECT entry, type FROM admission_info WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$type_result = $stmt->get_result();
$type_data = $type_result->fetch_assoc();

$entry = $type_data['entry'] ?? '';
$type = $type_data['type'] ?? '';

$pending_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_files'])) {
    $upload_dir = '../uploads/';
    $timestamp = date('Y-m-d H:i:s');

    function save_file($input_name, $username, $upload_dir) {
        if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES[$input_name]['tmp_name'];
            $file_name = basename($_FILES[$input_name]['name']);
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_name = $username . '_' . $input_name . '_' . time() . '.' . $ext;
            $dest_path = $upload_dir . $new_name;
            move_uploaded_file($file_tmp, $dest_path);
            return $new_name;
        }
        return null;
    }

    if ($entry === 'New') {
        $report_card = save_file('report_card', $username, $upload_dir);
        $gmc = save_file('gmc', $username, $upload_dir);
        $birth_cert = save_file('birth_cert', $username, $upload_dir);

        $stmt = $conn->prepare("INSERT INTO freshmen_files (username, control_number, report_card, gmc, birth_cert, submitted_at)
                                VALUES (?, ?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE report_card=?, gmc=?, birth_cert=?, submitted_at=?");
        $stmt->bind_param("ssssssssss", $username, $control_number, $report_card, $gmc, $birth_cert, $timestamp,
                                          $report_card, $gmc, $birth_cert, $timestamp);
        $stmt->execute();
    } elseif ($entry === 'Transferee') {
        $tor = save_file('tor', $username, $upload_dir);
        $dismissal = save_file('dismissal', $username, $upload_dir);
        $gmc = save_file('gmc', $username, $upload_dir);
        $nbi = save_file('nbi', $username, $upload_dir);
        $birth_cert = save_file('birth_cert', $username, $upload_dir);

        $stmt = $conn->prepare("INSERT INTO transferee_files (username, control_number, tor, dismissal, gmc, nbi, birth_cert, submitted_at)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE tor=?, dismissal=?, gmc=?, nbi=?, birth_cert=?, submitted_at=?");
        $stmt->bind_param("ssssssssssssss", $username, $control_number, $tor, $dismissal, $gmc, $nbi, $birth_cert, $timestamp,
                                                 $tor, $dismissal, $gmc, $nbi, $birth_cert, $timestamp);
        $stmt->execute();
    } elseif ($entry === 'Second Courser') {
        $tor = save_file('tor', $username, $upload_dir);
        $gmc = save_file('gmc', $username, $upload_dir);
        $birth_cert = save_file('birth_cert', $username, $upload_dir);

        $stmt = $conn->prepare("INSERT INTO second_courser_files (username, control_number, tor, gmc, birth_cert, submitted_at)
                                VALUES (?, ?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE tor=?, gmc=?, birth_cert=?, submitted_at=?");
        $stmt->bind_param("ssssssssss", $username, $control_number, $tor, $gmc, $birth_cert, $timestamp,
                                           $tor, $gmc, $birth_cert, $timestamp);
        $stmt->execute();
    }

    

    // ✅ Insert or update application_status to pending (0)
    $stmt = $conn->prepare("INSERT INTO application_status (username, status) 
                            VALUES (?, 0) 
                            ON DUPLICATE KEY UPDATE status = 0");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $pending_message = 'Your documents have been submitted and are now pending approval.';

    echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('submitBtn');
        if (btn) {
          btn.disabled = true;
          btn.innerText = 'Submitted';
        }
      });
    </script>";
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

    <div class="content">
      <?php renderStepProgress($status); ?>

      <div class="dashboard-cards">
        <div class="card control-card">
          <p class="label">Your Control Number</p>
          <h2 class="control-number"><?php echo $control_number; ?></h2>
        </div>

        <div class="card upload-card">
          <?php if (!empty($pending_message)): ?>
            <div class="message"><?php echo $pending_message; ?></div>
          <?php endif; ?>

          <form method="post" enctype="multipart/form-data" id="uploadForm">
            <h3 style="text-align: center; margin-bottom: 25px;">
  Admission Requirements for <span style="color: #007bff;">
  <?php echo htmlspecialchars(ucfirst($entry) . ' / ' . ucfirst($type)); ?>
  </span>
</h3>

<div class="requirement-grid">

<?php
function renderUploadField($label, $name) {
  echo '
    <div class="requirement-card">
      <p class="requirement-label">' . htmlspecialchars($label) . '</p>
      <input type="file" name="' . $name . '" id="' . $name . '" required>
    </div>
  ';
}

if ($entry === 'New') {
  renderUploadField('Report Card / ALS Certificate', 'report_card');
  renderUploadField('Certificate of Good Moral Character', 'gmc');
  renderUploadField('PSA Birth Certificate', 'birth_cert');

} elseif ($entry === 'Transferee') {
  renderUploadField('Transcript of Records / Certificate of Grades', 'tor');
  renderUploadField('Transfer Credentials / Honorable Dismissal', 'dismissal');
  renderUploadField('Certificate of Good Moral Character', 'gmc');
  renderUploadField('NBI Clearance', 'nbi');
  renderUploadField('PSA Birth Certificate', 'birth_cert');

} elseif ($entry === 'Second Courser') {
  renderUploadField('Transcript of Records', 'tor');
  renderUploadField('Certificate of Good Moral Character', 'gmc');
  renderUploadField('PSA Birth Certificate', 'birth_cert');
}
?>
</div>


            <button class="button2" type="submit" name="submit_files" id="submitBtn" style="margin-top: 30px;" >
  Submit All Files
</button>


          </form>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
