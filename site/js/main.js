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

  // ----- Formulário de contato (envio real via enviar.php) -----
  const form = document.querySelector('[data-contact-form]');
  if (form) {
    const submitBtn = form.querySelector('[type="submit"]');
    const submitLabel = form.querySelector('[data-submit-label]');
    const submitIcon = form.querySelector('[data-submit-icon]');
    const errorBox = form.querySelector('[data-contact-error]');
    const successCard = document.querySelector('[data-contact-success]');

    const showError = (msg) => {
      if (!errorBox) return;
      errorBox.textContent = msg;
      errorBox.hidden = false;
    };
    const hideError = () => { if (errorBox) errorBox.hidden = true; };

    const resetFormState = () => {
      form.reset();
      submitBtn.disabled = false;
      if (submitLabel) submitLabel.textContent = 'Enviar mensagem';
      if (submitIcon) submitIcon.style.display = '';
      hideError();
    };

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      hideError();

      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      submitBtn.disabled = true;
      if (submitLabel) submitLabel.textContent = 'Enviando…';
      if (submitIcon) submitIcon.style.display = 'none';

      try {
        const res = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          headers: { 'Accept': 'application/json' },
        });
        const data = await res.json().catch(() => ({ ok: false, error: 'Resposta inválida do servidor.' }));

        if (res.ok && data.ok) {
          form.hidden = true;
          if (successCard) successCard.hidden = false;
        } else {
          showError(data.error || 'Não conseguimos enviar agora. Tente novamente.');
          submitBtn.disabled = false;
          if (submitLabel) submitLabel.textContent = 'Enviar mensagem';
          if (submitIcon) submitIcon.style.display = '';
        }
      } catch (err) {
        showError('Erro de conexão. Verifique sua internet e tente novamente.');
        submitBtn.disabled = false;
        if (submitLabel) submitLabel.textContent = 'Enviar mensagem';
        if (submitIcon) submitIcon.style.display = '';
      }
    });

  }

  // Botão "Enviar nova mensagem" no card de sucesso — recarrega a página
  // com âncora #contato-form para já cair direto no formulário.
  // Event delegation no document garante que o handler funcione mesmo que
  // o ícone Lucide substitua a estrutura interna do botão.
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-contact-reset]');
    if (!btn) return;
    e.preventDefault();
    window.location.hash = 'contato-form';
    window.location.reload();
  });

  // ----- Lucide icons -----
  if (window.lucide && typeof window.lucide.createIcons === 'function') {
    window.lucide.createIcons();
  }
})();
