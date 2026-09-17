<?php
session_start();
require_once "config.php";

$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
$exam_title = "";
$sections = [];
$matched_users = [];

if ($exam_id > 0) {
    // Get exam title and skill_required
    $stmt = $conn->prepare("SELECT exam_title, skill_required FROM exams WHERE id = ?");
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $examData = $res->fetch_assoc();
        $exam_title = $examData['exam_title'];
        $skill_required = $examData['skill_required'];
    }
    $stmt->close();
// Get sections for the exam
$stmt = $conn->prepare("SELECT id, section_name FROM exam_sections WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$sections = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

    // Get users matching skill_required
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE skills LIKE CONCAT('%', ?, '%')");
    $stmt->bind_param("s", $skill_required);
    $stmt->execute();
    $matched_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$sectionOptions = "";
foreach ($sections as $sec) {
  $sectionOptions .= "<option value='{$sec['id']}'>" . htmlspecialchars($sec['section_name']) . "</option>";

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Questions - <?= htmlspecialchars($exam_title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { background: #f2f4f7; padding: 20px; }
    .card { border-radius: 12px; }
    .question-block { background: #fff; padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #ccc; }
  </style>
</head>
<body>
<div class="container">
  <div class="card p-4">
    <h4 class="mb-4">Add Questions for: <?= htmlspecialchars($exam_title) ?></h4>
    <form id="questionForm">
      <input type="hidden" name="exam_id" value="<?= $exam_id ?>">

      <div class="mb-3">
        <label for="user_ids" class="form-label">Select Users (for passkey)</label>
        <select name="user_ids[]" id="user_ids" class="form-select" multiple required>
          <?php foreach ($matched_users as $user): ?>
            <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div id="questionContainer"></div>

      <div class="text-center">
        <button type="button" class="btn btn-secondary" onclick="addQuestion()">+ Add Question</button>
        <button type="submit" class="btn btn-success mt-3">Submit & Generate Passkeys</button>
      </div>
    </form>
  </div>
</div>

<script>
let index = 0;
const sectionOptions = <?= $sectionOptions ?>;

function addQuestion() {
  const block = `
  <div class="question-block" id="q${index}">
    <div class="mb-2">
      <label>Section:</label>
      <select name="questions[${index}][section_id]" class="form-select" required>
        <option value="">-- Select Section --</option>
        ${sectionOptions}
      </select>
    </div>
    <div class="mb-2">
      <label>Question:</label>
      <textarea name="questions[${index}][question_text]" class="form-control" required></textarea>
    </div>
    <div class="row mb-2">
      <div class="col"><input type="text" name="questions[${index}][option_a]" class="form-control" placeholder="Option A" required></div>
      <div class="col"><input type="text" name="questions[${index}][option_b]" class="form-control" placeholder="Option B" required></div>
    </div>
    <div class="row mb-2">
      <div class="col"><input type="text" name="questions[${index}][option_c]" class="form-control" placeholder="Option C" required></div>
      <div class="col"><input type="text" name="questions[${index}][option_d]" class="form-control" placeholder="Option D" required></div>
    </div>
    <div class="mb-2">
      <label>Correct Answer:</label>
      <select name="questions[${index}][correct_answer]" class="form-select" required>
        <option value="">Select</option>
        <option value="A">Option A</option>
        <option value="B">Option B</option>
        <option value="C">Option C</option>
        <option value="D">Option D</option>
      </select>
    </div>
    <button type="button" class="btn btn-sm btn-danger" onclick="removeQuestion(${index})">Remove</button>
  </div>`;
  $('#questionContainer').append(block);
  index++;
}

function removeQuestion(i) {
  $('#q' + i).remove();
}

$('#questionForm').on('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  $.ajax({
    url: 'save_questions.php',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function(res) {
      Swal.fire("Success!", res, "success");
      $('#questionContainer').empty();
      addQuestion();
    },
    error: function() {
      Swal.fire("Error", "Could not save questions.", "error");
    }
  });
});

$(document).ready(function () {
  addQuestion(); // Load one question by default
});
</script>
</body>
</html>