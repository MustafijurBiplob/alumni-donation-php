<?php
    require_once __DIR__ . '/../includes/admin_auth.php';
    require_once __DIR__ . '/../includes/header.php';
    require_once __DIR__ . '/../includes/functions.php';

    // --- Date Range Filtering (Optional but good for balance sheets) ---
    $filter_start_date = isset($_GET['start_date']) ? sanitize_input($_GET['start_date']) : null;
    $filter_end_date = isset($_GET['end_date']) ? sanitize_input($_GET['end_date']) : null;

    // --- Calculate Income within the date range ---
    $sql_income = "SELECT SUM(amount) as total FROM donations WHERE status = 'Approved'";
    $params_income = [];
    $types_income = "";
    if ($filter_start_date) {
        $sql_income .= " AND updated_at >= ?"; // Use approval date (updated_at) for income period
        $params_income[] = $filter_start_date . " 00:00:00";
        $types_income .= "s";
    }
    if ($filter_end_date) {
        $sql_income .= " AND updated_at <= ?";
        $params_income[] = $filter_end_date . " 23:59:59";
        $types_income .= "s";
    }
    $stmt_income = $conn->prepare($sql_income);
    if (!empty($params_income)) {
        $stmt_income->bind_param($types_income, ...$params_income);
    }
    $stmt_income->execute();
    $result_income = $stmt_income->get_result()->fetch_assoc();
    $total_income_period = $result_income['total'] ?? 0.00;
    $stmt_income->close();

    // --- Calculate Expenses within the date range ---
    $sql_expense = "SELECT SUM(amount) as total FROM expenses WHERE 1=1";
    $params_expense = [];
    $types_expense = "";
     if ($filter_start_date) {
        $sql_expense .= " AND expense_date >= ?";
        $params_expense[] = $filter_start_date;
        $types_expense .= "s";
    }
    if ($filter_end_date) {
        $sql_expense .= " AND expense_date <= ?";
        $params_expense[] = $filter_end_date;
        $types_expense .= "s";
    }
    $stmt_expense = $conn->prepare($sql_expense);
     if (!empty($params_expense)) {
        $stmt_expense->bind_param($types_expense, ...$params_expense);
    }
    $stmt_expense->execute();
    $result_expense = $stmt_expense->get_result()->fetch_assoc();
    $total_expense_period = $result_expense['total'] ?? 0.00;
    $stmt_expense->close();

    // --- Calculate Overall Balance (regardless of date filter) ---
    $overall_income = get_total_donations();
    $overall_expense = get_total_expenses();
    $overall_balance = $overall_income - $overall_expense;

    // Calculate Balance for the selected period
    $period_balance = $total_income_period - $total_expense_period;

    $period_label = "Overall";
    if ($filter_start_date || $filter_end_date) {
        $start = $filter_start_date ? date("M j, Y", strtotime($filter_start_date)) : "Beginning";
        $end = $filter_end_date ? date("M j, Y", strtotime($filter_end_date)) : "Today";
        $period_label = "Period: {$start} - {$end}";
    }

    ?>

    <h1>Total Balance Sheet</h1>
    <?php display_message(); ?>
    <hr>

     <!-- Filter Form -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="row g-3 align-items-end bg-light p-3 rounded mb-4">
        <div class="col-md-4">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo htmlspecialchars($filter_start_date ?? ''); ?>">
            <small class="form-text text-muted">Filters income by approval date, expenses by expense date.</small>
        </div>
        <div class="col-md-4">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo htmlspecialchars($filter_end_date ?? ''); ?>">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary me-2">Filter Period</button>
            <a href="<?php echo BASE_URL; ?>admin/balance_sheet.php" class="btn btn-secondary">Show Overall</a>
        </div>
    </form>

    <h2><?php echo $period_label; ?> Summary</h2>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-header bg-success text-white">Income (Period)</div>
                <div class="card-body text-success">
                    <h5 class="card-title">$<?php echo number_format($total_income_period, 2); ?></h5>
                    <p class="card-text">Total approved donations within the selected period.</p>
                </div>
            </div>
        </div>
         <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">Expenses (Period)</div>
                <div class="card-body text-danger">
                    <h5 class="card-title">$<?php echo number_format($total_expense_period, 2); ?></h5>
                    <p class="card-text">Total expenses recorded within the selected period.</p>
                </div>
            </div>
        </div>
         <div class="col-md-4">
            <div class="card <?php echo ($period_balance >= 0) ? 'border-primary' : 'border-warning'; ?>">
                <div class="card-header <?php echo ($period_balance >= 0) ? 'bg-primary' : 'bg-warning'; ?> text-white">Net Balance (Period)</div>
                <div class="card-body <?php echo ($period_balance >= 0) ? 'text-primary' : 'text-warning'; ?>">
                    <h5 class="card-title">$<?php echo number_format($period_balance, 2); ?></h5>
                    <p class="card-text">Income minus expenses for the selected period.</p>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <h2>Overall Financial Status</h2>
     <table class="table table-bordered" style="max-width: 600px;">
        <tbody>
            <tr class="table-success">
                <td>Total Lifetime Income (Approved Donations)</td>
                <td class="text-end fw-bold">$<?php echo number_format($overall_income, 2); ?></td>
            </tr>
            <tr class="table-danger">
                <td>Total Lifetime Expenses</td>
                <td class="text-end fw-bold">($<?php echo number_format($overall_expense, 2); ?>)</td>
            </tr>
            <tr class="table-info">
                <th >Overall Current Balance</th>
                <th class="text-end">$<?php echo number_format($overall_balance, 2); ?></th>
            </tr>
        </tbody>
    </table>


     <div class="mt-4">
        <a href="<?php echo BASE_URL; ?>admin/income_report.php" class="btn btn-outline-success">View Income Details</a>
        <a href="<?php echo BASE_URL; ?>admin/expense_report.php" class="btn btn-outline-danger">View Expense Details</a>
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-secondary">Back to Admin Dashboard</a>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
