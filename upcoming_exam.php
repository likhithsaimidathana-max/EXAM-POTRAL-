<?php
session_start();
if (!isset($_SESSION["user_logged_in"]) || !isset($_SESSION["registration_id"])) {
    header("Location: login.php");
    exit;
}

require_once "config.php";
$registrationId = $_SESSION["registration_id"];
date_default_timezone_set("Asia/Kolkata");
$currentDateTime = new DateTime();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Exams</title>
    <link rel="stylesheet" href="login.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- Navbar -->
<div class="header bg-primary d-flex justify-content-between align-items-center p-3">
    <div class="d-flex align-items-center">
        <img src="icon box.png" alt="Logo" width="40" class="me-2">
        <span class="brand text-white h5 mb-0">ExamPortal</span>
    </div>
    <div>
        <a href="user.php" class="btn btn-light">DASHBOARD</a>
        <!-- <a href="logout.php" class="btn btn-light">Logout</a> -->
    </div>
</div>

<!-- Body -->
<div class="container mt-4">
    <h3 class="mb-4">📅 Upcoming Exams (Today & Tomorrow)</h3>
    <div class="row g-4">

<?php
$query = "
    SELECT e.*, p.passkey_n 
    FROM exam e
    INNER JOIN passkey p ON e.exam_id = p.exam_id
    WHERE p.registration_id = ? 
      AND e.examdate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
    ORDER BY e.examdate ASC, e.examtime ASC
";

$stmt = mysqli_prepare($conn, $query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $registrationId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        while ($exam = mysqli_fetch_assoc($result)) {
            $examId = $exam['exam_id'];
            $passkey = $exam['passkey_n'];
            $examDate = $exam['examdate'];
            $examTime = $exam['examtime'];
            $duration = intval($exam['duration']);
            $graceStart = new DateTime($exam['gracestart']);
            $graceEnd = new DateTime($exam['graceend']);

            $isWithinGrace = ($currentDateTime >= $graceStart && $currentDateTime <= $graceEnd);

            $label = '';
            if ($examDate == date('Y-m-d')) {
                $label = ' (Today)';
            } elseif ($examDate == date('Y-m-d', strtotime('+1 day'))) {
                $label = ' (Tomorrow)';
            }

            echo '
                <div class="col-md-4">
                    <div class="card p-3 shadow-sm">
                        <h5 class="card-title">' . htmlspecialchars($exam['examtitle']) . '</h5>
                        <p>' . htmlspecialchars($exam['examdescription']) . '</p>
                        <p><strong>Date:</strong> ' . htmlspecialchars($examDate) . $label . '</p>
                        <p><strong>Time:</strong> ' . date('h:i A', strtotime($examTime)) . '</p>
                        <p><strong>Duration:</strong> ' . $duration . ' minutes</p>';

            if ($isWithinGrace) {
                echo '
                        <div class="mb-2">
                            <label class="form-label"><strong>Your Passkey:</strong></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="passkey-' . $examId . '" value="' . htmlspecialchars($passkey) . '" readonly>
                                <button class="btn btn-outline-secondary" onclick="copyPasskey(\'passkey-' . $examId . '\')">Copy</button>
                            </div>
                        </div>
                        <a href="enter_exam_passkey.php?exam_id=' . $examId . '" class="btn btn-primary w-100 mt-2">Start Exam</a>';
            } elseif ($currentDateTime < $graceStart) {
                echo '<div class="alert alert-info mt-3">⏳ Passkey will be available during the exam window.</div>';
            } else {
                echo '<div class="alert alert-danger mt-3">⛔ This exam has closed. Go and meet your admin.</div>';
            }

            echo '
                    </div>
                </div>';
        }
    } else {
        echo "<p class='text-muted'>No exams scheduled for today or tomorrow.</p>";
    }

    mysqli_stmt_close($stmt);
} else {
    echo "<p class='text-danger'>Failed to prepare query: " . mysqli_error($conn) . "</p>";
}
?>

    </div>
</div>

<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

<!-- Copy Passkey Script with SweetAlert2 -->
<script>
function copyPasskey(inputId) {
    const input = document.getElementById(inputId);
    input.select();
    input.setSelectionRange(0, 99999); // For mobile devices
    document.execCommand("copy");

    Swal.fire({
        icon: 'success',
        title: 'Passkey Copied!',
        text: `Passkey: ${input.value}`,
        confirmButtonColor: '#3085d6'
    });
}
</script>

</body>
</html>
