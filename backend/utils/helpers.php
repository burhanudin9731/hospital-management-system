<?php
// ============================================================
// Hospital Management System - Helper Utilities
// backend/utils/helpers.php
// ============================================================

/**
 * Send a JSON response and exit
 */
function jsonResponse(bool $success, string $message, array $extra = [], int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

/**
 * Validate required POST fields — returns array of missing field names
 */
function requireFields(array $fields): array {
    $missing = [];
    foreach ($fields as $field) {
        if (empty($_POST[$field]) && $_POST[$field] !== '0') {
            $missing[] = $field;
        }
    }
    return $missing;
}

/**
 * Sanitize a string value
 */
function clean(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

/**
 * Return today's date as Y-m-d
 */
function today(): string {
    return date('Y-m-d');
}

/**
 * Format a MySQL datetime to a readable string
 */
function fmtDate(?string $date, string $format = 'd M Y'): string {
    if (!$date) return '—';
    return date($format, strtotime($date));
}

/**
 * Calculate age from date of birth string
 */
function calcAge(string $dob): int {
    return (int) date_diff(date_create($dob), date_create('today'))->y;
}

/**
 * Paginate a query — returns [offset, limit, page, totalPages]
 */
function paginate(int $total, int $page = 1, int $perPage = 20): array {
    $page       = max(1, $page);
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page       = min($page, $totalPages);
    $offset     = ($page - 1) * $perPage;
    return compact('offset', 'perPage', 'page', 'totalPages');
}
