<?php
// ============================================================
// Hospital Management System - Logout Handler
// backend/auth/logout.php
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();

// Clear all session data
$_SESSION = [];

// Destroy the session cookie
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

// Return JSON for AJAX or redirect for direct access
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
} else {
    // Direct browser visit — redirect to login
    $parts = explode('/', rtrim(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME'])), '/'));
    $root  = implode('/', array_slice($parts, 0, count($parts) - 2));
    header('Location: ' . $root . '/frontend/pages/auth/login.html');
}
exit;
