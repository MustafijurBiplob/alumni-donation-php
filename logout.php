<?php
    require_once 'config.php'; // Ensures session_start() is called
    require_once 'includes/functions.php';

    // Unset all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destroy the session
    session_destroy();

    // Set a logout message (optional)
    // Note: Since the session is destroyed, we can't use the session message system directly here.
    // A query parameter could be used, but redirecting without a message is cleaner.

    // Redirect to login page
    redirect('login.php?logged_out=1'); // Adding a param is optional
    exit;
    ?>
