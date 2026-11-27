<style>
    /* Shared reusable-pattern styles — inherit theme CSS variables. */
    .pattern-hero { padding: 5rem 0; text-align: center; }
    .pattern-hero[data-align="left"] { text-align: left; }
    .pattern-hero-title { font-size: 3rem; font-weight: 800; line-height: 1.1; margin-bottom: 1rem; color: var(--primary-color); }
    .pattern-hero-subtitle { font-size: 1.2rem; color: var(--secondary-color); max-width: 640px; margin: 0 auto; }
    .pattern-hero[data-align="left"] .pattern-hero-subtitle { margin: 0; }
    .pattern-hero-actions { margin-top: 2rem; }
    .pattern-btn { display: inline-block; padding: 0.75rem 1.75rem; border-radius: 0.375rem; font-weight: 600; text-decoration: none; transition: all 0.2s; border: 1px solid transparent; }
    .pattern-btn-primary { background: var(--primary-color); color: #fff; }
    .pattern-btn-primary:hover { filter: brightness(1.1); transform: translateY(-1px); }
    .pattern-feature-grid { padding: 4rem 0; }
    .pattern-feature-grid-heading { text-align: center; margin-bottom: 2.5rem; }
    .pattern-feature-grid-heading h2 { font-size: 2rem; font-weight: 700; color: var(--primary-color); }
    .pattern-feature-grid-items { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 2rem; }
    .pattern-feature-item { text-align: center; padding: 1.5rem; background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 0.75rem; transition: box-shadow 0.2s, transform 0.2s; }
    .pattern-feature-item:hover { box-shadow: 0 10px 25px rgba(0,0,0,0.08); transform: translateY(-3px); }
    .pattern-feature-icon { font-size: 2.5rem; margin-bottom: 0.75rem; color: var(--accent-color); }
    .pattern-feature-item h3 { font-size: 1.2rem; margin-bottom: 0.5rem; color: var(--text-color); }
    .pattern-feature-item p { color: var(--secondary-color); font-size: 0.95rem; }
    .pattern-cta { padding: 3.5rem 0; background: var(--primary-color); color: #fff; text-align: center; }
    .pattern-cta h2 { font-size: 2rem; margin-bottom: 0.75rem; color: #fff; }
    .pattern-cta p { opacity: 0.9; max-width: 560px; margin: 0 auto; }
    .pattern-cta-actions { margin-top: 1.75rem; }
    .pattern-cta .pattern-btn-primary { background: #fff; color: var(--primary-color); }
    .pattern-cta .pattern-btn-primary:hover { background: var(--accent-color); color: #fff; }
    .pattern-testimonial { padding: 2rem; text-align: center; max-width: 720px; margin: 0 auto; }
    .pattern-testimonial blockquote { margin: 0; font-size: 1.4rem; font-style: italic; line-height: 1.5; color: var(--text-color); }
    .pattern-testimonial figcaption { margin-top: 1rem; }
    .pattern-testimonial figcaption strong { color: var(--primary-color); }
    .pattern-testimonial figcaption span { display: block; color: var(--secondary-color); font-size: 0.9rem; margin-top: 0.25rem; }
    .widget-area { padding: 2rem 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; }
    .widget-area-footer { grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
    .widget-title { font-size: 1rem; font-weight: 700; color: var(--primary-color); margin-bottom: 0.5rem; }
    .widget-content { font-size: 0.95rem; color: var(--text-color); }
    .widget-content p { margin-bottom: 0.75rem; }
    .widget-content ul, .widget-content ol { margin: 0 0 0.75rem 1.25rem; padding: 0; }
    .widget-content a { color: var(--primary-color); text-decoration: none; }
    .widget-content a:hover { text-decoration: underline; }
    @media (max-width: 768px) {
        .pattern-hero { padding: 3rem 0; }
        .pattern-hero-title { font-size: 2.25rem; }
        .pattern-feature-grid-items { grid-template-columns: 1fr; }
        .pattern-testimonial blockquote { font-size: 1.2rem; }
    }
</style>
