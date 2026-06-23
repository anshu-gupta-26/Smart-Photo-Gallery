<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
requireAdmin();

// CSRF token generate karo
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateToken();
}

// User delete — POST only
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    if (!verifyToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request!');
        redirect(BASE_URL . '/admin/users.php');
    }
    $del_id = (int)($_POST['delete_user'] ?? 0);
    if ($del_id > 0 && $del_id !== $_SESSION['user_id']) {

        // Pehle user ki saari photos ki files delete karo
        $stmt = $pdo->prepare("SELECT filename FROM photos WHERE user_id = ?");
        $stmt->execute([$del_id]);
        $userPhotos = $stmt->fetchAll();
        foreach ($userPhotos as $p) {
            $file = UPLOAD_PATH . $p['filename'];
            if (file_exists($file)) unlink($file);
        }

        // Profile pic bhi delete karo
        $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = ?");
        $stmt->execute([$del_id]);
        $delUser = $stmt->fetch();
        if (!empty($delUser['profile_pic']) && $delUser['profile_pic'] !== 'default.png') {
            $picFile = PROFILE_UPLOAD_PATH . $delUser['profile_pic'];
            if (file_exists($picFile)) unlink($picFile);
        }

        // User delete karo — CASCADE se albums + photos bhi delete honge
        $pdo->prepare("DELETE FROM users WHERE id = ?")
            ->execute([$del_id]);

        setFlash('success', 'User aur uska saara data delete ho gaya!');
    }
    redirect(BASE_URL . '/admin/users.php');
}

// Role toggle — POST only
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_role'])) {
    if (!verifyToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request!');
        redirect(BASE_URL . '/admin/users.php');
    }
    $uid = (int)($_POST['toggle_role'] ?? 0);
    if ($uid > 0) {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$uid]);
        $u = $stmt->fetch();
        $newRole = ($u['role'] === 'admin') ? 'user' : 'admin';
        $pdo->prepare("UPDATE users SET role=? WHERE id=?")
            ->execute([$newRole, $uid]);
        setFlash('success', 'Role update ho gaya!');
    }
    redirect(BASE_URL . '/admin/users.php');
}

// All users fetch
$users = $pdo->query("
    SELECT u.*, 
           COUNT(DISTINCT p.id) as photo_count,
           COUNT(DISTINCT a.id) as album_count
    FROM users u
    LEFT JOIN photos p ON p.user_id = u.id
    LEFT JOIN albums a ON a.user_id = u.id
    GROUP BY u.id
    ORDER BY u.created_at DESC
")->fetchAll();

$pageTitle = "Manage Users";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="page-header">
  <div class="container">
    <h2 class="fw-bold mb-1">
      <i class="bi bi-people me-2"></i>Manage Users
    </h2>
    <p class="mb-0 opacity-75">
      Total <?= count($users) ?> users registered hain
    </p>
  </div>
</div>

<div class="container pb-5">
  <?= getFlash() ?>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>User</th>
            <th>Email</th>
            <th>Role</th>
            <th>Photos</th>
            <th>Albums</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $i => $u): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td>
              <strong>@<?= sanitize($u['username']) ?></strong><br>
              <small class="text-muted"><?= sanitize($u['full_name']) ?></small>
            </td>
            <td class="text-muted small"><?= sanitize($u['email']) ?></td>
            <td>
              <?php if ($u['role'] === 'admin'): ?>
                <span class="badge bg-danger">Admin</span>
              <?php else: ?>
                <span class="badge bg-primary">User</span>
              <?php endif; ?>
            </td>
            <td><?= $u['photo_count'] ?></td>
            <td><?= $u['album_count'] ?></td>
            <td class="small text-muted">
              <?= date('d M Y', strtotime($u['created_at'])) ?>
            </td>
            <td>
              <div class="d-flex gap-1">

                <!-- Role Toggle -->
                <form method="POST" class="d-inline">
                  <input type="hidden" name="csrf_token" 
                         value="<?= $_SESSION['csrf_token'] ?>">
                  <input type="hidden" name="toggle_role" 
                         value="<?= $u['id'] ?>">
                  <button type="submit" 
                          class="btn btn-sm btn-outline-warning"
                          title="Role Toggle">
                    <i class="bi bi-arrow-repeat"></i>
                  </button>
                </form>

                <!-- Delete -->
                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                <form method="POST" class="d-inline confirm-delete-form"
                      data-message="Is user ko delete karna chahte ho?">
                  <input type="hidden" name="csrf_token" 
                         value="<?= $_SESSION['csrf_token'] ?>">
                  <input type="hidden" name="delete_user" 
                         value="<?= $u['id'] ?>">
                  <button type="submit" 
                          class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
                <?php endif; ?>

              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>