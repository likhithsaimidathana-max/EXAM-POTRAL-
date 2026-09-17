<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['registration_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['exam_id'])) {
    header("Location: user.php");
    exit();
}

$exam_id = intval($_GET['exam_id']);
$questions = [];

if ($exam_id > 0) {
    // Correct join: questions.section_id should match sections.section_id
    $sql = "SELECT s.section_id, s.section_name, q.question_id, q.question, q.option1, q.option2, q.option3, q.option4, q.correctanswer
      FROM questions q
      JOIN sections s ON q.section_id = s.section_id
      WHERE q.exam_id = ?
      ORDER BY s.section_id, q.question_id";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        // Detailed error output to help debug prepare failure
        die("Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "i", $exam_id);

    if (!mysqli_stmt_execute($stmt)) {
        die("Execute failed: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        die("Getting result set failed: " . mysqli_stmt_error($stmt));
    }

  while ($row = mysqli_fetch_assoc($result)) {
    $questions[] = [
      'section_id' => intval($row['section_id']),
      'section' => $row['section_name'],
      'question_id' => intval($row['question_id']),
      'question' => $row['question'],
      'options' => [$row['option1'], $row['option2'], $row['option3'], $row['option4']],
      'correct' => intval($row['correctanswer'])
    ];
  }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Online Exam Portal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body { background-color: #f8fafc; }
    .question-box { background-color: white; border-radius: 10px; padding: 20px; }
    .question-number { width: 40px; height: 40px; margin: 2px; }
    .btn-clear { background-color: #ffc107; color: black; }
    .btn-answered { background-color: green; color: white; }
    .btn-unanswered { background-color: red; color: white; }
    .timer { font-size: 1.2rem; font-weight: bold; color: red; }
  </style>
</head>
<body>

<div id="startScreen" class="text-center mt-5">
  <h2>Welcome to the Exam</h2>
  <button class="btn btn-primary btn-lg" onclick="startExam()">Start Exam</button>
</div>

<div id="examContent" class="container-fluid p-4" style="display: none;">
  <div class="row">
    <div class="col-md-8">
      <div class="question-box">
        <div class="d-flex justify-content-between mb-2">
          <div><span id="timer" class="timer">Time: 01:00:00</span></div>
          <div><strong id="currentSection">Section:</strong></div>
        </div>
        <h5 id="questionText"><strong>Q1:</strong> Loading...</h5>
        <form id="questionForm"></form>
        <button type="button" class="btn btn-clear mb-3" onclick="clearSelection()">Clear</button>
        <div class="d-flex justify-content-between">
          <button class="btn btn-secondary" onclick="prevQuestion()">Previous</button>
          <button class="btn btn-primary" onclick="nextQuestion()">Next</button>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="accordion" id="sectionAccordion"></div>
      <div class="mt-4 text-end">
        <button class="btn btn-success" onclick="submitExam()">Submit Exam</button>
      </div>
    </div>
  </div>
</div>

<!-- Result Modal -->
<div class="modal fade" id="examOverModal" tabindex="-1" aria-labelledby="examOverModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="examOverModalLabel">Exam Over</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p>Thank you for completing the exam.</p>
        <p>Your Score: <strong id="scoreValue">Calculating...</strong></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="goToNextPage()">Next Page</button>
      </div>
    </div>
  </div>
</div>

<script>
  const questions = <?= json_encode($questions) ?>;
  const correctAnswers = questions.map(q => q.correct);

  let currentQuestion = 0;
  let userAnswers = Array(questions.length).fill(null);
  let timerInterval;

  function showQuestion(index) {
    currentQuestion = index;
    const q = questions[index];
    const questionText = document.getElementById('questionText');
    const questionForm = document.getElementById('questionForm');
    const currentSection = document.getElementById('currentSection');

    questionText.innerHTML = `<strong>Q${index + 1}:</strong> ${q.question}`;
    currentSection.textContent = `Section: ${q.section}`;

    questionForm.innerHTML = '';
    q.options.forEach((opt, i) => {
      const checked = (userAnswers[index] === i + 1) ? 'checked' : '';
      questionForm.innerHTML += `
        <div class="form-check">
          <input class="form-check-input" type="radio" name="option" id="option${i}" value="${i+1}" ${checked}>
          <label class="form-check-label" for="option${i}">${opt}</label>
        </div>`;
    });
  }

  function clearSelection() {
    userAnswers[currentQuestion] = null;
    document.querySelectorAll('input[name="option"]').forEach(radio => radio.checked = false);
    updateNavButtons();
  }

  function nextQuestion() {
    saveAnswer();
    if (currentQuestion < questions.length - 1) {
      showQuestion(currentQuestion + 1);
    }
  }

  function prevQuestion() {
    saveAnswer();
    if (currentQuestion > 0) {
      showQuestion(currentQuestion - 1);
    }
  }

  function saveAnswer() {
    const selectedOption = document.querySelector('input[name="option"]:checked');
    if (selectedOption) {
      userAnswers[currentQuestion] = parseInt(selectedOption.value);
    } else {
      userAnswers[currentQuestion] = null;
    }
    updateNavButtons();
  }

  function updateNavButtons() {
    // Optional: implement UI feedback for answered/unanswered
  }

  function startExam() {
    document.getElementById('startScreen').style.display = 'none';
    document.getElementById('examContent').style.display = 'block';
    showQuestion(0);
    startTimer(3600); // 1 hour in seconds
  }

  function startTimer(duration) {
    let time = duration;
    const timerEl = document.getElementById('timer');
    timerInterval = setInterval(() => {
      let minutes = Math.floor(time / 60);
      let seconds = time % 60;
      let hours = Math.floor(minutes / 60);
      minutes %= 60;

      timerEl.textContent = `Time: ${String(hours).padStart(2,'0')}:${String(minutes).padStart(2,'0')}:${String(seconds).padStart(2,'0')}`;
      if (--time < 0) {
        clearInterval(timerInterval);
        alert("⏰ Time's up! Exam will be submitted automatically.");
        submitExam();
      }
    }, 1000);
  }

  async function submitExam() {
    saveAnswer();
    clearInterval(timerInterval);

    // Build form data to post to server: include exam_id, and question_id/answer pairs
    const formData = new FormData();
    formData.append('exam_id', <?= $exam_id ?>);

    for (let i = 0; i < questions.length; i++) {
      const q = questions[i];
      // Use the index-based naming that submitexam.php expects: answer_0, question_id0
      formData.append('question_id' + i, q.question_id);
      if (userAnswers[i] !== null) {
        formData.append('answer_' + i, userAnswers[i]);
      }
    }

    try {
      const res = await fetch('submitexam.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      if (data.status === 'success') {
        const scoreValue = document.getElementById('scoreValue');
        scoreValue.textContent = `${data.score} / ${data.total_questions}`;
        const examOverModal = new bootstrap.Modal(document.getElementById('examOverModal'));
        examOverModal.show();
      } else {
        alert('Submission failed: ' + (data.message || 'unknown'));
      }
    } catch (err) {
      console.error(err);
      alert('Network error while submitting exam.');
    }
  }

  function goToNextPage() {
    window.location.href = 'user.php';
  }

  window.addEventListener("beforeunload", function (e) {
    return "Are you sure you want to leave? Your exam progress will be lost.";
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
