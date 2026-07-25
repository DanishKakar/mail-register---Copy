<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/auth.php';
require_admin($currentUser);

$activePage = 'users';
$pageTitle  = 'کاروونکي - ' . APP_NAME;
$errors = [];

// ---- Handle create / update / delete ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $username  = trim($_POST['username'] ?? '');
        $fullName  = trim($_POST['full_name'] ?? '');
        $role      = in_array($_POST['role'] ?? '', ['admin','editor','viewer'], true) ? $_POST['role'] : 'editor';
        $password  = $_POST['password'] ?? '';

        if ($username === '' || $fullName === '' || strlen($password) < 8) {
            $errors[] = 'کارن نوم، بشپړ نوم او پټنوم (لږ تر لږه 8 توري) ضروري دي.';
        } else {
            $check = db()->prepare('SELECT id FROM users WHERE username = :u');
            $check->execute(['u' => $username]);
            if ($check->fetch()) {
                $errors[] = 'دا کارن نوم مخکې کارول شوی دی.';
            } else {
                $stmt = db()->prepare('INSERT INTO users (username, password_hash, full_name, role) VALUES (:u, :p, :f, :r)');
                $stmt->execute([
                    'u' => $username,
                    'p' => password_hash($password, PASSWORD_DEFAULT),
                    'f' => $fullName,
                    'r' => $role,
                ]);
                flash_set('success', 'نوی کاروونکی جوړ شو.');
                redirect('users.php');
            }
        }
    } elseif ($action === 'toggle') {
        $uid = (int)($_POST['id'] ?? 0);
        if ($uid !== (int)$currentUser['id']) {
            db()->prepare('UPDATE users SET is_active = 1 - is_active WHERE id = :id')->execute(['id' => $uid]);
        }
        redirect('users.php');
    } elseif ($action === 'reset_password') {
        $uid = (int)($_POST['id'] ?? 0);
        $newPass = $_POST['new_password'] ?? '';
        if (strlen($newPass) < 8) {
            $errors[] = 'نوی پټنوم باید لږ تر لږه 8 توري ولري.';
        } else {
            db()->prepare('UPDATE users SET password_hash = :p WHERE id = :id')
                ->execute(['p' => password_hash($newPass, PASSWORD_DEFAULT), 'id' => $uid]);
            flash_set('success', 'پټنوم بدل شو.');
            redirect('users.php');
        }
    }
}

$users = db()->query('SELECT * FROM users ORDER BY id ASC')->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<div class="page-header"><h1>د کاروونکو مدیریت</h1></div>

<?php if ($errors): ?>
    <div class="alert alert-error"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<div class="card">
    <h3 style="margin-top:0">نوی کاروونکی اضافه کول</h3>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="form-grid">
            <div><label>کارن نوم</label><input type="text" name="username" required></div>
            <div><label>بشپړ نوم</label><input type="text" name="full_name" required></div>
            <div><label>رول</label>
                <select name="role">
                    <option value="editor">Editor (ثبت/سمون)</option>
                    <option value="viewer">Viewer (یوازې کتنه)</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div><label>پټنوم</label><input type="password" name="password" required minlength="8"></div>
        </div>
        <button class="btn btn-primary" style="margin-top:16px" type="submit">جوړول</button>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0">شتون لرونکي کاروونکي</h3>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>کارن نوم</th><th>بشپړ نوم</th><th>رول</th><th>حالت</th><th>وروستی ننوتل</th><th>کړنې</th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= e($u['username']) ?></td>
                    <td><?= e($u['full_name']) ?></td>
                    <td><?= e($u['role']) ?></td>
                    <td><span class="badge <?= $u['is_active'] ? 'badge-yes' : 'badge-no' ?>"><?= $u['is_active'] ? 'فعال' : 'غیرفعال' ?></span></td>
                    <td><?= e($u['last_login_at'] ?? '—') ?></td>
                    <td class="row-actions">
                        <?php if ((int)$u['id'] !== (int)$currentUser['id']): ?>
                        <form method="post" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                            <button class="btn btn-secondary btn-sm" type="submit"><?= $u['is_active'] ? 'غیرفعالول' : 'فعالول' ?></button>
                        </form>
                        <?php endif; ?>
                        <button class="btn btn-secondary btn-sm" type="button"
                                onclick="document.getElementById('reset-<?= (int)$u['id'] ?>').classList.toggle('hidden-form')">
                            پټنوم بدلول
                        </button>
                    </td>
                </tr>
                <tr id="reset-<?= (int)$u['id'] ?>" class="hidden-form">
                    <td colspan="6">
                        <form method="post" style="display:flex; gap:8px; align-items:center;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="reset_password">
                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                            <input type="password" name="new_password" placeholder="نوی پټنوم (لږ تر لږه 8 توري)" minlength="8" required style="max-width:260px">
                            <button class="btn btn-primary btn-sm" type="submit">خوندي کول</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>.hidden-form{display:none}</style>

<?php require __DIR__ . '/includes/footer.php'; ?>
