
<?php include 'includes/header.php'; ?>
<?php include 'includes/functions.php'; ?>

<div class="jumbotron text-center">
    <h1 class="display-4">অ্যালামনাই অ্যাসোসিয়েশনে স্বাগতম</h1>
    <p class="lead">সাবেক শিক্ষার্থীদের একত্রিত করে, ভবিষ্যতের জন্য সহায়তা করছি।</p>
    <hr class="my-4">
    <p>আপনার অনুদান আমাদের লক্ষ্য পূরণে এবং বিভিন্ন উদ্যোগে সহায়তা করে।</p>
    <a class="btn btn-primary btn-lg" href="<?php echo BASE_URL; ?>donation_goal.php" role="button">আরও জানুন ও অনুদান দিন</a>
</div>

<div class="row mt-5">
    <div class="col-md-6">
        <h2>আমাদের সম্পর্কে</h2>
        <p>আমাদের মিশন ও ভিশন সম্পর্কে জানুন এবং কীভাবে আমরা একটি ইতিবাচক পরিবর্তন আনার চেষ্টা করছি।</p>
        <a href="<?php echo BASE_URL; ?>about.php" class="btn btn-secondary">আরও পড়ুন</a>
    </div>
    <div class="col-md-6">
        <h2>সম্পৃক্ত হোন</h2>
        <p>প্রোফাইল ব্যবস্থাপনা, অনুদান প্রদান এবং আপনার সহায়তার প্রভাব দেখতে রেজিস্টার করুন বা লগইন করুন।</p>
        <?php if (!is_logged_in()): ?>
            <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-info me-2">রেজিস্টার করুন</a>
            <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-success">লগইন করুন</a>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-success">ড্যাশবোর্ডে যান</a>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
```

যদি আপনি চাইলে আমি বাংলা ফন্টের জন্য ইউনিকোড সাপোর্ট বা ওয়েবফন্ট যুক্ত করে দিতে পারি। জানান দিলে সেটাও করে দিচ্ছি!