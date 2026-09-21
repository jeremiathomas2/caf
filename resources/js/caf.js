(function(){
  'use strict';

  /* ============================================================
     Header: mobile menu, shadow, progress bar
     ============================================================ */
  var header = document.getElementById('siteHeader');
  var menuBtn = document.getElementById('menuBtn');
  var nav = document.getElementById('primaryNav');

  if (menuBtn && nav) {
    menuBtn.addEventListener('click', function(){
      var open = nav.classList.toggle('open');
      menuBtn.setAttribute('aria-expanded', String(open));
      menuBtn.textContent = open ? 'Close' : 'Menu';
    });
    nav.addEventListener('click', function(e){
      if (e.target.tagName === 'A' && window.innerWidth <= 860) {
        nav.classList.remove('open');
        menuBtn.setAttribute('aria-expanded','false');
        menuBtn.textContent = 'Menu';
      }
    });
  }

  var backTop = document.getElementById('backTop');
  var ticking = false;

  if (header) {
    var progress = document.createElement('div');
    progress.className = 'progress';
    progress.setAttribute('aria-hidden','true');
    header.appendChild(progress);

    window.addEventListener('scroll', function(){
      if (!ticking) { ticking = true; requestAnimationFrame(function(){
        var y = window.scrollY;
        header.classList.toggle('scrolled', y > 8);
        var max = document.documentElement.scrollHeight - window.innerHeight;
        progress.style.transform = 'scaleX(' + (max > 0 ? Math.min(1, y / max) : 0) + ')';
        if (backTop) backTop.classList.toggle('show', y > 700);
        ticking = false;
      }); }
    }, {passive:true});
  }

  if (backTop) {
    backTop.addEventListener('click', function(){
      window.scrollTo({top:0, behavior:'smooth'});
    });
  }

  /* ============================================================
     Theme toggle
     ============================================================ */
  var themeBtn = document.getElementById('themeBtn');
  if (themeBtn) {
    themeBtn.addEventListener('click', function(){
      var current = document.documentElement.getAttribute('data-theme');
      var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      var next;
      if (!current) next = prefersDark ? 'light' : 'dark';
      else next = current === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', next);
      try { localStorage.setItem('caf-theme', next); } catch(e){}
    });
  }

  /* ============================================================
     Language toggle (stub — swaps a few strings)
     ============================================================ */
  var langBtn = document.getElementById('langBtn');
  if (langBtn) {
    var lang = 'EN';
    langBtn.addEventListener('click', function(){
      lang = lang === 'EN' ? 'SW' : 'EN';
      langBtn.textContent = lang;
      document.documentElement.lang = lang === 'EN' ? 'en' : 'sw';
      // In production this would swap all copy via i18n layer.
    });
  }

  /* ============================================================
     Countdown
     ============================================================ */
  var countdownLabel = document.querySelector('.countdown-label');
  var daysEl = document.querySelector('[data-cd="days"]');
  var hoursEl = document.querySelector('[data-cd="hours"]');
  var minsEl = document.querySelector('[data-cd="mins"]');
  var secsEl = document.querySelector('[data-cd="secs"]');

  if (daysEl) {
    var REG_CLOSE = new Date('2026-10-30T23:59:59+03:00').getTime();
    var EVENT_DATE = new Date('2027-07-17T09:00:00+03:00').getTime();

    function pad(n){ return String(n).padStart(2,'0'); }

    function tick(){
      var now = Date.now();
      var target = REG_CLOSE;
      var label = 'Registration closes in';
      if (now > REG_CLOSE) {
        target = EVENT_DATE;
        label = 'Season 2 begins in';
      }
      var diff = Math.max(0, target - now);
      daysEl.textContent = Math.floor(diff / 86400000);
      hoursEl.textContent = pad(Math.floor((diff % 86400000) / 3600000));
      minsEl.textContent = pad(Math.floor((diff % 3600000) / 60000));
      secsEl.textContent = pad(Math.floor((diff % 60000) / 1000));
      if (countdownLabel) countdownLabel.textContent = label;
    }
    tick();
    setInterval(tick, 1000);
  }

  /* ============================================================
     Slideshows — crossfade with Ken Burns zoom
     ============================================================ */
  function crossfadeSlideshow(selector, interval){
    var slides = document.querySelectorAll(selector);
    if (slides.length < 2) return;
    var idx = 0;

    function sfxPrefetch(i){
      var img = new Image();
      img.src = slides[i].src;
    }

    function sfxShow(i){
      sfxPrefetch((i + 1) % slides.length);
      slides[i].classList.add('active');
      if (i !== idx) slides[idx].classList.remove('active');
      idx = i;
    }

    sfxPrefetch(idx);
    sfxPrefetch(1);
    if (!slides[0].classList.contains('active')) slides[0].classList.add('active');

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    setInterval(function(){
      requestAnimationFrame(function(){
        var next = (idx + 1) % slides.length;
        if (slides[next].complete) {
          sfxShow(next);
        } else {
          slides[next].addEventListener('load', function onLoad(){
            slides[next].removeEventListener('load', onLoad);
            sfxShow(next);
          }, {once:true});
        }
      });
    }, interval);
  }

  crossfadeSlideshow('.hero-slide', 4000);
  crossfadeSlideshow('.poster', 5000);

  /* ============================================================
     Group filters
     ============================================================ */
  var chips = document.querySelectorAll('.chip');
  var cards = document.querySelectorAll('#groupGrid .group-card');
  var count = document.getElementById('groupCount');

  if (chips.length && cards.length && count) {
    chips.forEach(function(chip){
      chip.addEventListener('click', function(){
        var f = chip.getAttribute('data-filter');
        chips.forEach(function(c){ c.setAttribute('aria-pressed', String(c === chip)); });
        var shown = 0;
        cards.forEach(function(card){
          var tags = card.getAttribute('data-tags').split(' ');
          var match = f === 'all' || tags.indexOf(f) !== -1;
          card.hidden = !match;
          if (match) shown++;
        });
        count.textContent = f === 'all'
          ? 'Showing all ' + shown + ' groups'
          : 'Showing ' + shown + (shown === 1 ? ' group' : ' groups');
      });
    });
  }

  /* ============================================================
     Scroll reveal
     ============================================================ */
  if (document.documentElement.classList.contains('js') && 'IntersectionObserver' in window) {
    var groups = [
      ['.section-head', 1, 0],
      ['.benefits .benefit', 3, 70],
      ['.steps .step', 3, 70],
      ['.groups .group-card', 3, 80],
      ['.season-teaser', 1, 0],
      ['.impact', 1, 0],
      ['.day', 1, 0],
      ['.sponsors', 1, 0],
      ['.testimonials .quote', 3, 90],
      ['.cta', 1, 0]
    ];
    groups.forEach(function(g){
      document.querySelectorAll(g[0]).forEach(function(node, i){
        node.classList.add('reveal');
        node.style.setProperty('--d', (i % g[1]) * g[2] + 'ms');
      });
    });
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(en){
        if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
      });
    }, {threshold: 0.1, rootMargin: '0px 0px -6% 0px'});
    document.querySelectorAll('.reveal').forEach(function(n){ io.observe(n); });
  }

  /* ============================================================
     FAQ — only one open at a time
     ============================================================ */
  document.querySelectorAll('details').forEach(function(d){
    d.addEventListener('toggle', function(){
      if (d.open) {
        document.querySelectorAll('details[open]').forEach(function(other){
          if (other !== d) other.open = false;
        });
      }
    });
  });

  /* ============================================================
     Register form — Singers / Others role toggle
     ============================================================ */
  var roleBtns = document.querySelectorAll('.role-btn');
  var catSelect = document.getElementById('category');
  var activeRole = 'singers';
  var CATS = {
    singers: ['A cappella groups','Gospel groups','Vocal ensembles','Choirs where appropriate','Individual vocal artists','Worship teams','Guest artists','Cultural performers'],
    others: ['Volunteers','Sponsors','Partners','Media / content creators']
  };
  var membersLabel = document.getElementById('membersLabel');
  var membersInput = document.getElementById('members');

  function applyRole(role) {
    activeRole = role;
    var opts = CATS[role] || CATS.singers;
    catSelect.innerHTML = '';
    opts.forEach(function(o){
      var opt = document.createElement('option');
      opt.textContent = o;
      catSelect.appendChild(opt);
    });
    roleBtns.forEach(function(b){
      var on = b.getAttribute('data-role') === role;
      b.classList.toggle('active', on);
      b.setAttribute('aria-pressed', String(on));
    });
    if (membersLabel && membersInput) {
      var others = role === 'others';
      membersLabel.textContent = others ? 'Number of people' : 'Number of members';
      membersInput.min = others ? 1 : 3;
      membersInput.max = others ? 100 : 12;
    }
  }

  if (roleBtns.length && catSelect) {
    roleBtns.forEach(function(b){
      b.addEventListener('click', function(){
        applyRole(b.getAttribute('data-role'));
      });
    });
  }

  /* ============================================================
     Enquiry form (mailto stub — wire to backend in production)
     ============================================================ */
  var form = document.getElementById('enquiryForm');
  var status = document.getElementById('formStatus');

  if (form && status) {
    var TO = 'hello@cultureacapellafestival.org';

    form.addEventListener('submit', function(e){
      e.preventDefault();
      var name = form.fname.value.trim();
      var email = form.email.value.trim();
      var group = form.group.value.trim();
      var link = form.link.value.trim();

      status.classList.remove('success','error');

      if (!name || !group) {
        status.textContent = 'Please add your name and your group name.';
        status.classList.add('error');
        (name ? form.group : form.fname).focus();
        return;
      }
      if (!/^\S+@\S+\.\S+$/.test(email)) {
        status.textContent = 'Please enter a valid email address so we can reply.';
        status.classList.add('error');
        form.email.focus();
        return;
      }
      if (!/^https?:\/\/.+/.test(link)) {
        status.textContent = 'Please paste a link to a recent performance (starting with http).';
        status.classList.add('error');
        form.link.focus();
        return;
      }
      if (form.terms && !form.terms.checked) {
        status.textContent = 'Please accept the CAF terms & conditions to continue.';
        status.classList.add('error');
        form.terms.focus();
        return;
      }

      var subject = 'CAF Season 2 enquiry — ' + group;
      var body = [
        'Group leader: ' + name,
        'Email: ' + email,
        'Group name: ' + group,
        'Type: ' + activeRole + (activeRole === 'singers' ? ' (singing)' : ' (non-singing)'),
        'Category: ' + form.category.value,
        'Members: ' + form.members.value,
        'Performance link: ' + link,
        '',
        form.msg.value.trim()
      ].join('\n');

      try {
        window.location.href = 'mailto:' + TO
          + '?subject=' + encodeURIComponent(subject)
          + '&body=' + encodeURIComponent(body);
      } catch(err) {}

      status.textContent = 'Thanks, ' + name.split(' ')[0] + '. Your email app should open with your enquiry ready to send. If not, write to ' + TO + '.';
      status.classList.add('success');
    });
  }

  /* ============================================================
     Cookie banner
     ============================================================ */
  var banner = document.getElementById('cookieBanner');
  var acceptBtn = document.getElementById('cookieAccept');
  var declineBtn = document.getElementById('cookieDecline');
  var KEY = 'caf-cookie-consent';

  if (banner) {
    var consent = null;
    try { consent = localStorage.getItem(KEY); } catch(e){}

    if (!consent) {
      setTimeout(function(){ banner.classList.add('show'); }, 1200);
    }
    if (acceptBtn && declineBtn) {
      acceptBtn.addEventListener('click', function(){
        try { localStorage.setItem(KEY, 'all'); } catch(e){}
        banner.classList.remove('show');
        // In production: load analytics here
      });
      declineBtn.addEventListener('click', function(){
        try { localStorage.setItem(KEY, 'essential'); } catch(e){}
        banner.classList.remove('show');
      });
    }
  }

  /* ============================================================
     Scrollspy for in-page hash links (single-page mode only)
     ============================================================ */
  var navLinks = document.querySelectorAll('.nav a[href^="#"]');
  var linkById = {};
  navLinks.forEach(function(a){ linkById[a.getAttribute('href').slice(1)] = a; });
  if (navLinks.length && 'IntersectionObserver' in window) {
    var spy = new IntersectionObserver(function(entries){
      entries.forEach(function(en){
        if (en.isIntersecting && linkById[en.target.id]) {
          navLinks.forEach(function(a){ a.removeAttribute('aria-current'); });
          linkById[en.target.id].setAttribute('aria-current','true');
        }
      });
    }, {rootMargin:'-40% 0px -55% 0px'});
    Object.keys(linkById).forEach(function(id){
      var s = document.getElementById(id);
      if (s) spy.observe(s);
    });
  }

  /* ============================================================
     Footer year
     ============================================================ */
  var yearEl = document.getElementById('year');
  if (yearEl) yearEl.textContent = new Date().getFullYear();

})();