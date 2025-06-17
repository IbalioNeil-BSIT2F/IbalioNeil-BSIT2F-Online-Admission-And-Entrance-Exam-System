<?php
include('../php/connection.php');
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['user'];

// Fetch user status
$stmt = $conn->prepare("SELECT * FROM check_status WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$status_result = $stmt->get_result();

if ($status_result->num_rows === 0) {
    header("Location: useradmission.php");
    exit();
}

$status = $status_result->fetch_assoc();

if ((int)$status['current_stage'] > 2) {
    header("Location: useradmission3.php");
    exit();
}

if ((int)$status['current_stage'] < 2 || empty($status['control_number'])) {
    header("Location: useradmission.php");
    exit();
}

$control_number = $status['control_number'];

// Fetch personal info
$stmt = $conn->prepare("SELECT firstname, lastname FROM personal_info WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$personal_result = $stmt->get_result();
$personal = $personal_result->fetch_assoc() ?: ['firstname' => '', 'lastname' => ''];

// Fetch admission type
$stmt = $conn->prepare("SELECT entry, type FROM admission_info WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$info_result = $stmt->get_result();
$info = $info_result->fetch_assoc();

$entry = $info['entry'] ?? '';
$type = $info['type'] ?? '';
$pending_message = '';

// File upload helper
function save_file($input_name, $username, $upload_dir = '../uploads/') {
    if (!isset($_FILES[$input_name]) || $_FILES[$input_name]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file_tmp = $_FILES[$input_name]['tmp_name'];
    $file_name = basename($_FILES[$input_name]['name']);
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $new_name = $username . '_' . $input_name . '_' . time() . '.' . $ext;
    $dest_path = $upload_dir . $new_name;

    if (move_uploaded_file($file_tmp, $dest_path)) {
        return $new_name;
    }

    return null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_files'])) {
    $timestamp = date('Y-m-d H:i:s');

    if ($entry === 'New') {
        $report_card = save_file('report_card', $username);
        $gmc = save_file('gmc', $username);
        $birth_cert = save_file('birth_cert', $username);

        $stmt = $conn->prepare("INSERT INTO freshmen_files (username, control_number, report_card, gmc, birth_cert, submitted_at)
                                VALUES (?, ?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE report_card=?, gmc=?, birth_cert=?, submitted_at=?");
        $stmt->bind_param("ssssssssss", $username, $control_number, $report_card, $gmc, $birth_cert, $timestamp,
                                        $report_card, $gmc, $birth_cert, $timestamp);
        $stmt->execute();

    } elseif ($entry === 'Transferee') {
        $tor = save_file('tor', $username);
        $dismissal = save_file('dismissal', $username);
        $gmc = save_file('gmc', $username);
        $nbi = save_file('nbi', $username);
        $birth_cert = save_file('birth_cert', $username);

        $stmt = $conn->prepare("INSERT INTO transferee_files (username, control_number, tor, dismissal, gmc, nbi, birth_cert, submitted_at)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE tor=?, dismissal=?, gmc=?, nbi=?, birth_cert=?, submitted_at=?");
        $stmt->bind_param("ssssssssssssss", $username, $control_number, $tor, $dismissal, $gmc, $nbi, $birth_cert, $timestamp,
                                             $tor, $dismissal, $gmc, $nbi, $birth_cert, $timestamp);
        $stmt->execute();

    } elseif ($entry === 'Second Courser') {
        $tor = save_file('tor', $username);
        $gmc = save_file('gmc', $username);
        $birth_cert = save_file('birth_cert', $username);

        $stmt = $conn->prepare("INSERT INTO second_courser_files (username, control_number, tor, gmc, birth_cert, submitted_at)
                                VALUES (?, ?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE tor=?, gmc=?, birth_cert=?, submitted_at=?");
        $stmt->bind_param("ssssssssss", $username, $control_number, $tor, $gmc, $birth_cert, $timestamp,
                                           $tor, $gmc, $birth_cert, $timestamp);
        $stmt->execute();
    }

    // Update current_stage to 3
    $stmt = $conn->prepare("UPDATE check_status SET current_stage = 3 WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    // Set application_status to pending (0)
    $stmt = $conn->prepare("INSERT INTO application_status (username, status) 
                            VALUES (?, 0) 
                            ON DUPLICATE KEY UPDATE status = 0");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $pending_message = 'Your documents have been submitted and are now pending approval.';
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

      <div class="main-container">
        <h2 class="form-title">Document Upload (<?php echo htmlspecialchars($entry); ?> Student)</h2>

        <?php if (!empty($pending_message)): ?>
            <div class="success-message"><?php echo $pending_message; ?></div>
        <?php endif; ?>

        <form action="useradmission2.php" method="POST" enctype="multipart/form-data" class="form-container">
            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" value="<?php echo htmlspecialchars($personal['firstname'] . ' ' . $personal['lastname']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Control Number:</label>
                <input type="text" value="<?php echo htmlspecialchars($control_number); ?>" readonly>
            </div>

            <?php if ($entry === 'New'): ?>
                <div class="form-group">
                    <label>Report Card (PDF/IMG):</label>
                    <input type="file" name="report_card" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
                <div class="form-group">
                    <label>Good Moral Certificate:</label>
                    <input type="file" name="gmc" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
                <div class="form-group">
                    <label>Birth Certificate:</label>
                    <input type="file" name="birth_cert" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>

            <?php elseif ($entry === 'Transferee'): ?>
                <div class="form-group">
                    <label>TOR:</label>
                    <input type="file" name="tor" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
                <div class="form-group">
                    <label>Honorable Dismissal:</label>
                    <input type="file" name="dismissal" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
                <div class="form-group">
                    <label>Good Moral Certificate:</label>
                    <input type="file" name="gmc" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
                <div class="form-group">
                    <label>NBI Clearance:</label>
                    <input type="file" name="nbi" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
                <div class="form-group">
                    <label>Birth Certificate:</label>
                    <input type="file" name="birth_cert" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>

            <?php elseif ($entry === 'Second Courser'): ?>
                <div class="form-group">
                    <label>TOR:</label>
                    <input type="file" name="tor" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
                <div class="form-group">
                    <label>Good Moral Certificate:</label>
                    <input type="file" name="gmc" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
                <div class="form-group">
                    <label>Birth Certificate:</label>
                    <input type="file" name="birth_cert" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" name="submit_files" class="submit-btn">Submit Documents</button>
            </div>
        </form>
    </div>
</body>
</html>