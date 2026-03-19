/* DemoBrand — soft language switch + form-state & inline-msg preserve (production, based on sandbox) */
(function () {
  if (window.__demoLangSoftNav) return;
  window.__demoLangSoftNav = 1;

  // --- tiny CSS guard (sandbox base + flag drag tweak) ---
  (function injectCSS() {
    if (document.getElementById('demo-lang-soft-nav-css')) return;
    const css = `
  html.demo-swap-freeze { contain: paint; }
  .demo-swap-overlay {
    position: fixed; inset: 0; pointer-events: none;
    background: rgba(0,0,0,0); z-index: 9999;
    will-change: opacity, transform; transform: translateZ(0);
  }
  /* keep the inline message area stable (no layout jump) */
  .inline-slot, .inline-msg { min-height: 24px; }

  /* prevent ghost drag on flags */
  .lang-switch img {
    -webkit-user-drag: none;
    user-select: none;
  }
`;
    const el = document.createElement('style');
    el.id = 'demo-lang-soft-nav-css';
    el.textContent = css;
    document.head.appendChild(el);
  })();

  function samePage(url) {
    try {
      const u = new URL(url, location.href);
      if (u.origin !== location.origin) return false;

      // Treat "/" and "/index.php" as equivalent (Access page vs "/?lang=…")
      const norm = (p) => (p || '/').replace(/\/index\.php$/i, '/');
      const curPath = norm(location.pathname);
      const targetPath = norm(u.pathname);
      return curPath === targetPath;
    } catch { return false; }
  }

  function pickSwapRoot(doc) {
    return doc.querySelector('.access-card')
        || doc.querySelector('.success-card')
        || doc.querySelector('main.layout');
  }

  // ---- form-state capture/restore (sandbox logic, slightly generalized) ----
  function getForm(root) {
    if (!root) return null;
    return root.querySelector('form#claimForm') || root.querySelector('form');
  }

  function captureFormState(root) {
    const form = getForm(root);
    if (!form) return null;

    const fields = [];
    const els = form.querySelectorAll('input, textarea, select');
    els.forEach(el => {
      if (!el.name) return;
      const tag = el.tagName.toLowerCase();
      const type = (el.type || '').toLowerCase();
      if (type === 'file') return; // skip files

      const item = { name: el.name, tag, type };

      if (tag === 'select') {
        if (el.multiple) {
          item.values = Array.from(el.options).filter(o => o.selected).map(o => o.value);
        } else {
          item.value = el.value;
        }
      } else if (type === 'checkbox' || type === 'radio') {
        item.value = el.value;
        item.checked = !!el.checked;
      } else {
        item.value = el.value;
      }
      fields.push(item);
    });

    // capture focus
    let focus = null;
    const ae = document.activeElement;
    if (ae && ae.name && (ae.tagName === 'INPUT' || ae.tagName === 'TEXTAREA')) {
      try {
        focus = {
          name: ae.name,
          start: ae.selectionStart ?? null,
          end: ae.selectionEnd ?? null
        };
      } catch { /* selection may fail on some types */ }
    }

    return { fields, focus };
  }

  function restoreFormState(root, state) {
    if (!state) return;
    const form = getForm(root);
    if (!form) return;

    // index saved items by name
    const byName = new Map();
    state.fields.forEach(f => {
      if (!byName.has(f.name)) byName.set(f.name, []);
      byName.get(f.name).push(f);
    });

    byName.forEach((items, name) => {
      const nodes = form.querySelectorAll(`[name="${CSS.escape(name)}"]`);
      if (!nodes.length) return;

      // reset radios/checkboxes first
      nodes.forEach(n => {
        if (n.type === 'radio' || n.type === 'checkbox') n.checked = false;
      });

      items.forEach(f => {
        nodes.forEach(n => {
          const tag = n.tagName.toLowerCase();
          const type = (n.type || '').toLowerCase();

          if (tag === 'select') {
            if (n.multiple && Array.isArray(f.values)) {
              Array.from(n.options).forEach(o => { o.selected = f.values.includes(o.value); });
            } else if (typeof f.value === 'string') {
              n.value = f.value;
            }
          } else if (type === 'radio' || type === 'checkbox') {
            if (n.value === f.value) n.checked = !!f.checked;
          } else if (type !== 'file') {
            if (typeof f.value === 'string') n.value = f.value;
          }
        });
      });
    });

    // restore focus if possible
    if (state.focus && state.focus.name) {
      const n = form.querySelector(`[name="${CSS.escape(state.focus.name)}"]`);
      if (n && (n.tagName === 'INPUT' || n.tagName === 'TEXTAREA')) {
        n.focus();
        try {
          if (state.focus.start != null && state.focus.end != null) {
            n.setSelectionRange(state.focus.start, state.focus.end);
          }
        } catch {}
      }
    }
  }

  // --- re-bind input behaviors on the new DOM after a swap ---
  function initFormEnhancements(root) {
    const form = getForm(root);
    if (!form) return;

    // 1) "has-value" accent
    const mark = (el) => {
      if (!el) return;
      if ((el.type || '').toLowerCase() === 'file') return;
      const val = (el.value ?? '').toString();
      if (val.length) el.classList.add('has-value');
      else el.classList.remove('has-value');
    };

    form.querySelectorAll('input, textarea, select').forEach((el) => {
      mark(el);
      if (!el.dataset.demoHasValueInit) {
        el.addEventListener('input', () => mark(el));
        el.addEventListener('change', () => mark(el));
        el.dataset.demoHasValueInit = '1';
      }
    });

    // 2) Secret Key Code → force A–Z, 0–9, uppercase
    const keyEl = form.querySelector('[name="key_code"]');
    if (keyEl && !keyEl.dataset.demoUpperInit) {
      const toUpperAZ09 = () => {
        const v = (keyEl.value || '').toString();
        keyEl.value = v.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
        mark(keyEl); // keep accent in sync
      };
      keyEl.addEventListener('input', toUpperAZ09);
      keyEl.addEventListener('change', toUpperAZ09);
      // normalize once on init too
      toUpperAZ09();
      keyEl.dataset.demoUpperInit = '1';
    }
  }

  // --- Inline error / cooldown message helpers (sandbox) ---
  const INLINE_MSG_MAP = {
    en: {
      all_required: 'Please fill all required fields.',
      email: 'Please enter a valid email address.',
      secret: 'Invalid or missing Secret Key Code, please try again.',
      cooldown: (n) => `You can resubmit your form in ${n} minutes.`
    },
    de: {
      all_required: 'Bitte fülle alle Pflichtfelder aus.',
      email: 'Bitte gib eine gültige E-Mail-Adresse ein.',
      secret: 'Ungültiger oder fehlender geheimer Schlüsselcode. Bitte versuche es erneut.',
      cooldown: (n) => `Du kannst das Formular in ${n} Minuten erneut senden.`
    }
  };

  function classifyInlineMsg(text) {
    if (!text) return { code: null, minutes: null, text: '' };
    const t = text.trim();
    const en = INLINE_MSG_MAP.en;
    const de = INLINE_MSG_MAP.de;

    if (t === en.all_required || t === de.all_required) {
      return { code: 'all_required', minutes: null, text: t };
    }
    if (t === en.email || t === de.email) {
      return { code: 'email', minutes: null, text: t };
    }
    if (t === en.secret || t === de.secret) {
      return { code: 'secret', minutes: null, text: t };
    }

    // cooldown – EN
    let m = t.match(/You can resubmit your form in\s+(\d+)\s+minutes/i);
    if (m) {
      const n = parseInt(m[1], 10);
      return { code: 'cooldown', minutes: isNaN(n) ? null : n, text: t };
    }
    // cooldown – DE
    m = t.match(/Du kannst das Formular in\s+(\d+)\s+Minuten erneut senden/i);
    if (m) {
      const n = parseInt(m[1], 10);
      return { code: 'cooldown', minutes: isNaN(n) ? null : n, text: t };
    }

    // unknown text → just keep it as-is
    return { code: null, minutes: null, text: t };
  }

  function renderInlineMsg(lang, state) {
    if (!state || (!state.code && !state.text)) return '';
    const shortLang = (lang || 'en').slice(0, 2);
    const table = INLINE_MSG_MAP[shortLang] || INLINE_MSG_MAP.en;

    if (state.code === 'cooldown') {
      const fn = table.cooldown;
      if (typeof fn === 'function') {
        const n = state.minutes != null ? state.minutes : 0;
        return fn(n);
      }
    }

    if (state.code && typeof table[state.code] === 'string') {
      return table[state.code];
    }

    // fallback: unknown message → keep original text
    return state.text || '';
  }

  async function softSwap(urlStr) {
    const url = new URL(urlStr, location.href);
    const requestedLang = url.searchParams.get('lang') || null;

    // capture before swap
    const curRoot = pickSwapRoot(document);
    const preserved = captureFormState(curRoot);

    // capture current inline message (if any)
    let inlineState = null;
    if (curRoot) {
      const slot = curRoot.querySelector('.inline-slot');
      if (slot) {
        const msgEl = slot.querySelector('.inline-msg');
        const rawText = msgEl ? msgEl.textContent : slot.textContent;
        if (rawText && rawText.trim().length) {
          inlineState = classifyInlineMsg(rawText);
        }
      }
    }

    // freeze paint
    document.documentElement.classList.add('demo-swap-freeze');
    const ov = document.createElement('div');
    ov.className = 'demo-swap-overlay';
    document.body.appendChild(ov);

    try {
      const res = await fetch(url.href, { credentials: 'same-origin', cache: 'no-store' });
      if (!res.ok) throw new Error(String(res.status));
      const html = await res.text();

      const parser = new DOMParser();
      const nextDoc = parser.parseFromString(html, 'text/html');

      const nextRoot = pickSwapRoot(nextDoc);
      if (!nextRoot || !curRoot) throw new Error('swap-root-missing');

      // swap the card / main content
      curRoot.replaceWith(nextRoot);

      // ---- ALSO sync Access-page processing overlay text (prevents EN/DE mismatch) ----
      const curOv = document.getElementById('processingOverlay');
      const nextOv = nextDoc.getElementById('processingOverlay');
      if (curOv && nextOv) {
        const curH2 = curOv.querySelector('.processing-card h2');
        const curP  = curOv.querySelector('.processing-card p');
        const nextH2 = nextOv.querySelector('.processing-card h2');
        const nextP  = nextOv.querySelector('.processing-card p');
        if (curH2 && nextH2) curH2.textContent = nextH2.textContent;
        if (curP  && nextP)  curP.textContent  = nextP.textContent;
      }

      // restore inline message in the target language (if we had one)
      if (inlineState && (inlineState.code || inlineState.text)) {
        const slot = nextRoot.querySelector('.inline-slot');
        if (slot) {
          let msgEl = slot.querySelector('.inline-msg');
          if (!msgEl) {
            msgEl = document.createElement('div');
            msgEl.className = 'inline-msg';
            slot.appendChild(msgEl);
          }

          const nextLangAttr =
            nextDoc.documentElement.getAttribute('lang') ||
            requestedLang ||
            document.documentElement.getAttribute('lang') ||
            'en';

          msgEl.textContent = renderInlineMsg(nextLangAttr, inlineState);
        }
      }

      // restore form state and enhancements
      restoreFormState(nextRoot, preserved);
      initFormEnhancements(nextRoot);

      // sync lang/title and push URL
      const nextLang = nextDoc.documentElement.getAttribute('lang');
      if (nextLang) document.documentElement.setAttribute('lang', nextLang);
      if (nextDoc.title) document.title = nextDoc.title;
      history.pushState(null, '', url.href);
    } catch (err) {
      console.error('[DemoBrand soft-nav preserve] swap failed', err);
      location.href = url.href; // safe fallback
    } finally {
      requestAnimationFrame(() => {
        document.documentElement.classList.remove('demo-swap-freeze');
        if (ov && ov.parentNode) ov.parentNode.removeChild(ov);
      });
    }
  }

  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href]');
    if (!a) return;
    let url; try { url = new URL(a.href, location.href); } catch { return; }
    if (!samePage(url)) return;
    if (!url.searchParams.has('lang')) return;

    e.preventDefault();
    e.stopPropagation();
    requestAnimationFrame(() => { softSwap(url.href); });
  }, true);

  // Prevent ghost-drag on language flags (production-only tweak)
  document.addEventListener('dragstart', (e) => {
    const a = e.target.closest('.lang-switch a');
    if (!a) return;
    e.preventDefault();
  }, true);

  window.addEventListener('popstate', () => {
    // optional: resync pressed state if used
  });
})();
