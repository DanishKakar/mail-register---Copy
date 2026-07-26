<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/auth.php';
require_admin($currentUser);

$activePage = 'users';
$pageTitle  = 'کاروونکي - ' . APP_NAME;
$errors = [];
$successMessage = flash_get('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $role = in_array($_POST['role'] ?? '', ['admin', 'editor', 'viewer'], true) ? $_POST['role'] : 'editor';
        $password = $_POST['password'] ?? '';

        if ($username === '' || $fullName === '' || strlen($password) < 8) {
            $errors[] = 'کارن نوم، بشپړ نوم او پټنوم (لږ تر لږه 8 توري) ضروري دي.';
        } else {
            $check = db()->prepare('SELECT id FROM users WHERE username = :u');
            $check->execute(['u' => $username]);
            if ($check->fetch()) {
                $errors[] = 'دا کارن نوم مخکې کارول شوی دی.';
            } else {
                db()->prepare('INSERT INTO users (username, password_hash, full_name, role) VALUES (:u, :p, :f, :r)')
                    ->execute([
                        'u' => $username,
                        'p' => password_hash($password, PASSWORD_DEFAULT),
                        'f' => $fullName,
                        'r' => $role,
                    ]);
                flash_set('success', 'نوی کاروونکی جوړ شو.');
                redirect('user.php');
            }
        }
    } elseif ($action === 'update') {
        $uid = (int)($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $role = in_array($_POST['role'] ?? '', ['admin', 'editor', 'viewer'], true) ? $_POST['role'] : 'editor';
        $password = trim($_POST['password'] ?? '');

        if ($uid <= 0 || $username === '' || $fullName === '') {
            $errors[] = 'د کاروونکي معلومات مو په سمه توګه ډکول لازمي دي.';
        } else {
            $check = db()->prepare('SELECT id FROM users WHERE username = :u AND id != :id');
            $check->execute(['u' => $username, 'id' => $uid]);
            if ($check->fetch()) {
                $errors[] = 'دا کارن نوم مخکې کارول شوی دی.';
            } else {
                if ($password !== '') {
                    if (strlen($password) < 8) {
                        $errors[] = 'نوی پټنوم باید لږ تر لږه 8 توري ولري.';
                    } else {
                        db()->prepare('UPDATE users SET username = :u, full_name = :f, role = :r, password_hash = :p WHERE id = :id')->execute([
                            'u' => $username,
                            'f' => $fullName,
                            'r' => $role,
                            'p' => password_hash($password, PASSWORD_DEFAULT),
                            'id' => $uid,
                        ]);
                    }
                } else {
                    db()->prepare('UPDATE users SET username = :u, full_name = :f, role = :r WHERE id = :id')->execute([
                        'u' => $username,
                        'f' => $fullName,
                        'r' => $role,
                        'id' => $uid,
                    ]);
                }

                if (!$errors) {
                    flash_set('success', 'کاروونکی سم شو.');
                    redirect('user.php');
                }
            }
        }
    } elseif ($action === 'delete') {
        $uid = (int)($_POST['id'] ?? 0);
        if ($uid > 0 && (int)$uid !== (int)$currentUser['id']) {
            db()->prepare('DELETE FROM users WHERE id = :id')->execute(['id' => $uid]);
            flash_set('success', 'کاروونکی حذف شو.');
        }
        redirect('user.php');
    } elseif ($action === 'toggle') {
        $uid = (int)($_POST['id'] ?? 0);
        if ($uid > 0 && (int)$uid !== (int)$currentUser['id']) {
            db()->prepare('UPDATE users SET is_active = 1 - is_active WHERE id = :id')->execute(['id' => $uid]);
        }
        redirect('user.php');
    }
}

$users = db()->query('SELECT * FROM users ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>د کاروونکو مدیریت</h1>
    <div class="row-actions">
        <button class="btn btn-primary" type="button" onclick="openModal('create-user-modal')">+ نوی کاروونکی</button>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($successMessage): ?>
    <div class="alert alert-success"><?= e($successMessage) ?></div>
<?php endif; ?>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
            <tr>
                <th>کارن نوم</th>
                <th>بشپړ نوم</th>
                <th>رول</th>
                <th>حالت</th>
                <th>وروستی ننوتل</th>
                <th>کړنې</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= e($u['username']) ?></td>
                    <td><?= e($u['full_name']) ?></td>
                    <td><?= e($u['role']) ?></td>
                    <td><span class="badge <?= $u['is_active'] ? 'badge-yes' : 'badge-no' ?>"><?= $u['is_active'] ? 'فعال' : 'غیرفعال' ?></span></td>
                    <td><?= e($u['last_login_at'] ?? '—') ?></td>
                    <td class="row-actions">
                        <button class="btn btn-secondary btn-sm" type="button"
                            data-id="<?= (int)$u['id'] ?>"
                            data-username="<?= e($u['username']) ?>"
                            data-full-name="<?= e($u['full_name']) ?>"
                            data-role="<?= e($u['role']) ?>"
                            onclick="openEditModal(this)">سمون</button>

                        <?php if ((int)$u['id'] !== (int)$currentUser['id']): ?>
                            <form method="post" style="display:inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                <button class="btn btn-secondary btn-sm" type="submit">
                                    <?= $u['is_active'] ? 'غیرفعالول' : 'فعالول' ?>
                                </button>
                            </form>

                            <button class="btn btn-danger btn-sm" type="button"
                                data-id="<?= (int)$u['id'] ?>"
                                data-name="<?= e($u['full_name']) ?>"
                                onclick="openDeleteModal(this)">حذف</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="create-user-modal" class="modal-backdrop" role="dialog" aria-modal="true" aria-label="Create user">
    <div class="modal">
        <div class="modal-header">
            <h3>نوی کاروونکی</h3>
            <button type="button" class="modal-close" onclick="closeModal('create-user-modal')">×</button>
        </div>
        <div class="modal-body">
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create">
                <div class="form-grid">
                    <div>
                        <label>کارن نوم</label>
                        <input type="text" name="username" required>
                    </div>
                    <div>
                        <label>بشپړ نوم</label>
                        <input type="text" name="full_name" required>
                    </div>
                    <div>
                        <label>رول</label>
                        <select name="role">
                            <option value="editor">Editor</option>
                            <option value="viewer">Viewer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label>پټنوم</label>
                        <input type="password" name="password" required minlength="8">
                    </div>
                </div>
                <div class="modal-actions">
                    <button class="btn btn-primary" type="submit">جوړول</button>
                    <button class="btn btn-secondary" type="button" onclick="closeModal('create-user-modal')">لغوه</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="edit-user-modal" class="modal-backdrop" role="dialog" aria-modal="true" aria-label="Edit user">
    <div class="modal">
        <div class="modal-header">
            <h3>کاروونکی سمول</h3>
            <button type="button" class="modal-close" onclick="closeModal('edit-user-modal')">×</button>
        </div>
        <div class="modal-body">
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="edit-user-id" name="id" value="0">
                <div class="form-grid">
                    <div>
                        <label>کارن نوم</label>
                        <input type="text" id="edit-username" name="username" required>
                    </div>
                    <div>
                        <label>بشپړ نوم</label>
                        <input type="text" id="edit-full-name" name="full_name" required>
                    </div>
                    <div>
                        <label>رول</label>
                        <select id="edit-role" name="role">
                            <option value="editor">Editor</option>
                            <option value="viewer">Viewer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label>نوی پټنوم (اختیاري)</label>
                        <input type="password" name="password" minlength="8">
                    </div>
                </div>
                <div class="modal-actions">
                    <button class="btn btn-primary" type="submit">خوندي کول</button>
                    <button class="btn btn-secondary" type="button" onclick="closeModal('edit-user-modal')">لغوه</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="delete-user-modal" class="modal-backdrop" role="dialog" aria-modal="true" aria-label="Delete user">
    <div class="modal modal-sm">
        <div class="modal-header">
            <h3>حذف کاروونکی</h3>
            <button type="button" class="modal-close" onclick="closeModal('delete-user-modal')">×</button>
        </div>
        <div class="modal-body">
            <p>ایا تاسو ډاډه یاست چې کاروونکی <strong id="delete-user-name"></strong> حذف کړئ؟</p>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="delete-user-id" name="id" value="0">
                <div class="modal-actions">
                    <button class="btn btn-danger" type="submit">حذف</button>
                    <button class="btn btn-secondary" type="button" onclick="closeModal('delete-user-modal')">لغوه</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.58);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    z-index: 1200;
}
.modal-backdrop.active {
    display: flex;
}
.modal {
    background: #fff;
    border-radius: 16px;
    width: min(560px, 100%);
    max-height: 90vh;
    overflow: auto;
    box-shadow: 0 20px 45px rgba(0,0,0,0.2);
}
.modal-sm {
    width: min(420px, 100%);
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
}
.modal-close {
    border: 0;
    background: none;
    font-size: 24px;
    cursor: pointer;
    color: #6b7280;
}
.modal-body {
    padding: 20px;
}
.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 16px;
}
body.modal-open {
    overflow: hidden;
}
</style>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('active');
    document.body.classList.add('modal-open');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
    if (!document.querySelector('.modal-backdrop.active')) {
        document.body.classList.remove('modal-open');
    }
}

function openEditModal(button) {
    document.getElementById('edit-user-id').value = button.getAttribute('data-id');
    document.getElementById('edit-username').value = button.getAttribute('data-username');
    document.getElementById('edit-full-name').value = button.getAttribute('data-full-name');
    document.getElementById('edit-role').value = button.getAttribute('data-role');
    openModal('edit-user-modal');
}

function openDeleteModal(button) {
    document.getElementById('delete-user-id').value = button.getAttribute('data-id');
    document.getElementById('delete-user-name').textContent = button.getAttribute('data-name');
    openModal('delete-user-modal');
}

document.addEventListener('click', function (event) {
    if (event.target.classList.contains('modal-backdrop')) {
        closeModal(event.target.id);
    }
});
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.modal-backdrop.active').forEach(function (modal) {
            modal.classList.remove('active');
        });
        document.body.classList.remove('modal-open');
    }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
