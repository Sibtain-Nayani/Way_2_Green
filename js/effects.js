/**
 * Way2Green - Atmospheric Ambient Effects & Counter Animations
 * Hardware-accelerated, lightweight, reliable across devices
 */

document.addEventListener('DOMContentLoaded', () => {
    initEcoParticles();
    initCounterAnimations();
});

/* ==========================================================================
   1. Ambient Floating Eco-Particles System (Gentle and subtle)
   ========================================================================== */
function initEcoParticles() {
    const scene = document.querySelector('.ambient-scene');
    if (!scene) return;

    if (scene.querySelector('.eco-particles')) return;

    const particleContainer = document.createElement('div');
    particleContainer.className = 'eco-particles';
    scene.appendChild(particleContainer);

    const count = window.innerWidth < 768 ? 10 : 18;

    for (let i = 0; i < count; i++) {
        const p = document.createElement('div');
        p.className = 'eco-particle';
        
        const size = Math.random() * 4 + 3; // 3px to 7px
        const left = Math.random() * 100;
        const duration = Math.random() * 12 + 18; // 18s to 30s
        const delay = Math.random() * -20;
        const opacity = Math.random() * 0.3 + 0.1;
        
        p.style.width = `${size}px`;
        p.style.height = `${size}px`;
        p.style.left = `${left}vw`;
        p.style.animationDuration = `${duration}s`;
        p.style.animationDelay = `${delay}s`;
        p.style.opacity = opacity;
        
        particleContainer.appendChild(p);
    }
}

/* ==========================================================================
   2. Number Counter Animation for Verified Environmental Impact
   ========================================================================== */
function initCounterAnimations() {
    const counters = document.querySelectorAll('[data-counter]');
    if (!counters.length) return;

    const runCounter = (el) => {
        if (el.dataset.animated) return;
        el.dataset.animated = "true";

        const target = parseInt(el.getAttribute('data-counter'), 10);
        const suffix = el.getAttribute('data-suffix') || '';
        const duration = 1800; // ms
        const startTime = performance.now();

        function update(now) {
            const progress = Math.min((now - startTime) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(eased * target);

            el.textContent = current.toLocaleString() + suffix;

            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                el.textContent = target.toLocaleString() + suffix;
            }
        }

        requestAnimationFrame(update);
    };

    if ('IntersectionObserver' in window) {
        const obs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    runCounter(entry.target);
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });

        counters.forEach(c => obs.observe(c));
    } else {
        counters.forEach(runCounter);
    }
}
