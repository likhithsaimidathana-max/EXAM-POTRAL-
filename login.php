<?php
require_once "config.php";
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - SmartExam Pro</title>
  <link rel="stylesheet" href="login.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="header d-flex justify-content-between align-items-center px-4 py-2 bg-light shadow">
  <div class="d-flex align-items-center">
    <img src="icon box.png" alt="Logo" width="40" class="me-2">
    <span class="fw-bold fs-4 text-primary">SmartExam Pro</span>
  </div>
  <div>
    <a href="login.php" class="btn btn-outline-primary btn-sm">Login</a>
    <a href="registration.php" class="btn btn-outline-primary btn-sm">Register</a>
  </div>
</div>
<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Admin login
    if ($email === "admin@smartexam.com" && $password === "admin123") {
        $_SESSION["admin_logged_in"] = true;
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Welcome Admin!',
                text: 'Login successful',
                confirmButtonText: 'Continue'
            }).then(() => {
                window.location.href = 'admin.php';
            });
        </script>";
        exit;
    }

    $query = "SELECT registration_id, fullname, email, password FROM registration WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row["password"])) {
            $_SESSION["user_logged_in"] = true;
            $_SESSION["registration_id"] = $row["registration_id"];
            $_SESSION["fullname"] = $row["fullname"];

            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Login Successful!',
                    text: 'Welcome, {$row["fullname"]}',
                    confirmButtonText: 'Continue'
                }).then(() => {
                    window.location.href = 'user.php';
                });
            </script>";
        } else {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Password',
                    text: 'Please try again.'
                });
            </script>"; 
        }
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'User Not Found',
                text: 'No account found with this email.'
            });
        </script>";
    }

    mysqli_close($conn);
}
?>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow">
        <div class="card-body">
          <h4 class="text-center mb-4">🔐 Login to SmartExam</h4>
          <form method="post">
            <div class="mb-3">
              <label for="email" class="form-label">📧 Email Address</label>
              <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">🔑 Password</label>
              <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
            <div class="text-center mt-3">
              <a href="registration.php">Don’t have an account? Register</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
