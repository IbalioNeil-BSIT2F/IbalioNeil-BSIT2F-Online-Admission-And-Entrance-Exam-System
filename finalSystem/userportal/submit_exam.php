<?php
include('../php/connection.php');
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user'];

// Fetch the exam_id for this user
$query = "
    SELECT ue.exam_id
    FROM confirmed_exams ce
    JOIN uploaded_exams ue ON ce.schedule = ue.schedule
    WHERE ce.username = ?
    LIMIT 1
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 0) {
    die("No exam found for your account.");
}

$exam_id = $result->fetch_assoc()['exam_id'];

// Get the active attempt_id
$attemptStmt = $conn->prepare("
    SELECT attempt_id 
    FROM exam_attempts 
    WHERE email = ? AND exam_id = ? AND is_submitted = 0 
    ORDER BY started_at DESC 
    LIMIT 1
");
$attemptStmt->bind_param("si", $email, $exam_id);
$attemptStmt->execute();
$attemptResult = $attemptStmt->get_result();
$attemptStmt->close();

if ($attemptResult->num_rows === 0) {
    die("No active attempt found for this exam.");
}

$attempt_id = $attemptResult->fetch_assoc()['attempt_id'];

// Fetch all questions for this exam
$questionQuery = $conn->prepare("SELECT question_no FROM questions WHERE exam_id = ?");
$questionQuery->bind_param("i", $exam_id);
$questionQuery->execute();
$questionsResult = $questionQuery->get_result();

$allQuestions = [];
while ($row = $questionsResult->fetch_assoc()) {
    $allQuestions[] = $row['question_no'];
}
$questionQuery->close();

// Get submitted answers from form (POST)
$submittedAnswers = $_POST['answers'] ?? [];

// Insert answers into exam_answers with attempt_id
$insertStmt = $conn->prepare("
    INSERT INTO exam_answers (attempt_id, email, exam_id, question_no, answer, submitted_at)
    VALUES (?, ?, ?, ?, ?, NOW())
");

foreach ($allQuestions as $qno) {
    $answer = isset($submittedAnswers[$qno]) ? $submittedAnswers[$qno] : 'N/A';
    $insertStmt->bind_param("isiss", $attempt_id, $email, $exam_id, $qno, $answer);
    $insertStmt->execute();
}
$insertStmt->close();

// Mark exam_attempts as submitted
$updateStmt = $conn->prepare("
    UPDATE exam_attempts
    SET ended_at = NOW(), is_submitted = 1
    WHERE attempt_id = ?
");
$updateStmt->bind_param("i", $attempt_id);
$updateStmt->execute();
$updateStmt->close();

// Update user's current_stage to 4
$stageStmt = $conn->prepare("
    UPDATE check_status
    SET current_stage = 4
    WHERE username = ?
");
$stageStmt->bind_param("s", $email);
$stageStmt->execute();
$stageStmt->close();

// Clear exam session
unset($_SESSION['exam_started']);

// Redirect to confirmation page
header("Location: useradmission4.php");
exit();
?>
