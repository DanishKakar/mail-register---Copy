<?php
/**
 * Core app bootstrap: secure session configuration + shared helpers.
 * Every protected page includes this file first.
 */

// ---- Secure session settings (must run before session_start) ----------
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
// If you serve the site over HTTPS (recommended in production) uncomment:
// ini_set('session.cookie_secure', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_name('mrsid');
    session_start();
}

date_default_timezone_set('Asia/Kabul');

require_once __DIR__ . '/db.php';

define('APP_NAME', 'د صادره او وارده مکتوبونو ثبت سیستم');
define('BASE_URL', '/'); // change if the app lives in a sub-folder

// ---- Small helpers ------------------------------------------------------

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_verify(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('د غلطي غوښتنه رد شوه (CSRF). مهرباني وکړئ فورمه بیا پرانیزئ.');
    }
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function flash_set(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function flash_get(string $key): ?string
{
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}
