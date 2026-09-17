<?php
session_start();
if (!isset($_SESSION['registration_id'])) {
    header("Location: login.php");
    exit();
}
if (!isset($_GET['exam_id'])) {
    header("Location: user.php");
    exit();
}

$examId = intval($_GET['exam_id']); // ✅ Define the variable properly before using it
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Exam Instructions & Start</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { background-color: #f8f9fa; }
    .card { border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
    .warning { color: red; font-weight: bold; }
  </style>
</head>
<body>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card p-4">
        <h2 class="mb-4 text-center">📌 Exam Instructions</h2>

        <ul>
          <li><span class="warning">The exam will run in fullscreen mode.</span></li>
          <li><span class="warning">If you exit fullscreen or switch tabs, the exam will be terminated.</span></li>
          <li>Do not refresh or close the browser during the exam.</li>
          <li>You must accept the terms to start.</li>
        </ul>

        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="agreeTerms" />
          <label class="form-check-label" for="agreeTerms">
            I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">terms and conditions</a>.
          </label>
        </div>

        <div class="d-flex justify-content-center">
          <button id="startExamBtn" class="btn btn-success w-75" disabled>Start Exam</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ✅ Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header">
        <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ul>
          <li>You must stay in fullscreen mode during the exam.</li>
          <li>Switching tabs or exiting fullscreen will end the exam immediately.</li>
          <li>Do not use unfair means during the exam. Any cheating will result in disqualification.</li>
        </ul>
        <p>By agreeing, you confirm that you understand and accept these terms.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- ✅ Bootstrap JS Bundle for Modal functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const examId = <?= json_encode($examId) ?>;

  // ✅ Validate passkey from sessionStorage using SweetAlert
  if (sessionStorage.getItem("passkeyValidated") !== "true") {
    Swal.fire({
      icon: 'warning',
      title: 'Passkey Required',
      text: 'Please enter the passkey first.',
      confirmButtonText: 'OK'
    }).then(() => {
      window.location.href = `enter_exam_passkey.php?exam_id=${examId}`;
    });
  }

  const agreeBox = document.getElementById("agreeTerms");
  const startBtn = document.getElementById("startExamBtn");

  agreeBox.addEventListener("change", () => {
    startBtn.disabled = !agreeBox.checked;
  });

  startBtn.addEventListener("click", async () => {
    const elem = document.documentElement;
    if (elem.requestFullscreen) await elem.requestFullscreen();
    else if (elem.webkitRequestFullscreen) await elem.webkitRequestFullscreen();
    else if (elem.msRequestFullscreen) await elem.msRequestFullscreen();

    // Redirect to the exam page
    window.location.href = `test.php?exam_id=${examId}`;
  });

  // Auto-terminate exam on fullscreen exit with SweetAlert
  document.addEventListener('fullscreenchange', () => {
    if (!document.fullscreenElement) {
      Swal.fire({
        icon: 'error',
        title: 'Fullscreen Exit Detected',
        text: 'You exited fullscreen. The exam is now terminated.',
        confirmButtonText: 'Return to Dashboard'
      }).then(() => {
        window.location.href = "user.php";
      });
    }
  });

  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState !== 'visible') {
      Swal.fire({
        icon: 'error',
        title: 'Tab Switch/Minimize Detected',
        text: 'You switched tabs or minimized. The exam is terminated.',
        confirmButtonText: 'Return to Dashboard'
      }).then(() => {
        window.location.href = "user.php";
      });
    }
  });
</script>

</body>
</html>
