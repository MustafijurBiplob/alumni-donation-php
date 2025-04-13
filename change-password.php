<?php
session_start();
require_once 'config.php'; // assumes you have a db connection in db.php

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $message = "New password and confirm password do not match.";
    } else {
        $user_id = $_SESSION['user_id'];

        // Fetch the current hashed password from database
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        // Verify current password
        if (password_verify($current_password, $hashed_password)) {
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $new_hashed_password, $user_id);
            if ($update->execute()) {
                $message = "Password changed successfully. Please wait...";
                // Trigger redirect to dashboard after a delay
                $redirect = true;
            } else {
                $message = "Failed to update password. Try again.";
            }
            $update->close();
        } else {
            $message = "Current password is incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Link to Bootstrap Icons for the sandclock icon -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sandclock {
            display: inline-block;
            font-size: 30px;
            margin-left: 10px;
            animation: rotateSandclock 1s infinite linear;
        }

        @keyframes rotateSandclock {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
    </style>
    <?php if (isset($redirect) && $redirect): ?>
        <!-- JavaScript to redirect after 4 seconds -->
        <script>
            setTimeout(function() {
                window.location.href = "dashboard.php";
            }, 4000); // 4 seconds delay
        </script>
    <?php endif; ?>
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-header text-center">
            <h4>Change Password</h4>
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if (isset($redirect) && $redirect): ?>
                <div class="alert alert-warning text-center">
                    <span>Wait <strong>3</strong> seconds to be redirected
                        <span class="sandclock"><i class="bi bi-hourglass-split"></i></span>
                    </span>
                </div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" name="new_password" id="new_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Change Password</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
