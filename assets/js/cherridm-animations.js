/**
 * CherriDM Animation & Interaction Controller
 * Powers scroll reveals, dynamic stat counters, smooth scrolling, and micro-interactions
 * Replicates behavior of https://www.cherridm.com/
 */

(function () {
    'use strict';

    const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function onReady(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    onReady(function () {
        initScrollReveals();
        initCountUpCounters();
        initScrollToTop();
        initMobileFlipCards();
    });

    /**
     * 1. Scroll-triggered element reveals via IntersectionObserver
     * Includes fallback safety timeout to ensure NO content remains invisible
     */
    function initScrollReveals() {
        const revealElements = document.querySelectorAll('.cdm-reveal, .cdm-reveal-left, .cdm-reveal-right, .cdm-reveal-zoom');
        if (!revealElements.length) return;

        // If user prefers reduced motion, reveal all elements immediately without transitions
        if (prefersReducedMotion) {
            revealElements.forEach(el => el.classList.add('cdm-visible'));
            return;
        }

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('cdm-visible');
                        obs.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -20px 0px'
            });

            revealElements.forEach(el => observer.observe(el));

            // Fail-safe: After 2.5s, force any unrevealed elements visible to guarantee zero missing content
            setTimeout(() => {
                revealElements.forEach(el => {
                    if (!el.classList.contains('cdm-visible')) {
                        el.classList.add('cdm-visible');
                        try { observer.unobserve(el); } catch (e) {}
                    }
                });
            }, 2500);
        } else {
            // Immediate fallback for browsers without IntersectionObserver
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
            const num = parseFloat(rawTarget.replace(/[^0-9.]/g, ''));

            if (isNaN(num)) return;

            const isFloat = rawTarget.includes('.');
            const formatVal = (v) => {
                const formatted = isFloat ? v.toFixed(1) : Math.round(v);
                return formatted + (hasPercent ? '%' : '') + (hasPlus ? '+' : '') + (hasSlash ? '/5' : '');
            };

            // If reduced motion is requested, render target number immediately
            if (prefersReducedMotion) {
                el.textContent = formatVal(num);
                return;
            }

            const duration = 1600; // ms
            const startTime = performance.now();

            function updateCounter(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                // Ease-out cubic curve: 1 - pow(1 - progress, 3)
                const easeOutProgress = 1 - Math.pow(1 - progress, 3);
                const currentVal = easeOutProgress * num;

                el.textContent = formatVal(currentVal);

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    el.textContent = formatVal(num);
                }
            }

            requestAnimationFrame(updateCounter);
        }

        if ('IntersectionObserver' in window && !prefersReducedMotion) {
            const counterObserver = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        obs.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.2 });

            counters.forEach(c => counterObserver.observe(c));

            // Fail-safe: Render all counters after 3s if observer didn't trigger
            setTimeout(() => {
                counters.forEach(c => animateCounter(c));
            }, 3000);
        } else {
            counters.forEach(c => animateCounter(c));
        }
    }

    /**
     * 3. Back to Top Button
     * Throttled via requestAnimationFrame to eliminate scroll jank
     */
    function initScrollToTop() {
        let btn = document.getElementById('cdmScrollTop');
        if (!btn) {
            btn = document.createElement('button');
            btn.id = 'cdmScrollTop';
            btn.className = 'cdm-scroll-top';
            btn.setAttribute('type', 'button');
            btn.setAttribute('aria-label', 'Scroll back to top');
            btn.innerHTML = '<i class="fas fa-chevron-up" aria-hidden="true"></i>';
            document.body.appendChild(btn);
        }

        let isTicking = false;
        const updateScrollBtn = () => {
            const shouldBeVisible = window.scrollY > 350;
            if (shouldBeVisible && !btn.classList.contains('visible')) {
                btn.classList.add('visible');
            } else if (!shouldBeVisible && btn.classList.contains('visible')) {
                btn.classList.remove('visible');
            }
            isTicking = false;
        };

        window.addEventListener('scroll', () => {
            if (!isTicking) {
                requestAnimationFrame(updateScrollBtn);
                isTicking = true;
            }
        }, { passive: true });

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: prefersReducedMotion ? 'auto' : 'smooth'
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
                // If user clicked a direct link inside back, allow standard navigation
                if (e.target.tagName === 'A' || e.target.closest('a')) return;
                // Otherwise toggle flip on mobile touch/click
                if (window.innerWidth <= 992) {
                    this.classList.toggle('is-flipped');
                }
            });
        });
    }
})();
