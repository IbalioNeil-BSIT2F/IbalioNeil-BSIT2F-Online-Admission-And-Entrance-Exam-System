<?php
session_start();
include('../php/connection.php');

// Get the user's email
$email = $_SESSION['user'] ?? '';

// Fetch latest exam_id the user took
$examQuery = $conn->prepare("SELECT exam_id FROM exam_attempts WHERE email = ? ORDER BY ended_at DESC LIMIT 1");
$examQuery->bind_param("s", $email);
$examQuery->execute();
$examResult = $examQuery->get_result();
$exam_id = $examResult->fetch_assoc()['exam_id'] ?? null;

$correct = 0;
$total = 0;

if ($exam_id !== null) {
    $scoreQuery = $conn->prepare("
        SELECT 
          SUM(CASE WHEN ea.answer = q.answer THEN 1 ELSE 0 END) AS correct_answers,
          COUNT(q.question_no) AS total_questions
        FROM exam_answers ea
        JOIN questions q ON ea.question_no = q.question_no AND ea.exam_id = q.exam_id
        WHERE ea.email = ? AND ea.exam_id = ?
    ");
    $scoreQuery->bind_param("si", $email, $exam_id);
    $scoreQuery->execute();
    $scoreResult = $scoreQuery->get_result();
    $row = $scoreResult->fetch_assoc();
    $correct = $row['correct_answers'] ?? 0;
    $total = $row['total_questions'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Congratulations!</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background: #e6f4ea;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .container {
      background: #ffffff;
      padding: 40px;
      max-width: 600px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      text-align: center;
    }

    .emoji {
      font-size: 64px;
      margin-bottom: 20px;
    }

    h1 {
      color: #28a745;
      margin-bottom: 10px;
    }

    p {
      color: #333;
      font-size: 18px;
      line-height: 1.6;
    }

    .score {
      font-size: 20px;
      margin: 20px 0;
      color: #000;
    }

    .btn {
      margin-top: 30px;
      display: inline-block;
      padding: 10px 20px;
      background: #28a745;
      color: #fff;
      text-decoration: none;
      border-radius: 6px;
      transition: background 0.3s;
    }

    .btn:hover {
      background: #218838;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="emoji">🎉</div>
    <h1>Congratulations!</h1>
    <p>
      You have been <strong>accepted</strong> to our university!<br>
      We look forward to welcoming you as a part of our academic community.
    </p>
    
    <?php if ($total > 0): ?>
      <div class="score">Your Exam Score: <strong><?php echo "$correct / $total"; ?></strong></div>
    <?php else: ?>
      <div class="score">Exam score data not available.</div>
    <?php endif; ?>

    <p>
      Please proceed to complete the next steps in your enrollment.
    </p>
    <a href="userdashboard.php" class="btn">Continue</a>
  </div>
</body>
</html>
