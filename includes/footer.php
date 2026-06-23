<footer class="bg-dark text-light py-4">  
  <div class="container text-center">
    <strong><i class="bi bi-images me-1"></i><?= SITE_NAME ?></strong>
    <p class="text-muted small mb-0">MCA Project &copy; <?= date('Y') ?></p>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>

<!-- Lightbox Modal -->
<div id="lightbox" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.92);z-index:9999;align-items:center;justify-content:center;">
  <button onclick="closeLightbox()" 
          style="position:absolute;top:20px;right:30px;background:none;border:none;color:white;font-size:2.5rem;cursor:pointer;">
    &times;
  </button>
  <img id="lightbox-img" src="" alt="" 
       style="max-width:90vw;max-height:85vh;border-radius:12px;object-fit:contain;">
  <div id="lightbox-title" 
       style="position:absolute;bottom:30px;left:50%;transform:translateX(-50%);
              color:white;font-size:1.1rem;font-weight:600;
              background:rgba(0,0,0,0.5);padding:8px 20px;border-radius:20px;">
  </div>
</div>

<script>
function openLightbox(src, title) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox-title').textContent = title;
    const lb = document.getElementById('lightbox');
    lb.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
    document.getElementById('lightbox-img').src = '';
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLightbox();
});

document.getElementById('lightbox').addEventListener('click', function(e) {
    if (e.target === this) closeLightbox();
});
</script>

</body>
</html>