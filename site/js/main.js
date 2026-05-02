// Prontu! — Interações do site
// Header scroll, menu mobile, carrossel de depoimentos, formulário de contato.

(function () {
  'use strict';

  // ----- Header sticky com efeito de scroll -----
  const header = document.querySelector('.site-header');
  if (header) {
    const onScroll = () => {
      if (window.scrollY > 24) header.classList.add('is-scrolled');
      else header.classList.remove('is-scrolled');
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // ----- Menu mobile -----
  const navToggle = document.querySelector('.site-nav__toggle');
  const nav = document.querySelector('.site-nav');
  if (navToggle && nav) {
    navToggle.addEventListener('click', () => {
      nav.classList.toggle('is-open');
      const expanded = nav.classList.contains('is-open');
      navToggle.setAttribute('aria-expanded', expanded);
    });
  }

  // ----- Marca página ativa no menu -----
  const here = (location.pathname.split('/').pop() || 'index.html').toLowerCase();
  document.querySelectorAll('.site-nav__link').forEach(a => {
    const target = (a.getAttribute('href') || '').toLowerCase();
    if (target === here || (here === '' && target === 'index.html')) {
      a.classList.add('is-active');
    }
  });

  // ----- Carrossel de depoimentos -----
  const slides = document.querySelectorAll('[data-testimonial]');
  const dots = document.querySelectorAll('[data-testimonial-dot]');
  if (slides.length > 1) {
    let i = 0;
    let timer = null;

    const show = (n) => {
      i = (n + slides.length) % slides.length;
      slides.forEach((s, idx) => {
        s.hidden = idx !== i;
        if (idx === i) {
          s.classList.remove('fade-up');
          // força reflow
          void s.offsetWidth;
          s.classList.add('fade-up');
        }
      });
      dots.forEach((d, idx) => d.classList.toggle('is-active', idx === i));
    };

    const start = () => {
      stop();
      timer = setInterval(() => show(i + 1), 6500);
    };
    const stop = () => { if (timer) { clearInterval(timer); timer = null; } };

    dots.forEach((d, idx) => d.addEventListener('click', () => { show(idx); start(); }));
    show(0);
    start();
  }

  // ----- Formulário de contato (envio simulado) -----
  const form = document.querySelector('[data-contact-form]');
  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const submitBtn = form.querySelector('[type="submit"]');
      const successCard = document.querySelector('[data-contact-success]');
      if (!submitBtn || !successCard) return;

      submitBtn.disabled = true;
      submitBtn.textContent = 'Enviando…';

      setTimeout(() => {
        form.hidden = true;
        successCard.hidden = false;
      }, 800);
    });
  }

  // ----- Lucide icons -----
  if (window.lucide && typeof window.lucide.createIcons === 'function') {
    window.lucide.createIcons();
  }
})();
