<?php
session_start();
require_once "config.php";

// Simple admin guard (optional) - adjust as needed
// if (!isset($_SESSION['is_admin'])) { header('Location: login.php'); exit(); }

$sql = "SELECT s.*, r.fullname, e.examtitle FROM scores s
        LEFT JOIN registration r ON s.registration_id = r.registration_id
        LEFT JOIN exam e ON s.exam_id = e.exam_id
        ORDER BY s.submitted_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Scores - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <a class="navbar-brand" href="#">Exam Portal - Admin</a>
    <div class="collapse navbar-collapse" id="adminNavbar">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link text-white" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="create_exam.php">Create Exam</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="add_question.php">Add Questions</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="admin_student_scores.php">Student Scores</a></li>
        <li class="nav-item"><a class="nav-link text-danger" href="login.php">Logout</a></li>
      </ul>
    </div>
  </nav>

  <div class="container mt-5">
    <h3 class="mb-4">Student Scores</h3>

    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-primary">
          <tr>
            <th>#</th>
            <th>Student</th>
            <th>Exam</th>
            <th>Score</th>
            <th>Total Questions</th>
            <th>Percentage</th>
            <th>Submitted At</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && mysqli_num_rows($result) > 0):
              $i = 1;
              while ($row = mysqli_fetch_assoc($result)):
                $score = (int)$row['score'];
                $total = (int)$row['total_questions'];
                $percent = $total > 0 ? round(($score / $total) * 100, 2) : 0;
          ?>
            <tr>
              <td><?php echo $i++; ?></td>
              <td><?php echo htmlspecialchars($row['fullname'] ?? 'Unknown'); ?></td>
              <td><?php echo htmlspecialchars($row['examtitle'] ?? 'N/A'); ?></td>
              <td><?php echo $score; ?></td>
              <td><?php echo $total; ?></td>
              <td><?php echo $percent; ?>%</td>
              <td><?php echo htmlspecialchars($row['submitted_at'] ?? ''); ?></td>
            </tr>
          <?php endwhile; else: ?>
            <tr>
              <td colspan="7" class="text-center">No scores found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
