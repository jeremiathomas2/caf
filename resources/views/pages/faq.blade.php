@extends('layouts.app')

@section('title', 'FAQ — Culture Acapella Festival · Season 2')
@section('description', 'Answers to the most common questions about registering for CAF Season 2: who can register, costs, selection, what happens with the money raised and more.')

@section('content')

<!-- ============================================================
     FAQ
     ============================================================ -->
<section class="section" id="faq" aria-labelledby="faqTitle">
  <div class="container">
    <div class="section-head center reveal">
      <h2 id="faqTitle">Questions before you register</h2>
    </div>

    <div class="faq">
      <details>
        <summary>Who can register for CAF Season 2?<span class="plus" aria-hidden="true"></span></summary>
        <p>Any acapella group from anywhere in the world is welcome, though the festival is designed with East African groups in mind. Groups must have between three and twelve members, sing without instrumental accompaniment (occasional vocal percussion is fine) and agree to our code of conduct.</p>
      </details>
      <details>
        <summary>How much does participation cost?<span class="plus" aria-hidden="true"></span></summary>
        <p>For Season 2, CAF has announced that after 30 October 2026 participating groups will contribute <strong>TSh 10,000 per participant/head</strong>, subject to the final official registration terms. The contribution is per participant, not per group. A partial payment plan is available — talk to the coordinator before the deadline.</p>
      </details>
      <details>
        <summary>When does registration close?<span class="plus" aria-hidden="true"></span></summary>
        <p>Registration for Season 2 closes on <strong>30 October 2026</strong>. Registrations submitted after the deadline may be subject to additional requirements or may not be accepted.</p>
      </details>
      <details>
        <summary>How are groups selected?<span class="plus" aria-hidden="true"></span></summary>
        <p>Every complete registration is reviewed by our judging panel against a published rubric covering vocal blend, arrangement, technical control, stage presence, cultural authenticity and message. Reviews happen in two rounds. Shortlisted groups are notified by email and SMS.</p>
      </details>
      <details>
        <summary>What happens with the money raised?<span class="plus" aria-hidden="true"></span></summary>
        <p>Proceeds from registration fees, sponsorships and the festival itself are directed to our beneficiary partners, who support children living in difficult circumstances. A full impact report is published after each season.</p>
      </details>
      <details>
        <summary>Do you help with accommodation and transport?<span class="plus" aria-hidden="true"></span></summary>
        <p>Selected groups receive a logistics pack covering accommodation options, transport to and from the venue, rehearsal times and technical requirements. Some support is available for groups travelling from outside Arusha — ask us when you register.</p>
      </details>
      <details>
        <summary>Can I check my registration status?<span class="plus" aria-hidden="true"></span></summary>
        <p>Yes. After you submit your registration you'll receive a unique code. Use the status lookup on this site to see your current status, the date it last changed, and any outstanding actions.</p>
      </details>
      <details>
        <summary>Is the festival open to the public?<span class="plus" aria-hidden="true"></span></summary>
        <p>Season 2 is primarily a festival for groups and invited guests. Audience tickets, if released, will be announced on this site and across our social channels.</p>
      </details>
      <details>
        <summary>Where can I read the official terms &amp; conditions?<span class="plus" aria-hidden="true"></span></summary>
        <p>Our full official Terms &amp; Conditions cover registration, eligibility, the participation deadline, contribution, performance, conduct, media, intellectual property and more. <a class="link" href="{{ route('terms') }}">Read the CAF Terms &amp; Conditions</a></p>
      </details>
    </div>
  </div>
</section>

@include('partials.register-strip')

@endsection