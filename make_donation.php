<?php
    require_once 'includes/user_auth.php'; // লগইন নিশ্চিত করতে
    require_once 'includes/header.php';
    require_once 'includes/functions.php';

    $user_id = $_SESSION['user_id'];
    $amount = $mobile_banking_number = $transaction_id = $transfer_date = "";
    $errors = [];
    $screenshot_path = null; // ফাইল আপলোড সফল হলে পাথ সংরক্ষণ করতে

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // ইনপুট স্যানিটাইজ করা
        $amount = filter_var(sanitize_input($_POST['amount']), FILTER_VALIDATE_FLOAT);
        $mobile_banking_number = sanitize_input($_POST['mobile_banking_number']);
        $transaction_id = sanitize_input($_POST['transaction_id']);
        $transfer_date = sanitize_input($_POST['transfer_date']);

        // ইনপুট ভ্যালিডেশন
        if ($amount === false || $amount <= 0) {
            $errors['amount'] = "অনুগ্রহ করে সঠিক দানের পরিমাণ লিখুন।";
        }
        if (empty($mobile_banking_number)) {
            $errors['mobile_banking_number'] = "মোবাইল ব্যাংকিং নম্বর প্রয়োজন।";
        }
        if (empty($transaction_id)) {
            $errors['transaction_id'] = "লেনদেন আইডি বা রেফারেন্স প্রয়োজন।";
        }
        if (empty($transfer_date)) {
            $errors['transfer_date'] = "ট্রান্সফার তারিখ প্রয়োজন।";
        } else {
            // ঐচ্ছিক: তারিখ ফরম্যাট যাচাই করা
            if (!strtotime($transfer_date)) {
                 $errors['transfer_date'] = "অবৈধ তারিখ ফরম্যাট।";
            }
        }

        // ফাইল আপলোড হ্যান্ডলিং (ঐচ্ছিক স্ক্রীনশট)
        if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/screenshots/';
            // ডিরেক্টরি না থাকলে তৈরি করা
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_info = pathinfo($_FILES['screenshot']['name']);
            $file_ext = strtolower($file_info['extension']);
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];

            if (in_array($file_ext, $allowed_ext)) {
                if ($_FILES['screenshot']['size'] <= 5000000) { // সর্বোচ্চ 5MB
                    // ইউনিক ফাইলনেম তৈরি করা
                    $new_filename = uniqid('ss_', true) . '.' . $file_ext;
                    $destination = $upload_dir . $new_filename;

                    if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $destination)) {
                        $screenshot_path = $destination; // আপলোডের রিলেটিভ পাথ সংরক্ষণ
                    } else {
                        $errors['screenshot'] = "ফাইল আপলোড করতে ব্যর্থ।";
                    }
                } else {
                    $errors['screenshot'] = "ফাইল খুব বড় (সর্বোচ্চ 5MB)।";
                }
            } else {
                $errors['screenshot'] = "অবৈধ ফাইল টাইপ। অনুমোদিত ফাইল টাইপসমূহ: " . implode(', ', $allowed_ext);
            }
        } elseif (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] != UPLOAD_ERR_NO_FILE) {
             // অন্য কোন আপলোড ত্রুটি হ্যান্ডলিং
             $errors['screenshot'] = "ফাইল আপলোডে ত্রুটি। কোড: " . $_FILES['screenshot']['error'];
        }

        // যদি কোন ত্রুটি না থাকে, তাহলে ডাটাবেসে ইনসার্ট করা
        if (empty($errors)) {
            $status = 'Pending'; // ডিফল্ট স্ট্যাটাস

            $stmt = $conn->prepare("INSERT INTO donations (user_id, amount, mobile_banking_number, transaction_id, transfer_date, screenshot, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("idsssss", $user_id, $amount, $mobile_banking_number, $transaction_id, $transfer_date, $screenshot_path, $status);

            if ($stmt->execute()) {
                set_message("দান সফলভাবে জমা হয়েছে! এটি এখন অনুমোদনের জন্য অপেক্ষমাণ।", "success");
                redirect('donation_history.php');
            } else {
                 set_message("দান জমা দিতে ব্যর্থ হয়েছে: " . $stmt->error, "danger");
                 // ঐচ্ছিক: DB ইনসার্ট ব্যর্থ হলে আপলোডকৃত ফাইল মুছে ফেলা
                 if ($screenshot_path && file_exists($screenshot_path)) {
                     unlink($screenshot_path);
                 }
            }
            $stmt->close();
        } else {
             set_message("নীচের ত্রুটিগুলি ঠিক করুন।", "warning");
             // ঐচ্ছিক: ভ্যালিডেশন ব্যর্থ হলে আপলোডকৃত ফাইল মুছে ফেলা
             if ($screenshot_path && file_exists($screenshot_path)) {
                 unlink($screenshot_path);
             }
        }
    }

    ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <h1>দান করুন</h1>
    <p>নীচে আপনার দানের বিস্তারিত তথ্য পূর্ণ করুন। আপনার অবদান 'Pending' হিসাবে চিহ্নিত হবে যতক্ষণ না প্রশাসক দ্বারা যাচাই করা হয়।</p>
    <hr>

    <div class="donation-form">
        <?php display_message(); ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="amount" class="form-label"><i class="fa fa-dollar-sign"></i> দান পরিমাণ ($)</label>
                <input type="number" step="0.01" class="form-control <?php echo isset($errors['amount']) ? 'is-invalid' : ''; ?>" id="amount" name="amount" value="<?php echo htmlspecialchars($amount); ?>" required>
                 <?php if (isset($errors['amount'])): ?><div class="invalid-feedback"><?php echo $errors['amount']; ?></div><?php endif; ?>
            </div>
             <div class="mb-3">
                <label for="mobile_banking_number" class="form-label"><i class="fa fa-phone"></i> মোবাইল ব্যাংকিং নম্বর (যা দিয়ে আপনি পাঠিয়েছেন)</label>
                <input type="text" class="form-control <?php echo isset($errors['mobile_banking_number']) ? 'is-invalid' : ''; ?>" id="mobile_banking_number" name="mobile_banking_number" value="<?php echo htmlspecialchars($mobile_banking_number); ?>" required>
                 <?php if (isset($errors['mobile_banking_number'])): ?><div class="invalid-feedback"><?php echo $errors['mobile_banking_number']; ?></div><?php endif; ?>
            </div>
             <div class="mb-3">
                <label for="transaction_id" class="form-label"><i class="fa fa-barcode"></i> রেফারেন্স / লেনদেন আইডি</label>
                <input type="text" class="form-control <?php echo isset($errors['transaction_id']) ? 'is-invalid' : ''; ?>" id="transaction_id" name="transaction_id" value="<?php echo htmlspecialchars($transaction_id); ?>" required>
                 <?php if (isset($errors['transaction_id'])): ?><div class="invalid-feedback"><?php echo $errors['transaction_id']; ?></div><?php endif; ?>
            </div>
             <div class="mb-3">
                <label for="transfer_date" class="form-label"><i class="fa fa-calendar"></i> ট্রান্সফারের তারিখ</label>
                <input type="date" class="form-control <?php echo isset($errors['transfer_date']) ? 'is-invalid' : ''; ?>" id="transfer_date" name="transfer_date" value="<?php echo htmlspecialchars($transfer_date); ?>" required>
                 <?php if (isset($errors['transfer_date'])): ?><div class="invalid-feedback"><?php echo $errors['transfer_date']; ?></div><?php endif; ?>
            </div>
             <div class="mb-3">
                <label for="screenshot" class="form-label"><i class="fa fa-camera"></i> স্ক্রীনশট আপলোড করুন (ঐচ্ছিক, সর্বোচ্চ 5MB)</label>
                <input type="file" class="form-control <?php echo isset($errors['screenshot']) ? 'is-invalid' : ''; ?>" id="screenshot" name="screenshot" accept="image/*,application/pdf">
                 <?php if (isset($errors['screenshot'])): ?><div class="invalid-feedback"><?php echo $errors['screenshot']; ?></div><?php endif; ?>
                 <div class="form-text">অনুমোদিত ফরম্যাট: JPG, JPEG, PNG, GIF, PDF।</div>
            </div>

            <button type="submit" class="btn btn-primary w-100"><i class="fa fa-check-circle"></i> দান জমা দিন</button>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>
