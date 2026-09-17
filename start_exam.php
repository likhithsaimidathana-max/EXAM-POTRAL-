<div class="start-screen">
    <h2>Start Your Exam</h2>
    <button class="btn btn-primary btn-lg" onclick="startExam()">Start Exam</button>
</div>











<?php
session_start();
if (!isset($_SESSION['registration_id'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';

$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
if ($exam_id <= 0) {
    die("Invalid exam ID.");
}

$sql = "SELECT * FROM exam WHERE exam_id = $exam_id";
$result = $conn->query($sql);
if (!$result || $result->num_rows == 0) {
    die("Exam not found.");
}
$exam = $result->fetch_assoc();

$duration = isset($exam['duration']) ? intval($exam['duration']) : 60;

$sql = "SELECT * FROM sections WHERE exam_id = $exam_id";
$sections_result = $conn->query($sql);
$sections = [];
if ($sections_result) {
    while ($row = $sections_result->fetch_assoc()) {
        $sections[] = $row;
    }
}

$sql = "SELECT * FROM questions WHERE exam_id = $exam_id";
$questions_result = $conn->query($sql);
$questions = [];
if ($questions_result) {
    while ($row = $questions_result->fetch_assoc()) {
        $questions[] = $row;
    }

    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam - <?php echo htmlspecialchars($exam['examtitle']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <style>
    body, html {
        height: 100%;
        margin: 0;
        overflow: hidden; /* Prevent scrolling on start screen */
    }

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
        .nav-btn { margin: 10px 5px; }
        .accordion-button::after {
            content: '\f078';
            font-family: 'FontAwesome';
            font-size: 18px;
            color: #007bff;
        }
        .accordion-button:not(.collapsed)::after {
            content: '\f077';
        }
        .question-container { display: none; }
        .question-container.active { display: block; }
    </style>
</head>
<body>

<div class="start-screen">
    <h2>Start Your Exam</h2>
    <button class="btn btn-primary btn-lg" onclick="startExam()">Start Exam</button>
</div>
    
<div class="container-box d-flex">
    <div class="left-panel">

    

    <h5>Sections</h5>
    <?php if (!empty($sections)): ?>
        <div class="accordion" id="sectionsAccordion">
            <?php foreach ($sections as $index => $section): ?>
                <div class="card">
                    <div class="card-header" id="heading-<?php echo $index; ?>">
                        <h5 class="mb-0">
                            <button class="btn btn-link accordion-button" type="button" data-toggle="collapse" data-target="#collapse-<?php echo $index; ?>" aria-expanded="true" aria-controls="collapse-<?php echo $index; ?>">
                                <?php echo htmlspecialchars($section['section_name']); ?>
                            </button>
                        </h5>
                    </div>
                    <div id="collapse-<?php echo $index; ?>" class="collapse" aria-labelledby="heading-<?php echo $index; ?>" data-parent="#sectionsAccordion">
                        <div class="card-body">
                            <div id="sections-<?php echo $index; ?>-questions">
                                <?php
                                $localIndex = 1;
                                foreach ($questions as $globalIndex => $question) {
                                    if (isset($question['section_id']) && $question['section_id'] == $section['section_id']) {
                                        echo '<button class="btn btn-outline-secondary question-btn unanswered" id="btn-' . $globalIndex . '" onclick="showQuestion(' . $globalIndex . ')">';
                                        echo $localIndex;
                                        echo '</button>';
                                        $localIndex++;
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No sections available.</p>
    <?php endif; ?>

    <hr>
    <h6>Questions</h6>
    <!-- You can fill this if you want all question buttons globally -->
    <div id="questionButtons">
        <?php foreach ($questions as $index => $question): ?>
            <button class="btn btn-outline-secondary question-btn unanswered" id="btn-<?php echo $index; ?>" onclick="showQuestion(<?php echo $index; ?>)">
                <?php echo $index + 1; ?>
            </button>
        <?php endforeach; ?>
    </div>

    <button class="btn btn-success mt-3" id="submitExam">Submit Exam</button>
</div>


    <div class="right-panel">
        <div style="text-align: right; font-weight: bold; font-size: 18px; color: #ff4d4d; margin-bottom: 5px;">
            Time Left: <span id="timerDisplay">--:--</span>
        </div>
        <h3><?php echo htmlspecialchars($exam['examtitle']); ?></h3>
        <p><strong>Instructions:</strong> <?php echo htmlspecialchars($exam['examdescription']); ?></p>
        <form id="examForm" onsubmit="event.preventDefault(); submitExamAjax();">
            <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
            <div id="questionContainer">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="question-container" id="question-<?php echo $index; ?>">
                        <p><strong>Q<?php echo $index + 1; ?>:</strong> <?php echo htmlspecialchars($question['question']); ?></p>
                        <input type="hidden" name="question_id<?php echo $index; ?>" value="<?php echo $question['question_id']; ?>">
                        <?php foreach (['option1', 'option2', 'option3', 'option4'] as $opt): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="answer_<?php echo $index; ?>" value="<?php echo $opt; ?>" onclick="markAnswered(<?php echo $index; ?>)">
                                <label class="form-check-label"><?php echo htmlspecialchars($question[$opt]); ?></label>
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

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
let questions = <?php echo json_encode($questions); ?>;
let currentQuestionIndex = 0;
let timeLeft = <?php echo $duration * 60; ?>;
let timerDisplay = document.getElementById('timerDisplay');
let timerInterval;
let examSubmitted = false;

function startExam() {
    const elem = document.documentElement;
    if (elem.requestFullscreen) elem.requestFullscreen();
    document.querySelector('.start-screen').style.display = 'none';
    document.querySelector('.container-box').style.display = 'flex';
    document.body.style.overflow = 'auto'; // Allow scrolling after starting exam
    startTimer();
    showQuestion(0);
}


function showQuestion(index) {
    document.querySelectorAll('.question-container').forEach(q => q.classList.remove('active'));
    document.getElementById('question-' + index).classList.add('active');
    currentQuestionIndex = index;
    updateQuestionButtonStatus(index);
}

function updateQuestionButtonStatus(index) {
    const btn = document.getElementById('btn-' + index);
    const container = document.getElementById('question-' + index);
    const answered = container.querySelector('input[type="radio"]:checked') !== null;
    btn.classList.remove('answered', 'viewed', 'unanswered');
    if (answered) {
        btn.classList.add('answered');
    } else {
        btn.classList.add('viewed');
    }
}

function markAnswered(index) {
    const btn = document.getElementById('btn-' + index);
    btn.classList.remove('viewed', 'unanswered');
    btn.classList.add('answered');
}

function clearAnswer() {
    const container = document.getElementById('question-' + currentQuestionIndex);
    container.querySelectorAll('input[type="radio"]').forEach(r => r.checked = false);
    const btn = document.getElementById('btn-' + currentQuestionIndex);
    btn.classList.remove('answered', 'viewed');
    btn.classList.add('unanswered');
}

function navigateQuestion(direction) {
    if (direction === 'next' && currentQuestionIndex < questions.length - 1) {
        showQuestion(currentQuestionIndex + 1);
    } else if (direction === 'prev' && currentQuestionIndex > 0) {
        showQuestion(currentQuestionIndex - 1);
    }
}

function startTimer() {
    timerInterval = setInterval(() => {
        let minutes = Math.floor(timeLeft / 60);
        let seconds = timeLeft % 60;
        timerDisplay.textContent = `${minutes}:${seconds < 10 ? '0' + seconds : seconds}`;

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
    if (examSubmitted) return;
    examSubmitted = true;

    const submitBtn = document.getElementById('submitExam');
    submitBtn.disabled = true;
    const formData = new FormData(document.getElementById('examForm'));
    fetch('submitexam.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        // console.log(data);
        // exit();
        if (data.status === 'success') {
            Swal.fire({
                title: 'Thank You!',
                html: `Your exam has been submitted.<br><strong>Your Score: ${data.score}/${data.total_questions}</strong>`,
                icon: 'success',
                confirmButtonText: 'Go to Dashboard',
            }).then(() => {
                window.location.href = 'user.php';
            });
        } else {
            Swal.fire('Error', data.message || 'Something went wrong.', 'error');
            submitBtn.disabled = false;
            examSubmitted = false;
        }
    })
    .catch(() => {
        Swal.fire('Error', 'Network error occurred.', 'error');
        submitBtn.disabled = false;
        examSubmitted = false;
    });
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        Swal.fire({
            title: 'Exit Detected!',
            text: "You pressed ESC. The exam will be submitted.",
            icon: 'warning',
            showConfirmButton: false,
            timer: 2000
        });

        setTimeout(() => {
            submitExamAjax();
        }, 2000);
    }
});
</script>
</body>
</html>
