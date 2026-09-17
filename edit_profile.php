<?php
session_start();
require 'config.php'; 


if (!isset($_SESSION['registration_id'])) {
    header("Location: login.php");
    exit();
}

$registration_id = $_SESSION['registration_id'];


$sql = "SELECT name, email, phone FROM user_registration WHERE registration_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $registration_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

    if ($password) {
        $update_sql = "UPDATE user_registration SET name=?, email=?, phone=?, password=? WHERE registration_id=?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssi", $name, $email, $phone, $password, $registration_id);
    } else {
        $update_sql = "UPDATE user_registration SET name=?, email=?, phone=? WHERE registration_id=?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssi", $name, $email, $phone, $registration_id);
    }

    if ($update_stmt->execute()) {
        $success = "Profile updated successfully!";
    } else {
        $error = "Error updating profile!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h4>Edit Profile</h4>
                </div>
                <div class="card-body">

                    <?php if(isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php elseif(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password (leave blank if not changing)</label>
                            <input type="password" name="password" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-success w-100">Update Profile</button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
