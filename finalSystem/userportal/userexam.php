<?php
session_start();
include('../php/connection.php');

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['user'];

// Get control number
$cnstmt = $conn->prepare("SELECT control_number FROM check_status WHERE username = ?");
$cnstmt->bind_param("s", $email);
$cnstmt->execute();
$cnstmt->bind_result($control_number);
$cnstmt->fetch();
$cnstmt->close();

// Get exam_id and category
$stmt = $conn->prepare("SELECT ec.exam_id, ec.category FROM confirmed_exams ce 
                        JOIN exam_category ec ON ce.schedule = ec.schedule 
                        WHERE ce.username = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($exam_id, $category);
if (!$stmt->fetch()) {
    echo "No scheduled exam found.";
    exit();
}
$stmt->close();

// Check if already submitted
$stmt = $conn->prepare("SELECT * FROM exam_attempts WHERE email = ? AND exam_id = ?");
$stmt->bind_param("si", $email, $exam_id);
$stmt->execute();
$res = $stmt->get_result();
$attempt = $res->fetch_assoc();

if ($attempt && $attempt['is_submitted'] == 1) {
    header("Location: useradmission4.php");
    exit();
}

// Create attempt if not exists
if (!$attempt) {
    $now = date("Y-m-d H:i:s");
    $stmt = $conn->prepare("INSERT INTO exam_attempts (email, exam_id, started_at, is_submitted) VALUES (?, ?, ?, 0)");
    $stmt->bind_param("sis", $email, $exam_id, $now);
    $stmt->execute();
    $attempt_id = $stmt->insert_id;
} else {
    $attempt_id = $attempt['attempt_id'];
}

// Fetch questions
$stmt = $conn->prepare("SELECT * FROM questions WHERE exam_id = ? ORDER BY question_no ASC");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$res = $stmt->get_result();
$questions = [];
while ($row = $res->fetch_assoc()) {
    $questions[] = $row;
}
$totalQuestions = count($questions);

// Get exam duration
$stmt = $conn->prepare("SELECT time_min FROM exam_category WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$stmt->bind_result($time_min);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($category); ?> - Online Exam</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f4f8fb;
            color: #333;
        }
        header {
            background: #003366;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header h1 {
            margin: 0;
            font-size: 22px;
        }
        #timer {
            font-weight: bold;
            font-size: 16px;
        }
        .exam-container {
            padding: 20px 30px;
        }
        .info-box {
            background: #e6f0ff;
            padding: 10px 20px;
            border-left: 5px solid #003366;
            margin-bottom: 20px;
            font-size: 16px;
        }
        .question-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .options label {
            display: block;
            margin: 10px 0;
            cursor: pointer;
        }
        .nav-buttons, .question-nav {
            margin-top: 20px;
            text-align: center;
        }
        .nav-buttons button, .question-nav button {
            padding: 10px 14px;
            background: #003366;
            color: white;
            border: none;
            border-radius: 6px;
            margin: 5px;
            cursor: pointer;
        }
        .question-nav button.active {
            background: #0055cc;
        }
        button:disabled {
            background: gray;
        }
    </style>
</head>
<body>

<header>
    <h1><?php echo htmlspecialchars($category); ?> Exam</h1>
    <div id="timer">Time Left: <span id="timeLeft"></span></div>
</header>

<div class="exam-container">
    <div class="info-box">Control Number: <strong><?php echo htmlspecialchars($control_number); ?></strong></div>

    <form id="examForm" method="POST">
        <input type="hidden" name="submit_exam" value="1">
        <input type="hidden" name="total" value="<?php echo $totalQuestions; ?>">
        <div class="question-box" id="questionBox"></div>

        <div class="question-nav" id="questionNav"></div>

        <div class="nav-buttons">
            <button type="button" onclick="prevQuestion()">Previous</button>
            <button type="button" onclick="nextQuestion()">Next</button>
            <button type="submit" id="submitBtn" disabled>Submit</button>
        </div>
    </form>
</div>

<script>
const questions = <?php echo json_encode($questions); ?>;
let current = 0;
let answers = {};

function renderQuestion() {
    const q = questions[current];
    let html = `<h3>Question ${current + 1} of ${questions.length}</h3>`;
    html += `<p>${q.question}</p><div class="options">`;

    ['opt1', 'opt2', 'opt3', 'opt4'].forEach((opt, i) => {
        const checked = answers[q.question_no] === q[opt] ? "checked" : "";
        html += `<label><input type="radio" name="q${q.question_no}" value="${q[opt]}" ${checked} onchange="storeAnswer()"> ${q[opt]}</label>`;
    });

    html += `</div>`;
    document.getElementById('questionBox').innerHTML = html;
    updateQuestionNav();
    updateSubmitButton();
}

function nextQuestion() {
    if (current < questions.length - 1) {
        current++;
        renderQuestion();
    }
}

function prevQuestion() {
    if (current > 0) {
        current--;
        renderQuestion();
    }
}

function storeAnswer() {
    const q = questions[current];
    const selected = document.querySelector(`input[name="q${q.question_no}"]:checked`);
    answers[q.question_no] = selected ? selected.value : null;
    updateSubmitButton();
}

function updateQuestionNav() {
    const nav = document.getElementById("questionNav");
    nav.innerHTML = "";
    questions.forEach((q, idx) => {
        const btn = document.createElement("button");
        btn.textContent = idx + 1;
        if (idx === current) btn.classList.add("active");
        btn.onclick = () => {
            current = idx;
            renderQuestion();
        };
        nav.appendChild(btn);
    });
}

function updateSubmitButton() {
    const allAnswered = questions.every(q => answers[q.question_no]);
    document.getElementById("submitBtn").disabled = !allAnswered;
}

document.getElementById("examForm").onsubmit = function(e) {
    e.preventDefault();
    storeAnswer();

    const formData = new FormData();
    formData.append('submit_exam', '1');
    formData.append('total', questions.length);

    questions.forEach(q => {
        const ans = answers[q.question_no] || 'N/A';
        formData.append(`answers[${q.question_no}]`, ans);
    });

    fetch('submit_exam.php', {
        method: 'POST',
        body: formData
    }).then(() => {
        window.location.href = "useradmission4.php";
    });
};

renderQuestion();

// Timer logic
let seconds = <?php echo (int)$time_min * 60; ?>;
function updateTimer() {
    const min = Math.floor(seconds / 60);
    const sec = seconds % 60;
    document.getElementById('timeLeft').textContent = 
        `${min.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}`;
    if (seconds <= 0) {
        document.getElementById("examForm").requestSubmit();
    } else {
        seconds--;
        setTimeout(updateTimer, 1000);
    }
}
updateTimer();

// Disable sidebar/topbar
document.querySelectorAll('.sidebar, .topbar, .nav-btn, .backbtn').forEach(el => {
    el.style.pointerEvents = 'none';
    el.style.opacity = '0.5';
});

// Warn before leaving
window.onbeforeunload = () => "Are you sure you want to leave? Your progress will be lost.";
</script>
</body>
</html>
