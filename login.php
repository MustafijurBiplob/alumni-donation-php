<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    // Redirect admins trying to access user login to admin area
    if (is_admin()) {
        set_message("You are logged in as an admin. Redirecting to admin dashboard.", "info");
        redirect('admin/dashboard.php');
    } else {
        // Regular user already logged in
        redirect('dashboard.php');
    }
}

$login_identifier = $password = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid request. Please try again.";
    } else {
        $login_identifier = sanitize_input($_POST['login_identifier']);
        $password = $_POST['password']; // Don't sanitize password before verification

        if (empty($login_identifier) || empty($password)) {
            $error = "Both email/mobile and password are required.";
        } else {
            // Determine if login identifier is email or mobile
            $field_type = filter_var($login_identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

            // Prepare statement with additional security checks
            $stmt = $conn->prepare("SELECT id, name, password, role, is_active FROM users WHERE $field_type = ?");
            $stmt->bind_param("s", $login_identifier);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                
                // Check if account is active
                if (!$user['is_active']) {
                    $error = "Your account is inactive. Please contact support.";
                } else {
                    // Verify password with timing-safe comparison
                    if (verify_password($password, $user['password'])) {
                        // Check if the user has the 'user' role
                        if ($user['role'] === 'user') {
                            // Regenerate session ID to prevent session fixation
                            session_regenerate_id(true);
                            
                            // Password is correct, start session for USER
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_name'] = $user['name'];
                            $_SESSION['user_role'] = $user['role'];
                            $_SESSION['last_activity'] = time();
                            
                            // Update last login time
                            $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                            $update_stmt->bind_param("i", $user['id']);
                            $update_stmt->execute();
                            $update_stmt->close();

                            set_message("Login successful! Welcome, " . htmlspecialchars($user['name']) . ".", "success");
                            redirect('dashboard.php'); // Redirect USER to user dashboard
                        } else if ($user['role'] === 'admin') {
                            // User is an admin, redirect them to the admin login page
                            $error = "Administrators should log in via the admin portal.";
                        } else {
                            // Unknown role
                            $error = "Invalid user role configured.";
                        }
                    } else {
                        // Delay response to mitigate timing attacks
                        usleep(rand(200000, 500000));
                        $error = "Invalid password.";
                    }
                }
            } else {
                // Delay response to mitigate timing attacks
                usleep(rand(200000, 500000));
                $error = "No user found with that " . ($field_type == 'email' ? 'email' : 'mobile number') . ".";
            }
            $stmt->close();
        }
    }
    
    if ($error) {
        set_message($error, "danger");
    }
}

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

include 'includes/header.php';
?>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h2 class="text-center mb-0">
                        <i class="bi bi-person-lock me-2"></i>User Login
                    </h2>
                </div>
                <div class="card-body p-4">
                    <?php display_message(); ?>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="loginForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="mb-3">
                            <label for="login_identifier" class="form-label">
                                <i class="bi bi-person-badge me-2"></i>Email or Mobile Number
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope-at"></i></span>
                                <input type="text" class="form-control" id="login_identifier" 
                                       name="login_identifier" value="<?php echo htmlspecialchars($login_identifier); ?>" 
                                       placeholder="Enter email or mobile" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-key me-2"></i>Password
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" 
                                       name="password" placeholder="Enter password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg" id="loginButton">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login
                            </button>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rememberMe">
                                <label class="form-check-label" for="rememberMe">
                                    <i class="bi bi-check-circle me-1"></i>Remember me
                                </label>
                            </div>
                            <a href="forgot_password.php" class="text-decoration-none">
                                <i class="bi bi-question-circle me-1"></i>Forgot password?
                            </a>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-2">Don't have an account? 
                            <a href="register.php" class="text-decoration-none">
                                <i class="bi bi-person-plus me-1"></i>Register here
                            </a>
                        </p>
                        <p class="mb-0">Are you an administrator? 
                            <a href="<?php echo BASE_URL; ?>admin/login.php" class="text-decoration-none">
                                <i class="bi bi-shield-lock me-1"></i>Admin Login
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
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
    
    // Disable form submission on Enter key in sensitive fields
    document.getElementById('loginForm').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && (e.target.id === 'password' || e.target.id === 'login_identifier')) {
            e.preventDefault();
            document.getElementById('loginButton').click();
        }
    });
    
    // Add loading state to login button
    document.getElementById('loginButton').addEventListener('click', function() {
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Logging in...';
        this.disabled = true;
        document.getElementById('loginForm').submit();
    });
});
</script>

<?php include 'includes/footer.php'; ?>