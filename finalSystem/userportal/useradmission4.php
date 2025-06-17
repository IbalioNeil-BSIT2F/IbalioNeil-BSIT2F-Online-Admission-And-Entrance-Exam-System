<?php
ob_start();
include('..\php\connection.php');
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['user'];

// Fetch grade status
$stmt = $conn->prepare("SELECT grade_status FROM application_status WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$grade_status = 0;
if ($row = $result->fetch_assoc()) {
    $grade_status = $row['grade_status'];
}

// Fetch check_status for step progress
$step_stmt = $conn->prepare("SELECT * FROM check_status WHERE username = ?");
$step_stmt->bind_param("s", $username);
$step_stmt->execute();
$step_result = $step_stmt->get_result();
$status = $step_result->fetch_assoc() ?: [
    'admission_info_completed' => 0,
    'personal_info_completed' => 0,
    'family_bg_completed' => 0,
    'education_bg_completed' => 0,
    'med_his_info_completed' => 0,
    'control_number_click' => 0,
    'control_number' => '',
    'current_stage' => 0
];

// Fetch admission_info
$admission_sql = $conn->prepare("SELECT entry, type, strand, lrn, program FROM admission_info WHERE username = ?");
$admission_sql->bind_param("s", $username);
$admission_sql->execute();
$admission_result = $admission_sql->get_result();
$admission_data = $admission_result->fetch_assoc() ?: [
    'entry' => '',
    'type' => '',
    'strand' => '',
    'lrn' => '',
    'program' => ''
];

// Fetch personal_info
$personal_sql = $conn->prepare("SELECT firstname, middlename, lastname, phonenumber, birthday, birthplace FROM personal_info WHERE username = ?");
$personal_sql->bind_param("s", $username);
$personal_sql->execute();
$personal_result = $personal_sql->get_result();
$personal_data = $personal_result->fetch_assoc() ?: [
    'firstname' => '',
    'middlename' => '',
    'lastname' => '',
    'phonenumber' => '',
    'birthday' => '',
    'birthplace' => ''
];

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

include('../php/useradmissionheader.php');
?>

<style>
.container-box { padding: 20px; border-radius: 10px; background-color: #f7f7f7; margin-bottom: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
.show-btn { padding: 10px 20px; font-size: 1rem; font-weight: bold; border: none; border-radius: 5px; background-color: #007bff; color: white; cursor: pointer; }
.show-btn:disabled { background-color: gray; cursor: not-allowed; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
td { padding: 8px; border-bottom: 1px solid #ddd; }
td.label { font-weight: bold; width: 30%; background-color: #f0f0f0; }
.upload-group input[type="file"] { border: 2px solid #aaa; border-radius: 5px; padding: 6px; background-color: #f9f9f9; width: 100%; }
.button2:disabled { background-color: #ccc; cursor: not-allowed; }
.message { padding: 10px; background-color: #f0f8ff; border: 1px solid #3399ff; color: #003366; border-radius: 5px; margin-bottom: 15px; }
</style>
</head>

<div class="content">
<?php renderStepProgress($status); ?>
<div class="maincontainer">

  <div class="container-box">
    <h2>Briefly Important Information</h2>
    <table>
      <tr><td class="label">Full Name</td><td><?php echo htmlspecialchars($personal_data['firstname'] . ' ' . $personal_data['middlename'] . ' ' . $personal_data['lastname']); ?></td></tr>
      <tr><td class="label">Phone Number</td><td><?php echo htmlspecialchars($personal_data['phonenumber']); ?></td></tr>
      <tr><td class="label">Birthday</td><td><?php echo htmlspecialchars($personal_data['birthday']); ?></td></tr>
      <tr><td class="label">Birthplace</td><td><?php echo htmlspecialchars($personal_data['birthplace']); ?></td></tr>
      <tr><td class="label">Entry</td><td><?php echo htmlspecialchars($admission_data['entry']); ?></td></tr>
      <tr><td class="label">Type</td><td><?php echo htmlspecialchars($admission_data['type']); ?></td></tr>
      <tr><td class="label">Strand</td><td><?php echo htmlspecialchars($admission_data['strand']); ?></td></tr>
      <tr><td class="label">LRN</td><td><?php echo htmlspecialchars($admission_data['lrn']); ?></td></tr>
      <tr><td class="label">Program</td><td><?php echo htmlspecialchars($admission_data['program']); ?></td></tr>
    </table>
  </div>

  <div class="container-box">
    <h2>Exam Results</h2>
    <form method="POST">
      <?php if ($grade_status == 1 || $grade_status == 2): ?>
        <button type="submit" name="show_result" class="show-btn">Show Result</button>
      <?php else: ?>
        <button type="button" class="show-btn" disabled>Show Result</button>
      <?php endif; ?>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['show_result'])) {
        if ($grade_status == 1) {
            header("Location: congrats_message.php");
            exit();
        } elseif ($grade_status == 2) {
            header("Location: sorry_message.php");
            exit();
        }
    }
    ?>
  </div>

</div>
</div>
</div>

</body>
</html>
<?php ob_end_flush(); ?>
