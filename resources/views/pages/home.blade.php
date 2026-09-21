@extends('layouts.app')

@section('title', 'Culture Acapella Festival — Season 2 · Singing to Change Lives · Arusha 2027')
@section('description', 'CAF Season 2 brings acapella groups together in Arusha on 17 July 2027 to celebrate harmony and raise support for children in difficult circumstances. Registration is open.')

@section('content')

<!-- ============================================================
     Hero
     ============================================================ -->
<section class="hero" id="top" aria-labelledby="heroTitle">
  <div class="hero-art" aria-hidden="true">
    <svg viewBox="0 0 1600 900" preserveAspectRatio="xMidYMid slice">
      <defs>
        <linearGradient id="skyGrad" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0" stop-color="#3A0C10"/>
          <stop offset=".45" stop-color="#5E181D"/>
          <stop offset=".8" stop-color="#7A2228"/>
          <stop offset="1" stop-color="#3A0C10"/>
        </linearGradient>
        <radialGradient id="spotGrad" cx=".5" cy=".3" r=".6">
          <stop offset="0" stop-color="#FFD9A8" stop-opacity=".55"/>
          <stop offset="1" stop-color="#FFD9A8" stop-opacity="0"/>
        </radialGradient>
        <radialGradient id="glowGrad" cx=".5" cy=".5" r=".5">
          <stop offset="0" stop-color="#E4572E" stop-opacity=".55"/>
          <stop offset="1" stop-color="#E4572E" stop-opacity="0"/>
        </radialGradient>
      </defs>

      <rect width="1600" height="900" fill="url(#skyGrad)"/>
      <ellipse cx="1100" cy="260" rx="620" ry="500" fill="url(#spotGrad)"/>
      <ellipse cx="1180" cy="720" rx="420" ry="360" fill="url(#glowGrad)"/>

      <!-- Layered wave lines -->
      <g stroke="#FBF5EF" stroke-opacity=".14" fill="none" stroke-width="2">
        <path d="M0 300 Q 200 220 400 300 T 800 280 T 1200 300 T 1600 260"/>
        <path d="M0 360 Q 200 300 400 360 T 800 340 T 1200 360 T 1600 330"/>
        <path d="M0 420 Q 200 380 400 420 T 800 410 T 1200 420 T 1600 400"/>
      </g>

      <!-- Stage floor -->
      <path d="M0 720 Q 400 690 800 710 T 1600 700 V 900 H 0 Z" fill="#1a0a0c" opacity=".85"/>

      <!-- Chorus silhouettes -->
      <g fill="#0F0507" opacity=".92">
        <g transform="translate(1020 470)">
          <circle cx="0" cy="0" r="34"/>
          <path d="M-58 230 C -58 130 -30 60 0 60 C 30 60 58 130 58 230 Z"/>
        </g>
        <g transform="translate(1130 450)">
          <circle cx="0" cy="0" r="36"/>
          <path d="M-62 250 C -62 140 -32 62 0 62 C 32 62 62 140 62 250 Z"/>
        </g>
        <g transform="translate(1240 480)">
          <circle cx="0" cy="0" r="32"/>
          <path d="M-54 220 C -54 130 -28 58 0 58 C 28 58 54 130 54 220 Z"/>
        </g>
        <g transform="translate(1340 500)">
          <circle cx="0" cy="0" r="30"/>
          <path d="M-50 200 C -50 120 -26 54 0 54 C 26 54 50 120 50 200 Z"/>
        </g>
        <g transform="translate(920 500)">
          <circle cx="0" cy="0" r="30"/>
          <path d="M-50 200 C -50 120 -26 54 0 54 C 26 54 50 120 50 200 Z"/>
        </g>
      </g>

      <!-- Microphone stand -->
      <g transform="translate(1180 240)" stroke="#0F0507" stroke-width="6" fill="none" opacity=".9">
        <rect x="-18" y="0" width="36" height="60" rx="18" fill="#0F0507"/>
        <path d="M0 60 V 260"/>
        <path d="M-30 260 H 30"/>
      </g>

      <!-- Sound waves from mic -->
      <g fill="none" stroke="#E4572E" stroke-width="3" stroke-linecap="round" opacity=".5">
        <path d="M1240 200 Q 1290 240 1240 280"/>
        <path d="M1270 175 Q 1340 240 1270 305"/>
        <path d="M1300 150 Q 1390 240 1300 330"/>
      </g>

      <!-- Small light dots -->
      <g fill="#FFD9A8" opacity=".6">
        <circle cx="500" cy="180" r="3"/>
        <circle cx="640" cy="240" r="2"/>
        <circle cx="380" cy="300" r="2.5"/>
        <circle cx="780" cy="160" r="2"/>
        <circle cx="280" cy="200" r="2"/>
      </g>
    </svg>

    <!-- Hero slideshow — crossfading festival photos -->
    <div class="hero-slides">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018614/IMG_0446.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018613/IMG_0452.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018612/IMG_0462.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018612/IMG_0525.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018612/IMG_0528.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018612/IMG_0381.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018611/IMG_0473.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018611/IMG_0494.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018610/IMG_0534.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018609/IMG_9853.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018609/IMG_0539.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018609/IMG_0545.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018608/IMG_9940.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018608/IMG_9957.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018607/IMG_9869.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018606/IMG_0144.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018606/IMG_0195.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018606/IMG_0128.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018605/IMG_0167.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018605/IMG_0202.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018604/IMG_0191.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018603/IMG_0265.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018603/IMG_0266.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018603/IMG_0307.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018603/IMG_0389.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018602/IMG_0279.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018601/IMG_9975.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018600/IMG_9978.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018599/IMG_0387.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018599/IMG_0394.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018598/IMG_0002.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018598/IMG_0371.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018597/IMG_0382.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018597/IMG_0010.jpg" alt="">
      <img class="hero-slide" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_1600,q_auto,f_auto/v1790018596/IMG_0430.jpg" alt="">
    </div>
  </div>
  <div class="hero-shade"></div>

  <div class="container hero-inner">
    <div class="hero-copy">
      <span class="eyebrow"><i></i> Season 2 · 2027</span>
      <h1 id="heroTitle">Singing to <em>Change Lives</em></h1>
      <p class="hero-lead">CAF Season 2 brings gospel a cappella groups from across the region to one stage in Arusha — to worship, celebrate craft and community, and to raise support for children living in difficult circumstances.</p>

      <div class="hero-meta">
        <span class="meta-chip">
          <i data-lucide="calendar-days" aria-hidden="true"></i>
          17 July 2027
        </span>
        <span class="meta-chip">
          <i data-lucide="map-pin" aria-hidden="true"></i>
          Arusha, Tanzania
        </span>
        <span class="meta-chip">
          <i data-lucide="clock" aria-hidden="true"></i>
          Registration closes 30 October 2026
        </span>
      </div>

      <div class="countdown" aria-label="Time until registration closes">
        <p class="countdown-label">Registration closes in</p>
        <div class="countdown-grid" id="countdown" role="timer" aria-live="polite">
          <div class="cd-tile"><div class="cd-num" data-cd="days">—</div><div class="cd-label">Days</div></div>
          <div class="cd-tile"><div class="cd-num" data-cd="hours">—</div><div class="cd-label">Hours</div></div>
          <div class="cd-tile"><div class="cd-num" data-cd="mins">—</div><div class="cd-label">Minutes</div></div>
          <div class="cd-tile"><div class="cd-num" data-cd="secs">—</div><div class="cd-label">Seconds</div></div>
        </div>
      </div>

      <div class="hero-actions">
        <a class="btn btn-primary" href="{{ route('register') }}">
          <i data-lucide="plus" aria-hidden="true"></i>
          Register your group
        </a>
        <a class="btn btn-secondary" href="{{ route('seasons') }}">Explore CAF</a>
      </div>

      <ul class="hero-trust">
        <li>Open to groups worldwide</li>
        <li>Judged by a professional panel</li>
        <li>Supporting children in need</li>
      </ul>
    </div>
  </div>
</section>

<!-- Live status strip -->
<div class="status-strip" role="status">
  <div class="container">
    <strong>Registration is open</strong>
    <span aria-hidden="true">·</span>
    <span>Group entries close 30 October 2026</span>
    <span aria-hidden="true">·</span>
    <a href="{{ route('register') }}">Register your group →</a>
  </div>
</div>

<!-- ============================================================
     Why join
     ============================================================ -->
<section class="section" id="why" aria-labelledby="whyTitle">
  <div class="container">
    <div class="section-head center reveal">
      <h2 id="whyTitle">Why join CAF Season 2</h2>
      <p>Five reasons acapella groups from across the region choose the Culture Acapella Festival.</p>
    </div>

    <ul class="benefits">
      <li class="benefit reveal">
        <span class="icon-badge" aria-hidden="true">
          <i data-lucide="music" aria-hidden="true"></i>
        </span>
        <h3>Showcase your talent</h3>
        <p>Perform on a professional stage, in front of an audience and a judging panel that celebrates acapella craft.</p>
      </li>
      <li class="benefit reveal">
        <span class="icon-badge" aria-hidden="true">
          <i data-lucide="users" aria-hidden="true"></i>
        </span>
        <h3>Connect with other groups</h3>
        <p>Share rehearsal rooms, techniques and stories with acapella groups from across Tanzania and beyond.</p>
      </li>
      <li class="benefit reveal">
        <span class="icon-badge" aria-hidden="true">
          <i data-lucide="heart-handshake" aria-hidden="true"></i>
        </span>
        <h3>Support a meaningful cause</h3>
        <p>Every season directs proceeds and awareness to children living in difficult circumstances. Singing to change lives is not a slogan — it is the programme.</p>
      </li>
      <li class="benefit reveal">
        <span class="icon-badge" aria-hidden="true">
          <i data-lucide="send" aria-hidden="true"></i>
        </span>
        <h3>Gain media exposure</h3>
        <p>Performances are filmed and shared across CAF's channels, with press and partner coverage across the season.</p>
      </li>
      <li class="benefit reveal">
        <span class="icon-badge" aria-hidden="true">
          <i data-lucide="user-plus" aria-hidden="true"></i>
        </span>
        <h3>Join the community</h3>
        <p>Become part of a growing acapella family that stays connected between seasons, on and off stage.</p>
      </li>
    </ul>
  </div>
</section>

<!-- ============================================================
     How it works
     ============================================================ -->
<section class="section section-alt section-halftone" aria-labelledby="howTitle">
  <div class="container">
    <div class="section-head center reveal">
      <h2 id="howTitle">How it works</h2>
      <p>Five stages from first registration to performing on the Season 2 stage.</p>
    </div>

    <ol class="steps">
      <li class="step reveal">
        <span class="step-num" aria-hidden="true">1</span>
        <h3>Register</h3>
        <p>Submit your group's profile, roster and a link to your most recent performance.</p>
      </li>
      <li class="step reveal">
        <span class="step-num" aria-hidden="true">2</span>
        <h3>Review</h3>
        <p>Our judging panel reviews every entry against the season rubric and shortlists the line-up.</p>
      </li>
      <li class="step reveal">
        <span class="step-num" aria-hidden="true">3</span>
        <h3>Confirm &amp; contribute</h3>
        <p>Registered groups confirm their participation and contribute TSh 10,000 per participant/head after 30 October 2026, by mobile money, card or bank transfer.</p>
      </li>
      <li class="step reveal">
        <span class="step-num" aria-hidden="true">4</span>
        <h3>Prepare</h3>
        <p>Receive your programme slot, technical rider and logistics pack so you arrive ready to perform.</p>
      </li>
      <li class="step reveal">
        <span class="step-num" aria-hidden="true">5</span>
        <h3>Perform</h3>
        <p>Take the stage in Arusha on 17 July 2027 — and help us raise the roof for the cause.</p>
      </li>
    </ol>

    <div style="margin-top:44px;text-align:center">
      <p style="font-size:15px;color:var(--muted);max-width:56ch;margin-inline:auto;margin-bottom:12px">Ready to take the stage? Registration is open until 30 October 2026.</p>
      <a class="btn btn-primary" href="{{ route('register') }}">
        <i data-lucide="arrow-right" aria-hidden="true"></i>
        Register your group
      </a>
    </div>
  </div>
</section>

<!-- ============================================================
     Payment & Donation Methods
     ============================================================ -->
<section class="section" aria-labelledby="paydonTitle">
  <div class="container">
    <div class="section-head center reveal">
      <h2 id="paydonTitle">Payment &amp; Donation Methods</h2>
      <p>Send your group's contribution or support the cause — every payment is safe, easy and transparent.</p>
    </div>

    <div class="mv-grid">
      <div class="mv-card reveal">
        <span class="icon-badge" aria-hidden="true">
          <i data-lucide="wallet" aria-hidden="true"></i>
        </span>
        <h3>Group contributions</h3>
        <p>After registration closes on 30 October 2026, each registered participant contributes <strong>TSh 10,000</strong> to confirm the group's place in the Season 2 line-up.</p>
        <div class="pay-chip">
          <span>M-Pesa</span>
          <span>Mixx by Yas</span>
          <span>Airtel Money</span>
          <span>Halopesa</span>
          <span>Visa</span>
          <span>Mastercard</span>
          <span>Bank transfer</span>
        </div>
      </div>
      <div class="mv-card reveal">
        <span class="icon-badge" aria-hidden="true">
          <i data-lucide="heart-handshake" aria-hidden="true"></i>
        </span>
        <h3>Donate to the cause</h3>
        <p>Every donation supports children with special needs, orphans and vulnerable children in Arusha. Funds are used for the stated purpose and kept properly accounted for.</p>
        <div class="pay-chip">
          <span>M-Pesa</span>
          <span>Mixx by Yas</span>
          <span>Airtel Money</span>
          <span>Halopesa</span>
          <span>Bank transfer</span>
        </div>
      </div>
    </div>

    <p class="paynote">Need a receipt, invoice or official payment documents?
      <a class="link" href="mailto:info@cultureacapellafestival.com?subject=Payment%20or%20donation%20receipt">Contact the finance team</a>
      and we'll confirm within one working day.
    </p>
  </div>
</section>

@endsection