<!-- ADMIN PORTAL -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Exam Portal - Admin</title>
  <link rel="stylesheet" href="admin.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

  <!-- Navbar -->
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
        <a class="nav-link text-white" href="add_question.php">Add Questions</a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-danger" href="#"></a>
      </li>
    </ul>
  </div>
</nav>
  
   <!-- first page -->
  <div class="container mt-4">
    <div class="stats-box">
      🧾 Total Exams: <span class="float-end">12</span>
    </div>
    <div class="stats-box">
      📝 Total Questions: <span class="float-end">250</span>
    </div>
    <div class="stats-box">
      ✅ Active Exams: <span class="float-end">3</span>
    </div>
    <div class="stats-box">
      🧑‍🎓 Registered Students: <span class="float-end">45</span>
    </div>
        <div class="mt-5">
 <h3>📢 ADD ANNOUNCEMENT</h3>
 <form>
        <div class="mb-3">
          <label for="announcement" class="form-label">Announcement Message</label>
          <textarea class="form-control" id="announcement" rows="3" placeholder="Enter your announcement..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Add Announcement</button>
        <a href="#" class="btn btn-secondary">Back</a>
      </form>
</div>
  </div>
     


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
