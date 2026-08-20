<?php
// includes/auth.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if the user is currently logged in.
 * @return bool True if logged in, false otherwise.
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get the currently logged in user's ID.
 * @return int|null User ID or null if not logged in.
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Require a user to be logged in to access the page.
 * If not logged in, redirects to login.php and exits.
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
?>
