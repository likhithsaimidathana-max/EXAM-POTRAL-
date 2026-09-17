<?php
session_start();
if (!isset($_SESSION['registration_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';
$registration_id = $_SESSION['registration_id'];

// Get user's past exam scores
$sql = "SELECT s.*, e.examtitle 
        FROM scores s 
        JOIN exam e ON s.exam_id = e.exam_id 
        WHERE s.registration_id = ? 
        ORDER BY s.submitted_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $registration_id);
$stmt->execute();
$result = $stmt->get_result();

$scores = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $scores[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Past Exam Scores</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Student Name:<?php echo htmlspecialchars($_SESSION["fullname"]); ?></h2>
    <h2>Registration No:<?php echo htmlspecialchars($_SESSION["registration_id"]); ?></h2>
    
    <h2 class="mb-4">Your  Exam Scores</h2>
    <?php if (empty($scores)): ?>
        <div class="alert alert-info">You haven't taken any exams yet.</div>
    <?php else: ?>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>S.NO</th>
                    <!-- <th>user name</th> -->
                    <th>Exam Title</th>
                    <th>Results</th>
                    <th>Total Questions</th>
                    <th>Exam DAte</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php foreach ($scores as $index => $score): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <!-- <td><?php echo htmlspecialchars($_SESSION["fullname"]); ?></td> -->
                        <td><?php echo htmlspecialchars($score['examtitle']); ?></td>
                        <td><?php echo $score['score']; ?></td>
                        <td><?php echo $score['total_questions']; ?></td>
                        <td><?php echo date("d M Y", strtotime($score['submitted_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <a href="user.php" class="btn btn-secondary">Back to Dashboard</a>
     <a href="f_pdf.php" class="btn btn-secondary">GENERATE PDF</a>
</div>
</body>
</html>
