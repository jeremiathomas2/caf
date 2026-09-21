@extends('layouts.app')

@section('title', 'Register Your Group — Culture Acapella Festival · Season 2')
@section('description', 'Tell us about your group and register for CAF Season 2 in Arusha. Share your details and a link to your most recent performance — we reply within one working day.')

@section('content')

<!-- ============================================================
     Register / enquiry
     ============================================================ -->
<section class="section section-alt" id="register" aria-labelledby="regTitle">
  <div class="container">
    <div class="section-head center reveal">
      <h2 id="regTitle">Register your group</h2>
      <p>Registration is open until 30 October 2026. After the deadline, participating groups contribute TSh 10,000 per participant/head, subject to the final official registration terms.</p>
    </div>

    <div class="cta reveal">
      <div>
        <span class="eyebrow" style="background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.24);color:#fff"><i></i> Registration open</span>
        <h3 style="font-family:var(--font-head);font-size:30px;font-weight:800;line-height:1.2;color:#fff;margin-top:20px">Tell us about your group</h3>
        <p>Share your group's details and a link to your most recent performance. We'll reply within one working day with next steps and the registration pack.</p>
        <p class="cta-alt">Prefer WhatsApp? Message us on <strong>+255 752 312 128</strong>.</p>

        <div class="poster-show" aria-hidden="true">
          <div class="poster-stage">
            <img class="poster active" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_480,q_auto,f_auto/v1790019859/poster-3.jpg" alt="">
            <img class="poster" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_480,q_auto,f_auto/v1790019858/poster-2.jpg" alt="">
            <img class="poster" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_480,q_auto,f_auto/v1790019858/poster-4.jpg" alt="">
            <img class="poster" src="https://res.cloudinary.com/ildlk8cq/image/upload/w_480,q_auto,f_auto/v1790019858/poster-1.jpg" alt="">
          </div>
        </div>
      </div>

      <form class="form-card" id="enquiryForm" novalidate>
        <div class="form-row">
          <div class="field">
            <label for="fname">Group leader's name</label>
            <input id="fname" name="fname" type="text" autocomplete="name" placeholder="Amina Njeri" required>
          </div>
          <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" placeholder="you@example.com" required>
          </div>
        </div>
        <div class="field">
          <label for="role">Registering as</label>
          <div class="role-toggle" id="role" role="group" aria-label="Registration type">
            <button class="role-btn active" type="button" data-role="singers" aria-pressed="true">Singers</button>
            <button class="role-btn" type="button" data-role="others" aria-pressed="false">Others</button>
          </div>
        </div>
        <div class="field">
          <label for="group">Group/Brand/service Name</label>
          <input id="group" name="group" type="text" autocomplete="organization" placeholder="Upendo Voices" required>
        </div>
        <div class="form-row">
          <div class="field">
            <label for="category">Category</label>
            <select id="category" name="category">
              <option>A cappella group</option>
              <option>Gospel group</option>
              <option>Vocal ensemble</option>
              <option>Choir</option>
              <option>Individual vocal artist</option>
              <option>Worship team</option>
              <option>Guest artist</option>
              <option>Cultural performers</option>
            </select>
          </div>
          <div class="field">
            <label for="members" id="membersLabel">Number of members</label>
            <input id="members" name="members" type="number" min="3" max="12" value="6" inputmode="numeric">
          </div>
        </div>
        <div class="field">
          <label for="link">Link to a recent performance</label>
          <input id="link" name="link" type="url" placeholder="https://youtube.com/..." required>
        </div>
        <div class="field">
          <label for="msg">Anything else we should know?</label>
          <textarea id="msg" name="msg" placeholder="Where you're travelling from, questions about the cause, special requirements"></textarea>
        </div>
        <div class="field tos">
          <label class="tos-label">
            <input id="terms" name="terms" type="checkbox" value="agree" required>
            <span>I have read and agree to the <a href="{{ route('terms') }}" target="_blank" rel="noopener">CAF Terms &amp; Conditions</a> (including the Season 2 participation contribution of TSh 10,000 per participant after 30 October 2026).</span>
          </label>
        </div>
        <button class="btn btn-primary" type="submit">
          Send my enquiry
          <i data-lucide="arrow-right" aria-hidden="true"></i>
        </button>
        <p class="status" id="formStatus" role="status" aria-live="polite"></p>
      </form>
    </div>
  </div>
</section>

@endsection