<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

$errors = [];
$success = '';
$user_id = $_SESSION['user_id'];

// CSRF token generate karo
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateToken();
}

// User ke albums lo (dropdown ke liye)
$stmt = $pdo->prepare("SELECT * FROM albums WHERE user_id = ? ORDER BY title");
$stmt->execute([$user_id]);
$albums = $stmt->fetchAll();

// Form submit hone par
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid request. Dobara try karo.";
    } else {
    
    $title    = sanitize($_POST['title'] ?? '');
    $album_id = !empty($_POST['album_id']) ? (int)$_POST['album_id'] : null;

    // File upload check
    if (empty($_FILES['photos']['name'][0])) {
        $errors[] = "Kam se kam ek photo select karo.";
    }

    if (empty($errors)) {
        $uploaded = 0;
        $files = $_FILES['photos'];
        $total = count($files['name']);

        for ($i = 0; $i < $total; $i++) {
            
            // Skip empty files
            if ($files['error'][$i] !== 0) continue;

            $originalName = $files['name'][$i];
            $tmpPath      = $files['tmp_name'][$i];
            $fileSize     = $files['size'][$i];
            $fileType     = mime_content_type($tmpPath);
            $ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            // Validation
            if (!in_array($fileType, ALLOWED_TYPES)) {
                $errors[] = "$originalName: Sirf JPG, PNG, GIF, WEBP allowed hai.";
                continue;
            }

            if ($fileSize > MAX_FILE_SIZE) {
                $errors[] = "$originalName: File size 5MB se zyada hai.";
                continue;
            }

            // Unique filename banao
            $newFilename = uniqid('photo_', true) . '.' . $ext;
            $destination = UPLOAD_PATH . $newFilename;

            // File move karo uploads folder mein
            if (move_uploaded_file($tmpPath, $destination)) {
                
                // Photo title
                $photoTitle = !empty($title) ? $title : 
                              pathinfo($originalName, PATHINFO_FILENAME);

                // Database mein save karo
                $stmt = $pdo->prepare("
                    INSERT INTO photos 
                    (user_id, album_id, title, filename, file_size, file_type) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $user_id, $album_id, $photoTitle,
                    $newFilename, $fileSize, $fileType
                ]);

                $uploaded++;
            }
        }

        if ($uploaded > 0) {
            setFlash('success', "$uploaded photo(s) successfully upload ho gayi!");
            redirect(BASE_URL . '/dashboard.php');
        } else {
            $errors[] = "Koi photo upload nahi ho saki. Dobara try karo.";
        }
        } // CSRF else closing
    }
}

$pageTitle = "Upload Photos";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<!-- Page Header -->
<div class="page-header">
  <div class="container">
    <h2 class="fw-bold mb-1">
      <i class="bi bi-cloud-upload me-2"></i>Photo Upload
    </h2>
    <p class="mb-0 opacity-75"></p>
  </div>
</div>

<div class="container pb-5" style="max-width:700px">

  <!-- Errors -->
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $e): ?>
        <div><i class="bi bi-exclamation-circle me-1"></i><?= $e ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="card p-4">
    <form method="POST" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

      <!-- Dropzone -->
      <div class="mb-4">
        <label class="form-label fw-semibold fs-5">
          <i class="bi bi-images me-2 text-primary"></i>
        </label>

        <div class="dropzone" id="dropzone">
  <i class="bi bi-cloud-arrow-up text-primary" style="font-size:3rem"></i>
  <h5 class="mt-2 text-primary">Click to upload or drag photos here</h5>
  <p class="text-muted small mb-0">JPG, PNG, GIF, WEBP • Max 5MB per photo • Multiple select allowed</p>
          <input type="file" name="photos[]" id="photo-upload-input"
                 accept="image/*" multiple
                 style="position:absolute;inset:0;opacity:0;cursor:pointer;">
        </div>

        <!-- Preview area -->
        <div class="row g-2 mt-2" id="upload-preview"></div>
      </div>

      <!-- Title -->
      <div class="mb-3">
        <label class="form-label fw-medium">
          Photo Title <span class="text-muted small">(optional)</span>
        </label>
        <input type="text" name="title" class="form-control"
       placeholder="Enter a title or leave it blank">
      </div>

      <!-- Album select -->
      <div class="mb-4">
        <label class="form-label fw-medium">
          <span class="text-muted small">(optional)</span>
        </label>
        <select name="album_id" class="form-select">
          <option value="">-- Koi album nahi --</option>
          <?php foreach ($albums as $album): ?>
            <option value="<?= $album['id'] ?>">
              <?= sanitize($album['title']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (empty($albums)): ?>
          <small class="text-muted">
            Koi album nahi hai — 
            <a href="<?= BASE_URL ?>/albums/create.php"></a>
          </small>
        <?php endif; ?>
      </div>

      <!-- Buttons -->
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
          <i class="bi bi-cloud-upload me-2"></i>Upload Photos
        </button>
        <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-outline-secondary px-4">
          Cancel
        </a>
      </div>

    </form>
  </div>

</div>

<style>
.dropzone {
    border: 2px dashed #4361ee;
    border-radius: 12px;
    padding: 3rem;
    text-align: center;
    cursor: pointer;
    position: relative;
    transition: all 0.3s;
    background: rgba(67,97,238,0.03);
}
.dropzone:hover { background: rgba(67,97,238,0.08); }
</style>

<script>
// Preview images before upload
document.getElementById('photo-upload-input').addEventListener('change', function() {
    const preview = document.getElementById('upload-preview');
    preview.innerHTML = '';
    
    Array.from(this.files).forEach(file => {
        if (!file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            const col = document.createElement('div');
            col.className = 'col-4 col-md-3';
            col.innerHTML = `
                <div class="card p-1 text-center">
                    <img src="${e.target.result}" 
                         class="img-fluid rounded" 
                         style="aspect-ratio:1;object-fit:cover;">
                    <small class="text-muted mt-1" style="font-size:0.7rem">
                        ${file.name.substring(0,15)}
                    </small>
                </div>`;
            preview.appendChild(col);
        };
        reader.readAsDataURL(file);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>