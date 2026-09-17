<?php
session_start();
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Basic CSRF check
  if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    $errorMsg = 'Invalid CSRF token.';
  } else {
    $examtitle = trim($_POST['examtitle'] ?? '');
    $examdescription = trim($_POST['examdescription'] ?? '');
    $examdate = trim($_POST['examdate'] ?? '');
    $examtime = trim($_POST['examtime'] ?? '');
    $duration = intval($_POST['duration'] ?? 0);
    $gracestart = trim($_POST['gracestart'] ?? '');
    $graceend = trim($_POST['graceend'] ?? '');
    $skillRequired = trim($_POST['skillRequired'] ?? '');

    $section_name = $_POST['section_name'] ?? [];
    $noOfquestions = $_POST['noofquestions'] ?? [];

    // Use prepared statement for exam insert
    $stmt = $conn->prepare("INSERT INTO exam (examtitle, examdescription, examdate, examtime, duration, gracestart, graceend, skillRequired) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
      $stmt->bind_param('ssssisss', $examtitle, $examdescription, $examdate, $examtime, $duration, $gracestart, $graceend, $skillRequired);
      if ($stmt->execute()) {
        $exam_id = $stmt->insert_id;

        // Insert sections using prepared statement
        $secStmt = $conn->prepare("INSERT INTO sections (exam_id, section_name, noofquestions) VALUES (?, ?, ?)");
        if ($secStmt) {
          for ($i = 0; $i < count($section_name); $i++) {
            $secName = trim($section_name[$i]);
            $numQ = intval($noOfquestions[$i]);
            $secStmt->bind_param('isi', $exam_id, $secName, $numQ);
            $secStmt->execute();
          }
          $secStmt->close();
        }

        $stmt->close();
        $_SESSION['flash'] = "Exam created successfully!";
        header("Location: add_question.php?exam_id=$exam_id");
        exit();
      } else {
        $errorMsg = $stmt->error;
      }
    } else {
      $errorMsg = $conn->error;
    }
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Create New Exam</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <a class="navbar-brand" href="#">Exam Portal - Admin</a>
    <div class="collapse navbar-collapse" id="adminNavbar">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link text-white" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="create_exam.php">Create Exam</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="add_question.php">Add Questions</a></li>
        <li class="nav-item"><a class="nav-link text-danger" href="login.php">Logout</a></li>
      </ul>
    </div>
  </nav>

  <div class="container mt-5">
    <h3 class="text-center mb-4">📝 Create New Exam</h3>

    <?php if (isset($errorMsg)): ?>
      <script>
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: <?= json_encode($errorMsg) ?>
        });
      </script>
    <?php endif; ?>

    <?php
    // Generate CSRF token for the form
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    ?>
    <form method="POST" action="" id="examForm" novalidate>
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
      <div class="mb-3">
        <label class="form-label">Exam Title</label>
        <input type="text" class="form-control" name="examtitle" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Exam Description</label>
        <textarea class="form-control" name="examdescription" rows="4" required></textarea>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Exam Date</label>
          <input type="date" class="form-control" name="examdate" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Exam Time</label>
          <input type="time" class="form-control" name="examtime" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Duration (in minutes)</label>
        <input type="number" class="form-control" name="duration" required>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Grace Start Time</label>
          <input type="datetime-local" class="form-control" name="gracestart" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Grace End Time</label>
          <input type="datetime-local" class="form-control" name="graceend" required>
        </div>
      </div>

      <div class="col-md-12 mb-3">
        <label class="form-label">Skill Required</label>
        <select class="form-select" name="skillRequired" required>
          <option value="" disabled selected>Select Required Skill</option>
          <option value="html">html</option>
          <option value="Python">Python</option>
          <option value="Java">Java</option>
          <option value="C++">C++</option>
          <option value="HTML & CSS">HTML & CSS</option>
          <option value="JavaScript">JavaScript</option>
          <option value="SQL">SQL</option>
          <option value="Data Structures & Algorithms">Data Structures & Algorithms</option>
          <option value="Object-Oriented Programming (OOP)">Object-Oriented Programming (OOP)</option>
          <option value="Web Development">Web Development</option>
          <option value="Android Development">Android Development</option>
          <option value="iOS Development">iOS Development</option>
          <option value="Machine Learning">Machine Learning</option>
          <option value="Artificial Intelligence">Artificial Intelligence</option>
          <option value="Operating Systems">Operating Systems</option>
          <option value="Aptitude & Reasoning">Aptitude & Reasoning</option>
          <option value="English">English</option>
        </select>
      </div>

      <h5 class="mt-4">📘 Sections</h5>
      <div id="sections-container">
        <div class="row mb-3 section-row">
          <div class="col-md-6">
            <input type="text" class="form-control" name="section_name[]" placeholder="Section Name" required>
          </div>
          <div class="col-md-4">
            <input type="number" class="form-control" name="noofquestions[]" placeholder="No. of Questions" required>
          </div>
          <div class="col-md-2">
            <button type="button" class="btn btn-danger w-100 remove-section">X</button>
          </div>
        </div>
      </div>

      <button type="button" class="btn btn-primary mb-4" id="add-section">Add Section</button>

      <div class="col-12 d-flex justify-content-center">
        <button type="submit" class="btn btn-success w-25 mt-3 fs-5">Create Exam</button>
      </div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Add section dynamically
    document.getElementById('add-section').addEventListener('click', function () {
      const container = document.getElementById('sections-container');
      const newSection = document.createElement('div');
      newSection.classList.add('row', 'mb-3', 'section-row');
      newSection.innerHTML = `
        <div class="col-md-6">
          <input type="text" class="form-control" name="section_name[]" placeholder="Section Name" required>
        </div>
        <div class="col-md-4">
          <input type="number" class="form-control" name="noofquestions[]" placeholder="No. of Questions" required>
        </div>
        <div class="col-md-2">
          <button type="button" class="btn btn-danger w-100 remove-section">X</button>
        </div>
      `;
      container.appendChild(newSection);
    });

    // Remove section
    document.getElementById('sections-container').addEventListener('click', function (e) {
      if (e.target.classList.contains('remove-section')) {
        const sectionRow = e.target.closest('.section-row');
        if (sectionRow) sectionRow.remove();
      }
    });

    // SweetAlert validation
    document.getElementById("examForm").addEventListener("submit", function (e) {
      const fields = this.querySelectorAll("input[required], textarea[required], select[required]");
      let missingFields = [];

      fields.forEach(field => {
        if (!field.value.trim()) {
          const label = field.closest(".mb-3, .col-md-6, .col-md-4")?.querySelector(".form-label")?.innerText || field.placeholder;
          missingFields.push(label);
        }
      });

      if (missingFields.length > 0) {
        e.preventDefault();
        Swal.fire({
          icon: 'warning',
          title: 'Missing Fields',
          html: 'Please fill out the following fields:<br><strong>' + missingFields.join(', ') + '</strong>',
        });
      }
    });
  </script>
</body>
</html>
