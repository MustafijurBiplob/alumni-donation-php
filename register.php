<?php
require_once 'config.php';
require_once 'includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$name = $email = $mobile = $address = $password = $confirm_password = $ssc_year = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitize_input($_POST['name']);
    $email = filter_var(sanitize_input($_POST['email']), FILTER_VALIDATE_EMAIL);
    $mobile = sanitize_input($_POST['mobile']);
    $address = sanitize_input($_POST['address']);
    $ssc_year = sanitize_input($_POST['ssc_year']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($name)) $errors['name'] = "নাম দেওয়া আবশ্যক।";
    if (!$email) $errors['email'] = "ভ্যালিড ইমেইল দেওয়া আবশ্যক।";
    else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute(); 
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors['email'] = "ইমেইল আগে থেকেই নিবন্ধিত।";
        $stmt->close();
    }

    if (empty($mobile)) $errors['mobile'] = "মোবাইল নম্বর দেওয়া আবশ্যক।";
    elseif (!preg_match('/^01[3-9]\d{8}$/', $mobile)) $errors['mobile'] = "সঠিক মোবাইল নম্বর দিন (01xxxxxxxxx)";
    else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE mobile = ?");
        $stmt->bind_param("s", $mobile);
        $stmt->execute(); 
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors['mobile'] = "মোবাইল নম্বর আগে থেকেই নিবন্ধিত।";
        $stmt->close();
    }

    if (empty($ssc_year)) $errors['ssc_year'] = "SSC পাসের সাল নির্বাচন করুন।";
    if (empty($password)) $errors['password'] = "পাসওয়ার্ড দেওয়া আবশ্যক।";
    elseif (strlen($password) < 6) $errors['password'] = "পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।";
    if ($password !== $confirm_password) $errors['confirm_password'] = "পাসওয়ার্ড মিলছে না।";

    if (empty($errors)) {
        $hashed_password = hash_password($password);
        $role = 'user';

        $stmt = $conn->prepare("INSERT INTO users (name, email, mobile, address, password, ssc_year, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $email, $mobile, $address, $hashed_password, $ssc_year, $role);

        if ($stmt->execute()) {
            set_message("নিবন্ধন সফল! অনুগ্রহ করে লগইন করুন।", "success");
            redirect('login.php');
        } else {
            set_message("নিবন্ধন ব্যর্থ: " . $conn->error, "danger");
        }
        $stmt->close();
    } else {
        set_message("অনুগ্রহ করে নীচের ত্রুটিগুলি সংশোধন করুন।", "warning");
    }
}

include 'includes/header.php';
?>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<div class="register-form container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4 text-primary">
                        <i class="bi bi-person-plus-fill me-2"></i>নিবন্ধন
                    </h2>
                    
                    <?php display_message(); ?>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="bi bi-person-fill me-1"></i>পুরো নাম
                            </label>
                            <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                   id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback d-flex align-items-center">
                                    <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo $errors['name']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope-fill me-1"></i>ইমেইল ঠিকানা
                            </label>
                            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                   id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback d-flex align-items-center">
                                    <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo $errors['email']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="mobile" class="form-label">
                                <i class="bi bi-phone-fill me-1"></i>মোবাইল নম্বর
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">+88</span>
                                <input type="tel" class="form-control <?php echo isset($errors['mobile']) ? 'is-invalid' : ''; ?>" 
                                       id="mobile" name="mobile" value="<?php echo htmlspecialchars($mobile); ?>" 
                                       placeholder="01XXXXXXXXX" required>
                                <span class="input-group-text" id="mobile-valid-icon">
                                    <i class="bi bi-circle text-muted"></i>
                                </span>
                            </div>
                            <?php if (isset($errors['mobile'])): ?>
                                <div class="invalid-feedback d-flex align-items-center">
                                    <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo $errors['mobile']; ?>
                                </div>
                            <?php endif; ?>
                            <small class="text-muted">বাংলাদেশী মোবাইল নম্বর (01XXXXXXXXX)</small>
                        </div>

                        <div class="mb-3">
                            <label for="ssc_year" class="form-label">
                                <i class="bi bi-mortarboard-fill me-1"></i>SSC পাসের সাল
                            </label>
                            <select class="form-select <?php echo isset($errors['ssc_year']) ? 'is-invalid' : ''; ?>" 
                                    id="ssc_year" name="ssc_year" required>
                                <option value="">-- নির্বাচন করুন --</option>
                                <?php
                                    $currentYear = date("Y");
                                    for ($y = $currentYear; $y >= 1950; $y--) {
                                        echo '<option value="'.$y.'"'.($ssc_year == $y ? ' selected' : '').'>'.$y.'</option>';
                                    }
                                ?>
                            </select>
                            <?php if (isset($errors['ssc_year'])): ?>
                                <div class="invalid-feedback d-flex align-items-center">
                                    <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo $errors['ssc_year']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">
                                <i class="bi bi-house-fill me-1"></i>ঠিকানা (ঐচ্ছিক)
                            </label>
                            <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($address); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock-fill me-1"></i>পাসওয়ার্ড
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                       id="password" name="password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="bi bi-eye-fill"></i>
                                </button>
                            </div>
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback d-flex align-items-center">
                                    <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo $errors['password']; ?>
                                </div>
                            <?php endif; ?>
                            <small class="text-muted">পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে</small>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">
                                <i class="bi bi-lock-fill me-1"></i>পাসওয়ার্ড নিশ্চিত করুন
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                       id="confirm_password" name="confirm_password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="bi bi-eye-fill"></i>
                                </button>
                            </div>
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback d-flex align-items-center">
                                    <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo $errors['confirm_password']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-3">
                            <i class="bi bi-person-plus-fill me-2"></i>নিবন্ধন করুন
                        </button>
                    </form>
                    
                    <p class="mt-3 text-center">
                        আগেই কি আপনার অ্যাকাউন্ট আছে? <a href="login.php" class="text-decoration-none">
                            <i class="bi bi-box-arrow-in-right me-1"></i>এখানে লগইন করুন
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Mobile number validation with icon
    const mobileField = document.getElementById("mobile");
    const mobileIcon = document.querySelector("#mobile-valid-icon i");
    
    mobileField.addEventListener("input", function() {
        const value = this.value.trim();
        const regex = /^01[3-9]\d{8}$/;
        
        if (regex.test(value)) {
            mobileIcon.className = "bi bi-check-circle-fill text-success";
        } else if (value.length > 0) {
            mobileIcon.className = "bi bi-x-circle-fill text-danger";
        } else {
            mobileIcon.className = "bi bi-circle text-muted";
        }
    });
    
    // Toggle password visibility
    document.querySelectorAll(".toggle-password").forEach(button => {
        button.addEventListener("click", function() {
            const input = this.parentElement.querySelector("input");
            const icon = this.querySelector("i");
            
            if (input.type === "password") {
                input.type = "text";
                icon.className = "bi bi-eye-slash-fill";
            } else {
                input.type = "password";
                icon.className = "bi bi-eye-fill";
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>