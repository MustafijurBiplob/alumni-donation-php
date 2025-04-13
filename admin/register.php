<?php
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../includes/functions.php';

    // IMPORTANT SECURITY NOTE: Protect or remove this page after setup.
    // ... (rest of the existing security comments) ...

    $name = $email = $mobile = $password = $confirm_password = "";
    $errors = [];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // ... (existing input sanitization and validation) ...
        $name = sanitize_input($_POST['name']);
        $email = filter_var(sanitize_input($_POST['email']), FILTER_VALIDATE_EMAIL);
        $mobile = sanitize_input($_POST['mobile']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $address = !empty($_POST['address']) ? sanitize_input($_POST['address']) : null;

        // Basic Validation (same as before)
        if (empty($name)) $errors['name'] = "Name is required.";
        if (empty($email)) $errors['email'] = "A valid email is required.";
        else {
            $stmt_check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt_check_email->bind_param("s", $email);
            $stmt_check_email->execute();
            $stmt_check_email->store_result();
            if ($stmt_check_email->num_rows > 0) $errors['email'] = "Email already registered.";
            $stmt_check_email->close();
        }
        if (empty($mobile)) $errors['mobile'] = "Mobile number is required.";
        else {
            $stmt_check_mobile = $conn->prepare("SELECT id FROM users WHERE mobile = ?");
            $stmt_check_mobile->bind_param("s", $mobile);
            $stmt_check_mobile->execute();
            $stmt_check_mobile->store_result();
            if ($stmt_check_mobile->num_rows > 0) $errors['mobile'] = "Mobile number already registered.";
            $stmt_check_mobile->close();
        }
        if (empty($password)) $errors['password'] = "Password is required.";
        elseif (strlen($password) < 6) $errors['password'] = "Password must be at least 6 characters long.";
        if ($password !== $confirm_password) $errors['confirm_password'] = "Passwords do not match.";


        // If no errors, proceed with registration
        if (empty($errors)) {
            $hashed_password = hash_password($password);
            $role = 'admin'; // Explicitly set role to admin

            $conn->begin_transaction(); // Start transaction

            try {
                // Insert into users table
                $stmt_user = $conn->prepare("INSERT INTO users (name, email, mobile, address, password, role) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_user->bind_param("ssssss", $name, $email, $mobile, $address, $hashed_password, $role);

                if ($stmt_user->execute()) {
                    $new_admin_user_id = $conn->insert_id; // Get the ID of the newly created admin user
                    $stmt_user->close();

                    // --- NEW: Insert into admin_registration_log ---
                    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null; // Get IP address
                    $log_notes = "Admin registered via admin/register.php"; // Example note

                    $stmt_log = $conn->prepare("INSERT INTO admin_registration_log (user_id, ip_address, notes) VALUES (?, ?, ?)");
                    $stmt_log->bind_param("iss", $new_admin_user_id, $ip_address, $log_notes);

                    if ($stmt_log->execute()) {
                        $stmt_log->close();
                        $conn->commit(); // Commit transaction if both inserts succeed
                        set_message("Admin registration successful! Please log in.", "success");
                        redirect('admin/login.php');
                    } else {
                        // Log insert failed
                        throw new Exception("Failed to log admin registration: " . $stmt_log->error);
                    }
                } else {
                    // User insert failed
                     throw new Exception("Admin user creation failed: " . $stmt_user->error);
                }
            } catch (Exception $e) {
                $conn->rollback(); // Roll back transaction on error
                set_message("Registration failed: " . $e->getMessage(), "danger");
                // Close any open statements if necessary
                if (isset($stmt_user) && $stmt_user->errno) $stmt_user->close();
                if (isset($stmt_log) && $stmt_log->errno) $stmt_log->close();
            }

        } else {
             set_message("Please correct the errors below.", "warning");
        }
    }

    // Use a simplified header/footer or the main ones? Using main ones for consistency.
    include __DIR__ . '/../includes/header.php';
    ?>

    <div class="register-form">
        <h2>Admin Registration</h2>
        <div class="alert alert-danger"><strong>Security Warning:</strong> Open admin registration is insecure. Protect this page appropriately.</div>
        <?php display_message(); ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
             <!-- Form fields remain the same as before -->
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?php echo $errors['name']; ?></div><?php endif; ?>
            </div>
             <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                 <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?php echo $errors['email']; ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="mobile" class="form-label">Mobile Number</label>
                <input type="tel" class="form-control <?php echo isset($errors['mobile']) ? 'is-invalid' : ''; ?>" id="mobile" name="mobile" value="<?php echo htmlspecialchars($mobile); ?>" required>
                 <?php if (isset($errors['mobile'])): ?><div class="invalid-feedback"><?php echo $errors['mobile']; ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address (Optional)</label>
                <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" id="password" name="password" required>
                 <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?php echo $errors['password']; ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" id="confirm_password" name="confirm_password" required>
                 <?php if (isset($errors['confirm_password'])): ?><div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div><?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register Admin</button>
        </form>
        <p class="mt-3 text-center">Already have an admin account? <a href="login.php">Login here</a></p>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
