<?php
session_start();

// Include the config.php file from the same directory
include 'config.php'; // ensure config.php is in the same directory as this file

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user name
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$username = $user['name'] ?? 'Unknown';

$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['opinion'])) {
    $opinion_text = htmlspecialchars($_POST['opinion']);

    $insert_stmt = $conn->prepare("INSERT INTO opinions (user_id, opinion_text) VALUES (?, ?)");
    $insert_stmt->bind_param("is", $user_id, $opinion_text);

    if ($insert_stmt->execute()) {
        $success_message = "✅ Your opinion has been published!";
    } else {
        $success_message = "❌ Failed to publish your opinion.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Your Opinion</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 700px;
            margin: 40px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
        }
        textarea {
            width: 100%;
            height: 120px;
            padding: 12px;
            font-size: 16px;
            border-radius: 6px;
            border: 1px solid #ccc;
            resize: vertical;
        }
        button {
            padding: 10px 25px;
            font-size: 16px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .success {
            margin-top: 15px;
            font-weight: bold;
            color: green;
        }
        .fail {
            margin-top: 15px;
            font-weight: bold;
            color: red;
        }
    </style>
</head>
<body>
    <h2><i class="fas fa-comment-dots"></i> Submit Your Opinion</h2>
    <p>Hello, <strong><?= htmlspecialchars($username) ?></strong>! Please write your opinion below.</p>

    <form method="POST">
        <textarea name="opinion" placeholder="Write your opinion here..." required></textarea><br><br>
        <button type="submit"><i class="fas fa-paper-plane"></i> Submit</button>
    </form>

    <?php if ($success_message): ?>
        <div class="<?= strpos($success_message, '✅') !== false ? 'success' : 'fail' ?>">
            <?= $success_message ?>
        </div>
    <?php endif; ?>
</body>
</html>
