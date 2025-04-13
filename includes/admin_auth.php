<?php
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/functions.php';

    require_login(); // First, ensure user is logged in
    require_admin(); // Then, ensure user is an admin
    ?>
