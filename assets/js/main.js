// Header scroll effect
const header = document.getElementById('header') || document.getElementById('cdmHeader');
if (header) {
  window.addEventListener('scroll', () => {
    header.classList.toggle('scrolled', window.scrollY > 50);
  }, { passive: true });
}

// Sidebar toggle
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebarOverlay');
const sidebarClose = document.getElementById('sidebarClose');

function openSidebar() { if (sidebar) sidebar.classList.add('active'); if (overlay) overlay.classList.add('active'); document.body.style.overflow = 'hidden'; }
function closeSidebar() { if (sidebar) sidebar.classList.remove('active'); if (overlay) overlay.classList.remove('active'); document.body.style.overflow = ''; }

if (menuToggle) menuToggle.addEventListener('click', openSidebar);
if (sidebarClose) sidebarClose.addEventListener('click', closeSidebar);
if (overlay) overlay.addEventListener('click', closeSidebar);

// Animated counters
function animateCounters() {
  document.querySelectorAll('[data-count]').forEach(el => {
    if (el.classList.contains('animated')) return;
    el.classList.add('animated');
    const target = el.getAttribute('data-count');
    const isPercent = target.includes('%');
    const hasPlus = target.includes('+');
    const hasSlash = target.includes('/5');
    const num = parseFloat(target);
    if (isNaN(num)) return;
    let current = 0;
    const isFloat = target.includes('.');
    const step = isFloat ? 0.1 : (Math.ceil(num / 60) || 1);
    const timer = setInterval(() => {
      current += step;
      if (current >= num) {
        current = num;
        clearInterval(timer);
      }
      let displayValue = isFloat ? current.toFixed(1) : Math.round(current);
      el.textContent = displayValue + (isPercent ? '%' : '') + (hasPlus ? '+' : '') + (hasSlash ? '/5' : '');
    }, 20);
  });
}

// Scroll animations
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      if (entry.target.classList.contains('stats') || entry.target.querySelector('[data-count]') || entry.target.hasAttribute('data-count')) {
        animateCounters();
      }
    }
  });
}, { threshold: 0.15 });

document.querySelectorAll('.fade-in, .stats, [data-count]').forEach(el => observer.observe(el));

// FAQ accordion
function toggleFaqItem(q) {
  const item = q.closest('.faq-item') || q.parentElement;
  const isActive = item.classList.contains('active');
  document.querySelectorAll('.faq-item').forEach(i => {
    i.classList.remove('active');
    const trigger = i.querySelector('.faq-q');
    if (trigger) trigger.setAttribute('aria-expanded', 'false');
  });
  if (!isActive) {
    item.classList.add('active');
    q.setAttribute('aria-expanded', 'true');
  }
}

document.querySelectorAll('.faq-q').forEach(q => {
  q.addEventListener('click', () => toggleFaqItem(q));
  q.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      toggleFaqItem(q);
    }
  });
});

// Project filter
document.querySelectorAll('.projects-filter button').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.projects-filter button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const cat = btn.getAttribute('data-filter');
    document.querySelectorAll('.project-card').forEach(card => {
      card.style.display = (cat === 'all' || card.getAttribute('data-category') === cat) ? '' : 'none';
    });
  });
});
