<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<?php
    require_once 'includes/user_auth.php'; // ব্যবহারকারী লগইন নিশ্চিত করুন
    require_once 'includes/header.php';
    require_once 'includes/functions.php';

    // মোট অনুমোদিত আয়ের তথ্য (দান)
    $total_income = get_total_donations(); // মোট অনুমোদিত দানের পরিমাণ আনুন

    // মোট খরচের তথ্য (সাধারণত শুধু অ্যাডমিনরা দেখে)
    // ব্যবহারকারীরা সাধারণত খরচ দেখবে না, তবে প্রয়োজন হলে পরবর্তীতে রোল অনুসারে লুকানো হবে
    $total_expense = get_total_expenses();

    // নেট ব্যালেন্স হিসাব করুন
    $net_balance = $total_income - $total_expense;

    ?>

    <h1>আয় ও খরচের সারাংশ</h1>
    <p>এই সারাংশে অনুমোদিত দান এবং রেকর্ডকৃত খরচের উপর ভিত্তি করে মোট আর্থিক অবস্থা দেখানো হয়েছে।</p>
    <hr>

    <div class="row">
        <!-- মোট আয়ের কার্ড -->
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">
                    <i class="fas fa-dollar-sign"></i> মোট আয় (অনুমোদিত দান)
                </div>
                <div class="card-body">
                    <h5 class="card-title">$<?php echo number_format($total_income, 2); ?></h5>
                    <p class="card-text">সব বৈধ দানের মোট পরিমাণ।</p>
                </div>
            </div>
        </div>

        <!-- মোট খরচের কার্ড -->
        <div class="col-md-4">
             <div class="card text-white bg-danger mb-3">
                <div class="card-header">
                    <i class="fas fa-credit-card"></i> মোট খরচ
                </div>
                <div class="card-body">
                    <?php if (is_admin()): // শুধু অ্যাডমিনরা দেখতে পারে ?>
                        <h5 class="card-title">$<?php echo number_format($total_expense, 2); ?></h5>
                        <p class="card-text">মোট রেকর্ডকৃত অপারেটিং খরচ।</p>
                         <a href="<?php echo BASE_URL; ?>admin/expense_report.php" class="btn btn-sm btn-outline-light">
                             <i class="fas fa-eye"></i> বিস্তারিত দেখুন
                         </a>
                    <?php else: ?>
                         <h5 class="card-title">N/A</h5>
                         <p class="card-text">খরচের বিস্তারিত তথ্য শুধুমাত্র প্রশাসকদের জন্য উপলব্ধ।</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- নেট ব্যালেন্সের কার্ড -->
         <div class="col-md-4">
             <div class="card <?php echo ($net_balance >= 0) ? 'text-white bg-info' : 'text-white bg-warning'; ?> mb-3">
                <div class="card-header">
                    <i class="fas fa-balance-scale"></i> নেট ব্যালেন্স
                </div>
                <div class="card-body">
                     <?php if (is_admin()): // শুধুমাত্র অ্যাডমিনরা দেখতে পারে ?>
                        <h5 class="card-title">$<?php echo number_format($net_balance, 2); ?></h5>
                        <p class="card-text">বর্তমান আর্থিক অবস্থান (আয় - খরচ)।</p>
                         <a href="<?php echo BASE_URL; ?>admin/balance_sheet.php" class="btn btn-sm btn-outline-light">
                             <i class="fas fa-file-invoice"></i> ব্যালেন্স শীট দেখুন
                         </a>
                     <?php else: ?>
                         <h5 class="card-title">N/A</h5>
                         <p class="card-text">বিস্তারিত ব্যালেন্স শুধুমাত্র প্রশাসকদের জন্য উপলব্ধ।</p>
                     <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ব্যবহারকারীদের জন্য নোট -->
    <?php if (!is_admin()): ?>
    <div class="alert alert-secondary" role="alert">
      নোট: আপনি ব্যবহারকারী হিসাবে মোট অনুমোদিত আয় দেখতে পারেন। খরচ এবং ব্যালেন্সের বিস্তারিত তথ্য শুধুমাত্র প্রশাসকদের জন্য সীমাবদ্ধ।
    </div>
    <?php endif; ?>

     <div class="mt-4">
        <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> ড্যাশবোর্ডে ফিরে যান
        </a>
    </div>

    <?php include 'includes/footer.php'; ?>
