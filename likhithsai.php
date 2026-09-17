<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Online Exam</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body, html { height: 100%; margin: 0; }
    .container-box { display: none; height: 100vh; padding: 20px; }
    .start-screen { height: 100vh; display: flex; align-items: center; justify-content: center; flex-direction: column; background: #f8f9fa; }
    .left-panel { width: 25%; border-right: 1px solid #ccc; padding-right: 10px; overflow-y: auto; }
    .right-panel { flex: 1; padding-left: 20px; position: relative; height: 100%; overflow: auto; }
    .question-btn { margin: 5px; width: 45px; height: 45px; }
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
  <p><strong id="examTitle"></strong></p>
  <p id="examDescription"></p>
  <button class="btn btn-primary btn-lg" onclick="startExam()">Start Exam</button>
</div>

<div class="container-box d-flex">
  <div class="left-panel">
    <h5>Sections</h5>
    <div id="sectionButtons"></div>
    <hr>
    <h6>Questions</h6>
    <div id="questionButtons"></div>
    <button class="btn btn-success mt-3" id="submitExam">Submit Exam</button>
  </div>

  <div class="right-panel">
    <div style="text-align: right; font-weight: bold; font-size: 18px; color: #ff4d4d; margin-bottom: 5px;">
      Time Left: <span id="timerDisplay">--:--</span>
    </div>
    <h3 id="examTitleHeader"></h3>
    <p><strong>Instructions:</strong> <span id="examDescText"></span></p>
    <form id="examForm" onsubmit="event.preventDefault(); submitExamAjax();">
      <div id="questionContainer"></div>
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
let questionData = [];            // Fill this with question objects
let sectionQuestionMap = {};      // Fill this with section ID => [question indexes]
let currentQuestionIndex = 0;
let currentSectionId = null;
let timeLeft = 3600; // in seconds, example 60 mins
let timerDisplay = document.getElementById('timerDisplay');
let timerInterval = null;

function startExam() {
  document.documentElement.requestFullscreen?.();
  document.querySelector('.start-screen').style.display = 'none';
  document.querySelector('.container-box').style.display = 'flex';
  startTimer();
  const firstSectionBtn = document.querySelector('.section-btn');
  if (firstSectionBtn) firstSectionBtn.click();
}

function startTimer() {
  timerInterval = setInterval(() => {
    let minutes = Math.floor(timeLeft / 60);
    let seconds = timeLeft % 60;
    timerDisplay.textContent = `${minutes}:${seconds < 10 ? '0' + seconds : seconds}`;
    timerDisplay.style.color = timeLeft <= 120 ? 'red' : 'black';

    if (timeLeft <= 0) {
      clearInterval(timerInterval);
      autoSubmitExam("Time's up!");
    }
    timeLeft--;
  }, 1000);
}

function renderSections(sections) {
  const sectionDiv = document.getElementById('sectionButtons');
  sectionDiv.innerHTML = '';
  sections.forEach(sec => {
    const btn = document.createElement('button');
    btn.className = 'btn btn-outline-primary btn-block section-btn';
    btn.textContent = sec.section_name;
    btn.dataset.sectionId = sec.id;
    btn.onclick = () => handleSectionClick(sec.id);
    sectionDiv.appendChild(btn);
  });
}

function handleSectionClick(sectionId) {
  currentSectionId = sectionId;
  document.querySelectorAll('.section-btn').forEach(btn => btn.classList.remove('active'));
  document.querySelector(`.section-btn[data-section-id="${sectionId}"]`)?.classList.add('active');
  renderQuestionButtons(sectionId);
  if (sectionQuestionMap[sectionId]?.length) {
    showQuestion(sectionQuestionMap[sectionId][0]);
  }
}

function renderQuestionButtons(sectionId) {
  const container = document.getElementById('questionButtons');
  container.innerHTML = '';
  sectionQuestionMap[sectionId].forEach((qIndex, i) => {
    const btn = document.createElement('button');
    btn.className = 'btn btn-outline-secondary question-btn';
    btn.innerText = i + 1;
    btn.id = 'btn-' + qIndex;
    btn.onclick = () => showQuestion(qIndex);
    btn.dataset.status = 'unanswered';
    container.appendChild(btn);
  });
}

function showQuestion(index) {
  document.querySelectorAll('.question-container').forEach(q => q.classList.remove('active'));
  document.getElementById('question-' + index)?.classList.add('active');
  currentQuestionIndex = index;
  const btn = document.getElementById('btn-' + index);
  if (btn && btn.dataset.status !== 'answered') {
    btn.dataset.status = 'viewed';
  }
  updateQuestionButtonStatus(index);
}

function updateQuestionButtonStatus(index) {
  const btn = document.getElementById('btn-' + index);
  if (!btn) return;
  btn.classList.remove('answered', 'viewed', 'unanswered');
  btn.classList.add(btn.dataset.status);
}

function markAnswered(index) {
  const btn = document.getElementById('btn-' + index);
  if (btn) {
    btn.dataset.status = 'answered';
    updateQuestionButtonStatus(index);
  }
}

function clearAnswer() {
  const currentQ = document.getElementById(`question-${currentQuestionIndex}`);
  currentQ?.querySelectorAll('input[type="radio"]').forEach(r => r.checked = false);
  const btn = document.getElementById('btn-' + currentQuestionIndex);
  if (btn) {
    btn.dataset.status = 'unanswered';
    updateQuestionButtonStatus(currentQuestionIndex);
  }
}

function navigateQuestion(dir) {
  const qList = sectionQuestionMap[currentSectionId] || [];
  const currentIndexInSection = qList.indexOf(currentQuestionIndex);
  if (dir === 'next' && currentIndexInSection < qList.length - 1) {
    showQuestion(qList[currentIndexInSection + 1]);
  } else if (dir === 'prev' && currentIndexInSection > 0) {
    showQuestion(qList[currentIndexInSection - 1]);
  }
}

function autoSubmitExam(message) {
  Swal.fire({
    title: 'Auto Submit',
    text: message,
    icon: 'info',
    showConfirmButton: false,
    timer: 2000
  });
  setTimeout(() => submitExamAjax(), 2000);
}

document.getElementById('submitExam').addEventListener('click', () => {
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
          html: `Your exam has been submitted.<br><strong>Your Score: ${data.score}/${data.total_questions}</strong>`,
          icon: 'success',
          showCancelButton: true,
          confirmButtonText: 'Go to Dashboard',
          cancelButtonText: 'Download Excel',
          reverseButtons: true
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = 'userdashbord.php';
          } else {
            window.location.href = `excel.php?exam_id=${formData.get('exam_id')}&score=${data.score}&total=${data.total_questions}`;
          }
        });
      } else {
        Swal.fire('Error', data.message || 'Something went wrong.', 'error');
      }
    })
    .catch(() => Swal.fire('Error', 'Network error occurred.', 'error'));
}
</script>
</body>
</html>
