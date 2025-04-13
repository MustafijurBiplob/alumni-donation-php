<?php
    require_once __DIR__ . '/../includes/admin_auth.php';
    require_once __DIR__ . '/../includes/header.php';
    require_once __DIR__ . '/../includes/functions.php';

    // --- Filtering Logic ---
    $filter_start_date = isset($_GET['start_date']) ? sanitize_input($_GET['start_date']) : null;
    $filter_end_date = isset($_GET['end_date']) ? sanitize_input($_GET['end_date']) : null;

    // Base SQL query
    $sql = "SELECT id, title, amount, expense_date, notes, created_at FROM expenses WHERE 1=1"; // Start with WHERE 1=1 for easy appending

    $params = [];
    $types = "";

    // Append filters to SQL query
    if ($filter_start_date) {
        $sql .= " AND expense_date >= ?";
        $params[] = $filter_start_date;
        $types .= "s";
    }
    if ($filter_end_date) {
        // Add 1 day to end date to include the whole day
        $end_date_inclusive = date('Y-m-d', strtotime($filter_end_date . ' +1 day'));
        $sql .= " AND expense_date < ?";
        $params[] = $end_date_inclusive;
        $types .= "s";
    }

    $sql .= " ORDER BY expense_date DESC, created_at DESC";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $expenses = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Calculate total expense for the filtered results
    $total_filtered_expense = 0;
    foreach ($expenses as $expense) {
        $total_filtered_expense += $expense['amount'];
    }

    ?>

    <h1>Expense Report</h1>
    <?php display_message(); ?>
    <hr>

    <!-- Filter Form -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="row g-3 align-items-end bg-light p-3 rounded mb-4">
        <div class="col-md-4">
            <label for="start_date" class="form-label">Start Date (Expense)</label>
            <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo htmlspecialchars($filter_start_date ?? ''); ?>">
        </div>
        <div class="col-md-4">
            <label for="end_date" class="form-label">End Date (Expense)</label>
            <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo htmlspecialchars($filter_end_date ?? ''); ?>">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary me-2">Filter</button>
            <a href="<?php echo BASE_URL; ?>admin/expense_report.php" class="btn btn-secondary">Clear</a>
        </div>
    </form>

    <!-- Summary -->
     <div class="alert alert-danger" role="alert">
        <strong>Total Expenses (Filtered): $<?php echo number_format($total_filtered_expense, 2); ?></strong>
        (Based on <?php echo count($expenses); ?> expense entries matching the criteria)
    </div>


    <!-- Expenses Table -->
    <?php if (empty($expenses)): ?>
        <div class="alert alert-warning" role="alert">
          No expenses found matching the selected filters.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Amount ($)</th>
                        <th>Expense Date</th>
                        <th>Notes</th>
                        <th>Recorded At</th>
                        <th>Action</th> <!-- Placeholder for Edit/Delete -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expenses as $expense): ?>
                        <tr>
                            <td><?php echo $expense['id']; ?></td>
                            <td><?php echo htmlspecialchars($expense['title']); ?></td>
                            <td><?php echo number_format($expense['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($expense['expense_date']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($expense['notes'])); ?></td>
                            <td><?php echo date("Y-m-d H:i", strtotime($expense['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning me-1" disabled title="Edit (Not Implemented)">Edit</button>
                                <button class="btn btn-sm btn-danger" disabled title="Delete (Not Implemented)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                 <tfoot>
                    <tr>
                        <th colspan="2" class="text-end">Total Filtered Expense:</th>
                        <th>$<?php echo number_format($total_filtered_expense, 2); ?></th>
                        <th colspan="4"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>

     <div class="mt-4">
        <a href="<?php echo BASE_URL; ?>admin/add_expense.php" class="btn btn-success">Add New Expense</a>
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-secondary">Back to Admin Dashboard</a>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
