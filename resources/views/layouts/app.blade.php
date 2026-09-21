@php
    $navLinks = [
        ['route' => 'home', 'label' => 'Home'],
        ['route' => 'about', 'label' => 'About'],
        ['route' => 'seasons', 'label' => 'Season'],
        ['route' => 'groups', 'label' => 'Groups'],
        ['route' => 'programme', 'label' => 'Programme'],
        ['route' => 'impact', 'label' => 'Impact'],
        ['route' => 'faq', 'label' => 'FAQ'],
    ];
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>@yield('title', 'Culture Acapella Festival — Season 2 · Singing to Change Lives · Arusha 2027')</title>
<meta name="description" content="@yield('description', 'CAF Season 2 brings acapella groups together in Arusha on 17 July 2027 to celebrate harmony and raise support for children in difficult circumstances. Registration is open.')">
<meta name="theme-color" content="#4A1215">
<link rel="icon" type="image/png" href="{{ asset('caf.png') }}">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:title" content="@yield('ogTitle', 'Culture Acapella Festival — Season 2 · Singing to Change Lives')">
<meta property="og:description" content="@yield('ogDescription', '17 July 2027 · Arusha, Tanzania. Gospel a cappella, one stage, one cause. Registration is open for acapella groups until 30 October 2026.')">
<meta property="og:site_name" content="Culture Acapella Festival">
<meta name="twitter:card" content="summary_large_image">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;500;700&family=Montserrat:wght@600;700;800&display=swap">

<script>
(function(){
  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches && 'IntersectionObserver' in window) {
    document.documentElement.classList.add('js');
  }
  try { var t = localStorage.getItem('caf-theme'); if (t) document.documentElement.setAttribute('data-theme', t); } catch(e){}
})();
</script>

@vite(['resources/css/caf.css', 'resources/js/app.js'])
</head>

<body>

<a class="skip" href="#main">Skip to main content</a>

<!-- ============================================================
     Header
     ============================================================ -->
<header class="site-header" id="siteHeader">
  <div class="container bar">
    <a class="logo" href="{{ route('home') }}" aria-label="Culture Acapella Festival home">
      <img class="logo-img" src="{{ asset('caf.png') }}" alt="Culture Acapella Festival logo" width="668" height="344">
      <span class="logo-text">
        <strong>Culture Acapella Festival</strong>
        <small>Season 2 · Singing to Change Lives</small>
      </span>
    </a>

    <button class="menu-btn" id="menuBtn" type="button" aria-expanded="false" aria-controls="primaryNav">Menu</button>

    <nav class="nav" id="primaryNav" aria-label="Main">
      <ul>
        @foreach ($navLinks as $link)
          <li>
            <a href="{{ route($link['route']) }}" {{ request()->routeIs($link['route']) ? 'aria-current="true"' : '' }}>{{ $link['label'] }}</a>
          </li>
        @endforeach
      </ul>
      <div class="header-tools">
        <button class="lang-btn" type="button" id="langBtn" aria-label="Switch language">EN</button>
        <button class="theme-btn" type="button" id="themeBtn" aria-label="Toggle dark mode">
          <svg class="sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
          <svg class="moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/></svg>
        </button>
        <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Register Now</a>
      </div>
    </nav>
  </div>
</header>

<main id="main">
@yield('content')
</main>

<!-- ============================================================
     Footer
     ============================================================ -->
<footer class="site-footer">
  <div class="container">
    <div class="foot-grid">
      <div class="foot-about">
        <a class="logo" href="{{ route('home') }}">
          <img class="logo-img" src="{{ asset('caf.png') }}" alt="Culture Acapella Festival logo" width="668" height="344">
          <span class="logo-text">
            <strong>Culture Acapella Festival</strong>
            <small>Singing to Change Lives</small>
          </span>
        </a>
        <p>A festival for acapella groups, hosted season after season in Tanzania. Singing to change lives, together.</p>
        <div class="social" aria-label="Follow us">
          <a href="#" aria-label="Instagram">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/></svg>
          </a>
          <a href="#" aria-label="Facebook">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
          </a>
          <a href="https://wa.me/255752312128" aria-label="WhatsApp">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
          </a>
          <a href="#" aria-label="YouTube">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 17a24 24 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.6 49.6 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24 24 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.6 49.6 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/></svg>
          </a>
        </div>
      </div>

      <div>
        <h3>Explore</h3>
        <ul>
          @foreach ($navLinks as $link)
            <li><a href="{{ route($link['route']) }}">{{ $link['label'] }}</a></li>
          @endforeach
          <li><a href="{{ route('register') }}">Register</a></li>
        </ul>
      </div>

      <div>
        <h3>Contact</h3>
        <ul>
          <li>Founder &amp; CEO — Robert Samwel</li>
          <li><a href="tel:+255756290251">0756 290 251</a></li>
          <li>Coordinator — Praise Nyombi</li>
          <li><a href="tel:+255752312128">0752 312 128</a></li>
          <li><a href="mailto:info@cultureacapellafestival.com">info@cultureacapellafestival.com</a></li>
          <li>Arusha, Tanzania · Open daily, 8am – 6pm EAT</li>
        </ul>
      </div>

      <div>
        <h3>Payments accepted</h3>
        <div class="pay">
          <span>M-Pesa</span>
          <span>Mixx by Yas</span>
          <span>Airtel Money</span>
          <span>Halopesa</span>
          <span>Visa</span>
          <span>Mastercard</span>
          <span>Bank transfer</span>
        </div>
      </div>
    </div>

    <div class="legal">
      <span>&copy; <span id="year">2026</span> Culture Acapella Festival · Season 2 · Singing to Change Lives | Developed by <a href="https://www.jezdantech.com" target="_blank" rel="noopener"> - Jezdan Group</a></span>
      <span>Privacy · <a href="{{ route('terms') }}">Terms &amp; Conditions</a> · Code of Conduct · Media Release</span>
    </div>
  </div>
</footer>

<!-- ============================================================
     Floating actions
     ============================================================ -->
<div class="fab" aria-hidden="false">
  <a class="fab-btn fab-wa" href="https://wa.me/255752312128" aria-label="Chat on WhatsApp" target="_blank" rel="noopener">
    <i data-lucide="message-circle" aria-hidden="true"></i>
  </a>
  <button class="fab-btn fab-top" id="backTop" type="button" aria-label="Back to top">
    <i data-lucide="arrow-up" aria-hidden="true"></i>
  </button>
</div>

<!-- Cookie banner -->
<div class="cookie" id="cookieBanner" role="dialog" aria-label="Cookie consent">
  <p>We use essential cookies to make this site work, and optional analytics to understand how it is used. You can accept or decline analytics — essential cookies stay either way.</p>
  <div class="cookie-actions">
    <button class="btn btn-primary" type="button" id="cookieAccept">Accept analytics</button>
    <button class="btn btn-ghost" type="button" id="cookieDecline">Essential only</button>
  </div>
</div>

</body>
</html>