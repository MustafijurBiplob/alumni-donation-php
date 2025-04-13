<?php
        http_response_code(403); // Set the correct HTTP status code
        include 'includes/header.php';
        require_once 'includes/functions.php'; // For display_message
    ?>

    <div class="text-center">
        <h1 class="display-1">403</h1>
        <h2>Access Forbidden</h2>
        <?php display_message(); // Display message set by require_admin() or other checks ?>
        <p class="lead">Sorry, you do not have permission to access this page.</p>
        <hr>
        <p>If you believe this is an error, please contact the site administrator.</p>
         <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-primary">Go to Homepage</a>
         <?php if (isset($_SESSION['user_id'])): ?>
             <a href="<?php echo BASE_URL; ?><?php echo $_SESSION['user_role'] === 'admin' ? 'admin/dashboard.php' : 'dashboard.php'; ?>" class="btn btn-secondary">Go to Your Dashboard</a>
         <?php else: ?>
              <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-success">Login</a>
         <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
