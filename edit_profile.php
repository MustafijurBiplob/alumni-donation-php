<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!is_logged_in()) {
    set_message("Please login to access this page.", "warning");
    redirect('login.php');
}

// Get current user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    set_message("User not found.", "danger");
    redirect('dashboard.php');
}

// Initialize variables
$name = $user['name'];
$email = $user['email'];
$mobile = $user['mobile'];
$address = $user['address'];
$ssc_year = $user['ssc_year'];
$profile_pic = $user['profile_pic'];
$errors = [];

// Determine mobile validation icon class
$mobile_icon_class = (preg_match('/^01[3-9]\d{8}$/', $mobile)) ? 'bi-check-circle-fill text-success' : 'bi-exclamation-circle-fill text-danger';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors['general'] = "Invalid request. Please try again.";
    } else {
        $name = sanitize_input($_POST['name']);
        $email = filter_var(sanitize_input($_POST['email']), FILTER_VALIDATE_EMAIL);
        $mobile = sanitize_input($_POST['mobile']);
        $address = sanitize_input($_POST['address']);
        $ssc_year = sanitize_input($_POST['ssc_year']);

        // Validation
        if (empty($name)) $errors['name'] = "Name is required.";
        if (!$email) $errors['email'] = "Valid email is required.";
        if (empty($mobile)) $errors['mobile'] = "Mobile number is required.";
        elseif (!preg_match('/^01[3-9]\d{8}$/', $mobile)) $errors['mobile'] = "Invalid mobile number format.";
        if (empty($ssc_year)) $errors['ssc_year'] = "SSC year is required.";

        // Check if email is changed and exists
        if ($email != $user['email']) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) $errors['email'] = "Email already registered.";
            $stmt->close();
        }

        // Check if mobile is changed and exists
        if ($mobile != $user['mobile']) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE mobile = ? AND id != ?");
            $stmt->bind_param("si", $mobile, $user_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) $errors['mobile'] = "Mobile number already registered.";
            $stmt->close();
        }

        // Handle profile picture upload
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['profile_pic']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $upload_dir = 'uploads/profile_pics/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
                $new_filename = 'user_' . $user_id . '_' . time() . '.' . $file_ext;
                $destination = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination)) {
                    // Delete old profile pic if exists
                    if ($profile_pic && file_exists($profile_pic)) {
                        unlink($profile_pic);
                    }
                    $profile_pic = $destination;
                } else {
                    $errors['profile_pic'] = "Failed to upload profile picture.";
                }
            } else {
                $errors['profile_pic'] = "Only JPG, PNG, and GIF files are allowed.";
            }
        }

        // Update if no errors
        if (empty($errors)) {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, mobile = ?, address = ?, ssc_year = ?, profile_pic = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("ssssssi", $name, $email, $mobile, $address, $ssc_year, $profile_pic, $user_id);
            
            if ($stmt->execute()) {
                // Update session name if changed
                $_SESSION['user_name'] = $name;
                
                set_message("Profile updated successfully!", "success");
                redirect('edit-profile.php');
            } else {
                $errors['general'] = "Error updating profile: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

include 'includes/header.php';
?>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2 class="text-center mb-0">
                        <i class="bi bi-person-gear me-2"></i>Edit Profile
                    </h2>
                </div>
                <div class="card-body">
                    <?php if (isset($errors['general'])): ?>
                        <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
                    <?php endif; ?>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="row">
                            <!-- Profile Picture Column -->
                            <div class="col-md-4 text-center mb-4">
                                <div class="profile-pic-container">
                                    <img src="<?php echo $profile_pic ? htmlspecialchars($profile_pic) : 'assets/default-profile.png'; ?>" 
                                         class="img-thumbnail rounded-circle mb-2" id="profilePicPreview" 
                                         style="width: 200px; height: 200px; object-fit: cover;">
                                    <div class="mb-3">
                                        <label for="profile_pic" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-camera me-1"></i>Change Photo
                                            <input type="file" class="d-none" id="profile_pic" name="profile_pic" accept="image/*">
                                        </label>
                                        <?php if (isset($errors['profile_pic'])): ?>
                                            <div class="text-danger small mt-1"><?php echo $errors['profile_pic']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Form Fields Column -->
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">
                                        <i class="bi bi-person-fill me-2"></i>Full Name
                                    </label>
                                    <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                           id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                                    <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-envelope-fill me-2"></i>Email Address
                                    </label>
                                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                           id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="mobile" class="form-label">
                                        <i class="bi bi-phone-fill me-2"></i>Mobile Number
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">+88</span>
                                        <input type="tel" class="form-control <?php echo isset($errors['mobile']) ? 'is-invalid' : ''; ?>" 
                                               id="mobile" name="mobile" value="<?php echo htmlspecialchars($mobile); ?>" 
                                               placeholder="01XXXXXXXXX" required>
                                        <span class="input-group-text" id="mobile-valid-icon">
                                            <i class="bi <?php echo $mobile_icon_class; ?>"></i>
                                        </span>
                                    </div>
                                    <?php if (isset($errors['mobile'])): ?>
                                        <div class="invalid-feedback d-block"><?php echo $errors['mobile']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="ssc_year" class="form-label">
                                        <i class="bi bi-mortarboard-fill me-2"></i>SSC Passing Year
                                    </label>
                                    <select class="form-select <?php echo isset($errors['ssc_year']) ? 'is-invalid' : ''; ?>" 
                                            id="ssc_year" name="ssc_year" required>
                                        <option value="">-- Select Year --</option>
                                        <?php
                                            $currentYear = date("Y");
                                            for ($y = $currentYear; $y >= 1950; $y--) {
                                                echo '<option value="'.$y.'"'.($ssc_year == $y ? ' selected' : '').'>'.$y.'</option>';
                                            }
                                        ?>
                                    </select>
                                    <?php if (isset($errors['ssc_year'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['ssc_year']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">
                                        <i class="bi bi-house-fill me-2"></i>Address
                                    </label>
                                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($address); ?></textarea>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                    <a href="dashboard.php" class="btn btn-outline-secondary me-md-2">
                                        <i class="bi bi-x-circle me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i>Save Changes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Change Password Card -->
            <div class="card shadow mt-4">
                <div class="card-header bg-warning text-dark">
                    <h3 class="mb-0">
                        <i class="bi bi-shield-lock me-2"></i>Change Password
                    </h3>
                </div>
                <div class="card-body">
                    <form action="change-password.php" method="post" id="changePasswordForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">
                                <i class="bi bi-lock-fill me-2"></i>Current Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">
                                <i class="bi bi-key-fill me-2"></i>New Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">
                                <i class="bi bi-key-fill me-2"></i>Confirm New Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-arrow-repeat me-1"></i>Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Profile picture preview
    document.getElementById('profile_pic').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('profilePicPreview').src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Mobile number validation
    document.getElementById("mobile").addEventListener("input", function() {
        const mobileField = this;
        const icon = document.querySelector("#mobile-valid-icon i");
        const value = mobileField.value.trim();
        const regex = /^01[3-9]\d{8}$/;
        
        if (regex.test(value)) {
            icon.className = "bi bi-check-circle-fill text-success";
        } else if (value.length > 0) {
            icon.className = "bi bi-exclamation-circle-fill text-danger";
        } else {
            icon.className = "bi bi-circle text-secondary";
        }
    });
    
    // Toggle password visibility
    document.querySelectorAll(".toggle-password").forEach(button => {
        button.addEventListener("click", function() {
            const input = this.parentElement.querySelector("input");
            const icon = this.querySelector("i");
            
            if (input.type === "password") {
                input.type = "text";
                icon.className = "bi bi-eye-slash";
            } else {
                input.type = "password";
                icon.className = "bi bi-eye";
            }
        });
    });
    
    // Form submission handling
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        const newPass = document.getElementById('new_password').value;
        const confirmPass = document.getElementById('confirm_password').value;
        
        if (newPass !== confirmPass) {
            e.preventDefault();
            alert('New password and confirmation do not match!');
        } else if (newPass.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long!');
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
