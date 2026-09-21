@extends('layouts.app')

@section('title', 'About Us — Culture Acapella Festival')
@section('description', 'The history and purpose of the Culture Acapella Festival (CAF): a gospel a cappella festival in Arusha, founded by Robert Samwel, with its mission, vision, core values, objectives and activities.')

@section('content')

<!-- ============================================================
     About — intro
     ============================================================ -->
<section class="section" id="about" aria-labelledby="aboutTitle">
  <div class="container">
    <div class="section-head center reveal">
      <span class="eyebrow" style="background:var(--cream-200);border-color:var(--sand);color:var(--brand-800)"><i></i> Our story</span>
      <h2 id="aboutTitle" style="margin-top:14px">About the Culture Acapella Festival</h2>
    </div>

    <div class="about-story reveal">
      <p>The <strong>Culture Acapella Festival (CAF)</strong> is a gospel a cappella festival based in Arusha, Tanzania, established to create a dedicated platform for a cappella groups, singers, worshippers and music lovers to come together through vocal music.</p>

      <p>The idea behind CAF was developed from a passion for a cappella music, gospel ministry, culture and community service. The festival seeks to give vocal groups an opportunity to showcase their God-given talents while creating an environment where music can bring people together and contribute positively to society.</p>

      <p>CAF places gospel a cappella music at the centre of its activities, while also embracing cultural expression, fellowship, creativity, youth participation and community support.</p>

      <div class="about-highlight">"Every voice raised in worship becomes hope to a child in need."</div>

      <p>This reflects CAF's wider goal of connecting music with social impact — particularly support for children with special needs, orphans and vulnerable children, or children experiencing difficult circumstances. Focus: <strong>Gospel A Cappella · Music · Culture · Community Impact</strong>.</p>

      <p>CAF is an independent festival founded and coordinated by <strong>Robert Samwel</strong>. Season 1 was held on <strong>11 July 2026</strong> at the Arusha Metropole Hall and became the foundation for CAF's development into an annual platform. Season 2 is planned for <strong>17 July 2027</strong> in Arusha, Tanzania.</p>

      <div class="about-meta">
        <span class="season-badge">Rooted in Arusha, Tanzania</span>
        <span class="season-badge gold">Gospel · A Cappella · Community</span>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     About — founder & leadership
     ============================================================ -->
<section class="section section-alt" aria-labelledby="foundersTitle">
  <div class="container">
    <div class="section-head center reveal">
      <h2 id="foundersTitle">Founder &amp; leadership</h2>
      <p>The people who lead the festival's vision, coordination and community relationships.</p>
    </div>

    <ul class="founders">
      <li class="founder-card reveal">
        <span class="founder-avatar" aria-hidden="true">RS</span>
        <h3>Robert Samwel</h3>
        <span class="founder-role">Founder &amp; CEO</span>
        <p>Founded and organized CAF, providing the overall vision and direction of the festival, coordinating its development, building partnerships and ensuring CAF keeps its focus on gospel music, a cappella culture and community impact.</p>
        <p style="margin-top:12px;font-weight:600;color:var(--heading)">Phone: 0756 290 251</p>
      </li>
      <li class="founder-card reveal">
        <span class="founder-avatar" aria-hidden="true">PN</span>
        <h3>Praise Nyombi</h3>
        <span class="founder-role">Coordinator</span>
        <p>Supports participant communication, registration, coordination of groups and festival activities, so every group enjoys a smooth journey from registration to the main stage.</p>
        <p style="margin-top:12px;font-weight:600;color:var(--heading)">Phone: 0752 312 128</p>
      </li>
    </ul>
  </div>
</section>

<!-- ============================================================
     About — mission, vision, core values
     ============================================================ -->
<section class="section" aria-labelledby="mvTitle">
  <div class="container">
    <div class="section-head center reveal">
      <h2 id="mvTitle">Mission, vision &amp; core values</h2>
      <p>The principles that guide every season, every group and every song on the CAF stage.</p>
    </div>

    <div class="mv-grid">
      <div class="mv-card reveal">
        <span class="icon-badge" aria-hidden="true">
          <i data-lucide="target" aria-hidden="true"></i>
        </span>
        <h3>Our mission</h3>
        <p>To provide a professional platform where gospel a cappella groups and vocal artists can worship, showcase their talents, connect with one another, celebrate culture and use music to make a positive difference in the community.</p>
      </div>
      <div class="mv-card reveal">
        <span class="icon-badge" aria-hidden="true">
          <i data-lucide="eye" aria-hidden="true"></i>
        </span>
        <h3>Our vision</h3>
        <p>To become a leading gospel a cappella festival in Tanzania and beyond, using music, culture and community engagement to inspire lives, develop talent and create positive social impact.</p>
      </div>
    </div>

    <div style="margin-top:44px">
      <h3 style="text-align:center;font-family:var(--font-head);font-size:20px;font-weight:700;margin-bottom:24px">Core values</h3>
      <ul class="values-grid">
        <li class="value-card reveal">
          <span class="icon-badge" aria-hidden="true"><i data-lucide="music" aria-hidden="true"></i></span>
          <h3>Faith</h3>
          <p>CAF places gospel music and worship at the heart of the festival.</p>
        </li>
        <li class="value-card reveal">
          <span class="icon-badge" aria-hidden="true"><i data-lucide="award" aria-hidden="true"></i></span>
          <h3>Excellence</h3>
          <p>High-quality vocal performance, professionalism and continuous improvement.</p>
        </li>
        <li class="value-card reveal">
          <span class="icon-badge" aria-hidden="true"><i data-lucide="users" aria-hidden="true"></i></span>
          <h3>Unity</h3>
          <p>Bringing different groups, churches, communities and individuals together through music.</p>
        </li>
        <li class="value-card reveal">
          <span class="icon-badge" aria-hidden="true"><i data-lucide="shield-check" aria-hidden="true"></i></span>
          <h3>Integrity</h3>
          <p>Honesty, transparency, accountability and responsible leadership.</p>
        </li>
        <li class="value-card reveal">
          <span class="icon-badge" aria-hidden="true"><i data-lucide="heart-handshake" aria-hidden="true"></i></span>
          <h3>Service</h3>
          <p>Using music as a means of serving and supporting people in need.</p>
        </li>
        <li class="value-card reveal">
          <span class="icon-badge" aria-hidden="true"><i data-lucide="globe" aria-hidden="true"></i></span>
          <h3>Culture</h3>
          <p>Valuing African and Tanzanian cultural identity and encouraging respectful cultural expression.</p>
        </li>
        <li class="value-card reveal">
          <span class="icon-badge" aria-hidden="true"><i data-lucide="mic" aria-hidden="true"></i></span>
          <h3>Talent Development</h3>
          <p>Opportunities for singers and vocal groups to develop, perform and gain exposure.</p>
        </li>
        <li class="value-card reveal">
          <span class="icon-badge" aria-hidden="true"><i data-lucide="heart" aria-hidden="true"></i></span>
          <h3>Respect</h3>
          <p>Every participant, guest, volunteer, sponsor and audience member is treated with dignity.</p>
        </li>
        <li class="value-card reveal">
          <span class="icon-badge" aria-hidden="true"><i data-lucide="sparkles" aria-hidden="true"></i></span>
          <h3>Community Impact</h3>
          <p>Ensuring the festival has benefits that reach beyond the stage.</p>
        </li>
        <li class="value-card reveal">
          <span class="icon-badge" aria-hidden="true"><i data-lucide="link" aria-hidden="true"></i></span>
          <h3>Collaboration</h3>
          <p>Partnerships between artists, churches, organizations, businesses and communities create greater impact.</p>
        </li>
      </ul>
    </div>
  </div>
</section>

<!-- ============================================================
     About — objectives
     ============================================================ -->
<section class="section section-alt" aria-labelledby="objectivesTitle">
  <div class="container">
    <div class="section-head center reveal">
      <h2 id="objectivesTitle">CAF objectives</h2>
      <p>The main goals that shape the festival season after season.</p>
    </div>

    <ol class="objectives reveal">
      <li>Promote gospel a cappella music.</li>
      <li>Create opportunities for a cappella groups to perform.</li>
      <li>Discover and support emerging vocal talent.</li>
      <li>Encourage young people to participate in positive creative activities.</li>
      <li>Promote unity through music.</li>
      <li>Celebrate Tanzanian and African culture.</li>
      <li>Build connections between different vocal groups.</li>
      <li>Create opportunities for artists to gain exposure.</li>
      <li>Encourage professional standards in gospel music.</li>
      <li>Support children and vulnerable members of the community through appropriate festival initiatives.</li>
      <li>Develop CAF into an annual festival.</li>
      <li>Build partnerships with sponsors, institutions, churches, businesses and community organizations.</li>
    </ol>

    <div class="about-highlight" style="margin-top:36px">"Singing to Change Lives" — singing is not only entertainment; it is also worship, encouragement, inspiration, unity and service to the community.</div>
  </div>
</section>

<!-- ============================================================
     About — activities & merchandise
     ============================================================ -->
<section class="section" aria-labelledby="activitiesTitle">
  <div class="container">
    <div class="section-head center reveal">
      <h2 id="activitiesTitle">What CAF does</h2>
      <p>CAF conducts activities throughout the year, all built on one platform: the human voice.</p>
    </div>

    <ul class="activities">
      <li class="activity reveal">
        <span class="icon-badge" aria-hidden="true"><i data-lucide="calendar-days" aria-hidden="true"></i></span>
        <h3>Festival</h3>
        <p>The main annual Culture Acapella Festival.</p>
      </li>
      <li class="activity reveal">
        <span class="icon-badge" aria-hidden="true"><i data-lucide="mic" aria-hidden="true"></i></span>
        <h3>A cappella performances</h3>
        <p>Live performances from invited and registered vocal groups.</p>
      </li>
      <li class="activity reveal">
        <span class="icon-badge" aria-hidden="true"><i data-lucide="music" aria-hidden="true"></i></span>
        <h3>Worship sessions</h3>
        <p>Collective worship through vocal music.</p>
      </li>
      <li class="activity reveal">
        <span class="icon-badge" aria-hidden="true"><i data-lucide="graduation-cap" aria-hidden="true"></i></span>
        <h3>Talent development</h3>
        <p>Training, mentorship and opportunities for emerging singers.</p>
      </li>
      <li class="activity reveal">
        <span class="icon-badge" aria-hidden="true"><i data-lucide="globe" aria-hidden="true"></i></span>
        <h3>Cultural activities</h3>
        <p>Activities celebrating Tanzanian and African cultural identity.</p>
      </li>
      <li class="activity reveal">
        <span class="icon-badge" aria-hidden="true"><i data-lucide="heart-handshake" aria-hidden="true"></i></span>
        <h3>Community outreach</h3>
        <p>Activities supporting children and vulnerable communities.</p>
      </li>
      <li class="activity reveal">
        <span class="icon-badge" aria-hidden="true"><i data-lucide="users" aria-hidden="true"></i></span>
        <h3>Networking</h3>
        <p>Connecting vocal groups, musicians, churches, organizations and potential partners.</p>
      </li>
      <li class="activity reveal">
        <span class="icon-badge" aria-hidden="true"><i data-lucide="shopping-bag" aria-hidden="true"></i></span>
        <h3>CAF merchandise</h3>
        <p>Branded products supporting the festival — see the range below.</p>
      </li>
    </ul>

    <div class="merch reveal">
      <h3>CAF-branded products</h3>
      <div class="merch-chips">
        <span>T-shirts</span><span>Caps</span><span>Hoodies</span><span>Capes</span><span>Flasks</span><span>Cups</span><span>Diaries</span><span>Key holders</span><span>Bags</span><span>And more CAF-branded products</span>
      </div>
    </div>
  </div>
</section>

@include('partials.register-strip')

@endsection