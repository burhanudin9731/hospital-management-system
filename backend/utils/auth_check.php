<?php
// ============================================================
// Hospital Management System - Auth Utilities
// backend/utils/auth_check.php
// ============================================================

// Safe session start — never double-starts even if the calling
// file already called session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function loginUrl(): string {
    $dir   = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $parts = explode('/', rtrim($dir, '/'));
    $root  = implode('/', array_slice($parts, 0, count($parts) - 2));
    return $root . '/frontend/pages/auth/login.html';
}

// Detects whether the request came from fetch() / AJAX
// so we return JSON instead of an HTML redirect
function isAjax(): bool {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $xreq   = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    return str_contains($accept, 'application/json')
        || strtolower($xreq) === 'xmlhttprequest';
}

function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        if (isAjax()) {
            // fetch() call — return JSON 401 so JS can handle it
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Session expired. Please log in again.',
                'data'    => []
            ]);
        } else {
            // Normal browser request — redirect to login page
            header('Location: ' . loginUrl());
        }
        exit;
    }
}

function requireRole(array $roles): void {
    requireLogin();
    if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied.', 'data' => []]);
        exit;
    }
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function setSession(array $user): void {
    $_SESSION['user_id']  = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email']    = $user['email'];
    $_SESSION['role']     = $user['role'];
}

function destroySession(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function hashPassword(string $plain): string {
    return hash('sha256', $plain);
}

function verifyPassword(string $plain, string $stored): bool {
    return hash('sha256', $plain) === $stored;
}

function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}
