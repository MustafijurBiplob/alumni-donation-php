<?php
    require_once __DIR__ . '/../includes/admin_auth.php';
    require_once __DIR__ . '/../includes/header.php';
    require_once __DIR__ . '/../includes/functions.php';

    // --- Filtering Logic ---
    $filter_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
    $filter_start_date = isset($_GET['start_date']) ? sanitize_input($_GET['start_date']) : null;
    $filter_end_date = isset($_GET['end_date']) ? sanitize_input($_GET['end_date']) : null;

    // Base SQL query
    $sql = "SELECT d.id, d.amount, d.transaction_id, d.transfer_date, d.updated_at as approval_date, u.name as user_name, u.id as user_id
            FROM donations d
            JOIN users u ON d.user_id = u.id
            WHERE d.status = 'Approved'";

    $params = [];
    $types = "";

    // Append filters to SQL query
    if ($filter_user_id) {
        $sql .= " AND d.user_id = ?";
        $params[] = $filter_user_id;
        $types .= "i";
    }
    if ($filter_start_date) {
        $sql .= " AND d.transfer_date >= ?";
        $params[] = $filter_start_date;
        $types .= "s";
    }
    if ($filter_end_date) {
        // Add 1 day to end date to include the whole day
        $end_date_inclusive = date('Y-m-d', strtotime($filter_end_date . ' +1 day'));
        $sql .= " AND d.transfer_date < ?"; // Use transfer_date or approval_date (updated_at)? Using transfer_date here.
        $params[] = $end_date_inclusive; // Use the adjusted date
        $types .= "s";
    }

    $sql .= " ORDER BY d.transfer_date DESC, d.updated_at DESC";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $approved_donations = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Calculate total income for the filtered results
    $total_filtered_income = 0;
    foreach ($approved_donations as $donation) {
        $total_filtered_income += $donation['amount'];
    }

    // Fetch list of users for the filter dropdown
    $user_list_result = $conn->query("SELECT id, name FROM users ORDER BY name ASC");
    $users_for_filter = $user_list_result->fetch_all(MYSQLI_ASSOC);

    ?>

    <h1>Income Report (Approved Donations)</h1>
    <?php display_message(); ?>
    <hr>

    <!-- Filter Form -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="row g-3 align-items-end bg-light p-3 rounded mb-4">
        <div class="col-md-3">
            <label for="user_id" class="form-label">Filter by User</label>
            <select name="user_id" id="user_id" class="form-select">
                <option value="">All Users</option>
                <?php foreach ($users_for_filter as $user): ?>
                    <option value="<?php echo $user['id']; ?>" <?php echo ($filter_user_id == $user['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['name']); ?> (ID: <?php echo $user['id']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="start_date" class="form-label">Start Date (Transfer)</label>
            <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo htmlspecialchars($filter_start_date ?? ''); ?>">
        </div>
        <div class="col-md-3">
            <label for="end_date" class="form-label">End Date (Transfer)</label>
            <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo htmlspecialchars($filter_end_date ?? ''); ?>">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary me-2">Filter</button>
            <a href="<?php echo BASE_URL; ?>admin/income_report.php" class="btn btn-secondary">Clear</a>
        </div>
    </form>

    <!-- Summary -->
     <div class="alert alert-success" role="alert">
        <strong>Total Income (Filtered): $<?php echo number_format($total_filtered_income, 2); ?></strong>
        (Based on <?php echo count($approved_donations); ?> approved donations matching the criteria)
    </div>


    <!-- Donations Table -->
    <?php if (empty($approved_donations)): ?>
        <div class="alert alert-warning" role="alert">
          No approved donations found matching the selected filters.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Donation ID</th>
                        <th>User Name</th>
                        <th>Amount ($)</th>
                        <th>Transfer Date</th>
                        <th>Approval Date</th>
                        <th>Transaction ID</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($approved_donations as $donation): ?>
                        <tr>
                            <td><?php echo $donation['id']; ?></td>
                            <td><a href="?user_id=<?php echo $donation['user_id']; ?>"><?php echo htmlspecialchars($donation['user_name']); ?></a></td>
                            <td><?php echo number_format($donation['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($donation['transfer_date']); ?></td>
                            <td><?php echo date("Y-m-d H:i", strtotime($donation['approval_date'])); ?></td>
                            <td><?php echo htmlspecialchars($donation['transaction_id']); ?></td>
                             <td>
                                 <a href="<?php echo BASE_URL; ?>download_receipt.php?id=<?php echo $donation['id']; ?>" class="btn btn-sm btn-info" target="_blank">View Receipt</a>
                                 <!-- Add link to view donation details if needed -->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                 <tfoot>
                    <tr>
                        <th colspan="2" class="text-end">Total Filtered Income:</th>
                        <th>$<?php echo number_format($total_filtered_income, 2); ?></th>
                        <th colspan="4"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>

     <div class="mt-4">
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-secondary">Back to Admin Dashboard</a>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
