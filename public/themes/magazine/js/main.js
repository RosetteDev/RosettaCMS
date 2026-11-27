// Magazine theme — subtle fade-in for stories
document.addEventListener('DOMContentLoaded', function () {
    const items = document.querySelectorAll('.mag-story, .mag-feature');
    if (!('IntersectionObserver' in window)) return;
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'none';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    items.forEach(item => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(14px)';
        item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(item);
    });
});
