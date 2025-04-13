<?php include 'includes/header.php'; ?>
<?php include_once 'includes/functions.php'; // ডেটা আনার জন্য সম্ভাব্য ব্যবহৃত ফাংশন ?>

<h1>অনুদান লক্ষ্যসমূহ</h1>
<hr>

<p class="lead">আপনার উদারতা আমাদের মিশনকে এগিয়ে নিয়ে যায় এবং সরাসরি আমাদের কমিউনিটিতে প্রভাব ফেলে। দেখুন, কীভাবে আপনার অনুদান পরিবর্তন আনছে।</p>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">স্কলারশিপ ফান্ড</h5>
                <p class="card-text">যোগ্য শিক্ষার্থীদের একাডেমিক স্বপ্ন পূরণে সহায়তা করুন। এই অনুদান শিক্ষার খরচ, বই ও আবাসন ব্যয় কভার করে।</p>
                <p><strong>লক্ষ্য:</strong> বার্ষিক $৫০,০০০</p>
                <div class="progress mb-2">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 65%;" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100">65%</div>
                </div>
                <a href="<?php echo BASE_URL; ?>make_donation.php?purpose=scholarship" class="btn btn-primary">এখনই অনুদান দিন</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">ক্যাম্পাস উন্নয়ন</h5>
                <p class="card-text">নতুন ল্যাব, লাইব্রেরি রিসোর্স ও বিনোদনমূলক সুবিধা তৈরিতে সহায়তা করুন।</p>
                <p><strong>লক্ষ্য:</strong> $১,০০,০০০ প্রকল্প</p>
                <div class="progress mb-2">
                    <div class="progress-bar bg-info" role="progressbar" style="width: 40%;" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">40%</div>
                </div>
                <a href="<?php echo BASE_URL; ?>make_donation.php?purpose=development" class="btn btn-primary">এখনই অনুদান দিন</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">অ্যালামনাই ইভেন্ট ও নেটওয়ার্কিং</h5>
                <p class="card-text">পুনর্মিলনী, ওয়ার্কশপ এবং নেটওয়ার্কিং ইভেন্ট আয়োজনে সহায়তা করুন যা আমাদের সাবেক শিক্ষার্থীদের সংযুক্ত রাখে।</p>
                <p><strong>লক্ষ্য:</strong> বার্ষিক $২০,০০০</p>
                <div class="progress mb-2">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: 80%;" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100">80%</div>
                </div>
                <a href="<?php echo BASE_URL; ?>make_donation.php?purpose=events" class="btn btn-primary">এখনই অনুদান দিন</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">সাধারণ তহবিল</h5>
                <p class="card-text">আমাদের অ্যাসোসিয়েশনের নিয়মিত পরিচালন ব্যয় এবং নতুন প্রয়োজনের জন্য নমনীয় সহায়তা দিন।</p>
                <p><strong>লক্ষ্য:</strong> চলমান</p>
                <!-- চলমান লক্ষ্য হওয়ায় কোনো প্রগ্রেস বার নেই -->
                <a href="<?php echo BASE_URL; ?>make_donation.php" class="btn btn-primary">এখনই অনুদান দিন</a>
            </div>
        </div>
    </div>
</div>

<h2>কেন অনুদান দিবেন?</h2>
<ul>
    <li><strong>প্রভাব:</strong> শিক্ষার্থী, ক্যাম্পাস উন্নয়ন ও অ্যালামনাই প্রোগ্রামে সরাসরি সহায়তা করুন।</li>
    <li><strong>সম্প্রীতি:</strong> অ্যালামনাইদের মাঝে বন্ধন দৃঢ় করুন।</li>
    <li><strong>উত্তরাধিকার:</strong> আমাদের শিক্ষা প্রতিষ্ঠানের সফলতা ও খ্যাতি বজায় রাখতে অবদান রাখুন।</li>
    <li><strong>স্বচ্ছতা:</strong> আমাদের লক্ষ্য পূরণের অগ্রগতি দেখুন এবং আপনার টাকা কোথায় যাচ্ছে তা জানতে পারেন (বিস্তারিত ইউজার ড্যাশবোর্ডে)।</li>
</ul>

<p>পরিবর্তন আনতে প্রস্তুত? অনুদান দিতে লগইন করুন বা নিবন্ধন করুন।</p>
<?php if (!is_logged_in()): ?>
    <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-success me-2">লগইন করে অনুদান দিন</a>
    <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-info">নিবন্ধন করে অনুদান দিন</a>
<?php else: ?>
    <a href="<?php echo BASE_URL; ?>make_donation.php" class="btn btn-success">অনুদান দিন</a>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
