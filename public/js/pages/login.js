/* Extraido de views/auth/login.php (CSP Fase 2) - Vanilla JS, sem dependencias. */
// var de proposito: o SPA (PageCache.applyPage) reexecuta este arquivo a cada
// navegacao; let/const no topo lancariam SyntaxError na segunda execucao.
var cardIndex = 0;
var totalStopPositions = 4;
var autoSlideTimer = null;

function resetAutoTimer() {
    if (autoSlideTimer) clearInterval(autoSlideTimer);
    autoSlideTimer = setInterval(() => slideInfinite(1), 5000);
}

function updateTrack() {
    const track = document.getElementById('cardsTrack');
    const dots = document.querySelectorAll('.carousel-dot');
    if (track) {
        track.style.transform = `translateX(calc(-${cardIndex} * (33.333% + 4.66px)))`;
    }
    dots.forEach((d, idx) => {
        d.classList.toggle('active', idx === cardIndex);
    });
}

function slideInfinite(dir) {
    cardIndex = (cardIndex + dir + totalStopPositions) % totalStopPositions;
    updateTrack();
    resetAutoTimer();
}

function goToCard(idx) {
    cardIndex = idx;
    updateTrack();
    resetAutoTimer();
}

resetAutoTimer();

var carouselElem = document.getElementById('cardsViewport');
if (carouselElem) {
    carouselElem.addEventListener('mouseenter', () => {
        if (autoSlideTimer) clearInterval(autoSlideTimer);
    });
    carouselElem.addEventListener('mouseleave', () => {
        resetAutoTimer();
    });
}

// Ligacao direta (a tela de login e standalone: app.js/DelegatedActions nao carregam aqui).
// Os data-action equivalentes ficam como reserva caso um dia entre no layout.
document.querySelectorAll('[data-action="goto-card"]').forEach(function (el) {
    el.addEventListener('click', function () { goToCard(parseInt(el.dataset.idx || '0', 10)); });
});
document.querySelectorAll('[data-action="slide-step"]').forEach(function (el) {
    el.addEventListener('click', function () { slideInfinite(parseInt(el.dataset.delta || '0', 10)); });
});
document.querySelectorAll('[data-action="toggle-password"]').forEach(function (el) {
    el.addEventListener('click', function () { togglePasswordVisibility(); });
});

function togglePasswordVisibility() {
    const input = document.getElementById('password-input');
    const eyeIcon = document.getElementById('eye-icon');
    if (!input || !eyeIcon) return;

    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
    } else {
        input.type = 'password';
        eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
    }
}
