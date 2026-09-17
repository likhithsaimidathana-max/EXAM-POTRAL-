      <!-- ADMIN POTRAL -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

</head>
<body>
    
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <a class="navbar-brand" href="#">Exam Portal - Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="adminNavbar">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link text-white" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="create_exam.php">Create Exam</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="admin_student_scores.php">Student Scores</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="">Add Questions</a>
        </li>
        <!-- <li class="nav-item">
          <a class="nav-link text-white" href="#">View Exams</a>
        </li> -->
        <li class="nav-item">
          <a class="nav-link text-danger" href="login.php">Logout</a>
        </li>
      </ul>
    </div>
  </nav>
  <!-- first page  -->
  <center>
    <div class="mt-5">
      <h1>👋Welcome,Admin!</h1>
    </div>
    <!-- <h5>Manage your exams,reports and settings from one place easily</h5> -->
    <img src="icon box.png" alt="icon"> <br>
    <a href="dashboard.php" class="btn btn-primary text-white fs-5">Go to Dashboard</a>
  </cen ter>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>  
</body>
</html>