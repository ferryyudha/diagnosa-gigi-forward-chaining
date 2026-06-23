/**
 * SiPaGi — Main JavaScript
 * Handles UI interactions, animations, and utilities
 */

/* ─────────────────────────────────────────────────────
   MODAL SYSTEM
   ───────────────────────────────────────────────────── */
function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

// Close modal on overlay click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(m => {
            m.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});

/* ─────────────────────────────────────────────────────
   PROGRESS BARS — Animasi saat halaman dimuat
   ───────────────────────────────────────────────────── */
function animateProgressBars() {
    document.querySelectorAll('.persentase-fill[data-width]').forEach(el => {
        const width = el.getAttribute('data-width');
        requestAnimationFrame(() => {
            setTimeout(() => { el.style.width = width + '%'; }, 100);
        });
    });

    // SVG Progress Circles (untuk hasil diagnosa)
    const gradId = 'sipagi-grad-' + Date.now();
    document.querySelectorAll('.progress-circle').forEach(circle => {
        const svg = circle.querySelector('svg');
        if (!svg) return;

        // Tambahkan gradient definition ke SVG
        if (!svg.querySelector('defs')) {
            const defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
            defs.innerHTML = `
                <linearGradient id="${gradId}" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#0ea5e9"/>
                    <stop offset="100%" stop-color="#06b6d4"/>
                </linearGradient>`;
            svg.insertBefore(defs, svg.firstChild);
        }

        const fillEl = circle.querySelector('.fill');
        if (!fillEl) return;

        const pct = parseFloat(fillEl.getAttribute('data-pct') || 0);
        const r   = parseFloat(fillEl.getAttribute('r') || 30);
        const circ = 2 * Math.PI * r;

        fillEl.setAttribute('stroke', `url(#${gradId})`);
        fillEl.style.strokeDasharray  = circ;
        fillEl.style.strokeDashoffset = circ;
        fillEl.style.transition = 'stroke-dashoffset 1.2s cubic-bezier(0.4,0,0.2,1)';

        requestAnimationFrame(() => {
            setTimeout(() => {
                fillEl.style.strokeDashoffset = circ - (pct / 100) * circ;
            }, 200);
        });
    });
}

/* ─────────────────────────────────────────────────────
   ACCORDION — Untuk detail hasil diagnosa
   ───────────────────────────────────────────────────── */
function toggleDetail(header) {
    const body  = header.nextElementSibling;
    const arrow = header.querySelector('.accordion-arrow');
    if (!body) return;
    body.classList.toggle('open');
    if (arrow) arrow.style.transform = body.classList.contains('open') ? 'rotate(180deg)' : '';
}

/* ─────────────────────────────────────────────────────
   PRINT
   ───────────────────────────────────────────────────── */
function printHasil() {
    window.print();
}

/* ─────────────────────────────────────────────────────
   AUTO-DISMISS ALERTS
   ───────────────────────────────────────────────────── */
function initAlerts() {
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.4s, transform 0.4s';
            alert.style.opacity    = '0';
            alert.style.transform  = 'translateY(-6px)';
            setTimeout(() => alert.remove(), 400);
        }, 4500);
    });
}

/* ─────────────────────────────────────────────────────
   CONFIRM DIALOGS (replaces ugly browser confirm)
   ───────────────────────────────────────────────────── */
function confirmDelete(msg, callback) {
    if (window.confirm(msg || 'Apakah Anda yakin ingin menghapus data ini?')) {
        callback && callback();
    }
}

/* ─────────────────────────────────────────────────────
   SIDEBAR ACTIVE LINK HIGHLIGHT
   ───────────────────────────────────────────────────── */
function highlightActiveNav() {
    const path = window.location.pathname;
    document.querySelectorAll('.nav-link').forEach(link => {
        const href = link.getAttribute('href') || '';
        if (href && path.endsWith(href.split('/').pop())) {
            link.classList.add('active');
        }
    });
}

/* ─────────────────────────────────────────────────────
   NUMBER COUNTER ANIMATION (dashboard stats)
   ───────────────────────────────────────────────────── */
function animateCounters() {
    document.querySelectorAll('[data-count]').forEach(el => {
        const target   = parseInt(el.getAttribute('data-count'), 10);
        const duration = 1200;
        const step     = target / (duration / 16);
        let current    = 0;

        const timer = setInterval(() => {
            current = Math.min(current + step, target);
            el.textContent = Math.floor(current).toLocaleString('id-ID');
            if (current >= target) clearInterval(timer);
        }, 16);
    });
}

/* ─────────────────────────────────────────────────────
   INIT ON DOM READY
   ───────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function() {
    animateProgressBars();
    initAlerts();
    animateCounters();
    highlightActiveNav();

    // Auto-open first accordion item in hasil page
    const firstHeader = document.querySelector('.hasil-item-header');
    if (firstHeader) {
        toggleDetail(firstHeader);
    }

    // Floating label inputs
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('focus', () => {
            input.closest('.form-group')?.classList.add('focused');
        });
        input.addEventListener('blur', () => {
            input.closest('.form-group')?.classList.remove('focused');
        });
    });
});
