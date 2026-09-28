@extends('layouts.app')

@section('title', 'Participating Groups — Culture Acapella Festival')
@section('description', 'Meet the CAFS1-26 and a cappella groups who performed at CAF Season 1 in Arusha. The Season 2 line-up is announced after registration closes on 30 October 2026.')

@section('content')

<!-- ============================================================
     Groups
     ============================================================ -->
<section class="section section-alt" id="groups" aria-labelledby="groupsTitle">
  <div class="container">
    <div class="section-head reveal">
      <h2 id="groupsTitle">Participating groups</h2>
      <p>The groups who performed at CAF Season 1 in Arusha. The Season 2 line-up is announced after registration closes on 30 October 2026.</p>
    </div>

    <ul class="groups" id="groupGrid">

      <li class="group-card reveal" data-tags="CAFS1-26">
        <div class="group-art" style="background:linear-gradient(135deg,#4A1215,#E4572E)">
          <img src="https://res.cloudinary.com/ildlk8cq/image/upload/w_800,q_auto,f_auto/v1790018609/IMG_9853.jpg" alt="Harmonic Voice performing at CAF Season 1" loading="lazy">
          <span class="group-art-label">CAFS1-26</span>
        </div>
        <div class="group-body">
          <p class="group-meta">CAFS1-26 a cappella · Season 1</p>
          <h3>Harmonic Voice</h3>
          <p>A CAFS1-26 vocal ensemble bringing rich, layered harmony to the CAF stage.</p>
          <div class="group-foot">
            <span class="season-badge" style="font-size:11.5px">Season 1 · Performed</span>
            <a class="link" href="{{ route('register') }}">Join in
              <i data-lucide="arrow-right" aria-hidden="true"></i>
            </a>
          </div>
        </div>
      </li>

      <li class="group-card reveal" data-tags="CAFS1-26">
        <div class="group-art" style="background:linear-gradient(135deg,#7A2228,#D9913F)">
          <img src="https://res.cloudinary.com/ildlk8cq/image/upload/w_800,q_auto,f_auto/v1790018601/IMG_9975.jpg" alt="Celestials TZ performing at CAF Season 1" loading="lazy">
          <span class="group-art-label">CAFS1-26</span>
        </div>
        <div class="group-body">
          <p class="group-meta">CAFS1-26 a cappella · Season 1</p>
          <h3>Celestials TZ</h3>
          <p>A Tanzanian CAFS1-26 group known for soaring lead lines and tight vocal blend.</p>
          <div class="group-foot">
            <span class="season-badge" style="font-size:11.5px">Season 1 · Performed</span>
            <a class="link" href="{{ route('register') }}">Join in
              <i data-lucide="arrow-right" aria-hidden="true"></i>
            </a>
          </div>
        </div>
      </li>

      <li class="group-card reveal" data-tags="CAFS1-26">
        <div class="group-art" style="background:linear-gradient(135deg,#3A0C10,#7A2228)">
          <img src="https://res.cloudinary.com/ildlk8cq/image/upload/w_800,q_auto,f_auto/v1790018612/IMG_0462.jpg" alt="Sojourners TZ performing at CAF Season 1" loading="lazy">
          <span class="group-art-label">CAFS1-26</span>
        </div>
        <div class="group-body">
          <p class="group-meta">CAFS1-26 a cappella · Season 1</p>
          <h3>Sojourners TZ</h3>
          <p>A group built around devotional repertoire and warm four-part harmony.</p>
          <div class="group-foot">
            <span class="season-badge" style="font-size:11.5px">Season 1 · Performed</span>
            <a class="link" href="{{ route('register') }}">Join in
              <i data-lucide="arrow-right" aria-hidden="true"></i>
            </a>
          </div>
        </div>
      </li>

      <li class="group-card reveal" data-tags="CAFS1-26">
        <div class="group-art" style="background:linear-gradient(135deg,#5E181D,#E8B368)">
          <img src="https://res.cloudinary.com/ildlk8cq/image/upload/w_800,q_auto,f_auto/v1790018599/IMG_0394.jpg" alt="The Trio TZ performing at CAF Season 1" loading="lazy">
          <span class="group-art-label">CAFS1-26</span>
        </div>
        <div class="group-body">
          <p class="group-meta">Vocal ensemble · Season 1</p>
          <h3>The Trio TZ</h3>
          <p>An intimate trio pairing CAFS1-26 standards with original Swahili arrangements.</p>
          <div class="group-foot">
            <span class="season-badge" style="font-size:11.5px">Season 1 · Performed</span>
            <a class="link" href="{{ route('register') }}">Join in
              <i data-lucide="arrow-right" aria-hidden="true"></i>
            </a>
          </div>
        </div>
      </li>

      <li class="group-card reveal" data-tags="CAFS1-26">
        <div class="group-art" style="background:linear-gradient(135deg,#E4572E,#D9913F)">
          <img src="https://res.cloudinary.com/ildlk8cq/image/upload/w_800,q_auto,f_auto/v1790018598/IMG_0002.jpg" alt="Kingdom Builders performing at CAF Season 1" loading="lazy">
          <span class="group-art-label">CAFS1-26</span>
        </div>
        <div class="group-body">
          <p class="group-meta">CAFS1-26 a cappella · Season 1</p>
          <h3>Kingdom Builders</h3>
          <p>A CAFS1-26-focused group blending contemporary CAFS1-26 with traditional psalms.</p>
          <div class="group-foot">
            <span class="season-badge" style="font-size:11.5px">Season 1 · Performed</span>
            <a class="link" href="{{ route('register') }}">Join in
              <i data-lucide="arrow-right" aria-hidden="true"></i>
            </a>
          </div>
        </div>
      </li>

      <li class="group-card reveal" data-tags="CAFS1-26">
        <div class="group-art" style="background:linear-gradient(135deg,#4A1215,#3A0C10)">
          <img src="https://res.cloudinary.com/ildlk8cq/image/upload/w_800,q_auto,f_auto/v1790018606/IMG_0144.jpg" alt="Davidic Praises performing at CAF Season 1" loading="lazy">
          <span class="group-art-label">CAFS1-26</span>
        </div>
        <div class="group-body">
          <p class="group-meta">CAFS1-26 a cappella · Season 1</p>
          <h3>Davidic Praises</h3>
          <p>A praise ensemble lifting original songs of CAFS1-26 with layered vocals.</p>
          <div class="group-foot">
            <span class="season-badge" style="font-size:11.5px">Season 1 · Performed</span>
            <a class="link" href="{{ route('register') }}">Join in
              <i data-lucide="arrow-right" aria-hidden="true"></i>
            </a>
          </div>
        </div>
      </li>

      <li class="group-card reveal" data-tags="CAFS1-26">
        <div class="group-art" style="background:linear-gradient(135deg,#6E1A20,#E29A5B)">
          <img src="https://res.cloudinary.com/ildlk8cq/image/upload/w_800,q_auto,f_auto/v1790018603/IMG_0266.jpg" alt="Glorious Voice performing at CAF Season 1" loading="lazy">
          <span class="group-art-label">CAFS1-26</span>
        </div>
        <div class="group-body">
          <p class="group-meta">CAFS1-26 a cappella · Season 1</p>
          <h3>Glorious Voice</h3>
          <p>A joyful ensemble delivering CAFS1-26 harmony with energy and conviction.</p>
          <div class="group-foot">
            <span class="season-badge" style="font-size:11.5px">Season 1 · Performed</span>
            <a class="link" href="{{ route('register') }}">Join in
              <i data-lucide="arrow-right" aria-hidden="true"></i>
            </a>
          </div>
        </div>
      </li>

    </ul>
  </div>
</section>

@include('partials.register-strip')

@endsection