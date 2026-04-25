<?php
// ============================================================
// Hospital Management System - Session / Auth Utilities
// ============================================================

session_start();

/**
 * Redirect to login if user is not authenticated.
 */
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: /frontend/pages/auth/login.html');
        exit;
    }
}

/**
 * Redirect to login if user does not have one of the allowed roles.
 */
function requireRole(array $roles): void {
    requireLogin();
    if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
        header('Location: /frontend/pages/auth/login.html');
        exit;
    }
}

/**
 * Return true when a user session is active.
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Store user data in the session after a successful login.
 */
function setSession(array $user): void {
    $_SESSION['user_id']  = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email']    = $user['email'];
    $_SESSION['role']     = $user['role'];
}

/**
 * Destroy the session (logout).
 */
function destroySession(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/**
 * Hash a plain-text password with SHA-256.
 */
function hashPassword(string $plain): string {
    return hash('sha256', $plain);
}

/**
 * Verify plain-text password against stored SHA-256 hash.
 */
function verifyPassword(string $plain, string $stored): bool {
    return hash('sha256', $plain) === $stored;
}

/**
 * Sanitize a string value coming from user input.
 */
function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}
