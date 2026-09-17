<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';
$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
$sql = "SELECT * FROM exams WHERE id = $exam_id";
$result = $conn->query($sql);
$exam = $result->fetch_assoc();
$duration_minutes = isset($exam['duration_minutes']) ? intval($exam['duration_minutes']) : 60; // default to 60

$sql = "SELECT * FROM exam_sections WHERE exam_id = $exam_id";
$sections_result = $conn->query($sql);
$sections = [];
while ($row = $sections_result->fetch_assoc()) {
    $sections[] = $row;
}
$questions = [];
$sectionMap = [];
foreach ($sections as $section) {
    $sectionMap[$section['id']] = [];
}
if (!empty($sections)) {
    foreach ($sections as $section) {
        $section_id = $section['id'];
        $sql = "SELECT * FROM questions WHERE section_id = $section_id";
        $questions_result = $conn->query($sql);
        while ($row = $questions_result->fetch_assoc()) {
            $questions[] = $row;
            $sectionMap[$section_id][] = count($questions) - 1;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam - <?php echo htmlspecialchars($exam['exam_title']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body, html { height: 100%; margin: 0; }
        .container-box { display: none; height: 100vh; padding: 20px; }
        .start-screen {
            height: 100vh; display: flex; align-items: center;
            justify-content: center; flex-direction: column; background: #f8f9fa;
        }
        .left-panel {
            width: 25%; border-right: 1px solid #ccc;
            padding-right: 10px; overflow-y: auto;
        }
        .right-panel {
            flex: 1; padding-left: 20px; position: relative;
            height: 100%; overflow: auto;
        }
        .question-btn {
            margin: 5px; width: 45px; height: 45px;
        }
        .question-btn.active { background-color: #007bff; color: white; }
        .question-btn.answered { background-color: #28a745; color: white; }
        .question-btn.viewed { background-color: #ffa500; color: white; }
        .question-btn.unanswered { background-color: #dc3545; color: white; }
        .question-container { display: none; }
        .question-container.active { display: block; }
        .nav-btn { margin: 10px 5px; }
        .section-btn.active { background-color: #007bff; color: white; }
    </style>
</head>
<body>

<div class="start-screen">
    <h2>Ready to begin your exam?</h2>
    <p><strong><?php echo htmlspecialchars($exam['exam_title']); ?></strong></p>
    <p><?php echo htmlspecialchars($exam['description']); ?></p>
    <button class="btn btn-primary btn-lg" onclick="startExam()">Start Exam</button>
</div>

<div class="container-box d-flex">
    <div class="left-panel">
        <h5>Sections</h5>
        <?php foreach ($sections as $section): ?>
            <button class="btn btn-outline-primary btn-block section-btn" data-section-id="<?php echo $section['id']; ?>">
                <?php echo htmlspecialchars($section['section_name']); ?>
            </button>
        <?php endforeach; ?>
        <hr>
        <h6>Questions</h6>
        <div id="questionButtons"></div>
        <button class="btn btn-success mt-3" id="submitExam">Submit Exam</button>
    </div>

    <div class="right-panel">
        <div style="text-align: right; font-weight: bold; font-size: 18px; color: #ff4d4d; margin-bottom: 5px;">
            Time Left: <span id="timerDisplay">--:--</span>
        </div>
        <h3><?php echo htmlspecialchars($exam['exam_title']); ?></h3>
        <p><strong>Instructions:</strong> <?php echo htmlspecialchars($exam['description']); ?></p>
        <form id="examForm" onsubmit="event.preventDefault(); submitExamAjax();">
            <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
            <div id="questionContainer">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="question-container" id="question-<?php echo $index; ?>">
                        <p><strong>Q<?php echo $index + 1; ?>:</strong> <?php echo htmlspecialchars($question['question_text']); ?></p>
                        <input type="hidden" name="question_id_<?php echo $index; ?>" value="<?php echo $question['question_id']; ?>">
                        <?php foreach (['A', 'B', 'C', 'D'] as $opt): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="answer_<?php echo $index; ?>" value="<?php echo $opt; ?>" onclick="markAnswered(<?php echo $index; ?>)">
                                <label class="form-check-label"><?php echo htmlspecialchars($question["option_" . strtolower($opt)]); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </form>

        <div class="mt-3">
            <button class="btn btn-primary nav-btn" onclick="navigateQuestion('prev')">Previous</button>
            <button class="btn btn-primary nav-btn" onclick="navigateQuestion('next')">Next</button>
            <button class="btn btn-warning nav-btn" onclick="clearAnswer()">Clear</button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
let questionData = <?php echo json_encode($questions); ?>;
let sectionQuestionMap = <?php echo json_encode($sectionMap); ?>;
let currentQuestionIndex = 0;
let currentSectionId = null;
let timeLeft = <?php echo $duration_minutes * 60; ?>;
let timerDisplay = document.getElementById('timerDisplay');
let timerInterval;

function startExam() {
    const elem = document.documentElement;
    if (elem.requestFullscreen) elem.requestFullscreen();
    else if (elem.webkitRequestFullscreen) elem.webkitRequestFullscreen();
    else if (elem.msRequestFullscreen) elem.msRequestFullscreen();
    document.querySelector('.start-screen').style.display = 'none';
    document.querySelector('.container-box').style.display = 'flex';
    startTimer();
    const firstSectionBtn = document.querySelector('.section-btn');
    if (firstSectionBtn) firstSectionBtn.click();
}
document.querySelectorAll('.section-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const sectionId = btn.getAttribute('data-section-id');
        currentSectionId = sectionId;

        document.querySelectorAll('.section-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        renderQuestionButtons(sectionId);
        const firstQIndex = sectionQuestionMap[sectionId][0];
        if (firstQIndex !== undefined) {
            showQuestion(firstQIndex);
        } else {
            document.querySelectorAll('.question-container').forEach(q => q.classList.remove('active'));
        }
    });
});

function renderQuestionButtons(sectionId) {
    const container = document.getElementById('questionButtons');
    container.innerHTML = '';
    sectionQuestionMap[sectionId].forEach((qIndex, i) => {
        const btn = document.createElement('button');
        btn.className = 'btn btn-outline-secondary question-btn';
        btn.innerText = i + 1;
        btn.id = 'btn-' + qIndex;
        btn.setAttribute('onclick', showQuestion(${qIndex}));
        btn.setAttribute('data-status', 'unanswered');
        container.appendChild(btn);
    });
}

function showQuestion(index) {
    document.querySelectorAll('.question-container').forEach(q => q.classList.remove('active'));
    document.getElementById('question-' + index).classList.add('active');
    currentQuestionIndex = index;

    const btn = document.getElementById('btn-' + index);
    if (btn.getAttribute('data-status') !== 'answered') {
        btn.setAttribute('data-status', 'viewed');
    }
    updateQuestionButtonStatus(index);
}

function updateQuestionButtonStatus(index) {
    const btn = document.getElementById('btn-' + index);
    const status = btn.getAttribute('data-status');
    btn.classList.remove('answered', 'viewed', 'unanswered');
    if (status === 'answered') btn.classList.add('answered');
    else if (status === 'viewed') btn.classList.add('viewed');
    else btn.classList.add('unanswered');
}

function markAnswered(index) {
    const btn = document.getElementById('btn-' + index);
    btn.setAttribute('data-status', 'answered');
    updateQuestionButtonStatus(index);
}

function clearAnswer() {
    const currentQ = document.querySelector(#question-${currentQuestionIndex});
    currentQ.querySelectorAll('input[type="radio"]').forEach(r => r.checked = false);
    const btn = document.getElementById('btn-' + currentQuestionIndex);
    btn.setAttribute('data-status', 'unanswered');
    updateQuestionButtonStatus(currentQuestionIndex);
}

function navigateQuestion(dir) {
    const qList = sectionQuestionMap[currentSectionId];
    const currentIndexInSection = qList.indexOf(currentQuestionIndex);

    if (dir === 'next' && currentIndexInSection < qList.length - 1) {
        showQuestion(qList[currentIndexInSection + 1]);
    } else if (dir === 'prev' && currentIndexInSection > 0) {
        showQuestion(qList[currentIndexInSection - 1]);
    }
}

function startTimer() {
    timerInterval = setInterval(() => {
        let minutes = Math.floor(timeLeft / 60);
        let seconds = timeLeft % 60;
        timerDisplay.textContent = ${minutes}:${seconds < 10 ? '0' + seconds : seconds};

        if (timeLeft <= 120) {
            timerDisplay.style.color = 'red';
        } else {
            timerDisplay.style.color = 'black';
        }

        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            autoSubmitExam("Time's up!");
        }
        timeLeft--;
    }, 1000);
}

function autoSubmitExam(message) {
    Swal.fire({
        title: 'Auto Submit',
        text: message,
        icon: 'info',
        showConfirmButton: false,
        timer: 2000
    });
    setTimeout(() => {
        submitExamAjax();
    }, 2000);
}

document.getElementById('submitExam').addEventListener('click', function () {
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to submit the exam?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, submit it!',
        cancelButtonText: 'No, stay'
    }).then((result) => {
        if (result.isConfirmed) {
            submitExamAjax();
        }
    });
});
function submitExamAjax() {
    const submitBtn = document.getElementById('submitExam');
    submitBtn.disabled = true;
    const formData = new FormData(document.getElementById('examForm'));
    fetch('submitexam.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
    title: 'Thank You!',
    html: Your exam has been submitted.<br><strong>Your Score: ${data.score}/${data.total_questions}</strong>,
    icon: 'success',
    showCancelButton: true,
    confirmButtonText: 'Go to Dashboard',
    cancelButtonText: 'Download Excel',
    reverseButtons: true
}).then((result) => {
    if (result.isConfirmed) {
        window.location.href = 'userdashbord.php';
    } else {
        window.location.href = excel.php?exam_id=${formData.get('exam_id')}&score=${data.score}&total=${data.total_questions};

    }
});
   } else {
            Swal.fire('Error', data.message || 'Something went wrong.', 'error');
            submitBtn.disabled = false;
        }
    })
    .catch(() => {
        Swal.fire('Error', 'Network error occurred.', 'error');
        submitBtn.disabled = false;
    });
}
</script>
</body>
</html>