// Business theme — progressively enhanced interactions
document.addEventListener('DOMContentLoaded', function () {
    // Sticky nav shadow
    const nav = document.querySelector('nav.site-nav');
    if (nav) {
        const onScroll = () => nav.classList.toggle('is-scrolled', window.scrollY > 8);
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }
});
