

<?php
    // Database Configuration
    define('DB_HOST', 'localhost'); // Replace with your host if different
    define('DB_USERNAME', 'root');    // Replace with your database username
    define('DB_PASSWORD', '');        // Replace with your database password
    define('DB_NAME', 'alumni_donation'); // Replace with your database name

    // Establish Database Connection
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Check Connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Start Session
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Base URL (Adjust if your project is in a subdirectory)
    define('BASE_URL', 'http://localhost/alumni-donation-app/'); // Adjust this path

    // Error Reporting (Disable in production)
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ?>
