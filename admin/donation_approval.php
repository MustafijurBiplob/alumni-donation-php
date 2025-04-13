<?php
    require_once __DIR__ . '/../includes/admin_auth.php';
    require_once __DIR__ . '/../includes/header.php';
    require_once __DIR__ . '/../includes/functions.php';

    // Handle Approve/Reject Actions
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['donation_id']) && isset($_POST['action'])) {
        $donation_id = (int)$_POST['donation_id'];
        $action = $_POST['action']; // 'approve' or 'reject'

        if ($donation_id > 0 && ($action === 'approve' || $action === 'reject')) {
            $new_status = ($action === 'approve') ? 'Approved' : 'Rejected';
            // Add updated_at timestamp if needed
            $stmt = $conn->prepare("UPDATE donations SET status = ?, updated_at = NOW() WHERE id = ? AND status = 'Pending'");
            $stmt->bind_param("si", $new_status, $donation_id);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    set_message("Donation ID {$donation_id} has been {$new_status}.", "success");
                    // Optional: Send notification to user
                } else {
                    set_message("Donation ID {$donation_id} could not be updated (might already be processed or not found).", "warning");
                }
            } else {
                set_message("Error updating donation status: " . $stmt->error, "danger");
            }
            $stmt->close();
            // Redirect to avoid form resubmission on refresh
            redirect('admin/donation_approval.php');
        } else {
             set_message("Invalid action or donation ID.", "danger");
             redirect('admin/donation_approval.php');
        }
    }


    // Fetch Pending Donations
    $stmt = $conn->prepare("SELECT d.id, d.amount, d.mobile_banking_number, d.transaction_id, d.transfer_date, d.screenshot, d.created_at, u.name as user_name, u.mobile as user_mobile
                           FROM donations d
                           JOIN users u ON d.user_id = u.id
                           WHERE d.status = 'Pending'
                           ORDER BY d.created_at ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    $pending_donations = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    ?>

    <h1>Donation Approval Panel</h1>
    <?php display_message(); ?>
    <p>Review and approve or reject pending donations.</p>
    <hr>

    <?php if (empty($pending_donations)): ?>
        <div class="alert alert-info" role="alert">
          There are no pending donations to review at this time.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>User Name</th>
                        <th>User Mobile</th>
                        <th>Amount ($)</th>
                        <th>Transfer Date</th>
                        <th>Transaction ID</th>
                        <th>Submitted At</th>
                        <th>Screenshot</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_donations as $donation): ?>
                        <tr>
                            <td><?php echo $donation['id']; ?></td>
                            <td><?php echo htmlspecialchars($donation['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($donation['user_mobile']); ?></td>
                            <td><?php echo number_format($donation['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($donation['transfer_date']); ?></td>
                            <td><?php echo htmlspecialchars($donation['transaction_id']); ?></td>
                            <td><?php echo date("Y-m-d H:i", strtotime($donation['created_at'])); ?></td>
                            <td>
                                <?php if (!empty($donation['screenshot'])): ?>
                                    <a href="<?php echo BASE_URL . htmlspecialchars($donation['screenshot']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">View</a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display: inline-block; margin-right: 5px;">
                                    <input type="hidden" name="donation_id" value="<?php echo $donation['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                                </form>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display: inline-block;">
                                    <input type="hidden" name="donation_id" value="<?php echo $donation['id']; ?>">
                                    <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this donation?');">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

     <div class="mt-4">
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-secondary">Back to Admin Dashboard</a>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
