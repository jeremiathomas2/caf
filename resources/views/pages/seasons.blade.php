@extends('layouts.app')

@section('title', 'Seasons — Culture Acapella Festival · Season 2')
@section('description', 'The festival runs season to season. Explore the Season 2 hub and archive — every edition has its own theme, its own line-up and its own story.')

@section('content')

<!-- ============================================================
     Season hub / archive
     ============================================================ -->
<section class="section" id="season" aria-labelledby="seasonTitle">
  <div class="container">
    <div class="section-head center reveal">
      <h2 id="seasonTitle">Seasons</h2>
      <p>The festival runs season to season. Every edition has its own theme, its own line-up and its own story.</p>
    </div>

    <div class="season-teaser reveal">
      <div class="season-teaser-copy">
        <div class="season-badges">
          <span class="season-badge">Season 2 · Now live</span>
          <span class="season-badge gold">Theme: Singing to Change Lives</span>
        </div>
        <h3 style="font-family:var(--font-head);font-size:26px;font-weight:800;line-height:1.2">
          Season 2 — Arusha, 17 July 2027
        </h3>
        <p>Registration is open until 30 October 2026. Shortlisted groups receive their programme slot, technical rider and logistics pack after registration closes.</p>
        <div class="season-badges">
          <span class="season-badge">17 July 2027</span>
          <span class="season-badge">Arusha, Tanzania</span>
          <span class="season-badge">Registration open</span>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px">
          <a class="btn btn-primary" href="{{ route('register') }}">Register your group</a>
          <a class="btn btn-ghost" href="{{ route('programme') }}">View programme</a>
        </div>
      </div>
      <div class="season-teaser-art" aria-hidden="true">
        <svg viewBox="0 0 320 320" fill="none">
          <circle cx="160" cy="160" r="150" stroke="#FBF5EF" stroke-opacity=".2" stroke-width="2"/>
          <circle cx="160" cy="160" r="110" stroke="#FBF5EF" stroke-opacity=".3" stroke-width="2"/>
          <circle cx="160" cy="160" r="70" stroke="#FBF5EF" stroke-opacity=".4" stroke-width="2"/>
          <text x="160" y="150" text-anchor="middle" fill="#FBF5EF" font-family="Montserrat" font-weight="800" font-size="46">02</text>
          <text x="160" y="196" text-anchor="middle" fill="#FBF5EF" font-family="Comfortaa" font-weight="500" font-size="13" opacity=".85">SEASON</text>
          <g fill="#FFD9A8" opacity=".85">
            <circle cx="60" cy="90" r="3"/>
            <circle cx="260" cy="230" r="3"/>
            <circle cx="230" cy="80" r="2.5"/>
            <circle cx="80" cy="240" r="2.5"/>
          </g>
        </svg>
      </div>
    </div>

    <div class="legacy reveal">
      <div class="season-badges">
        <span class="season-badge">Season 1 · Archive</span>
        <span class="season-badge gold">Foundation edition</span>
      </div>
      <h3 style="font-family:var(--font-head);font-size:26px;font-weight:800;line-height:1.2;margin-top:18px">Season 1 — a festival is born</h3>

      <div class="season-badges" style="margin-top:16px">
        <span class="season-badge">11 July 2026</span>
        <span class="season-badge">16:00 – 22:00</span>
        <span class="season-badge">Arusha Metropole Hall</span>
        <span class="season-badge">Arusha, Tanzania</span>
      </div>

      <p style="margin-top:18px;font-size:16px;line-height:1.7;color:var(--text)">Season 1 brought together several gospel and a cappella groups and included performances, worship, fellowship and community-focused activities. It became the foundation for CAF's development into an annual platform.</p>

      <h4 style="font-family:var(--font-head);font-size:15px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:var(--heading);margin:24px 0 12px">Participating groups</h4>
      <ul class="legacy-groups">
        <li>Harmonic Voice</li>
        <li>Celestials TZ</li>
        <li>Sojourners TZ</li>
        <li>The Trio TZ</li>
        <li>Kingdom Builders</li>
        <li>Davidic Praises</li>
        <li>Glorious Voice</li>
      </ul>

      <p style="margin-top:18px;font-size:15.5px;line-height:1.7;color:var(--text)">The programme also included <strong>Healing String</strong>, invited guests and a <strong>saxophone/worship session</strong>.</p>

      <p style="margin-top:18px;font-size:15.5px;line-height:1.7;color:var(--text)">Season 2 builds on this experience on <strong>17 July 2027</strong> in Arusha, with a longer preparation period for wider participation, stronger organization and greater community involvement.</p>

      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:20px">
        <a class="btn btn-primary" href="{{ route('register') }}">Register for Season 2</a>
        <a class="btn btn-ghost" href="{{ route('about') }}">Read our story</a>
      </div>
    </div>
  </div>
</section>

@include('partials.register-strip')

@endsection