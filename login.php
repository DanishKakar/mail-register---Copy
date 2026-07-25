<?php
require_once __DIR__ . '/config/app.php';

// Already logged in? go to dashboard.
if (!empty($_SESSION['user_id'])) {
    redirect(BASE_URL . 'index.php');
}

$error   = '';
$timeout = isset($_GET['timeout']);

const MAX_ATTEMPTS   = 5;
const LOCKOUT_SECONDS = 300; // 5 minutes

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $ip       = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    // ---- Brute-force throttling based on recent failed attempts from this IP ----
    $stmt = db()->prepare(
        'SELECT COUNT(*) AS attempts FROM login_logs
         WHERE ip_address = :ip AND success = 0
           AND created_at > (NOW() - INTERVAL :secs SECOND)'
    );
    $stmt->execute(['ip' => $ip, 'secs' => LOCKOUT_SECONDS]);
    $recentFails = (int) $stmt->fetch()['attempts'];

    if ($recentFails >= MAX_ATTEMPTS) {
        $error = 'ډیرې ناسمې هڅې وشوې. مهرباني وکړئ ' . ceil(LOCKOUT_SECONDS / 60) . ' دقیقې وروسته بیا هڅه وکړئ.';
    } elseif ($username === '' || $password === '') {
        $error = 'مهرباني وکړئ کارن نوم او پټنوم دواړه ولیکئ.';
    } else {
        $stmt = db()->prepare('SELECT * FROM users WHERE username = :u AND is_active = 1 LIMIT 1');
        $stmt->execute(['u' => $username]);
        $user = $stmt->fetch();

        $ok = $user && password_verify($password, $user['password_hash']);

        // Log every attempt (success or failure) for audit purposes.
        $log = db()->prepare(
            'INSERT INTO login_logs (user_id, username, ip_address, success) VALUES (:uid, :un, :ip, :ok)'
        );
        $log->execute([
            'uid' => $user['id'] ?? null,
            'un'  => $username,
            'ip'  => $ip,
            'ok'  => $ok ? 1 : 0,
        ]);

        if ($ok) {
            session_regenerate_id(true);
            $_SESSION['user_id']       = $user['id'];
            $_SESSION['username']      = $user['username'];
            $_SESSION['full_name']     = $user['full_name'];
            $_SESSION['role']          = $user['role'];
            $_SESSION['last_activity'] = time();
            $_SESSION['created_at']    = time();

            db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id')
                ->execute(['id' => $user['id']]);

            redirect(BASE_URL . 'index.php');
        } else {
            $error = 'کارن نوم یا پټنوم ناسم دی.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ps" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ننوتل - <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-box">
        <div class="login-brand">
            <div class="login-logo">📖</div>
            <h1><?= e(APP_NAME) ?></h1>
            <p class="subtitle">د ډیتابس امریت</p>
        </div>

        <?php if ($timeout): ?>
            <div class="alert alert-warning">ستاسو ناستې د بې فعالیت له امله پای ته ورسیده. بیا ننوځئ.</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="off" novalidate>
            <?= csrf_field() ?>
            <label for="username">کارن نوم</label>
            <input type="text" id="username" name="username" required autofocus value="<?= e($_POST['username'] ?? '') ?>">

            <label for="password">پټنوم</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="btn btn-primary btn-block">ننوتل</button>
        </form>
        <p class="login-footer">Database Directorate &copy; <?= date('Y') ?></p>
    </div>
</body>
</html>
