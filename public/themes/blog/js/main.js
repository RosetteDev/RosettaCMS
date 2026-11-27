// Blog theme — minimal progressive enhancement
document.addEventListener('DOMContentLoaded', function () {
    // Highlight the current page in the nav
    const path = window.location.pathname.replace(/\/$/, '');
    document.querySelectorAll('.site-nav .nav-menu a').forEach(a => {
        try {
            const href = new URL(a.href).pathname.replace(/\/$/, '');
            if (href === path && href !== '') a.classList.add('active');
        } catch (e) { /* ignore */ }
    });
});
