/* ============================================================
   CAF Management System — admin shell behaviour

   The admin is a server-rendered multi-page app: navigation,
   tables and charts come from Blade. This file only supplies the
   progressive enhancements the stylesheet expects.

   Server-supplied configuration is read from a JSON island:
     <script type="application/json" id="caf-admin-config">…</script>
   ============================================================ */

/* ---- icons ---- */
const ICONS = {
  grid:'<rect x="3" y="3" width="7.5" height="7.5" rx="2"/><rect x="13.5" y="3" width="7.5" height="7.5" rx="2"/><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="2"/><rect x="3" y="13.5" width="7.5" height="7.5" rx="2"/>',
  file:'<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6"/><path d="M9 17h4"/>',
  star:'<path d="m12 2.6 2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.4 6.2 20.5l1.1-6.5-4.7-4.6 6.5-.9z"/>',
  card:'<rect x="2" y="5" width="20" height="14" rx="2.5"/><path d="M2 10h20"/><path d="M6 15h3"/>',
  message:'<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
  chart:'<path d="M3 3v18h18"/><path d="M7 16v-5"/><path d="M12 16V8"/><path d="M17 16v-8"/>',
  layers:'<path d="M12 2 2 7l10 5 10-5-10-5z"/><path d="m2 17 10 5 10-5"/><path d="m2 12 10 5 10-5"/>',
  users:'<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
  shield:'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
  sliders:'<path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M1 14h6"/><path d="M9 8h6"/><path d="M17 16h6"/>',
  search:'<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
  bell:'<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>',
  sun:'<circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.9 4.9 1.4 1.4"/><path d="m17.7 17.7 1.4 1.4"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m4.9 19.1 1.4-1.4"/><path d="m17.7 6.3 1.4-1.4"/>',
  moon:'<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>',
  chevron:'<path d="m6 9 6 6 6-6"/>',
  plus:'<path d="M12 5v14"/><path d="M5 12h14"/>',
  x:'<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
  check:'<path d="M20 6 9 17l-5-5"/>',
  filter:'<path d="M22 3H2l8 9.5V19l4 2v-8.5z"/>',
  download:'<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
  clock:'<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
  alert:'<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
  trend:'<path d="m22 7-8.5 8.5-5-5L2 17"/><path d="M16 7h6v6"/>',
  arrowUp:'<path d="M12 19V5"/><path d="m5 12 7-7 7 7"/>',
  arrowDown:'<path d="M12 5v14"/><path d="m19 12-7 7-7-7"/>',
  send:'<path d="m22 2-7 20-4-9-9-4z"/><path d="M22 2 11 13"/>',
  eye:'<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>',
  music:'<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>',
  refresh:'<path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/>',
  globe:'<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a15 15 0 0 1 0 18a15 15 0 0 1 0-18z"/>',
  tag:'<path d="M20.6 13.4 12 22l-9-9V3h10z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
  edit:'<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4z"/>',
  play:'<path d="m5 3 14 9-14 9z"/>',
  calendar:'<rect x="3" y="4" width="18" height="18" rx="2.5"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>',
  lock:'<rect x="3" y="11" width="18" height="11" rx="2.5"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
  zap:'<path d="M13 2 3 14h8l-1 8 10-12h-8z"/>'
};

function icon(name, cls = 'ico') {
  return `<svg class="${cls}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ICONS[name] || ''}</svg>`;
}

/* ---- helpers ---- */
const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
const store = {
  get(key, fallback = null) {
    try {
      const value = localStorage.getItem(key);
      return value === null ? fallback : value;
    } catch (e) {
      return fallback;
    }
  },
  set(key, value) {
    try {
      localStorage.setItem(key, value);
    } catch (e) {
      /* private browsing — preferences simply do not persist */
    }
  },
};

const config = (() => {
  const el = $('#caf-admin-config');

  return el ? JSON.parse(el.textContent) : { commands: [], theme: 'light' };
})();

const state = {
  theme: store.get('caf-theme', config.theme || 'light'),
  collapsed: store.get('caf-sidebar', '0') === '1',
  cmdkIndex: 0,
  cmdkItems: [],
};

/* ============================================================
   THEME
   ============================================================ */
function applyTheme() {
  document.documentElement.setAttribute('data-theme', state.theme);

  const ic = $('#themeIcon');
  if (ic) {
    ic.innerHTML = state.theme === 'dark' ? ICONS.sun : ICONS.moon;
  }

  store.set('caf-theme', state.theme);
}

function toggleTheme() {
  state.theme = state.theme === 'dark' ? 'light' : 'dark';
  applyTheme();
  toast(state.theme === 'dark' ? 'Dark theme enabled' : 'Light theme enabled', 'info');
}

/* ============================================================
   TOASTS
   ============================================================ */
function toast(msg, tone = '') {
  const host = $('#toasts');

  if (!host) {
    return;
  }

  const el = document.createElement('div');
  el.className = 'toast ' + tone;
  el.innerHTML = `${icon(tone === 'danger' || tone === 'warn' ? 'alert' : tone === 'info' ? 'zap' : 'check')}<span>${msg}</span>`;
  host.appendChild(el);

  setTimeout(() => {
    el.style.transition = 'opacity .3s, transform .3s';
    el.style.opacity = '0';
    el.style.transform = 'translateX(16px)';
    setTimeout(() => el.remove(), 300);
  }, 2600);
}

function playFlashToasts() {
  const flashes = config.flashes || [];

  flashes.forEach((flash, i) => {
    setTimeout(() => toast(flash.message, flash.tone), i * 260);
  });
}

/* ============================================================
   SIDEBAR
   ============================================================ */
function openSidebar() {
  $('#sidebar')?.classList.add('open');
  $('#scrim')?.classList.add('show');
}

function closeSidebar() {
  $('#sidebar')?.classList.remove('open');
  $('#scrim')?.classList.remove('show');
}

function setCollapsed(collapsed) {
  document.body.classList.toggle('collapsed', collapsed);
  store.set('caf-sidebar', collapsed ? '1' : '0');
}

/* ============================================================
   DROPDOWNS
   ============================================================ */
function closeDropdowns() {
  $$('.dd.show').forEach((el) => el.classList.remove('show'));
  $$('[data-dd-trigger][aria-expanded="true"]').forEach((el) => el.setAttribute('aria-expanded', 'false'));
}

function openDropdown(dd, trigger) {
  const anchor = trigger.getBoundingClientRect();
  dd.classList.add('show');
  trigger.setAttribute('aria-expanded', 'true');

  if (dd.dataset.align === 'right') {
    dd.style.left = 'auto';
    dd.style.right = '14px';
    dd.style.top = (anchor.bottom + 8) + 'px';
  } else {
    dd.style.top = (anchor.bottom + 8) + 'px';
    dd.style.left = Math.min(anchor.left, window.innerWidth - dd.offsetWidth - 12) + 'px';
  }
}

/* ============================================================
   COMMAND PALETTE
   ============================================================ */
function allCommands() {
  return config.commands || [];
}

function openCmdk() {
  const modal = $('#cmdk');

  if (!modal) {
    return;
  }

  modal.classList.add('show');
  state.cmdkItems = allCommands();
  state.cmdkIndex = 0;
  renderCmdk();

  const inp = $('#cmdkInput');
  inp.value = '';
  setTimeout(() => inp.focus(), 40);
}

function closeCmdk() {
  $('#cmdk')?.classList.remove('show');
}

function renderCmdk() {
  const list = $('#cmdkList');

  if (!list) {
    return;
  }

  if (!state.cmdkItems.length) {
    list.innerHTML = '<div class="cmdk-empty">No results found</div>';
    return;
  }

  let html = '';
  let lastGroup = '';

  state.cmdkItems.forEach((c, i) => {
    if (c.group !== lastGroup) {
      html += `<div class="cmdk-group">${c.group}</div>`;
      lastGroup = c.group;
    }

    html += `<button type="button" class="cmdk-item ${i === state.cmdkIndex ? 'sel' : ''}" data-idx="${i}">
      ${icon(c.icon)}
      <span>${c.label}</span>
      ${c.url ? '<span class="hint">↵</span>' : ''}
    </button>`;
  });

  list.innerHTML = html;

  $$('.cmdk-item', list).forEach((el) => {
    el.addEventListener('click', () => runCmdk(+el.dataset.idx));
  });
}

function runCmdk(i) {
  const command = state.cmdkItems[i];

  if (!command) {
    return;
  }

  closeCmdk();

  if (command.url) {
    window.location.href = command.url;
    return;
  }

  if (command.method && command.action) {
    const form = document.createElement('form');
    form.method = 'post';
    form.action = command.action;
    form.innerHTML = `<input type="hidden" name="_token" value="${config.csrf}">`;
    document.body.appendChild(form);
    form.submit();
    return;
  }

  toast(command.message || 'Done', 'info');
}

/* ============================================================
   TABS
   ============================================================ */
function bindTabs() {
  $$('[data-tabs]').forEach((group) => {
    const name = group.dataset.tabs;

    $$('[data-tab]', group).forEach((tab) => {
      tab.addEventListener('click', () => {
        const target = tab.dataset.tab;

        $$('[data-tab]', group).forEach((t) => t.classList.toggle('sel', t === tab));
        $$(`[data-tabpanel][data-tabpanel-group="${name}"]`).forEach((panel) => {
          panel.hidden = panel.dataset.tabpanel !== target;
        });

        try {
          const url = new URL(window.location.href);
          url.searchParams.set('tab', target);
          window.history.replaceState({}, '', url);
        } catch (e) {
          /* history is unavailable in some embedded contexts */
        }
      });
    });
  });
}

/* ============================================================
   BULK SELECT
   ============================================================ */
function bindBulkSelect() {
  const master = $('[data-checkall]');

  if (!master) {
    return;
  }

  const form = master.closest('form');
  const boxes = () => $$('[data-bulk-row]', form);
  const counter = $('[data-bulk-count]');

  const sync = () => {
    const all = boxes();
    const checked = all.filter((b) => b.checked);
    master.checked = all.length > 0 && checked.length === all.length;
    master.indeterminate = checked.length > 0 && checked.length < all.length;

    if (counter) {
      counter.textContent = checked.length;
    }

    form?.querySelectorAll('[data-bulk-when-selected]').forEach((el) => {
      el.hidden = checked.length === 0;
    });
  };

  master.addEventListener('change', () => {
    boxes().forEach((b) => { b.checked = master.checked; });
    sync();
  });

  boxes().forEach((b) => b.addEventListener('change', sync));
  sync();
}

/* ============================================================
   CONFIRMATIONS
   ============================================================ */
function bindConfirms() {
  document.addEventListener('submit', (e) => {
    const message = e.target.dataset.confirm;

    if (message && !window.confirm(message)) {
      e.preventDefault();
    }
  });

  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-confirm]');

    if (trigger && trigger.tagName === 'A' && !window.confirm(trigger.dataset.confirm)) {
      e.preventDefault();
    }
  });
}

/* ============================================================
   MODALS
   ============================================================ */
function bindModals() {
  document.addEventListener('click', (e) => {
    const opener = e.target.closest('[data-modal-open]');

    if (opener) {
      e.preventDefault();
      const modal = $(`#${CSS.escape(opener.dataset.modalOpen)}`);
      modal?.classList.add('show');
      modal?.querySelector('input,select,textarea,button')?.focus();
      return;
    }

    if (e.target.closest('[data-modal-close]')) {
      e.preventDefault();
      e.target.closest('.modal')?.classList.remove('show');
      return;
    }

    const modal = e.target.closest('.modal.show');

    if (modal && e.target === modal) {
      modal.classList.remove('show');
    }
  });
}

/* ============================================================
   FILTER FIELDS
   ============================================================ */
function bindFilters() {
  $$('[data-filter-for]').forEach((input) => {
    const target = $(`#${CSS.escape(input.dataset.filterFor)}`);

    if (!target) {
      return;
    }

    input.addEventListener('input', () => {
      const q = input.value.toLowerCase().trim();
      let visible = 0;

      $$('[data-filter-row]', target).forEach((row) => {
        const hit = !q || row.textContent.toLowerCase().includes(q);
        row.hidden = !hit;

        if (hit) {
          visible++;
        }
      });

      const empty = $('[data-filter-empty]', target);
      if (empty) {
        empty.hidden = visible > 0;
      }
    });
  });

  $$('[data-submit-on-change]').forEach((el) => {
    el.addEventListener('change', () => el.form?.requestSubmit());
  });
}

/* ============================================================
   BOOT
   ============================================================ */
function boot() {
  applyTheme();
  setCollapsed(state.collapsed);

  $('#themeBtn')?.addEventListener('click', toggleTheme);
  $('#menuBtn')?.addEventListener('click', openSidebar);
  $('#scrim')?.addEventListener('click', closeSidebar);
  $('#collapseBtn')?.addEventListener('click', () => setCollapsed(!document.body.classList.contains('collapsed')));
  $('#searchBtn')?.addEventListener('click', openCmdk);

  $('#cmdk')?.addEventListener('click', (e) => {
    if (e.target === $('#cmdk')) {
      closeCmdk();
    }
  });

  $('#cmdkInput')?.addEventListener('input', (e) => {
    const q = e.target.value.toLowerCase().trim();

    state.cmdkItems = allCommands().filter((c) =>
      c.label.toLowerCase().includes(q) || (c.group || '').toLowerCase().includes(q),
    );
    state.cmdkIndex = 0;
    renderCmdk();
  });

  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-dd-trigger]');

    if (trigger) {
      e.stopPropagation();
      const dd = $(`#${CSS.escape(trigger.dataset.ddTrigger)}`);
      const wasOpen = dd?.classList.contains('show');
      closeDropdowns();

      if (dd && !wasOpen) {
        openDropdown(dd, trigger);
      }

      return;
    }

    if (!e.target.closest('.dd')) {
      closeDropdowns();
    }

    if (window.innerWidth < 1024 && e.target.closest('#nav a')) {
      closeSidebar();
    }
  });

  document.addEventListener('keydown', (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      $('#cmdk')?.classList.contains('show') ? closeCmdk() : openCmdk();
      return;
    }

    if (e.key === 'Escape') {
      closeCmdk();
      closeDropdowns();

      if (window.innerWidth < 1024) {
        closeSidebar();
      }

      return;
    }

    if (!$('#cmdk')?.classList.contains('show')) {
      return;
    }

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      state.cmdkIndex = Math.min(state.cmdkIndex + 1, state.cmdkItems.length - 1);
      renderCmdk();
    }

    if (e.key === 'ArrowUp') {
      e.preventDefault();
      state.cmdkIndex = Math.max(state.cmdkIndex - 1, 0);
      renderCmdk();
    }

    if (e.key === 'Enter') {
      e.preventDefault();
      runCmdk(state.cmdkIndex);
    }
  });

  window.addEventListener('resize', () => closeDropdowns());

  bindTabs();
  bindBulkSelect();
  bindConfirms();
  bindModals();
  bindFilters();
  playFlashToasts();
}

document.addEventListener('DOMContentLoaded', boot);
