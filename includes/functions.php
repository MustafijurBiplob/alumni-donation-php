<?php
    // Ensure config is included
    require_once __DIR__ . '/../config.php';

    // Function to redirect
    function redirect($url) {
        header("Location: " . BASE_URL . $url);
        exit();
    }

    // Function to check if user is logged in
    function is_logged_in() {
        return isset($_SESSION['user_id']);
    }

    // Function to check if user is admin
    function is_admin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    // Function to sanitize input data
    function sanitize_input($data) {
        global $conn; // Need the database connection for escaping
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = $conn->real_escape_string($data); // Escape for SQL
        return $data;
    }

    // Function to display messages (e.g., success, error)
    function display_message() {
        if (isset($_SESSION['message'])) {
            $type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info'; // success, danger, warning, info
            echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
            echo $_SESSION['message'];
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
            // Clear the message after displaying
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
    }

    // Function to set messages
    function set_message($msg, $type = 'info') {
        $_SESSION['message'] = $msg;
        $_SESSION['message_type'] = $type;
    }

    // Function to hash password
    function hash_password($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // Function to verify password
    function verify_password($password, $hash) {
        return password_verify($password, $hash);
    }

    // --- Authentication Check Functions ---

    function require_login() {
        if (!is_logged_in()) {
            set_message("You must be logged in to access this page.", "warning");
            redirect('login.php');
        }
    }

    function require_admin() {
        if (!is_admin()) {
             set_message("You do not have permission to access this page.", "danger");
             redirect('403.php'); // Redirect to unauthorized page
        }
    }

    // --- Data Fetching Functions (Examples) ---

    function get_user_by_id($user_id) {
        global $conn;
        $stmt = $conn->prepare("SELECT id, name, email, mobile, address, role FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    function get_total_donations($user_id = null) {
        global $conn;
        $sql = "SELECT SUM(amount) as total FROM donations WHERE status = 'Approved'";
        if ($user_id !== null) {
            $sql .= " AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
        } else {
            $stmt = $conn->prepare($sql);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['total'] ?? 0.00;
    }

     function get_total_expenses() {
        global $conn;
        $sql = "SELECT SUM(amount) as total FROM expenses";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['total'] ?? 0.00;
    }


    ?>
