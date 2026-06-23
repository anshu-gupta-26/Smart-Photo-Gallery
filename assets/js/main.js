// Dark mode on load
(function() {
    if (localStorage.getItem('darkMode') === 'true') {
        document.documentElement.setAttribute('data-bs-theme', 'dark');
    }
})();

document.addEventListener('DOMContentLoaded', function () {

    // Auto hide alerts
    setTimeout(function () {
        document.querySelectorAll('.alert').forEach(function(alert) {
            var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        });
    }, 5000);

    // Delete confirmation links
    document.querySelectorAll('.confirm-delete').forEach(function(el) {
        el.addEventListener('click', function(e) {
            const msg = this.dataset.message || 'Kya aap sure hain?';
            if (!confirm(msg + '\nYe action undo nahi hogi!')) {
                e.preventDefault();
            }
        });
    });

    // Delete confirmation forms
    document.querySelectorAll('.confirm-delete-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const msg = this.dataset.message || 'Kya aap sure hain?';
            if (!confirm(msg + '\nYe action undo nahi hogi!')) {
                e.preventDefault();
            }
        });
    });

    // Dark Mode
    const darkBtn = document.getElementById('darkModeBtn');
    if (darkBtn) {
        // Set icon based on current mode
        if (localStorage.getItem('darkMode') === 'true') {
            darkBtn.innerHTML = '<i class="bi bi-sun-fill fs-5"></i>';
        }

        darkBtn.addEventListener('click', function() {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            if (isDark) {
                document.documentElement.removeAttribute('data-bs-theme');
                darkBtn.innerHTML = '<i class="bi bi-moon-stars-fill fs-5"></i>';
                localStorage.setItem('darkMode', 'false');
            } else {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
                darkBtn.innerHTML = '<i class="bi bi-sun-fill fs-5"></i>';
                localStorage.setItem('darkMode', 'true');
            }
        });
    }
});