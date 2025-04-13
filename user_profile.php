<?php
$conn = new mysqli("localhost", "root", "", "alumni_donation");

try {
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    if (!isset($_GET['id'])) {
        echo "User ID is missing.";
        exit;
    }

    $user_id = intval($_GET['id']);
    $sql = "SELECT * FROM users WHERE id = $user_id";
    $result = $conn->query($sql);
} catch (Exception $e) {
    echo "<p style='color:red;'>ত্রুটি: " . $e->getMessage() . "</p>";
} finally {
    if ($conn instanceof mysqli) {
        @$conn->close(); // Close the connection
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>ইউজার প্রোফাইল</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f0f5;
            margin: 0;
            padding: 0;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 85vh;
        }
        .profile-box {
            background: white;
            padding: 30px;
            width: 400px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            text-align: center;
        }
        .profile-pic img {
            width: 130px;
            height: 130px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #003366;
            margin-bottom: 15px;
        }
        .profile-info p {
            margin: 10px 0;
            font-size: 16px;
            color: #333;
        }
        .profile-info i {
            margin-right: 8px;
            color: #003366;
        }
        h2 {
            margin-top: 0;
            color: #003366;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            font-size: 16px;
            text-decoration: none;
            color: #003366;
            font-weight: bold;
            padding: 8px 15px;
            border: 2px solid #003366;
            border-radius: 5px;
        }
        .back-link:hover {
            background-color: #003366;
            color: white;
            transition: 0.3s ease;
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container">
    <?php if ($result && $result->num_rows > 0): 
        $user = $result->fetch_assoc();
    ?>
    <div class="profile-box">
        <div class="profile-pic">
            <img src="<?php echo !empty($user['profile_pic']) ? $user['profile_pic'] : 'uploads/profile_pics/default.png'; ?>" alt="প্রোফাইল ছবি">
        </div>
        <h2><?php echo htmlspecialchars($user['name']); ?></h2>
        <div class="profile-info">
            <p><i class="fas fa-hashtag"></i> <strong>আইডি:</strong> <?php echo $user['id']; ?></p>
            <p><i class="fas fa-phone"></i> <strong>মোবাইল:</strong> <?php echo htmlspecialchars($user['mobile']); ?></p>
            <p><i class="fas fa-envelope"></i> <strong>ইমেইল:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><i class="fas fa-map-marker-alt"></i> <strong>ঠিকানা:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
            <p><i class="fas fa-user-tag"></i> <strong>রোল:</strong> <?php echo $user['role'] === 'admin' ? 'অ্যাডমিন' : 'ইউজার'; ?></p>
            <p><i class="fas fa-clock"></i> <strong>যোগদানের তারিখ:</strong> <?php echo $user['created_at']; ?></p>
        </div>
        
        <!-- Return to User List Link -->
        <a href="user_list.php" class="back-link">ব্যাক টু ইউজার লিস্ট</a>
    </div>
    <?php else: ?>
        <div class="profile-box">
            <p><i class="fas fa-exclamation-circle"></i> ইউজার পাওয়া যায়নি।</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    // Automatically trigger hover effect after page loads
    document.addEventListener("DOMContentLoaded", function() {
        var backLink = document.querySelector(".back-link");
        backLink.classList.add("hover");
    });
</script>

</body>
</html>
