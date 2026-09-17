<?php
require_once "config.php"; 
session_start();

$messageScript = "";  // To hold SweetAlert JS

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $dob = $_POST['dob'];
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $college = trim($_POST['college']);
    $yearofstudy = trim($_POST['yearofstudy']);
    $passingoutyear = trim($_POST['passingoutyear']);
    $branch = $_POST['branch'];

    $skills_input = $_POST['skills'] ?? [];
    if (!is_array($skills_input)) {
        $skills_input = [$skills_input];
    }
    $skills = implode(",", $skills_input);

    $password = $_POST['password'];
    $confirm_password = $_POST['comformpassword'];

    if ($password !== $confirm_password) {
        $messageScript = "
            <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Passwords do not match.'
            });
            </script>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO registration (fullname, dob, email, phone, college, yearofstudy, passingoutyear, branch, skills, password) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssssssss", $fullname, $dob, $email, $phone, $college, $yearofstudy, $passingoutyear, $branch, $skills, $hashed_password);
            if (mysqli_stmt_execute($stmt)) {
                $messageScript = "
                <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Registration successful!',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'login.php';
                });
                </script>";
            } else {
                $messageScript = "
                <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something went wrong. Please try again.'
                });
                </script>";
            }
            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Register - SmartExam Pro</title>
  <link rel="stylesheet" href="login.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- SweetAlert CDN -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="header d-flex justify-content-between align-items-center px-4 py-2 bg-light shadow">
  <div class="d-flex align-items-center">
    <img src="icon box.png" alt="Logo" width="40" class="me-2" />
    <span class="fw-bold fs-4 text-primary">SmartExam Pro</span>
  </div>
  <div>
    <a href="login.php" class="btn btn-outline-primary btn-sm">Login</a>
    <a href="registration.php" class="btn btn-primary btn-sm">Register</a>
  </div>
</div>

<?php
// Output SweetAlert if messageScript is set
if (!empty($messageScript)) {
    echo $messageScript;
}
?>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow">
        <div class="card-body">
          <h3 class="text-center mb-4">📝 Create Your Account</h3>
          <form method="post">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="fullname" class="form-control" required />
              </div>
              <div class="col-md-6">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="dob" class="form-control" required />
              </div>
              <div class="col-md-6">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required />
              </div>
              <div class="col-md-6">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-control" required />
              </div>
              <div class="col-md-6">
                <label class="form-label">College</label>
                <input type="text" name="college" class="form-control" required />
              </div>
              <div class="col-md-6">
                <label class="form-label">Year of Study</label>
                <input type="text" name="yearofstudy" class="form-control" required />
              </div>
              <div class="col-md-6">
                <label class="form-label">Passing Out Year</label>
                <input type="text" name="passingoutyear" class="form-control" required />
              </div>
              <div class="col-md-6">
                <label class="form-label">Branch</label>
                <select name="branch" class="form-select" required>
                  <option disabled selected>Select Branch</option>
                  <option>Computer Science</option>
                  <option>ECE</option>
                  <option>EEE</option>
                  <option>Mechanical</option>
                  <option>Civil</option>
                </select>
              </div>
              <div class="col-md-12">
                <label class="form-label" id="skills" name="skills">Skills</label>
                <select name="skills[]" class="form-select" multiple required>
                  <option>HTML</option>
                  <option>Python</option>
                  <option>Java</option>
                  <option>C++</option>
                  <option>HTML & CSS</option>
                  <option>JavaScript</option>
                  <option>SQL</option>
                  <option>Data Structures & Algorithms</option>
                  <option>Object-Oriented Programming (OOP)</option>
                  <option>Web Development</option>
                  <option>Android Development</option>
                  <option>iOS Development</option>
                  <option>Machine Learning</option>
                  <option>Artificial Intelligence</option>
                  <option>Operating Systems</option>
                  <option>Aptitude & Reasoning</option>
                  <option>English</option>
                </select>
                <small class="text-muted">Hold Ctrl (Windows) to select multiple</small>
              </div>
              <div class="col-md-6">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required />
              </div>
              <div class="col-md-6">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="comformpassword" class="form-control" required />
              </div>
            </div>
            <div class="text-center mt-4">
              <button type="submit" class="btn btn-primary w-100">Register</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
