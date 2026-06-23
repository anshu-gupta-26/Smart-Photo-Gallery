<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$errors = [];

// CSRF token generate karo
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateToken();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid request. Dobara try karo.";
    } else {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $bio       = sanitize($_POST['bio'] ?? '');
    $email     = sanitize($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email daalo.";
    }

    // Profile picture upload
    $profile_pic = $user['profile_pic'];
    if (!empty($_FILES['profile_pic']['name'])) {
        $file     = $_FILES['profile_pic'];
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // MIME type check karo — extension nahi
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileMime     = mime_content_type($file['tmp_name']);

        if (!in_array($fileMime, $allowedMimes)) {
            $errors[] = "Sirf JPG, PNG, WEBP allowed hai.";
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = "Image 2MB se choti honi chahiye.";
        } else {
            // Old profile pic delete karo
            if (!empty($user['profile_pic']) && $user['profile_pic'] !== 'default.png') {
                $oldFile = PROFILE_UPLOAD_PATH . $user['profile_pic'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            $newName = 'profile_' . $user_id . '_' . time() . '.' . $ext;
            move_uploaded_file($file['tmp_name'], PROFILE_UPLOAD_PATH . $newName);
            $profile_pic = $newName;
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET full_name=?, bio=?, email=?, profile_pic=? 
            WHERE id=?
        ");
        $stmt->execute([$full_name, $bio, $email, $profile_pic, $user_id]);

        // Session update karo
        $_SESSION['full_name']   = $full_name;
        $_SESSION['profile_pic'] = $profile_pic;

        setFlash('success', 'Profile update ho gayi!');
        redirect(BASE_URL . '/user/profile.php');
        } // CSRF else closing
    }
}

$pageTitle = "Edit Profile";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="page-header">
  <div class="container">
    <h2 class="fw-bold mb-1">
      <i class="bi bi-pencil me-2"></i>Edit Profile
    </h2>
  </div>
</div>

<div class="container pb-5" style="max-width:600px">

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $e): ?>
        <div><?= $e ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="card p-4">
    <form method="POST" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

      <!-- Profile Picture -->
      <div class="text-center mb-4">
        <?php
          $pic = !empty($user['profile_pic']) && $user['profile_pic'] !== 'default.png'
              ? BASE_URL . '/uploads/profiles/' . $user['profile_pic']
              : 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name'] ?: $user['username']) . '&size=100&background=4361ee&color=fff&rounded=true';
        ?>
        <img src="<?= $pic ?>" id="pic-preview"
             class="rounded-circle border border-3 border-primary mb-3"
             width="100" height="100" style="object-fit:cover;">
        <div>
          <label class="btn btn-outline-primary btn-sm">
  <i class="bi bi-camera me-1"></i>Change Photo
  <input type="file" name="profile_pic" accept="image/*"
         style="display:none"
         onchange="previewPic(this)">
</label>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-medium">Full Name</label>
        <input type="text" name="full_name" class="form-control"
               value="<?= sanitize($_POST['full_name'] ?? $user['full_name']) ?>"
               placeholder="Apna pura naam">
      </div>

      <div class="mb-3">
        <label class="form-label fw-medium">Email *</label>
        <input type="email" name="email" class="form-control"
               value="<?= sanitize($_POST['email'] ?? $user['email']) ?>" required>
      </div>

      <div class="mb-4">
        <label class="form-label fw-medium">Bio</label>
        <textarea name="bio" class="form-control" rows="3"
          placeholder="Write something about yourself..."
        ><?= sanitize($_POST['bio'] ?? $user['bio']) ?></textarea>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4 fw-semibold">
  <i class="bi bi-save me-2"></i>Save Changes
</button>
        <a href="<?= BASE_URL ?>/user/profile.php"
           class="btn btn-outline-secondary px-4">Cancel</a>
      </div>

    </form>
  </div>
</div>

<script>
function previewPic(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('pic-preview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>