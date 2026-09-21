@extends('layouts.app')

@section('title', 'Community Impact — Culture Acapella Festival')
@section('description', 'CAF connects gospel a cappella music with social responsibility — supporting children with special needs, orphans and vulnerable children through the festival and its initiatives.')

@section('content')

<!-- ============================================================
     Impact
     ============================================================ -->
<section class="section" id="impact" aria-labelledby="impactTitle">
  <div class="container">
    <div class="impact reveal">
      <div>
        <span class="eyebrow" style="background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.24);color:#fff"><i></i> Singing to Change Lives</span>
        <h2 id="impactTitle" style="margin-top:20px">Every voice raised is hope to a child</h2>
        <p>One of CAF's distinctive purposes is to connect music with social responsibility. The festival seeks to support initiatives benefiting children with special needs, orphans, vulnerable children and children facing difficult circumstances — as well as other appropriate community-support initiatives.</p>
        <blockquote style="font-size:17px;line-height:1.6;font-style:italic;color:rgba(255,255,255,.95);margin-top:20px;padding-left:18px;border-left:4px solid var(--accent)">"Every voice raised in worship becomes hope to a child in need."</blockquote>
        <div class="impact-stats">
          <div class="impact-stat">
            <strong>2026</strong>
            <span>Season 1 · 11 July · Arusha</span>
          </div>
          <div class="impact-stat">
            <strong>2027</strong>
            <span>Season 2 · 17 July · Arusha</span>
          </div>
          <div class="impact-stat">
            <strong>S2</strong>
            <span>Singing to change lives</span>
          </div>
        </div>
      </div>
      <a class="btn btn-primary" href="{{ route('register') }}">Support the cause — register</a>
    </div>
  </div>
</section>

<!-- ============================================================
     Who we support
     ============================================================ -->
<section class="section" aria-labelledby="supportTitle">
  <div class="container">
    <div class="section-head center reveal">
      <h2 id="supportTitle">Who CAF supports</h2>
      <p>The long-term intention is a festival where music, worship and community service work together.</p>
    </div>

    <ul class="benefits">
      <li class="benefit reveal">
        <span class="icon-badge" aria-hidden="true"><i data-lucide="heart" aria-hidden="true"></i></span>
        <h3>Children with special needs</h3>
        <p>Festival initiatives reach children with special needs through partner community programmes.</p>
      </li>
      <li class="benefit reveal">
        <span class="icon-badge" aria-hidden="true"><i data-lucide="home" aria-hidden="true"></i></span>
        <h3>Orphans &amp; vulnerable children</h3>
        <p>Support is directed towards orphans and vulnerable children in the community.</p>
      </li>
      <li class="benefit reveal">
        <span class="icon-badge" aria-hidden="true"><i data-lucide="sparkles" aria-hidden="true"></i></span>
        <h3>Difficult circumstances</h3>
        <p>Children facing difficult circumstances are at the centre of CAF's community support.</p>
      </li>
      <li class="benefit reveal">
        <span class="icon-badge" aria-hidden="true"><i data-lucide="users" aria-hidden="true"></i></span>
        <h3>Community initiatives</h3>
        <p>Other appropriate community-support initiatives are carried alongside the festival season.</p>
      </li>
    </ul>

    <p style="text-align:center;font-size:14.5px;color:var(--muted);max-width:56ch;margin:28px auto 0">Where CAF receives donations or funds specifically designated for community support, the organizers use those funds for the stated purpose and maintain appropriate records.</p>
  </div>
</section>

<!-- ============================================================
     Partners CTA
     ============================================================ -->
<section class="section section-alt" aria-labelledby="partnersTitle">
  <div class="container">
    <div class="section-head center reveal">
      <h2 id="partnersTitle">Partner with CAF</h2>
      <p>CAF works with sponsors, businesses, organizations, institutions and community partners who share its purpose, values and vision.</p>
    </div>

    <div class="impact reveal">
      <div>
        <span class="eyebrow" style="background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.24);color:#fff"><i></i> Collaboration</span>
        <h2 style="margin-top:20px">Make a difference beyond the stage</h2>
        <p>Partnerships between artists, churches, organizations, businesses and communities create greater impact. Join CAF as a sponsor, institution, church, business or community organization.</p>
      </div>
      <a class="btn btn-primary" href="mailto:info@cultureacapellafestival.com?subject=Partner%20with%20CAF">Get in touch →</a>
    </div>
  </div>
</section>

<!-- ============================================================
     Voices from Season 1
     ============================================================ -->
<section class="section" aria-labelledby="voicesTitle">
  <div class="container">
    <div class="section-head center reveal">
      <h2 id="voicesTitle">Voices from Season 1</h2>
      <p>Groups who performed at the first festival share what it meant to them.</p>
    </div>
    <div class="testimonials">
      <figure class="quote reveal">
        <div class="quote-mark" aria-hidden="true">"</div>
        <p>The whole day felt like a family reunion. We came as one group and left as part of a bigger choir.</p>
        <figcaption class="quote-author">
          <span class="quote-avatar" aria-hidden="true">HV</span>
          <span>
            <strong>Season 1 participant</strong>
            <small>Harmonic Voice · Arusha 2026</small>
          </span>
        </figcaption>
      </figure>
      <figure class="quote reveal">
        <div class="quote-mark" aria-hidden="true">"</div>
        <p>CAF gave our singers a stage and a purpose. They rehearsed harder than ever once they knew what we were singing for.</p>
        <figcaption class="quote-author">
          <span class="quote-avatar" aria-hidden="true">CT</span>
          <span>
            <strong>Season 1 participant</strong>
            <small>Celestials TZ · Arusha 2026</small>
          </span>
        </figcaption>
      </figure>
      <figure class="quote reveal">
        <div class="quote-mark" aria-hidden="true">"</div>
        <p>Even the technical side was taken seriously — the sound, the stage, the programme. It felt like a real festival.</p>
        <figcaption class="quote-author">
          <span class="quote-avatar" aria-hidden="true">TT</span>
          <span>
            <strong>Season 1 participant</strong>
            <small>The Trio TZ · Arusha 2026</small>
          </span>
        </figcaption>
      </figure>
    </div>
  </div>
</section>

@include('partials.register-strip')

@endsection