/* Convene homepage experience layer. Reads voice-search state; never alters its logic. */
(() => {
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const $ = (s, r = document) => r.querySelector(s);
  const hero = $('.convene-landing');

  /* Rotating headline word */
  const rotor = $('[data-rotor]');
  if (rotor && !reduce) {
    const n = rotor.children.length; let i = 0;
    setInterval(() => {
      i = (i + 1) % n;
      Array.from(rotor.children).forEach(c => (c.style.transform = `translateY(${-100 * i}%)`));
    }, 2400);
  }

  /* Concierge */
  const btn = $('#voice-search'), status = $('#voice-status'), transcript = $('#voice-transcript');
  const bubble = $('[data-bubble]'), bubbleText = $('[data-bubble-text]');
  if (hero && btn) {
    const say = {
      idle: ['Tell me about your next event, and I’ll find the perfect venue.', 'Try: “A boardroom for 20 guests in Abdali.”', 'Give me a place and a headcount. I’ll do the rest.'],
      hover: ['Press me and just speak. I’m ready.'],
      listening: ['I’m listening. Where, and for how many guests?'],
      processing: ['One moment while I check availability…'],
      responding: ['Here’s what I found. Taking you there now.'],
    };
    let state = 'idle', k = 0, hovering = false;
    const show = (t) => {
      if (!bubble || bubbleText.textContent === t) return;
      bubble.classList.add('is-swap');
      setTimeout(() => { bubbleText.textContent = t; bubble.classList.remove('is-swap'); }, 220);
    };
    const set = (s) => {
      if (s === state) return;
      state = s; hero.dataset.state = s;
      show(say[hovering && s === 'idle' ? 'hover' : s][0]);
    };

    /* Derive state from what the existing voice script already changes */
    const derive = () => {
      const sig = (btn.className + ' ' + (btn.closest('[data-home-voice-widget]')?.className || '')).toLowerCase();
      const has = (re) => re.test(sig);
      if (btn.getAttribute('aria-pressed') === 'true' || has(/listen|record/)) return set('listening');
      if (btn.getAttribute('aria-busy') === 'true' || has(/process|busy|load|transcrib|think|search/)) return set('processing');
      if (has(/respon|success|done|speak/) || (transcript && transcript.textContent.trim())) return set('responding');
      set('idle');
    };
    const mo = new MutationObserver(derive);
    mo.observe(btn, { attributes: true, attributeFilter: ['class', 'aria-pressed', 'aria-busy', 'disabled'] });
    [status, transcript, $('[data-voice-label]')].forEach(el => el && mo.observe(el, { childList: true, characterData: true, subtree: true }));
    btn.addEventListener('click', () => setTimeout(derive, 60));

    /* Idle message rotation */
    setInterval(() => { if (state === 'idle' && !hovering) { k = (k + 1) % say.idle.length; show(say.idle[k]); } }, 6000);

    /* Hover + eye tracking */
    btn.addEventListener('pointerenter', () => { hovering = true; if (state === 'idle') show(say.hover[0]); });
    btn.addEventListener('pointerleave', () => { hovering = false; if (state === 'idle') show(say.idle[k]); });
    if (!reduce) {
      const svg = $('.cv-avatar__svg', btn);
      addEventListener('pointermove', (e) => {
        const r = svg.getBoundingClientRect();
        const dx = Math.max(-1, Math.min(1, (e.clientX - (r.left + r.width / 2)) / 400));
        const dy = Math.max(-1, Math.min(1, (e.clientY - (r.top + r.height / 2)) / 400));
        svg.style.setProperty('--ex', dx * 7 + 'px'); svg.style.setProperty('--ey', dy * 5 + 'px');
        svg.style.setProperty('--tx', dx * 5 + 'px'); svg.style.setProperty('--ty', dy * 3 + 'px');
        svg.style.setProperty('--rot', dx * 3 + 'deg');
      }, { passive: true });
    }
  }

  /* Scroll-scrubbed statement */
  const scrub = $('[data-scrub]');
  if (scrub) {
    scrub.innerHTML = scrub.textContent.trim().split(/\s+/).map(w => `<span class="w">${w}</span>`).join(' ');
    const words = scrub.querySelectorAll('.w');
    const run = () => {
      const r = scrub.getBoundingClientRect(), vh = innerHeight;
      const p = Math.min(1, Math.max(0, (vh * 0.85 - r.top) / (r.height + vh * 0.35)));
      const on = Math.round(p * words.length);
      words.forEach((w, i) => w.classList.toggle('on', i < on));
    };
    addEventListener('scroll', run, { passive: true }); run();
  }

  /* Parallax (hero aurora + location images) */
  if (!reduce) {
    const items = [...document.querySelectorAll('[data-parallax]')];
    const imgs = [...document.querySelectorAll('.home-location-card__image img')];
    let tick = false;
    const frame = () => {
      tick = false;
      const y = scrollY;
      items.forEach(el => (el.style.transform = `translate3d(0,${y * el.dataset.parallax}px,0)`));
      imgs.forEach(img => {
        const r = img.parentElement.getBoundingClientRect();
        if (r.bottom < 0 || r.top > innerHeight) return;
        img.style.setProperty('--py', ((r.top + r.height / 2 - innerHeight / 2) * -0.06).toFixed(1) + 'px');
      });
    };
    addEventListener('scroll', () => { if (!tick) { tick = true; requestAnimationFrame(frame); } }, { passive: true });
    frame();
  }

  /* Hero: pointer-reactive stack and animated counters */
  const card = $('[data-hero]'), stack = $('[data-stack]');
  if (card && stack && !reduce) {
    card.addEventListener('pointermove', (e) => {
      const r = card.getBoundingClientRect();
      stack.style.setProperty('--mx', ((e.clientX - r.left) / r.width - 0.5).toFixed(2));
      stack.style.setProperty('--my', ((e.clientY - r.top) / r.height - 0.5).toFixed(2));
    }, { passive: true });
  }
  document.querySelectorAll('[data-count]').forEach((el) => {
    const n = +el.dataset.count; if (!n || reduce) return;
    const t0 = performance.now();
    const step = (t) => { const p = Math.min(1, (t - t0) / 1400); el.textContent = Math.round(n * (1 - Math.pow(1 - p, 3))); if (p < 1) requestAnimationFrame(step); };
    requestAnimationFrame(step);
  });
})();
