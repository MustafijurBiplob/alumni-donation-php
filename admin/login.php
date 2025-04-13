<?php
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../includes/functions.php';

    // যদি অ্যাডমিন হিসেবে ইতিমধ্যেই লগইন করা থাকে তবে রিডিরেক্ট করুন
    if (is_logged_in() && is_admin()) {
        redirect('admin/dashboard.php');
    }
    // না অ্যাডমিন লগইন ব্যবহারকারীদের কি রিডিরেক্ট করা উচিত? ঐচ্ছিক।
    // elseif (is_logged_in()) {
    //     redirect('dashboard.php'); // অথবা একটি মেসেজ দেখান
    // }


    $login_identifier = $password = "";
    $error = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $login_identifier = sanitize_input($_POST['login_identifier']);
        $password = $_POST['password'];

        if (empty($login_identifier) || empty($password)) {
            $error = "ইমেইল/মোবাইল এবং পাসওয়ার্ড উভয়ই প্রয়োজন।";
        } else {
            $field_type = filter_var($login_identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

            $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE $field_type = ?");
            $stmt->bind_param("s", $login_identifier);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();

                // পাসওয়ার্ড এবং রোল যাচাই করুন
                if (verify_password($password, $user['password'])) {
                    if ($user['role'] === 'admin') {
                        // পাসওয়ার্ড সঠিক এবং ব্যবহারকারী অ্যাডমিন, সেশন শুরু করুন
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_role'] = $user['role'];

                        set_message("অ্যাডমিন লগইন সফল! স্বাগতম, " . htmlspecialchars($user['name']) . ".", "success");
                        redirect('admin/dashboard.php');
                    } else {
                        // সঠিক পাসওয়ার্ড কিন্তু অ্যাডমিন নয়
                        $error = "অ্যাক্সেস অস্বীকার। এই লগইন শুধুমাত্র অ্যাডমিনের জন্য।";
                    }
                } else {
                    // ভুল পাসওয়ার্ড
                    $error = "অবৈধ প্রমাণপত্র।"; // নিরাপত্তার জন্য সাধারণ রাখুন
                }
            } else {
                // কোনো ব্যবহারকারী পাওয়া যায়নি
                $error = "অবৈধ প্রমাণপত্র।"; // নিরাপত্তার জন্য সাধারণ রাখুন
            }
            $stmt->close();
        }
         if ($error) {
            set_message($error, "danger");
        }
    }

    include __DIR__ . '/../includes/header.php';
    ?>

    <div class="login-form">
        <h2>অ্যাডমিন লগইন</h2>
        <?php display_message(); ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-3">
                <label for="login_identifier" class="form-label">অ্যাডমিন ইমেইল বা মোবাইল</label>
                <input type="text" class="form-control" id="login_identifier" name="login_identifier" value="<?php echo htmlspecialchars($login_identifier); ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">পাসওয়ার্ড</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">অ্যাডমিন হিসেবে লগইন করুন</button>
        </form>
         <!-- ঐচ্ছিক: যদি অ্যাডমিন রেজিস্ট্রেশন প্রয়োজন হয় তবে লিঙ্ক দিন, অন্যথায় অপসারণ করুন -->
         <p class="mt-3 text-center">অ্যাডমিন একাউন্ট প্রয়োজন? <a href="register.php">এখানে রেজিস্টার করুন</a></p>
         <p class="mt-2 text-center">আপনি কি সাধারণ ব্যবহারকারী? <a href="<?php echo BASE_URL; ?>login.php">এখানে লগইন করুন</a></p>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
