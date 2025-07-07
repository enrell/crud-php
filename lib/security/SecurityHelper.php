<?php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security helper functions
function sanitizeInput(string $input): string {
    return trim($input);
}

function isAuthenticated(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireAuth(): void {
    if (!isAuthenticated()) {
        header('Location: /views/login.php');
        exit();
    }
}

?>
