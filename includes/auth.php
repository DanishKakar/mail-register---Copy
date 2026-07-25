<?php
/**
 * Include this at the top of every page that requires a logged-in user.
 * Assumes config/app.php has already been required (session started).
 */

if (empty($_SESSION['user_id'])) {
    redirect(BASE_URL . 'login.php');
}

// Idle timeout: 30 minutes of inactivity logs the user out automatically.
const SESSION_IDLE_LIMIT = 1800;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_IDLE_LIMIT) {
    session_unset();
    session_destroy();
    redirect(BASE_URL . 'login.php?timeout=1');
}
$_SESSION['last_activity'] = time();

// Periodically rotate the session ID to mitigate fixation attacks.
if (empty($_SESSION['created_at'])) {
    $_SESSION['created_at'] = time();
} elseif (time() - $_SESSION['created_at'] > 900) {
    session_regenerate_id(true);
    $_SESSION['created_at'] = time();
}

$currentUser = [
    'id'        => $_SESSION['user_id'],
    'username'  => $_SESSION['username'],
    'full_name' => $_SESSION['full_name'],
    'role'      => $_SESSION['role'],
];

/** Call this on pages that only admins may access (e.g. user management). */
function require_admin(array $currentUser): void
{
    if ($currentUser['role'] !== 'admin') {
        http_response_code(403);
        die('تاسو د دې پاڼې لیدلو صلاحیت نلرئ.');
    }
}
