// Photography theme — reveal on scroll + lightbox
document.addEventListener('DOMContentLoaded', function () {
    // Reveal-on-scroll
    const items = document.querySelectorAll('.portfolio-item, .featured-single, .section-title');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'none';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        items.forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(18px)';
            item.style.transition = 'opacity 0.55s ease, transform 0.55s ease';
            observer.observe(item);
        });
    }

    // Lightbox
    const overlay = document.getElementById('lightbox');
    const overlayImg = overlay ? overlay.querySelector('img') : null;
    const triggers = document.querySelectorAll('.lightbox-trigger');
    if (overlay && overlayImg && triggers.length) {
        triggers.forEach(trigger => {
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                overlayImg.src = this.dataset.lightbox;
                overlayImg.alt = this.dataset.lightboxTitle || '';
                overlay.classList.add('open');
                document.body.style.overflow = 'hidden';
            });
        });
        const close = () => { overlay.classList.remove('open'); document.body.style.overflow = ''; };
        overlay.addEventListener('click', close);
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
    }
});
