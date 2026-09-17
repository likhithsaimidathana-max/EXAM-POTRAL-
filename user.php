<?php
session_start();
if (!isset($_SESSION["user_logged_in"])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="login.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<!-- Navbar -->
<div class="header bg-primary d-flex justify-content-between align-items-center p-3">
    <div class="d-flex align-items-center">
        <img src="icon box.png" alt="Logo" width="40" class="me-2">
        <span class="brand text-white h5 mb-0">ExamPortal</span>
    </div>
    <div>
        <a href="login.php" class="btn btn-light">Logout</a>
    </div>
</div>

<!-- Body -->
<div class="container mt-5">
    <h4 class="text-center mb-4">👋 Welcome, <?php echo htmlspecialchars($_SESSION["fullname"]); ?>!</h4>
    <p class="text-center text-muted">Your user ID: <?php echo htmlspecialchars($_SESSION["registration_id"]); ?></p>

    <div class="row g-4 justify-content-center mb-5">
        <div class="col-md-3">
            <div class="card text-center p-4">
                <div class="card-body">
                    <!-- <img src="calendar--v1.png" alt="Upcoming Exams"> -->
                    <h5 class="card-title mt-3">Upcoming Exams</h5>
                    <p class="card-text">See exams scheduled for you.</p>
                    <a href="upcoming_exam.php" class="btn btn-primary">View Exams</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-4">
                <div class="card-body">
                    <!-- <img src="https://img.icons8.com/ios-filled/50/scroll.png" alt="Results"> -->
                    <h5 class="card-title mt-3">Past Results</h5>
                    <p class="card-text">View your previous exam scores.</p>
                    <a href="pastexam.php" class="btn btn-primary">View Results</a>
                </div>
            </div>
        </div>
        <!-- <div class="col-md-3"> -->
            <!-- <div class="card text-center p-4"> -->
                <!-- <div class="card-body"> -->
                    <!-- <img src="settings.png" alt="Profile Settings"> -->
                    <!-- <h5 class="card-title mt-3">Profile Settings</h5> -->
                    <!-- <p class="card-text">Update your profile and password.</p> -->
                    <!-- <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a> -->
                <!-- </div> -->
            <!-- </div> -->
        <!-- </div> -->
    </div>

    <hr>
    <h5><span class="me-2">📢</span>Important Announcements</h5>
    <ul class="list-group mt-3">
        
    </ul>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
