<?php
require_once '../includes/user_auth.php';
require_once '../includes/functions.php';
require_once 'config.php'; // Ensure this contains your database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get user information from database
$user_id = $_SESSION['user_id'];

// Debugging: Check if connection exists
if (!isset($conn) || !$conn) {
    die("Database connection not established");
}

// Prepare the statement with error handling - using backticks for table name with hyphen
$stmt = $conn->prepare("SELECT * FROM `user-info` WHERE id = ?");
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Bind parameters with error handling
if (!$stmt->bind_param("i", $user_id)) {
    die("Error binding parameters: " . $stmt->error);
}

// Execute with error handling
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}

$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    set_message("ব্যবহারকারীর তথ্য পাওয়া যায়নি", "danger");
    redirect('dashboard.php');
}

// Format date of birth for display
$formatted_dob = $user['dob'] ? date("d F, Y", strtotime($user['dob'])) : 'প্রদান করা হয়নি';
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>ব্যবহারকারীর তথ্য</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php require_once 'includes/header.php'; ?>
    <style>
        .info-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .info-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .info-label {
            font-weight: 600;
            color: #555;
        }
        .info-value {
            margin-bottom: 10px;
        }
        .empty-field {
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h1><i class="fas fa-user-circle"></i> ব্যবহারকারীর তথ্য</h1>
            <?php display_message(); ?>
            
            <div class="d-flex justify-content-between mb-4">
                <a href="dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> ড্যাশবোর্ডে ফিরে যান
                </a>
                <a href="edit_profile.php" class="btn btn-primary">
                    <i class="fas fa-edit"></i> তথ্য সম্পাদনা করুন
                </a>
            </div>
            
            <!-- Personal Information Card -->
            <div class="card info-card">
                <div class="card-header bg-primary text-white">
                    <h3><i class="fas fa-id-card"></i> ব্যক্তিগত তথ্য</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-header">
                                <h4><i class="fas fa-signature"></i> নাম</h4>
                            </div>
                            <div class="info-value">
                                <span class="info-label">বাংলা নাম:</span>
                                <p><?= !empty($user['name_bn']) ? htmlspecialchars($user['name_bn']) : '<span class="empty-field">প্রদান করা হয়নি</span>' ?></p>
                            </div>
                            <div class="info-value">
                                <span class="info-label">ইংরেজি নাম:</span>
                                <p><?= !empty($user['name_en']) ? htmlspecialchars($user['name_en']) : '<span class="empty-field">প্রদান করা হয়নি</span>' ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-header">
                                <h4><i class="fas fa-users"></i> পরিবার</h4>
                            </div>
                            <div class="info-value">
                                <span class="info-label">পিতার নাম:</span>
                                <p><?= !empty($user['father_name']) ? htmlspecialchars($user['father_name']) : '<span class="empty-field">প্রদান করা হয়নি</span>' ?></p>
                            </div>
                            <div class="info-value">
                                <span class="info-label">মাতার নাম:</span>
                                <p><?= !empty($user['mother_name']) ? htmlspecialchars($user['mother_name']) : '<span class="empty-field">প্রদান করা হয়নি</span>' ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="info-value">
                                <span class="info-label">জন্ম তারিখ:</span>
                                <p><?= $formatted_dob ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-value">
                                <span class="info-label">রক্তের গ্রুপ:</span>
                                <p><?= !empty($user['blood_group']) ? htmlspecialchars($user['blood_group']) : '<span class="empty-field">প্রদান করা হয়নি</span>' ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-value">
                                <span class="info-label">সন্তান:</span>
                                <p>
                                    ছেলে: <?= $user['sons'] ?? 0 ?>, 
                                    মেয়ে: <?= $user['daughters'] ?? 0 ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Information Card -->
            <div class="card info-card">
                <div class="card-header bg-info text-white">
                    <h3><i class="fas fa-address-book"></i> যোগাযোগের তথ্য</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-value">
                                <span class="info-label">মোবাইল নম্বর:</span>
                                <p><?= !empty($user['mobile']) ? htmlspecialchars($user['mobile']) : '<span class="empty-field">প্রদান করা হয়নি</span>' ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-value">
                                <span class="info-label">ইমেইল:</span>
                                <p><?= !empty($user['email']) ? htmlspecialchars($user['email']) : '<span class="empty-field">প্রদান করা হয়নি</span>' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Address Information -->
            <div class="row">
                <!-- Current Address -->
                <div class="col-md-6">
                    <div class="card info-card">
                        <div class="card-header bg-secondary text-white">
                            <h4><i class="fas fa-map-marker-alt"></i> বর্তমান ঠিকানা</h4>
                        </div>
                        <div class="card-body">
                            <div class="info-value">
                                <p>
                                    <?php 
                                    $current_address = array_filter([
                                        $user['current_village'] ?? null,
                                        $user['current_union'] ?? null,
                                        $user['current_upazila'] ?? null,
                                        $user['current_district'] ?? null,
                                        $user['current_division'] ?? null
                                    ]);
                                    echo !empty($current_address) ? implode(', ', $current_address) : '<span class="empty-field">প্রদান করা হয়নি</span>';
                                    ?>
                                </p>
                                <p>পোস্ট অফিস: <?= !empty($user['current_postoffice']) ? htmlspecialchars($user['current_postoffice']) : '<span class="empty-field">প্রদান করা হয়নি</span>' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Permanent Address -->
                <div class="col-md-6">
                    <div class="card info-card">
                        <div class="card-header bg-secondary text-white">
                            <h4><i class="fas fa-home"></i> স্থায়ী ঠিকানা</h4>
                        </div>
                        <div class="card-body">
                            <div class="info-value">
                                <p>
                                    <?php 
                                    $permanent_address = array_filter([
                                        $user['permanent_village'] ?? null,
                                        $user['permanent_union'] ?? null,
                                        $user['permanent_upazila'] ?? null,
                                        $user['permanent_district'] ?? null,
                                        $user['permanent_division'] ?? null
                                    ]);
                                    echo !empty($permanent_address) ? implode(', ', $permanent_address) : '<span class="empty-field">প্রদান করা হয়নি</span>';
                                    ?>
                                </p>
                                <p>পোস্ট অফিস: <?= !empty($user['permanent_postoffice']) ? htmlspecialchars($user['permanent_postoffice']) : '<span class="empty-field">প্রদান করা হয়নি</span>' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Workplace Information -->
            <div class="card info-card">
                <div class="card-header bg-success text-white">
                    <h3><i class="fas fa-briefcase"></i> কর্মস্থলের তথ্য</h3>
                </div>
                <div class="card-body">
                    <div class="info-value">
                        <span class="info-label">কর্মস্থলের নাম:</span>
                        <p><?= !empty($user['workplace_name']) ? htmlspecialchars($user['workplace_name']) : '<span class="empty-field">প্রদান করা হয়নি</span>' ?></p>
                    </div>
                    <div class="info-value">
                        <span class="info-label">কর্মস্থলের ঠিকানা:</span>
                        <p><?= !empty($user['workplace_address']) ? htmlspecialchars($user['workplace_address']) : '<span class="empty-field">প্রদান করা হয়নি</span>' ?></p>
                    </div>
                    <div class="info-value">
                        <span class="info-label">কর্মস্থলের ইমেইল:</span>
                        <p><?= !empty($user['workplace_email']) ? htmlspecialchars($user['workplace_email']) : '<span class="empty-field">প্রদান করা হয়নি</span>' ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>