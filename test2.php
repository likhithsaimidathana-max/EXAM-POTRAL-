<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Page</title>
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
    <p><strong>Sample Exam Title</strong></p>
    <p>This is a demo description.</p>
    <button class="btn btn-primary btn-lg" onclick="startExam()">Start Exam</button>
</div>

<div class="container-box d-flex">
    <div class="left-panel">
        <h5>Sections</h5>
        <button class="btn btn-outline-primary btn-block section-btn" data-section-id="1">Section 1</button>
        <hr>
        <h6>Questions</h6>
        <div id="questionButtons"></div>
        <button class="btn btn-success mt-3" id="submitExam">Submit Exam</button>
    </div>

    <div class="right-panel">
        <div style="text-align: right; font-weight: bold; font-size: 18px; color: #ff4d4d; margin-bottom: 5px;">
            Time Left: <span id="timerDisplay">--:--</span>
        </div>
        <h3>Sample Exam Title</h3>
        <p><strong>Instructions:</strong> This is a demo description.</p>
        <form id="examForm">
            <div id="questionContainer">
                <div class="question-container" id="question-0">
                    <p><strong>Q1:</strong> What is 2 + 2?</p>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answer_0" value="A" onclick="markAnswered(0)">
                        <label class="form-check-label">3</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answer_0" value="B" onclick="markAnswered(0)">
                        <label class="form-check-label">4</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answer_0" value="C" onclick="markAnswered(0)">
                        <label class="form-check-label">5</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answer_0" value="D" onclick="markAnswered(0)">
                        <label class="form-check-label">6</label>
                    </div>
                </div>
            </div>
        </form>

        <div class="mt-3">
            <button class="btn btn-primary nav-btn" onclick="navigateQuestion('prev')">Previous</button>
            <button class="btn btn-primary nav-btn" onclick="navigateQuestion('next')">Next</button>
            <button class="btn btn-warning nav-btn" onclick="clearAnswer()">Clear</button>
        </div>
    </div>
</div>

<script>
let questionData = [{ id: 0, section_id: 1 }];
let sectionQuestionMap = { "1": [0] };
let currentQuestionIndex = 0;
let currentSectionId = "1";
let timeLeft = 60 * 5;
let timerDisplay = document.getElementById('timerDisplay');
let timerInterval;

function startExam() {
    document.documentElement.requestFullscreen?.();
    document.querySelector('.start-screen').style.display = 'none';
    document.querySelector('.container-box').style.display = 'flex';
    startTimer();
    document.querySelector('.section-btn').click();
}

document.querySelectorAll('.section-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const sectionId = btn.getAttribute('data-section-id');
        currentSectionId = sectionId;
        document.querySelectorAll('.section-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        renderQuestionButtons(sectionId);
        const firstQIndex = sectionQuestionMap[sectionId][0];
        showQuestion(firstQIndex);
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
        btn.setAttribute('onclick', `showQuestion(${qIndex})`);
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
    const currentQ = document.getElementById('question-' + currentQuestionIndex);
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
        timerDisplay.textContent = `${minutes}:${seconds < 10 ? '0' + seconds : seconds}`;
        if (timeLeft <= 120) timerDisplay.style.color = 'red';
        else timerDisplay.style.color = 'black';
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
    Swal.fire('Submitted', 'Your responses have been saved.', 'success');
}
</script>
</body>
</html>
