/**
 * CherriDM Animation & Interaction Controller
 * Powers scroll reveals, dynamic stat counters, smooth scrolling, and micro-interactions
 * Replicates behavior of https://www.cherridm.com/
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initScrollReveals();
        initCountUpCounters();
        initScrollToTop();
        initMobileFlipCards();
    });

    /**
     * 1. Scroll-triggered element reveals via IntersectionObserver
     */
    function initScrollReveals() {
        const revealElements = document.querySelectorAll('.cdm-reveal, .cdm-reveal-left, .cdm-reveal-right, .cdm-reveal-zoom');
        if (!revealElements.length) return;

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('cdm-visible');
                        obs.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.12,
                rootMargin: '0px 0px -40px 0px'
            });

            revealElements.forEach(el => observer.observe(el));
        } else {
            // Fallback for older browsers
            revealElements.forEach(el => el.classList.add('cdm-visible'));
        }
    }

    /**
     * 2. Smooth Animated Counters on Scroll
     * Animates any element with [data-count] e.g. data-count="159" or data-count="100%"
     */
    function initCountUpCounters() {
        const counters = document.querySelectorAll('[data-count]');
        if (!counters.length) return;

        function animateCounter(el) {
            if (el.dataset.animated === 'true') return;
            el.dataset.animated = 'true';

            const rawTarget = el.getAttribute('data-count').trim();
            const hasPercent = rawTarget.includes('%');
            const hasPlus = rawTarget.includes('+');
            const hasSlash = rawTarget.includes('/5');
            const num = parseFloat(rawTarget);

            if (isNaN(num)) return;

            const duration = 1800; // ms
            const startTime = performance.now();
            const isFloat = rawTarget.includes('.');

            function updateCounter(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                // Ease-out cubic curve: 1 - pow(1 - progress, 3)
                const easeOutProgress = 1 - Math.pow(1 - progress, 3);
                const currentVal = easeOutProgress * num;

                const formatted = isFloat ? currentVal.toFixed(1) : Math.round(currentVal);
                el.textContent = formatted + (hasPercent ? '%' : '') + (hasPlus ? '+' : '') + (hasSlash ? '/5' : '');

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    const finalVal = isFloat ? num.toFixed(1) : Math.round(num);
                    el.textContent = finalVal + (hasPercent ? '%' : '') + (hasPlus ? '+' : '') + (hasSlash ? '/5' : '');
                }
            }

            requestAnimationFrame(updateCounter);
        }

        if ('IntersectionObserver' in window) {
            const counterObserver = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        obs.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.25 });

            counters.forEach(c => counterObserver.observe(c));
        } else {
            counters.forEach(c => animateCounter(c));
        }
    }

    /**
     * 3. Back to Top Button
     */
    function initScrollToTop() {
        let btn = document.getElementById('cdmScrollTop');
        if (!btn) {
            btn = document.createElement('button');
            btn.id = 'cdmScrollTop';
            btn.className = 'cdm-scroll-top';
            btn.setAttribute('aria-label', 'Scroll back to top');
            btn.innerHTML = '<i class="fas fa-chevron-up"></i>';
            document.body.appendChild(btn);
        }

        window.addEventListener('scroll', () => {
            if (window.scrollY > 350) {
                btn.classList.add('visible');
            } else {
                btn.classList.remove('visible');
            }
        }, { passive: true });

        btn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    /**
     * 4. Touch support for 3D flip cards on mobile devices
     */
    function initMobileFlipCards() {
        const cards = document.querySelectorAll('.cdm-flip-card');
        cards.forEach(card => {
            card.addEventListener('click', function (e) {
                // If user clicked a direct link inside back, allow click
                if (e.target.tagName === 'A' || e.target.closest('a')) return;
                // Otherwise toggle flip on touch/click
                if (window.innerWidth <= 992) {
                    this.classList.toggle('is-flipped');
                }
            });
        });
    }
})();
