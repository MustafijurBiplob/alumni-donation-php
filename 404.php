<?php
        http_response_code(404); // Set the correct HTTP status code
        include 'includes/header.php';
    ?>

    <div class="text-center">
        <h1 class="display-1">404</h1>
        <h2>Page Not Found</h2>
        <p class="lead">Sorry, the page you are looking for does not exist.</p>
        <hr>
        <p>You might have mistyped the address or the page may have moved.</p>
        <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-primary">Go to Homepage</a>
        <?php if (isset($_SESSION['user_id'])): ?>
             <a href="<?php echo BASE_URL; ?><?php echo $_SESSION['user_role'] === 'admin' ? 'admin/dashboard.php' : 'dashboard.php'; ?>" class="btn btn-secondary">Go to Dashboard</a>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
