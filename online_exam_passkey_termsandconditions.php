<?php
session_start();
require_once "config.php";

// Check login and exam_id in GET
if (!isset($_SESSION["registration_id"]) || !isset($_GET["exam_id"])) {
    header("Location: login.php");
    exit;
}

$registrationId = $_SESSION["registration_id"];
$examId = intval($_GET["exam_id"]);
$error = "";
$redirectWithSessionScript = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $submittedPasskey = trim($_POST["passkey"]);

    $sql = "SELECT passkey_id FROM passkey 
            WHERE registration_id = ? AND exam_id = ? AND passkey_n = ? AND is_used = 0 LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iis", $registrationId, $examId, $submittedPasskey);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $passkeyId);

    if (mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);

        // Mark passkey as used
        $update = mysqli_prepare($conn, "UPDATE passkey SET is_used = 1 WHERE passkey_id = ?");
        mysqli_stmt_bind_param($update, "i", $passkeyId);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);

        $_SESSION["exam_id"] = $examId;

        // JS: store flag and redirect
        $redirectWithSessionScript = "
            <script>
                sessionStorage.setItem('passkeyValidated', 'true');
                Swal.fire({
                    icon: 'success',
                    title: 'Passkey Validated!',
                    text: 'Redirecting to instructions...',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = 'exam_instructions.php?exam_id=$examId';
                });
            </script>
        ";
    } else {
        $error = "❌ Invalid or already used passkey!";
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Enter Exam Passkey</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { background-color: #f8f9fa; }
    .card { border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
    .passcode-input { max-width: 300px; }
  </style>
</head>
<body>

<?= $redirectWithSessionScript ?>

<?php if (!empty($error)) : ?>
<script>
  Swal.fire({
    icon: 'error',
    title: 'Oops...',
    text: <?= json_encode($error) ?>,
    confirmButtonColor: '#d33'
  });
</script>
<?php endif; ?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="card p-4">
        <h2 class="mb-4 text-center">🔐 Enter Exam Passkey</h2>
        <form method="POST">
          <div class="mb-4 text-center">
            <input type="text" name="passkey" class="form-control passcode-input mx-auto" placeholder="e.g. o167" required />
          </div>
          <div class="d-flex justify-content-center">
            <button class="btn btn-primary w-75" type="submit">Next</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

</body>
</html>
