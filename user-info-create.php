<?php
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/functions.php';

$success = $error = '';

// ফর্ম সাবমিশন
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'name_bn', 'name_en', 'father_name', 'mother_name', 'mobile', 'email', 'dob',
        'blood_group', 'sons', 'daughters',
        'current_division', 'current_district', 'current_upazila', 'current_union',
        'current_postoffice', 'current_village',
        'permanent_division', 'permanent_district', 'permanent_upazila', 'permanent_union',
        'permanent_postoffice', 'permanent_village',
        'workplace_name', 'workplace_address', 'workplace_email'
    ];

    $data = [];
    foreach ($fields as $field) {
        $data[$field] = $_POST[$field] ?? '';
    }

    $registration_date = date('Y-m-d');

    $stmt = $conn->prepare("
        INSERT INTO users (
            name_bn, name_en, father_name, mother_name, mobile, email, dob, blood_group, sons, daughters,
            current_division, current_district, current_upazila, current_union, current_postoffice, current_village,
            permanent_division, permanent_district, permanent_upazila, permanent_union, permanent_postoffice, permanent_village,
            workplace_name, workplace_address, workplace_email, registration_date
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?
        )
    ");

    $stmt->bind_param(
        "sssssssssssssssssssssssss",
        $data['name_bn'], $data['name_en'], $data['father_name'], $data['mother_name'],
        $data['mobile'], $data['email'], $data['dob'], $data['blood_group'],
        $data['sons'], $data['daughters'],
        $data['current_division'], $data['current_district'], $data['current_upazila'],
        $data['current_union'], $data['current_postoffice'], $data['current_village'],
        $data['permanent_division'], $data['permanent_district'], $data['permanent_upazila'],
        $data['permanent_union'], $data['permanent_postoffice'], $data['permanent_village'],
        $data['workplace_name'], $data['workplace_address'], $data['workplace_email'],
        $registration_date
    );

    if ($stmt->execute()) {
        $success = "সদস্যের তথ্য সফলভাবে যোগ হয়েছে!";
    } else {
        $error = "ত্রুটি ঘটেছে: " . $stmt->error;
    }
}
?>

<h2>সদস্যের তথ্য যোগ করুন</h2>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST">
    <h4>ব্যক্তিগত তথ্য</h4>
    <div class="form-group"><label>নাম (বাংলা)</label><input type="text" name="name_bn" class="form-control" required></div>
    <div class="form-group"><label>নাম (ইংরেজি)</label><input type="text" name="name_en" class="form-control" required></div>
    <div class="form-group"><label>পিতার নাম</label><input type="text" name="father_name" class="form-control"></div>
    <div class="form-group"><label>মাতার নাম</label><input type="text" name="mother_name" class="form-control"></div>
    <div class="form-group"><label>মোবাইল</label><input type="text" name="mobile" class="form-control" required></div>
    <div class="form-group"><label>ইমেইল</label><input type="email" name="email" class="form-control"></div>
    <div class="form-group"><label>জন্ম তারিখ</label><input type="date" name="dob" class="form-control"></div>
    <div class="form-group"><label>রক্তের গ্রুপ</label><input type="text" name="blood_group" class="form-control"></div>
    <div class="form-group"><label>ছেলের সংখ্যা</label><input type="number" name="sons" class="form-control" value="0"></div>
    <div class="form-group"><label>মেয়ের সংখ্যা</label><input type="number" name="daughters" class="form-control" value="0"></div>

    <h4>বর্তমান ঠিকানা</h4>
    <div class="form-group"><label>বিভাগ</label><input type="text" name="current_division" class="form-control"></div>
    <div class="form-group"><label>জেলা</label><input type="text" name="current_district" class="form-control"></div>
    <div class="form-group"><label>উপজেলা</label><input type="text" name="current_upazila" class="form-control"></div>
    <div class="form-group"><label>ইউনিয়ন</label><input type="text" name="current_union" class="form-control"></div>
    <div class="form-group"><label>ডাকঘর</label><input type="text" name="current_postoffice" class="form-control"></div>
    <div class="form-group"><label>গ্রাম</label><input type="text" name="current_village" class="form-control"></div>

    <h4>স্থায়ী ঠিকানা</h4>
    <div class="form-group"><label>বিভাগ</label><input type="text" name="permanent_division" class="form-control"></div>
    <div class="form-group"><label>জেলা</label><input type="text" name="permanent_district" class="form-control"></div>
    <div class="form-group"><label>উপজেলা</label><input type="text" name="permanent_upazila" class="form-control"></div>
    <div class="form-group"><label>ইউনিয়ন</label><input type="text" name="permanent_union" class="form-control"></div>
    <div class="form-group"><label>ডাকঘর</label><input type="text" name="permanent_postoffice" class="form-control"></div>
    <div class="form-group"><label>গ্রাম</label><input type="text" name="permanent_village" class="form-control"></div>

    <h4>কর্মস্থলের তথ্য</h4>
    <div class="form-group"><label>কর্মস্থলের নাম</label><input type="text" name="workplace_name" class="form-control"></div>
    <div class="form-group"><label>ঠিকানা</label><input type="text" name="workplace_address" class="form-control"></div>
    <div class="form-group"><label>ইমেইল</label><input type="email" name="workplace_email" class="form-control"></div>

    <br>
    <button type="submit" class="btn btn-success">তথ্য সংরক্ষণ করুন</button>
</form>

<a href="<?php echo BASE_URL; ?>admin/user_list.php" class="btn btn-secondary mt-3">সদস্য তালিকায় ফিরে যান</a>

<?php include __DIR__ . '/includes/footer.php'; ?>
