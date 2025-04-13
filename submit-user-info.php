<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location:login.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'alumni_donation');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Prepare data
    $data = [
        'name_bn' => $_POST['name_bn'],
        'name_en' => $_POST['name_en'],
        'father_name' => $_POST['father_name'],
        'mother_name' => $_POST['mother_name'],
        'mobile' => $_POST['mobile'],
        'email' => $_POST['email'],
        'dob' => $_POST['dob'],
        'blood_group' => $_POST['blood_group'],
        'sons' => $_POST['sons'],
        'daughters' => $_POST['daughters'],
        'current_division' => $_POST['current_division'],
        'current_district' => $_POST['current_district'],
        'current_upazila' => $_POST['current_upazila'],
        'current_union' => $_POST['current_union'],
        'current_postoffice' => $_POST['current_postoffice'],
        'current_village' => $_POST['current_village'],
        'permanent_division' => $_POST['permanent_division'],
        'permanent_district' => $_POST['permanent_district'],
        'permanent_upazila' => $_POST['permanent_upazila'],
        'permanent_union' => $_POST['permanent_union'],
        'permanent_postoffice' => $_POST['permanent_postoffice'],
        'permanent_village' => $_POST['permanent_village'],
        'workplace_name' => $_POST['workplace_name'],
        'workplace_address' => $_POST['workplace_address'],
        'workplace_email' => $_POST['workplace_email']
    ];
    
    // Build update query
    $sql = "UPDATE `user-info` SET ";
    foreach ($data as $field => $value) {
        $sql .= "`$field` = '" . $conn->real_escape_string($value) . "', ";
    }
    $sql = rtrim($sql, ', ') . " WHERE id = $user_id";
    
    if ($conn->query($sql)) {
        echo "<div class='alert alert-success'>Information updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// Fetch existing data to pre-fill the form
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM `user-info` WHERE id = $user_id";
$result = $conn->query($query);
$user_data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Information Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .section-title {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center mb-4">User Information Form</h2>
        
        <form method="POST">
            <!-- Personal Information Section -->
            <div class="form-section">
                <h4 class="section-title">Personal Information</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name_bn" class="form-label">Name (Bangla)</label>
                        <input type="text" class="form-control" id="name_bn" name="name_bn" 
                               value="<?= htmlspecialchars($user_data['name_bn'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="name_en" class="form-label">Name (English)</label>
                        <input type="text" class="form-control" id="name_en" name="name_en" 
                               value="<?= htmlspecialchars($user_data['name_en'] ?? '') ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="father_name" class="form-label">Father's Name</label>
                        <input type="text" class="form-control" id="father_name" name="father_name" 
                               value="<?= htmlspecialchars($user_data['father_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="mother_name" class="form-label">Mother's Name</label>
                        <input type="text" class="form-control" id="mother_name" name="mother_name" 
                               value="<?= htmlspecialchars($user_data['mother_name'] ?? '') ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="mobile" class="form-label">Mobile</label>
                        <input type="text" class="form-control" id="mobile" name="mobile" 
                               value="<?= htmlspecialchars($user_data['mobile'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($user_data['email'] ?? '') ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="dob" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="dob" name="dob" 
                               value="<?= htmlspecialchars($user_data['dob'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="blood_group" class="form-label">Blood Group</label>
                        <select class="form-select" id="blood_group" name="blood_group">
                            <option value="">Select</option>
                            <option value="A+" <?= ($user_data['blood_group'] ?? '') == 'A+' ? 'selected' : '' ?>>A+</option>
                            <option value="A-" <?= ($user_data['blood_group'] ?? '') == 'A-' ? 'selected' : '' ?>>A-</option>
                            <option value="B+" <?= ($user_data['blood_group'] ?? '') == 'B+' ? 'selected' : '' ?>>B+</option>
                            <option value="B-" <?= ($user_data['blood_group'] ?? '') == 'B-' ? 'selected' : '' ?>>B-</option>
                            <option value="AB+" <?= ($user_data['blood_group'] ?? '') == 'AB+' ? 'selected' : '' ?>>AB+</option>
                            <option value="AB-" <?= ($user_data['blood_group'] ?? '') == 'AB-' ? 'selected' : '' ?>>AB-</option>
                            <option value="O+" <?= ($user_data['blood_group'] ?? '') == 'O+' ? 'selected' : '' ?>>O+</option>
                            <option value="O-" <?= ($user_data['blood_group'] ?? '') == 'O-' ? 'selected' : '' ?>>O-</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="sons" class="form-label">Number of Sons</label>
                        <input type="number" class="form-control" id="sons" name="sons" min="0" 
                               value="<?= htmlspecialchars($user_data['sons'] ?? 0) ?>">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="daughters" class="form-label">Number of Daughters</label>
                        <input type="number" class="form-control" id="daughters" name="daughters" min="0" 
                               value="<?= htmlspecialchars($user_data['daughters'] ?? 0) ?>">
                    </div>
                </div>
            </div>

            <!-- Current Address Section -->
            <div class="form-section">
                <h4 class="section-title">Current Address</h4>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="current_division" class="form-label">Division</label>
                        <input type="text" class="form-control" id="current_division" name="current_division" 
                               value="<?= htmlspecialchars($user_data['current_division'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="current_district" class="form-label">District</label>
                        <input type="text" class="form-control" id="current_district" name="current_district" 
                               value="<?= htmlspecialchars($user_data['current_district'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="current_upazila" class="form-label">Upazila</label>
                        <input type="text" class="form-control" id="current_upazila" name="current_upazila" 
                               value="<?= htmlspecialchars($user_data['current_upazila'] ?? '') ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="current_union" class="form-label">Union</label>
                        <input type="text" class="form-control" id="current_union" name="current_union" 
                               value="<?= htmlspecialchars($user_data['current_union'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="current_postoffice" class="form-label">Post Office</label>
                        <input type="text" class="form-control" id="current_postoffice" name="current_postoffice" 
                               value="<?= htmlspecialchars($user_data['current_postoffice'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="current_village" class="form-label">Village</label>
                        <input type="text" class="form-control" id="current_village" name="current_village" 
                               value="<?= htmlspecialchars($user_data['current_village'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Permanent Address Section -->
            <div class="form-section">
                <h4 class="section-title">Permanent Address</h4>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="permanent_division" class="form-label">Division</label>
                        <input type="text" class="form-control" id="permanent_division" name="permanent_division" 
                               value="<?= htmlspecialchars($user_data['permanent_division'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="permanent_district" class="form-label">District</label>
                        <input type="text" class="form-control" id="permanent_district" name="permanent_district" 
                               value="<?= htmlspecialchars($user_data['permanent_district'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="permanent_upazila" class="form-label">Upazila</label>
                        <input type="text" class="form-control" id="permanent_upazila" name="permanent_upazila" 
                               value="<?= htmlspecialchars($user_data['permanent_upazila'] ?? '') ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="permanent_union" class="form-label">Union</label>
                        <input type="text" class="form-control" id="permanent_union" name="permanent_union" 
                               value="<?= htmlspecialchars($user_data['permanent_union'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="permanent_postoffice" class="form-label">Post Office</label>
                        <input type="text" class="form-control" id="permanent_postoffice" name="permanent_postoffice" 
                               value="<?= htmlspecialchars($user_data['permanent_postoffice'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="permanent_village" class="form-label">Village</label>
                        <input type="text" class="form-control" id="permanent_village" name="permanent_village" 
                               value="<?= htmlspecialchars($user_data['permanent_village'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Workplace Information Section -->
            <div class="form-section">
                <h4 class="section-title">Workplace Information</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="workplace_name" class="form-label">Organization Name</label>
                        <input type="text" class="form-control" id="workplace_name" name="workplace_name" 
                               value="<?= htmlspecialchars($user_data['workplace_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="workplace_email" class="form-label">Work Email</label>
                        <input type="email" class="form-control" id="workplace_email" name="workplace_email" 
                               value="<?= htmlspecialchars($user_data['workplace_email'] ?? '') ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="workplace_address" class="form-label">Work Address</label>
                    <textarea class="form-control" id="workplace_address" name="workplace_address" rows="3"><?= htmlspecialchars($user_data['workplace_address'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="text-center mb-4">
                <button type="submit" class="btn btn-primary btn-lg">Submit Information</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>