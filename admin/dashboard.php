<?php
    require_once __DIR__ . '/../includes/admin_auth.php'; // নিশ্চিত করে অ্যাডমিন লগইন
    require_once __DIR__ . '/../includes/header.php';
    require_once __DIR__ . '/../includes/functions.php';

    // অ্যাডমিন ড্যাশবোর্ড ডেটা সংগ্রহ
    // মোট ব্যবহারকারী
    $result_users = $conn->query("SELECT COUNT(id) as total FROM users");
    $total_users = $result_users->fetch_assoc()['total'] ?? 0;

    // মোট অনুদান (অনুমোদিত পরিমাণ)
    $total_donations_amount = get_total_donations(); // বিদ্যমান ফাংশন ব্যবহার

    // অপেক্ষমাণ অনুদান (সংখ্যা)
    $result_pending = $conn->query("SELECT COUNT(id) as total FROM donations WHERE status = 'Pending'");
    $pending_donations_count = $result_pending->fetch_assoc()['total'] ?? 0;

    // মোট ব্যয়
    $total_expense = get_total_expenses(); // বিদ্যমান ফাংশন ব্যবহার

    // বর্তমান ব্যালেন্স
    $current_balance = $total_donations_amount - $total_expense;
?>

<h1>অ্যাডমিন ড্যাশবোর্ড</h1>
<?php display_message(); ?>
<p class="lead">এটি অ্যালামনাই অ্যাসোসিয়েশন সিস্টেমের সারসংক্ষেপ।</p>
<hr>

<div class="row">
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-header">মোট ব্যবহারকারী</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $total_users; ?></h5>
                <a href="<?php echo BASE_URL; ?>admin/user_management.php" class="btn btn-sm btn-outline-light">ব্যবহারকারী ব্যবস্থাপনা</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">মোট অনুমোদিত অনুদান</div>
            <div class="card-body">
                <h5 class="card-title">৳<?php echo number_format($total_donations_amount, 2); ?></h5>
                <a href="<?php echo BASE_URL; ?>admin/income_report.php" class="btn btn-sm btn-outline-light">আয় রিপোর্ট দেখুন</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-dark bg-warning mb-3">
            <div class="card-header">অপেক্ষমাণ অনুদান</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $pending_donations_count; ?></h5>
                <a href="<?php echo BASE_URL; ?>admin/donation_approval.php" class="btn btn-sm btn-outline-dark">অনুমোদন / বাতিল করুন</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger mb-3">
            <div class="card-header">মোট ব্যয়</div>
            <div class="card-body">
                <h5 class="card-title">৳<?php echo number_format($total_expense, 2); ?></h5>
                <a href="<?php echo BASE_URL; ?>admin/expense_report.php" class="btn btn-sm btn-outline-light">ব্যয় রিপোর্ট দেখুন</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header">বর্তমান ব্যালেন্স</div>
            <div class="card-body">
                <h5 class="card-title">৳<?php echo number_format($current_balance, 2); ?></h5>
                <a href="<?php echo BASE_URL; ?>admin/balance_sheet.php" class="btn btn-sm btn-outline-light">ব্যালেন্স শীট দেখুন</a>
            </div>
        </div>
    </div>
</div>

<h2>অ্যাডমিন অ্যাকশন</h2>
<div class="list-group">
    <a href="<?php echo BASE_URL; ?>admin/donation_approval.php" class="list-group-item list-group-item-action">অনুদান অনুমোদন প্যানেল</a>
    <a href="<?php echo BASE_URL; ?>admin/user_management.php" class="list-group-item list-group-item-action">ব্যবহারকারী ব্যবস্থাপনা</a>
    <a href="<?php echo BASE_URL; ?>admin/income_report.php" class="list-group-item list-group-item-action">আয় রিপোর্ট</a>
    <a href="<?php echo BASE_URL; ?>admin/add_expense.php" class="list-group-item list-group-item-action">নতুন ব্যয় যোগ করুন</a>
    <a href="<?php echo BASE_URL; ?>admin/expense_report.php" class="list-group-item list-group-item-action">ব্যয় রিপোর্ট</a>
    <a href="<?php echo BASE_URL; ?>admin/balance_sheet.php" class="list-group-item list-group-item-action">মোট ব্যালেন্স শীট</a>
    <a href="<?php echo BASE_URL; ?>admin/user_list.php" class="list-group-item list-group-item-action">সদস্যদের তালিকা</a>
    <a href="<?php echo BASE_URL; ?>logout.php" class="list-group-item list-group-item-action text-danger">প্রস্থান করুন</a>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
