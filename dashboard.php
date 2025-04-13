<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/user_auth.php';
require_once 'includes/functions.php';

// Initialize the overlay flag
$show_overlay = $_SESSION['show_overlay'] ?? false;
if ($show_overlay) {
    unset($_SESSION['show_overlay']); // One-time show
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>ব্যবহারকারী ড্যাশবোর্ড</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
        }

        .blur-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            backdrop-filter: blur(6px);
            background-color: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .blur-message {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            text-align: center;
        }

        .blur-message h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .blur-message p {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .blur-message a {
            background: #007bff;
            color: #fff;
            padding: 10px 25px;
            border-radius: 6px;
            text-decoration: none;
        }

        .blurred {
            filter: blur(8px);
            pointer-events: none;
        }
    </style>
</head>

<body>

<?php require_once 'includes/header.php'; ?>

<?php
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$total_user_donation = 0;
$last_donation_date = 'প্রযোজ্য নয়';
$pending_donation = false;

$profile_picture = 'uploads/profile_pics/' . $user_id . '.png';
if (!file_exists($profile_picture)) {
    $profile_picture = 'uploads/profile_pics/default.png';
}

// Approved donation total
$stmt_total = $conn->prepare("SELECT SUM(amount) as total FROM donations WHERE user_id = ? AND status = 'Approved'");
$stmt_total->bind_param("i", $user_id);
$stmt_total->execute();
$result_total = $stmt_total->get_result()->fetch_assoc();
$total_user_donation = $result_total['total'] ?? 0.00;
$stmt_total->close();

// Last donation date
$stmt_last_date = $conn->prepare("SELECT MAX(transfer_date) as last_date FROM donations WHERE user_id = ?");
$stmt_last_date->bind_param("i", $user_id);
$stmt_last_date->execute();
$result_last_date = $stmt_last_date->get_result()->fetch_assoc();
if ($result_last_date['last_date']) {
    $date = strtotime($result_last_date['last_date']);
    setlocale(LC_TIME, 'bn_BD.UTF-8'); // For Bengali
    $last_donation_date = strftime("%e %B, %Y", $date);
}
$stmt_last_date->close();

// Check pending donations
$stmt_pending = $conn->prepare("SELECT id FROM donations WHERE user_id = ? AND status = 'Pending' LIMIT 1");
$stmt_pending->bind_param("i", $user_id);
$stmt_pending->execute();
$stmt_pending->store_result();
$pending_donation = $stmt_pending->num_rows > 0;
$stmt_pending->close();
?>

<?php if ($show_overlay): ?>
<!-- Registration overlay -->
<div class="blur-overlay" id="blurOverlay">
    <div class="blur-message">
        <h2>আপনার নিবন্ধন সম্পন্ন হয়েছে</h2>
        <p>একটু ধৈর্য ধরে আপনার সকল তথ্য দিন।</p>
        <a href="user-info.php">তথ্য দিন</a>
    </div>
</div>
<?php endif; ?>

<!-- Dashboard main -->
<div id="mainContent" class="<?php echo $show_overlay ? 'blurred' : ''; ?>">

    <h1>ব্যবহারকারী ড্যাশবোর্ড</h1>
    <?php display_message(); ?>
    <p class="lead">আবার স্বাগতম, <?php echo htmlspecialchars($user_name); ?>!</p>
    <hr>

    <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" class="profile-img">

    <div class="row">
        <!-- Approved Donation -->
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-header"><i class="fas fa-hand-holding-heart"></i> মোট অনুমোদিত অনুদান</div>
                <div class="card-body">
                    <h5 class="card-title">৳<?php echo number_format($total_user_donation, 2); ?></h5>
                    <p class="card-text">আপনার সহায়তার জন্য ধন্যবাদ!</p>
                </div>
            </div>
        </div>

        <!-- Last Donation -->
        <div class="col-md-4">
            <div class="card text-dark bg-light mb-3">
                <div class="card-header"><i class="fas fa-calendar-check"></i> সর্বশেষ অনুদানের তারিখ</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $last_donation_date; ?></h5>
                    <p class="card-text">আপনার সর্বশেষ অনুদানের তারিখ।</p>
                </div>
            </div>
        </div>

        <!-- Pending Donation -->
        <div class="col-md-4">
            <div class="card <?php echo $pending_donation ? 'text-white bg-warning' : 'text-dark bg-light'; ?> mb-3">
                <div class="card-header"><i class="fas fa-hourglass-half"></i> অনুমোদনের অপেক্ষায়</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $pending_donation ? 'হ্যাঁ' : 'না'; ?></h5>
                    <p class="card-text">
                        <?php echo $pending_donation ? 'আপনার অনুদান অনুমোদনের অপেক্ষায় রয়েছে।' : 'আপনার সব অনুদান প্রক্রিয়াকৃত হয়েছে।'; ?>
                    </p>
                    <?php if ($pending_donation): ?>
                        <a href="donation_history.php" class="btn btn-sm btn-outline-dark">
                            <i class="fas fa-clock"></i> ইতিহাস দেখুন
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <h2><i class="fas fa-bolt"></i> দ্রুত একশন</h2>
    <div class="list-group">
        <a href="make_donation.php" class="list-group-item list-group-item-action">
            <i class="fas fa-plus-circle"></i> নতুন অনুদান দিন
        </a>
        <a href="donation_history.php" class="list-group-item list-group-item-action">
            <i class="fas fa-file-alt"></i> আমার হিসাব
        </a>
        <a href="income_expense_summary.php" class="list-group-item list-group-item-action">
            <i class="fas fa-chart-line"></i> বাৎসরিক সার্বিক হিসাব
        </a>
        <a href="edit_profile.php" class="list-group-item list-group-item-action">
            <i class="fas fa-user-edit"></i> প্রোফাইল সম্পাদনা করুন
        </a>
        <a href="submit-user-info.php" class="list-group-item list-group-item-action">
            <i class="fas fa-user"></i> আপনার সম্পর্কে তথ্য দিন
        </a>
        <a href="opinion.php" class="list-group-item list-group-item-action">
            <i class="fas fa-comments"></i> মতামত দিন
        </a>
        <a href="all-opinion.php" class="list-group-item list-group-item-action">
            <i class="fas fa-comments"></i> সকল মতামত
        </a>
        <a href="logout.php" class="list-group-item list-group-item-action text-danger">
            <i class="fas fa-sign-out-alt"></i> প্রস্থান করুন
        </a>
    </div>

</div> <!-- #mainContent -->

<?php include 'includes/footer.php'; ?>

<?php if ($show_overlay): ?>
<!-- Auto-hide overlay -->
<script>
    setTimeout(() => {
        document.getElementById("blurOverlay").style.display = "none";
        document.getElementById("mainContent").classList.remove("blurred");
    }, 7000);
</script>
<?php endif; ?>

</body>
</html>
