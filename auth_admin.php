<?php
session_start();

// Temporary bypass for admin access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    // Set admin session manually for recovery
    $_SESSION['user_id'] = 1; // Example user ID
    $_SESSION['role'] = 'Admin';
    echo "Temporary admin session created. Please reset credentials!";
} else if ($_SESSION['role'] !== 'Admin') {
    // Redirect to the login page if not an admin
    header('Location: login.php');
    exit;
}
?>
