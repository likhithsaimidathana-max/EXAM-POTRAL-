<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Online Exam Portal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8fafc;
    }
    .question-box {
      background-color: white;
      border-radius: 10px;
      padding: 20px;
    }
    .question-number {
      width: 40px;
      height: 40px;
      margin: 2px;
    }
    .btn-clear {
      background-color: #ffc107;
      color: black;
    }
    .btn-answered {
      background-color: green !important;
      color: white;
    }
    .btn-viewed {
      background-color: orange !important;
      color: white;
    }
    .btn-unvisited {
      background-color: red !important;
      color: white;
    }
    .timer {
      font-size: 1.2rem;
      font-weight: bold;
      color: red;
    }
    .modal-body {
      text-align: center;
    }
  </style>
</head>
<body>

<!-- Start Screen -->
<div id="startScreen" class="text-center mt-5">
  <h2>Welcome to the Exam</h2>
  <button class="btn btn-primary btn-lg" onclick="startExam()">Start Exam</button>
</div>

<!-- Exam UI -->
<div id="examContent" class="container-fluid p-4" style="display: none;">
  <div class="row">
    <!-- Question Section -->
    <div class="col-md-8">
      <div class="question-box">
        <div class="d-flex justify-content-between mb-2">
          <div><span id="timer" class="timer">Time: 01:00:00</span></div>
          <div><strong id="currentSection">Section: HTML</strong></div>
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

    <!-- Sidebar -->
    <div class="col-md-4">
      <div class="accordion" id="sectionAccordion">
        <!-- HTML -->
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#htmlSection">HTML</button>
          </h2>
          <div id="htmlSection" class="accordion-collapse collapse show" data-bs-parent="#sectionAccordion">
            <div class="accordion-body d-flex flex-wrap" id="htmlButtons"></div>
          </div>
        </div>

        <!-- CSS -->
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cssSection">CSS</button>
          </h2>
          <div id="cssSection" class="accordion-collapse collapse" data-bs-parent="#sectionAccordion">
            <div class="accordion-body d-flex flex-wrap" id="cssButtons"></div>
          </div>
        </div>

        <!-- React -->
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#reactSection">REACT</button>
          </h2>
          <div id="reactSection" class="accordion-collapse collapse" data-bs-parent="#sectionAccordion">
            <div class="accordion-body d-flex flex-wrap" id="reactButtons"></div>
          </div>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="mt-4 text-end">
        <button class="btn btn-success" onclick="submitExam()">Submit Exam</button>
      </div>
    </div>
  </div>
</div>

<!-- Exam Over Modal -->
<div class="modal fade" id="examOverModal" tabindex="-1" aria-labelledby="examOverModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="examOverModalLabel">Exam Over</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Thank you for completing the exam.</p>
        <p>Your result will be available soon.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="goToNextPage()">Next Page</button>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const questions = [
    // HTML (5)
    { question: "Who is making the Web standards?", options: ["Google", "the World Wide Web Consortium", "Mozilla", "Firefox"] },
    { question: "What does HTML stand for?", options: ["Hyper Tool Markup Language", "Hyper Text Markup Language", "Hyperlinks Text Mark Language", "None"] },
    { question: "Choose the correct HTML element for the largest heading:", options: ["<heading>", "<h1>", "<h6>", "<head>"] },
    { question: "What is the correct HTML element for inserting a line break?", options: ["<break>", "<br>", "<lb>", "<b>"] },
    { question: "What is the correct HTML for adding a background color?", options: ["<body style='background-color:yellow;'>", "<background>yellow</background>", "<body bg='yellow'>", "<bg>yellow</bg>"] },
    // CSS (5)
    { question: "What does CSS stand for?", options: ["Creative Style Sheets", "Cascading Style Sheets", "Colorful Style Sheets", "Computer Style Sheets"] },
    { question: "Which HTML tag is used to define an internal style sheet?", options: ["<script>", "<style>", "<css>", "<link>"] },
    { question: "Which property is used to change the background color?", options: ["color", "bgcolor", "background-color", "background"] },
    { question: "Which CSS property controls the text size?", options: ["font-style", "text-size", "font-size", "text-style"] },
    { question: "How do you make text bold in CSS?", options: ["font-weight: bold;", "text-style: bold;", "font: bold;", "style: bold;"] },
    // React (5)
    { question: "What is React primarily used for?", options: ["Server-side rendering", "Database management", "Building user interfaces", "Designing UI mockups"] },
    { question: "What command is used to create a new React app?", options: ["npx create-react-app", "npm new react-app", "npx react new", "npm init react-app"] },
    { question: "What is a React component?", options: ["A class or function that returns HTML", "A database", "A server route", "A CSS rule"] },
    { question: "Which hook is used to manage state in functional components?", options: ["useEffect", "useState", "useRef", "useContext"] },
    { question: "JSX allows us to write ___ in React?", options: ["HTML", "CSS", "XML", "JavaScript and HTML combined"] }
  ];

  let currentQuestion = 0;
  let userAnswers = Array(questions.length).fill(null);
  let visitedQuestions = Array(questions.length).fill(false);
  let timerInterval;

  function showQuestion(index) {
    currentQuestion = index;
    visitedQuestions[index] = true;
    const q = questions[index];
    document.getElementById("questionText").innerHTML = `<strong>Q${index + 1}:</strong> ${q.question}`;
    let html = "";
    q.options.forEach((opt, i) => {
      const id = `q${index}_opt${i}`;
      const checked = userAnswers[index] === i ? "checked" : "";
      html += `
        <div class="form-check">
          <input class="form-check-input" type="radio" name="q${index}" id="${id}" value="${i}" ${checked}
            onchange="userAnswers[${index}] = ${i}; updateButtonColor(${index});">
          <label class="form-check-label" for="${id}">${opt}</label>
        </div>`;
    });
    document.getElementById("questionForm").innerHTML = html;

    let section = index < 5 ? "HTML" : index < 10 ? "CSS" : "REACT";
    document.getElementById("currentSection").innerText = `Section: ${section}`;

    updateButtonColor(index);
  }

  function clearSelection() {
    document.querySelectorAll(`input[name="q${currentQuestion}"]`).forEach(el => el.checked = false);
    userAnswers[currentQuestion] = null;
    updateButtonColor(currentQuestion);
  }

  function nextQuestion() {
    if (currentQuestion < questions.length - 1) showQuestion(currentQuestion + 1);
  }

  function prevQuestion() {
    if (currentQuestion > 0) showQuestion(currentQuestion - 1);
  }

  function goToQuestion(index) {
    showQuestion(index);
  }

  function renderButtons() {
    for (let i = 0; i < questions.length; i++) {
      const sectionId = i < 5 ? "htmlButtons" : i < 10 ? "cssButtons" : "reactButtons";
      const button = document.createElement("button");
      button.className = "btn question-number btn-unvisited";
      button.id = `btn-${i}`;
      button.innerText = i + 1;
      button.onclick = () => goToQuestion(i);
      document.getElementById(sectionId).appendChild(button);
    }
  }

  function updateButtonColor(index) {
    const btn = document.getElementById(`btn-${index}`);
    btn.className = "btn question-number";

    if (userAnswers[index] !== null) {
      btn.classList.add("btn-answered");
    } else if (visitedQuestions[index]) {
      btn.classList.add("btn-viewed");
    } else {
      btn.classList.add("btn-unvisited");
    }
  }

  function startExam() {
    launchFullscreen();
    document.getElementById("startScreen").style.display = "none";
    document.getElementById("examContent").style.display = "block";
    renderButtons();
    showQuestion(0);
    startTimer(3600);
  }

  function submitExam() {
    clearInterval(timerInterval);
    document.exitFullscreen?.();
    let myModal = new bootstrap.Modal(document.getElementById('examOverModal'));
    myModal.show();
  }

  function startTimer(duration) {
    let time = duration;
    timerInterval = setInterval(() => {
      const hours = Math.floor(time / 3600);
      const minutes = Math.floor((time % 3600) / 60);
      const seconds = time % 60;
      document.getElementById("timer").textContent =
        `Time: ${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
      if (--time < 0) {
        clearInterval(timerInterval);
        alert("Time's up! Submitting exam.");
        submitExam();
      }
    }, 1000);
  }

  function launchFullscreen() {
    const el = document.documentElement;
    if (el.requestFullscreen) el.requestFullscreen();
    else if (el.mozRequestFullScreen) el.mozRequestFullScreen();
    else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
    else if (el.msRequestFullscreen) el.msRequestFullscreen();
  }

  // Submit if ESC is pressed
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      submitExam();
    }
  });

  // Submit if user exits fullscreen
  function onFullScreenChange() {
    const isFullscreen =
      document.fullscreenElement ||
      document.webkitFullscreenElement ||
      document.mozFullScreenElement ||
      document.msFullscreenElement;

    if (!isFullscreen) {
      alert("You have exited fullscreen. The exam will now be submitted.");
      submitExam();
    }
  }

  document.addEventListener("fullscreenchange", onFullScreenChange);
  document.addEventListener("webkitfullscreenchange", onFullScreenChange);
  document.addEventListener("mozfullscreenchange", onFullScreenChange);
  document.addEventListener("msfullscreenchange", onFullScreenChange);

  // Optional security: disable right-click and F12
  document.addEventListener("contextmenu", e => e.preventDefault());
  document.addEventListener("keydown", function (e) {
    if (e.key === "F12" || (e.ctrlKey && e.shiftKey && e.key === "I")) {
      e.preventDefault();
    }
  });

  function goToNextPage() {
    window.location.href = "user.php";
  }
</script>
</body>
</html>
