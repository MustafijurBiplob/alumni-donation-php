<?php
    require_once 'includes/user_auth.php'; // Ensures user is logged in
    require_once 'includes/header.php';
    require_once 'includes/functions.php';

    $user_id = $_SESSION['user_id'];

    // Fetch donation history for the logged-in user
    $stmt = $conn->prepare("SELECT id, amount, transaction_id, transfer_date, status, created_at FROM donations WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $donations = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

?>

<h1><i class="fas fa-history"></i> আমার অনুদানের ইতিহাস</h1>
<?php display_message(); ?>
<hr>

<?php if (empty($donations)): ?>
    <div class="alert alert-info" role="alert">
        <i class="fas fa-exclamation-circle"></i> আপনি এখনো কোনো অনুদান দেননি। <a href="make_donation.php" class="alert-link">আপনার প্রথম অনুদান দিন!</a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-light">
                <tr>
                    <th>জমাদানের তারিখ</th>
                    <th>ট্রান্সফার তারিখ</th>
                    <th>পরিমাণ (৳)</th>
                    <th>ট্রানজেকশন আইডি</th>
                    <th>অবস্থা</th>
                    <th>ক্রিয়া</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($donations as $donation): ?>
                    <tr>
                        <td><?php echo date("Y-m-d H:i", strtotime($donation['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($donation['transfer_date']); ?></td>
                        <td><?php echo number_format($donation['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($donation['transaction_id']); ?></td>
                        <td>
                            <?php
                            $status = htmlspecialchars($donation['status']);
                            $badge_class = 'bg-secondary'; // Default
                            if ($status == 'Approved') {
                                $badge_class = 'bg-success';
                            } elseif ($status == 'Pending') {
                                $badge_class = 'bg-warning text-dark';
                            } elseif ($status == 'Rejected') {
                                $badge_class = 'bg-danger';
                            }
                            echo "<span class='badge {$badge_class}'>{$status}</span>";
                            ?>
                        </td>
                         <td>
                            <?php if ($donation['status'] == 'Approved'): ?>
                                <a href="download_receipt.php?id=<?php echo $donation['id']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                    <i class="fas fa-download"></i> রসিদ ডাউনলোড করুন
                                </a>
                            <?php elseif ($donation['status'] == 'Pending'): ?>
                                <span class="text-muted"><i class="fas fa-clock"></i> অনুমোদনের অপেক্ষায়</span>
                             <?php elseif ($donation['status'] == 'Rejected'): ?>
                                 <span class="text-muted"><i class="fas fa-times-circle"></i> প্রত্যাখ্যাত</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<div class="mt-4">
    <a href="<?php echo BASE_URL; ?>make_donation.php" class="btn btn-success">
        <i class="fas fa-donate"></i> অন্য একটি অনুদান দিন
    </a>
    <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> ড্যাশবোর্ডে ফিরে যান
    </a>
</div>

<?php include 'includes/footer.php'; ?>
