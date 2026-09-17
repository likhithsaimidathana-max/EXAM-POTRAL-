<?php
session_start();
require_once "config.php";

$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
$examtitle = "";
$skillRequired = "";
$sections = [];
$matched_users = [];

if ($exam_id > 0) {
    $query = "SELECT examtitle, skillRequired FROM exam WHERE exam_id = $exam_id";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $examdata = mysqli_fetch_assoc($result);
        $examtitle = $examdata['examtitle'];
        $skillRequired = $examdata['skillRequired'];
    }

    $query = "SELECT section_id, section_name, noofquestions FROM sections WHERE exam_id = $exam_id";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $sections[] = $row;
        }
    }

    $escaped_skill = mysqli_real_escape_string($conn, $skillRequired);
    $query = "SELECT registration_id, fullname FROM registration WHERE skills LIKE '%$escaped_skill%'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $matched_users[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Questions - <?= htmlspecialchars($examtitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- SweetAlert2 CSS and JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="container my-5">
    <h3 class="mb-3">Exam: <?= htmlspecialchars($examtitle) ?></h3>

    <!-- Multi-User Dropdown -->
    <div class="mb-3">
        <label for="fullname" class="form-label">Users Matching Skill</label>
        <select class="form-select" id="fullname" name="fullname[]" multiple required style="height: 150px;">
            <?php foreach ($matched_users as $user): ?>
                <option value="<?= htmlspecialchars($user['registration_id']) ?>">
                    <?= htmlspecialchars($user['fullname']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Section Dropdown -->
    <div class="mb-3">
        <label for="section_name" class="form-label">Exam Section</label>
        <select class="form-select" id="section_name" name="section_name" required>
            <option value="">-- Select a section_name --</option>
            <?php foreach ($sections as $sec): ?>
                <option value="<?= $sec['section_id'] ?>">
                    <?= htmlspecialchars($sec['section_name']) ?> (Max: <?= intval($sec['noofquestions']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Question Form -->
    <form id="questionForm">
        <div class="card p-4 mb-4">
            <div class="mb-3">
                <label class="form-label">Question</label>
                <textarea class="form-control" id="question" name="question" required></textarea>
            </div>
            <div class="row mb-3">
                <div class="col"><input type="text" class="form-control" id="option1" placeholder="Option 1" required /></div>
                <div class="col"><input type="text" class="form-control" id="option2" placeholder="Option 2" required /></div>
                <div class="col"><input type="text" class="form-control" id="option3" placeholder="Option 3" required /></div>
                <div class="col"><input type="text" class="form-control" id="option4" placeholder="Option 4" required /></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Correct Answer</label>
                <select class="form-select" id="correctanswer" required>
                    <option value="">Select</option>
                    <option value="1">Option 1</option>
                    <option value="2">Option 2</option>
                    <option value="3">Option 3</option>
                    <option value="4">Option 4</option>
                </select>
            </div>
            <button type="button" class="btn btn-success" onclick="addOrUpdateQuestion()">Add Question</button>
        </div>
    </form>

    <!-- Questions List -->
    <ul id="questionsList" class="list-group mb-4"></ul>

    <!-- Submit All -->
    <button class="btn btn-primary" onclick="submitExam()">Submit All Questions & Passkeys</button>
</div>

<script>
    const sectionLimits = <?= json_encode(array_column($sections, 'noofquestions', 'section_id')) ?>;
    let sectionQuestionCounts = {};
    let questions = [];

    function addOrUpdateQuestion() {
        const sectionSelect = document.getElementById('section_name');
    const sectionId = sectionSelect.value;
    const sectionName = sectionSelect.options[sectionSelect.selectedIndex]?.text.split(" (")[0].trim();

        const question = document.getElementById('question').value.trim();
        const option1 = document.getElementById('option1').value.trim();
        const option2 = document.getElementById('option2').value.trim();
        const option3 = document.getElementById('option3').value.trim();
        const option4 = document.getElementById('option4').value.trim();
        const correctanswer = document.getElementById('correctanswer').value;

        if (!sectionId || !question || !option1 || !option2 || !option3 || !option4 || !correctanswer) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Fields',
                text: 'Please fill all fields.'
            });
            return;
        }

        sectionQuestionCounts[sectionId] = sectionQuestionCounts[sectionId] || 0;

        if (sectionQuestionCounts[sectionId] >= sectionLimits[sectionId]) {
            Swal.fire({
                icon: 'error',
                title: 'Section Limit Reached',
                text: `You can only add ${sectionLimits[sectionId]} questions to this section.`
            });
            return;
        }

        // Store section_id for server-side insert
        questions.push({
            section_id: parseInt(sectionId),
            question,
            option1,
            option2,
            option3,
            option4,
            correctanswer: parseInt(correctanswer)
        });

        sectionQuestionCounts[sectionId]++;

    const li = document.createElement('li');
    li.className = 'list-group-item';
    li.textContent = `Section ${sectionName}: ${question}`;
        document.getElementById('questionsList').appendChild(li);
        document.getElementById('questionForm').reset();
    }

    function generatePasskey() {
        const letter = String.fromCharCode(97 + Math.floor(Math.random() * 26));
        const number = Math.floor(Math.random() * 1000);
        return letter + number.toString().padStart(3, '0');
    }

    function submitExam() {
        if (questions.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Questions Added',
                text: 'Add at least one question before submitting.'
            });
            return;
        }

        const selectedUsers = Array.from(document.getElementById('fullname').selectedOptions).map(opt => opt.value);
        if (selectedUsers.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Users Selected',
                text: 'Select at least one user.'
            });
            return;
        }

        const userPasskeys = selectedUsers.map(id => ({
            registration_id: id,
            passkey_n: generatePasskey()
        }));

        fetch("save_questions.php", {
            method: "POST",
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                questions: questions,
                userPasskeys: userPasskeys,
                exam_id: <?= $exam_id ?>
            })
        }).then(res => {
            if (!res.ok) {
                throw new Error('Server returned ' + res.status + ': ' + res.statusText);
            }
            return res.json();
        })
        .then(data => {
            console.log("Response from server:", data);
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Submitted',
                    text: 'Questions and passkeys submitted successfully!'
                }).then(() => {
                    window.location.href = 'admin.php';
                });
            } else {
                const errorMsg = data.error || 'Unknown error occurred';
                console.error("Server error:", errorMsg);
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: errorMsg
                });
            }
        }).catch(err => {
            console.error("Fetch error:", err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Network error: ' + err.message
            });
        });
    }
</script>

</body>
</html>
