<?php
    require_once __DIR__ . '/../includes/admin_auth.php';
    require_once __DIR__ . '/../includes/header.php';
    require_once __DIR__ . '/../includes/functions.php';

    $title = $amount = $expense_date = $notes = "";
    $errors = [];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Sanitize inputs
        $title = sanitize_input($_POST['title']);
        $amount = filter_var(sanitize_input($_POST['amount']), FILTER_VALIDATE_FLOAT);
        $expense_date = sanitize_input($_POST['expense_date']);
        $notes = sanitize_input($_POST['notes']); // Allow some basic text

        // Validate inputs
        if (empty($title)) {
            $errors['title'] = "Expense title is required.";
        }
        if ($amount === false || $amount <= 0) {
            $errors['amount'] = "Please enter a valid expense amount.";
        }
        if (empty($expense_date)) {
            $errors['expense_date'] = "Date of expense is required.";
        } elseif (!strtotime($expense_date)) {
             $errors['expense_date'] = "Invalid date format.";
        }

        // If no errors, insert into database
        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO expenses (title, amount, expense_date, notes) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sdss", $title, $amount, $expense_date, $notes);

            if ($stmt->execute()) {
                set_message("Expense added successfully!", "success");
                // Clear form fields after successful submission
                $title = $amount = $expense_date = $notes = "";
                // Redirect to expense report or stay on page? Redirecting is often better.
                redirect('admin/expense_report.php');
            } else {
                 set_message("Failed to add expense: " . $stmt->error, "danger");
            }
            $stmt->close();
        } else {
             set_message("Please correct the errors below.", "warning");
        }
    }
    ?>

    <h1>Add New Expense</h1>
    <p>Record operational costs or other expenditures.</p>
    <hr>

    <div class="expense-form">
        <?php display_message(); ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-3">
                <label for="title" class="form-label">Expense Title</label>
                <input type="text" class="form-control <?php echo isset($errors['title']) ? 'is-invalid' : ''; ?>" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
                 <?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?php echo $errors['title']; ?></div><?php endif; ?>
            </div>
             <div class="mb-3">
                <label for="amount" class="form-label">Amount ($)</label>
                <input type="number" step="0.01" class="form-control <?php echo isset($errors['amount']) ? 'is-invalid' : ''; ?>" id="amount" name="amount" value="<?php echo htmlspecialchars($amount); ?>" required>
                 <?php if (isset($errors['amount'])): ?><div class="invalid-feedback"><?php echo $errors['amount']; ?></div><?php endif; ?>
            </div>
             <div class="mb-3">
                <label for="expense_date" class="form-label">Date of Expense</label>
                <input type="date" class="form-control <?php echo isset($errors['expense_date']) ? 'is-invalid' : ''; ?>" id="expense_date" name="expense_date" value="<?php echo htmlspecialchars($expense_date); ?>" required>
                 <?php if (isset($errors['expense_date'])): ?><div class="invalid-feedback"><?php echo $errors['expense_date']; ?></div><?php endif; ?>
            </div>
             <div class="mb-3">
                <label for="notes" class="form-label">Notes (Optional)</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($notes); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100">Add Expense</button>
        </form>
    </div>

     <div class="mt-4 text-center">
        <a href="<?php echo BASE_URL; ?>admin/expense_report.php" class="btn btn-secondary">View Expense Report</a>
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-outline-secondary">Back to Admin Dashboard</a>
    </div>


    <?php include __DIR__ . '/../includes/footer.php'; ?>
