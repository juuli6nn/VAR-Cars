<?php
require_once '../../includes/admin-guard.php';
require_once '../../includes/data.php';

$errors = array();
$values = array(
    'full_name' => '',
    'email'     => '',
    'address'   => '',
    'contact'   => '+63 ',
);

// ── Handle actions ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // Toggle a user's admin rights
    if ($action == 'toggle_admin') {
        $userId = (int) (isset($_POST['user_id']) ? $_POST['user_id'] : 0);

        $stmt = mysqli_prepare($conn, "SELECT email, is_admin FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $target = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ($target) {
            $newFlag = $target['is_admin'] ? 0 : 1;
            $stmt = mysqli_prepare($conn, "UPDATE users SET is_admin = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'ii', $newFlag, $userId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            log_activity($conn, $newFlag ? 'grant_admin' : 'revoke_admin', $target['email']);
            $_SESSION['flash']      = $target['email'] . ($newFlag ? ' is now an admin.' : ' is no longer an admin.');
            $_SESSION['flash_type'] = 'success';
        }
        header('Location: users.php');
        exit;
    }

    // Add a brand-new user
    if ($action == 'add_user') {
        $values['full_name'] = trim(isset($_POST['full_name']) ? $_POST['full_name'] : '');
        $values['email']     = trim(isset($_POST['email'])     ? $_POST['email']     : '');
        $values['address']   = trim(isset($_POST['address'])   ? $_POST['address']   : '');
        $values['contact']   = trim(isset($_POST['contact'])   ? $_POST['contact']   : '');
        $password            = isset($_POST['password']) ? $_POST['password'] : '';
        $makeAdmin           = isset($_POST['is_admin']) ? 1 : 0;

        // same rules as public registration
        if ($values['full_name'] == '' || strlen($values['full_name']) < 2) {
            $errors['full_name'] = 'Enter the full name (at least 2 characters).';
        }
        if ($values['email'] == '') {
            $errors['email'] = 'Email address is required.';
        } elseif (!preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $values['email'])) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        if ($password == '') {
            $errors['password'] = 'Password is required.';
        } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
            $errors['password'] = 'At least 8 characters, one uppercase letter, and one number.';
        }
        if ($values['address'] == '') {
            $errors['address'] = 'Address is required.';
        }
        if ($values['contact'] == '' || !preg_match('/^[\d\s\+\-\(\)]{10,20}$/', $values['contact'])) {
            $errors['contact'] = 'Enter a valid contact number.';
        }

        if (empty($errors)) {
            $hashed = md5($password);
            // admin-created accounts are verified immediately (is_verified = 1)
            $sql  = "INSERT INTO users (full_name, email, password, address, contact, is_verified, is_admin)
                     VALUES (?, ?, ?, ?, ?, 1, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'sssssi',
                $values['full_name'],
                $values['email'],
                $hashed,
                $values['address'],
                $values['contact'],
                $makeAdmin
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                log_activity($conn, 'add_user', $values['email'] . ($makeAdmin ? ' (admin)' : ''));
                $_SESSION['flash']      = 'User "' . $values['email'] . '" created.';
                $_SESSION['flash_type'] = 'success';
                header('Location: users.php');
                exit;
            } else {
                $code = mysqli_stmt_errno($stmt);
                mysqli_stmt_close($stmt);
                $errors['email'] = ($code == 1062)
                    ? 'That email is already registered.'
                    : 'Could not create the user. Please try again.';
            }
        }
    }
}

// ── Load all users for the table ──────────────────────────────
$users = array();
$result = mysqli_query($conn,
    "SELECT id, full_name, email, is_verified, is_admin, created_at
     FROM users ORDER BY created_at DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

$pageTitle = 'Users';
$adminPage = 'users';

require_once '../../includes/admin-header.php';
?>

<div class="admin-page-head">
    <div>
        <h1>Users &amp; Roles</h1>
        <p><?= count($users) ?> registered account<?= count($users) != 1 ? 's' : '' ?></p>
    </div>
</div>

<!-- Add user form -->
<div style="background:#1B2B34;border:1px solid #3C4A56;border-radius:12px;padding:1.5rem;margin-bottom:2rem;">
    <h2 style="margin:0 0 1rem;font-size:1.05rem;color:#fff;">Add a User</h2>
    <form method="POST" action="users.php" novalidate>
        <input type="hidden" name="action" value="add_user">

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="full_name">Full Name <span>*</span></label>
                <input class="form-input <?= isset($errors['full_name']) ? 'error' : '' ?>" type="text"
                       id="full_name" name="full_name"
                       value="<?= htmlspecialchars($values['full_name'], ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['full_name'])): ?><span class="form-error"><?= htmlspecialchars($errors['full_name'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email <span>*</span></label>
                <input class="form-input <?= isset($errors['email']) ? 'error' : '' ?>" type="email"
                       id="email" name="email"
                       value="<?= htmlspecialchars($values['email'], ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['email'])): ?><span class="form-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="password">Password <span>*</span></label>
                <input class="form-input <?= isset($errors['password']) ? 'error' : '' ?>" type="text"
                       id="password" name="password" placeholder="Min 8 chars, 1 uppercase, 1 number">
                <?php if (isset($errors['password'])): ?><span class="form-error"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            </div>
            <div class="form-group">
                <label class="form-label" for="contact">Contact <span>*</span></label>
                <input class="form-input <?= isset($errors['contact']) ? 'error' : '' ?>" type="text"
                       id="contact" name="contact"
                       value="<?= htmlspecialchars($values['contact'], ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['contact'])): ?><span class="form-error"><?= htmlspecialchars($errors['contact'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="address">Address <span>*</span></label>
            <input class="form-input <?= isset($errors['address']) ? 'error' : '' ?>" type="text"
                   id="address" name="address"
                   value="<?= htmlspecialchars($values['address'], ENT_QUOTES, 'UTF-8') ?>">
            <?php if (isset($errors['address'])): ?><span class="form-error"><?= htmlspecialchars($errors['address'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
        </div>

        <label style="display:flex;align-items:center;gap:0.5rem;color:#cfd8df;font-size:0.9rem;margin:0.25rem 0 1rem;cursor:pointer;">
            <input type="checkbox" name="is_admin" value="1"> Grant admin access
        </label>

        <button type="submit" class="admin-btn admin-btn--primary">Create User</button>
    </form>
</div>

<!-- Users table -->
<table class="admin-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Verified</th>
            <th>Role</th>
            <th>Joined</th>
            <th class="text-right">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
            <td style="color:#fff;font-weight:500;"><?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= $u['is_verified'] ? 'Yes' : '<span style="color:#c0392b;">No</span>' ?></td>
            <td>
                <?php if ($u['is_admin']): ?>
                    <span style="color:#2ecc71;font-weight:600;">Admin</span>
                <?php else: ?>
                    User
                <?php endif; ?>
            </td>
            <td style="color:#888;font-size:0.8rem;"><?= htmlspecialchars($u['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="text-right">
                <form method="POST" action="users.php" style="display:inline;">
                    <input type="hidden" name="action" value="toggle_admin">
                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                    <button type="submit" class="admin-btn admin-btn--ghost" style="padding:0.35rem 0.75rem;font-size:0.8rem;">
                        <?= $u['is_admin'] ? 'Revoke admin' : 'Make admin' ?>
                    </button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once '../../includes/admin-footer.php'; ?>
