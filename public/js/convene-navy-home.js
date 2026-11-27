/* Convene · Navy homepage interactions.
   Hero slides, parallax, reveal on scroll, venue showcase with drag,
   Nashmi tips, EN/AR toggle and a reduced-motion switch.
   Voice search itself stays in space-voice-search.js (#voice-search). */
(() => {
    const root = document.getElementById('nvh');
    if (!root) return;

    const $ = (s, el = document) => el.querySelector(s);
    const $$ = (s, el = document) => [...el.querySelectorAll(s)];
    const html = document.documentElement;
    const store = {
        get(k) { try { return localStorage.getItem(k); } catch { return null; } },
        set(k, v) { try { localStorage.setItem(k, v); } catch { /* ignore */ } },
    };
    const reduceQuery = matchMedia('(prefers-reduced-motion: reduce)');
    const isCalm = () => html.classList.contains('nv-calm') || (reduceQuery.matches && !html.classList.contains('nv-motion'));
    let lang = 'en';
    const T = (en, ar) => (lang === 'ar' ? ar : en);

    /* ---------------- header ---------------- */
    const header = $('[data-site-header]');

    /* ---------------- hero ---------------- */
    const figures = $$('.nvh-photos figure');
    const circles = $$('.nvh-circles button');
    const cnt = $('#nvh-cnt');
    let hi = 0;
    let heroTimer = null;

    function setHero(i, first) {
        hi = i;
        figures.forEach((f, k) => f.classList.toggle('on', k === i));
        circles.forEach((b, k) => {
            b.classList.toggle('on', k === i);
            b.setAttribute('aria-pressed', String(k === i));
        });
        if (cnt) cnt.textContent = String(i + 1).padStart(2, '0');
        const label = $('#nvh-slide-label');
        if (label && circles[i]) label.textContent = lang === 'ar' ? circles[i].dataset.labelAr : circles[i].dataset.label;
    }

    function restartHero() {
        clearInterval(heroTimer);
        if (figures.length < 2) return;
        heroTimer = setInterval(() => { if (!isCalm() && !document.hidden) setHero((hi + 1) % figures.length); }, 6500);
    }
    circles.forEach((b) => b.addEventListener('click', () => { setHero(+b.dataset.slide); restartHero(); }));

    /* ---------------- reveal ---------------- */
    const io = 'IntersectionObserver' in window
        ? new IntersectionObserver((entries) => entries.forEach((e) => {
            if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
        }), { threshold: 0.15 })
        : null;
    $$('.nvh-rv, .nvh-card, .nvh-tile', root).forEach((el) => (io ? io.observe(el) : el.classList.add('in')));

    /* ---------------- showcase ---------------- */
    const show = $('#venues');
    const slides = $$('.nvh-slides div');
    const titles = $$('.nvh-titles h3');
    const metas = $$('.nvh-slmeta p');
    const thumbs = $$('.nvh-thumbs button');
    let si = 0;

    function paintShow(i, dir = 1) {
        const n = slides.length;
        const d = html.dir === 'rtl' ? -1 : 1;
        titles.forEach((h, k) => {
            h.style.setProperty('--d', d);
            h.className = k === i ? 'cur' : k === (i + 1) % n ? 'nxt' : k === (i - 1 + n) % n ? 'prv' : '';
        });
        slides.forEach((s, k) => {
            s.classList.toggle('back', dir < 0 && k !== i);
            s.classList.toggle('on', k === i);
        });
        metas.forEach((m, k) => m.classList.toggle('on', k === i));
        thumbs.forEach((b, k) => b.classList.toggle('on', k === i));
        const more = $('#nvh-more');
        if (more && thumbs[i]) more.href = thumbs[i].dataset.url;
        const si2 = $('#nvh-si');
        if (si2) si2.textContent = String(i + 1).padStart(2, '0');
        const bar = $('#nvh-bar');
        if (bar) bar.style.width = `${((i + 1) / n) * 100}%`;
        const blur = $('#nvh-blur');
        if (blur && slides[i]) blur.style.backgroundImage = `url('${slides[i].dataset.blur}')`;
    }

    function go(i, dir) {
        const n = slides.length;
        if (!n) return;
        i = (i + n) % n;
        if (i === si) return;
        dir = dir || (i > si ? 1 : -1);
        si = i;
        paintShow(i, dir);
    }

    if (show && slides.length) {
        const frame = $('#nvh-frame');
        const cur = $('#nvh-dcur');
        let x0 = null;
        frame.addEventListener('pointermove', (e) => {
            if (e.pointerType !== 'mouse') return;
            const r = frame.getBoundingClientRect();
            const overControl = e.target.closest('a, button');
            cur.style.left = `${e.clientX - r.left}px`;
            cur.style.top = `${e.clientY - r.top}px`;
            cur.classList.toggle('on', !overControl);
            frame.style.cursor = overControl ? 'pointer' : 'none';
        });
        frame.addEventListener('pointerleave', () => cur.classList.remove('on'));
        frame.addEventListener('pointerdown', (e) => {
            if (e.target.closest('a, button')) return;
            x0 = e.clientX;
            cur.classList.add('down');
        });
        addEventListener('pointerup', (e) => {
            cur.classList.remove('down');
            if (x0 === null) return;
            const dx = e.clientX - x0;
            x0 = null;
            const rtl = html.dir === 'rtl';
            let fwd;
            if (Math.abs(dx) > 40) {
                fwd = rtl ? dx > 0 : dx < 0;
            } else {
                const r = frame.getBoundingClientRect();
                const right = e.clientX > r.left + r.width / 2;
                fwd = rtl ? !right : right;
            }
            go(si + (fwd ? 1 : -1), fwd ? 1 : -1);
        });
        $('#nvh-next')?.addEventListener('click', () => go(si + 1, 1));
        $('#nvh-prev')?.addEventListener('click', () => go(si - 1, -1));
        thumbs.forEach((b) => b.addEventListener('click', () => go(+b.dataset.i)));
        addEventListener('keydown', (e) => {
            if (!['ArrowRight', 'ArrowLeft'].includes(e.key) || e.target.closest('input, select, textarea')) return;
            const r = show.getBoundingClientRect();
            if (r.top > innerHeight / 2 || r.bottom < innerHeight / 2) return;
            const rtl = html.dir === 'rtl';
            const fwd = (e.key === 'ArrowRight') !== rtl;
            go(si + (fwd ? 1 : -1), fwd ? 1 : -1);
        });
    }

    /* ---------------- Nashmi ---------------- */
    const bub = $('#nvh-bub');
    const tip = $('#nvh-tip');
    const TIPS = {
        top: ['Tell me the city and the number of guests, and I will find the space.', 'احكِ لي المدينة وعدد الضيوف وأنا أدوّر لك على المكان.'],
        recs: ['These spaces were just added. Tap ♡ to save one.', 'هاي مساحات أضيفت حديثًا. اضغط ♡ لتحفظ اللي يعجبك.'],
        venues: ['Drag the photos left or right to move between venues.', 'اسحب الصور يمين أو يسار لتتنقّل بين الأماكن.'],
        cities: ['Pick a city to see every space you can book there.', 'اختر مدينة لترى كل المساحات فيها.'],
        'event-types': ['Conference, workshop or dinner? Pick a type.', 'مؤتمر، ورشة، أو عشاء عمل؟ اختر النوع.'],
        cta: ['Ready? Tap me and say the city and the number of guests.', 'جاهز؟ اضغط عليّ وقل لي المدينة وعدد الضيوف.'],
    };
    let curTip = '';
    let bubTimer = null;
    const showBubble = (ms = 7000) => {
        bub.classList.remove('out');
        clearTimeout(bubTimer);
        if (ms) bubTimer = setTimeout(() => bub.classList.add('out'), ms);
    };
    function updateTip(force) {
        let k = 'top';
        for (const id of Object.keys(TIPS)) {
            const el = document.getElementById(id);
            if (el && el.getBoundingClientRect().top < innerHeight * 0.55) k = id;
        }
        if (k === curTip && !force) return;
        curTip = k;
        bub.classList.add('out');
        setTimeout(() => { tip.textContent = T(...TIPS[k]); showBubble(); }, isCalm() ? 0 : 350);
    }
    $('#nvh-nash').addEventListener('mouseenter', () => showBubble(0));
    $('#nvh-nash').addEventListener('mouseleave', () => showBubble(2500));
    const voiceStatus = $('#voice-status');
    if (voiceStatus && 'MutationObserver' in window) {
        new MutationObserver(() => { if (voiceStatus.textContent.trim()) showBubble(12000); })
            .observe(voiceStatus, { childList: true, characterData: true, subtree: true });
    }
    $$('[data-nv-voice]').forEach((b) => b.addEventListener('click', () => $('#voice-search')?.click()));

    /* ---------------- scroll: header, parallax ---------------- */
    const photos = $('[data-parallax="photos"]');
    const headline = $('[data-parallax="headline"]');
    const fog = $('[data-parallax="fog"]');
    const ctaImg = $('[data-parallax="cta"]');
    const tileImgs = $$('.nvh-tile img');
    let ticking = false;

    function onScroll() {
        ticking = false;
        const y = scrollY;
        const h = innerHeight;
        header?.classList.toggle('nv-solid', y > 40);
        // Below 1300px the hero search bar reaches the corner where Nashmi sits, so Nashmi waits until the hero scrolls away.
        $('#nvh-nash')?.classList.toggle('is-hero', innerWidth < 1300 && y < h * 0.55);
        $('#nvh-nash')?.classList.toggle('in-hero', y < h * 0.55);
        updateTip();
        if (isCalm()) return;
        if (y < h * 1.2) {
            if (photos) photos.style.transform = `translateY(${y * 0.35}px) scale(${1 + (y / h) * 0.08})`;
            if (headline) { headline.style.transform = `translateY(${y * 0.55}px)`; headline.style.opacity = String(Math.max(0, 1 - y / (h * 0.7))); }
            if (fog) fog.style.transform = `translateY(${-y * 0.25}px)`;
        }
        tileImgs.forEach((im) => {
            const r = im.parentElement.getBoundingClientRect();
            if (r.bottom < 0 || r.top > h) return;
            im.style.transform = `translateY(${(r.top + r.height / 2 - h / 2) * -0.08}px)`;
        });
        if (ctaImg) {
            const r = ctaImg.parentElement.getBoundingClientRect();
            if (r.top < h && r.bottom > 0) ctaImg.style.transform = `translateY(${r.top * 0.2}px) scale(1.1)`;
        }
    }
    addEventListener('scroll', () => { if (!ticking) { ticking = true; requestAnimationFrame(onScroll); } }, { passive: true });
    addEventListener('resize', () => requestAnimationFrame(onScroll));

    function resetMotion() {
        [photos, headline, fog, ctaImg, ...tileImgs].forEach((el) => { if (el) { el.style.transform = ''; } });
        if (headline) headline.style.opacity = '';
    }

    /* ---------------- calm toggle ---------------- */
    const calmBtn = $('#nvh-calm');
    function setCalm(on) {
        html.classList.toggle('nv-calm', on);
        html.classList.toggle('nv-motion', !on);
        calmBtn?.setAttribute('aria-pressed', String(on));
        if (on) resetMotion();
    }
    const savedCalm = store.get('convene-calm');
    if (savedCalm !== null) setCalm(savedCalm === '1');
    calmBtn?.addEventListener('click', () => {
        const on = !html.classList.contains('nv-calm');
        setCalm(on);
        store.set('convene-calm', on ? '1' : '0');
        onScroll();
    });

    /* ---------------- language ---------------- */
    const langBtn = $('#nvh-lang');
    const enHtml = new Map();
    $$('[data-en]', root).forEach((el) => enHtml.set(el, el.getAttribute('data-en')));
    const enPh = new Map();
    $$('[data-ph-ar]', root).forEach((el) => enPh.set(el, el.placeholder));
    const original = { lang: html.lang, dir: html.dir };

    function applyLang(next, first) {
        lang = next;
        const ar = lang === 'ar';
        html.lang = ar ? 'ar' : (original.lang || 'en');
        html.dir = ar ? 'rtl' : (original.dir || 'ltr');
        $$('[data-en]', root).forEach((el) => {
            const value = ar ? el.getAttribute('data-ar') : enHtml.get(el);
            if (value === null) return;
            if (el.tagName === 'OPTION') el.textContent = value; else el.innerHTML = value;
        });
        $$('[data-ph-ar]', root).forEach((el) => { el.placeholder = ar ? el.dataset.phAr : enPh.get(el); });
        const langField = $('#landing-lang');
        if (langField) langField.value = ar ? 'ar' : 'en';
        langBtn.textContent = ar ? 'EN' : 'ع';
        langBtn.setAttribute('lang', ar ? 'en' : 'ar');
        langBtn.setAttribute('aria-label', ar ? 'Switch to English' : 'التبديل إلى العربية');
        setHero(hi, true);
        paintShow(si);
        if (!first) { curTip = ''; updateTip(true); }
    }
    langBtn.addEventListener('click', () => {
        const next = lang === 'ar' ? 'en' : 'ar';
        store.set('convene-lang', next);
        applyLang(next);
    });

    /* ---------------- init ---------------- */
    applyLang(store.get('convene-lang') === 'ar' ? 'ar' : 'en', true);
    restartHero();
    onScroll();
    updateTip(true);
    reduceQuery.addEventListener?.('change', () => { if (isCalm()) resetMotion(); });
})();
