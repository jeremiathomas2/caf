@extends('layouts.app')

@section('title', 'Terms & Conditions — Culture Acapella Festival')
@section('description', 'The official terms and conditions for participating in the Culture Acapella Festival (CAF): registration, eligibility, contribution, conduct, media, intellectual property and more.')

@section('content')

<!-- ============================================================
     Terms & Conditions
     ============================================================ -->
<section class="section" id="terms" aria-labelledby="termsTitle">
  <div class="container">
    <div class="section-head center reveal">
      <span class="eyebrow" style="background:var(--cream-200);border-color:var(--sand);color:var(--brand-800)"><i></i> Legal</span>
      <h2 id="termsTitle" style="margin-top:14px">Culture Acapella Festival — Terms &amp; Conditions</h2>
      <p style="max-width:64ch;margin-inline:auto">These terms govern registration and participation in the Culture Acapella Festival (CAF). Registration or participation in CAF constitutes acknowledgement that the participant has read, understood and agreed to comply with the applicable festival terms and conditions.</p>
    </div>

    <ol class="terms-ol reveal">
      <li>
        <h3>1. Registration</h3>
        <p>All participating groups must complete the official CAF registration process within the announced registration period. Providing false or misleading information may result in cancellation of registration.</p>
      </li>
      <li>
        <h3>2. Eligibility</h3>
        <p>Participants must comply with the festival's stated requirements for their respective category. CAF reserves the right to review participation applications before confirming inclusion in the official programme.</p>
      </li>
      <li>
        <h3>3. Registration Deadline</h3>
        <p>Participants must register before the officially announced deadline. For Season 2, the current registration deadline is <strong>30 October 2026</strong>. Registrations submitted after the deadline may be subject to additional requirements or may not be accepted.</p>
      </li>
      <li>
        <h3>4. Participation Contribution</h3>
        <p>Where CAF has announced a participation contribution, participating groups are responsible for ensuring that the required contribution is submitted according to the official instructions. For Season 2, CAF has announced that after 30 October 2026, participating groups will contribute <strong>TSh 10,000 per participant/head</strong>, subject to the final official registration terms.</p>
      </li>
      <li>
        <h3>5. Performance</h3>
        <p>Participating groups must arrive at the venue at the time communicated by the CAF organizing team. Groups are expected to respect their assigned performance duration. Failure to arrive on time may affect the group's position in the programme.</p>
      </li>
      <li>
        <h3>6. Professional Conduct</h3>
        <p>All participants are expected to maintain professional and respectful conduct. The following are not permitted: violence, harassment, abusive behaviour, discrimination, deliberate damage to property, or behaviour that seriously disrupts the festival.</p>
      </li>
      <li>
        <h3>7. Gospel &amp; Festival Standards</h3>
        <p>CAF is primarily a gospel a cappella festival. Performances should therefore remain consistent with the festival's purpose, values and programme guidelines.</p>
      </li>
      <li>
        <h3>8. Respect for Other Participants</h3>
        <p>Participants must respect other groups, artists, organizers, volunteers, guests and members of the audience. Competition or differences in musical style should never become a reason for disrespect.</p>
      </li>
      <li>
        <h3>9. Changes to the Programme</h3>
        <p>CAF organizers reserve the right to make reasonable changes to performance schedules, running order, venue arrangements, activities, guest appearances and programme structure where necessary for logistical, safety or organizational reasons.</p>
      </li>
      <li>
        <h3>10. Cancellation or Postponement</h3>
        <p>CAF may postpone, modify or cancel an activity where circumstances beyond the organizers' reasonable control make it necessary — for example government restrictions, safety concerns, natural disasters, venue problems, serious emergencies or other circumstances affecting the successful and safe running of the event.</p>
      </li>
      <li>
        <h3>11. Media &amp; Photography</h3>
        <p>By participating in CAF, participants acknowledge that photographs, video and audio recordings may be taken during festival activities. CAF may use appropriate event footage and photographs for promotion, social media, the website, reports, archives and future festival marketing. Individual participants or groups should notify the CAF team in advance if there is a specific legitimate concern regarding particular media use.</p>
      </li>
      <li>
        <h3>12. Intellectual Property</h3>
        <p>Participants retain ownership of their original musical works and creative material unless a separate written agreement states otherwise. CAF should not reproduce or commercially exploit a participant's original work beyond agreed festival-related promotional use without appropriate permission.</p>
      </li>
      <li>
        <h3>13. Sponsors &amp; Partners</h3>
        <p>CAF may work with sponsors, businesses, organizations, institutions and other partners. Sponsors and partners must respect CAF's purpose, values and applicable agreements.</p>
      </li>
      <li>
        <h3>14. Merchandise</h3>
        <p>CAF-branded merchandise may be sold as part of festival fundraising, branding and sustainability activities. Prices, availability and designs may change depending on production costs and stock.</p>
      </li>
      <li>
        <h3>15. Donations &amp; Community Support</h3>
        <p>Where CAF receives donations or funds specifically designated for community support, the organizers should use those funds for the stated purpose and maintain appropriate records.</p>
      </li>
      <li>
        <h3>16. Code of Conduct</h3>
        <p>All participants, volunteers, staff and guests are expected to contribute to a safe, respectful and welcoming festival environment.</p>
      </li>
      <li>
        <h3>17. Organizer's Authority</h3>
        <p>The CAF organizing team has authority to make reasonable decisions concerning festival operations, participant coordination, scheduling, safety and programme management.</p>
      </li>
      <li>
        <h3>18. Acceptance of Terms</h3>
        <p>Registration or participation in CAF constitutes acknowledgement that the participant has read, understood and agreed to comply with the applicable festival terms and conditions.</p>
      </li>
    </ol>

    <div class="impact reveal">
      <div>
        <span class="eyebrow" style="background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.24);color:#fff"><i></i> Official contacts</span>
        <h2 style="margin-top:20px">Questions about participation or terms?</h2>
        <p>Contact our coordinator or the founder directly — we are happy to clarify anything before you register.</p>
        <div class="impact-stats">
          <div class="impact-stat">
            <strong>Robert Samwel</strong>
            <span>Founder &amp; CEO · 0756 290 251</span>
          </div>
          <div class="impact-stat">
            <strong>Praise Nyombi</strong>
            <span>Coordinator · 0752 312 128</span>
          </div>
          <div class="impact-stat">
            <strong>Arusha</strong>
            <span>Arusha, Tanzania</span>
          </div>
        </div>
      </div>
      <a class="btn btn-primary" href="{{ route('register') }}">Register your group</a>
    </div>
  </div>
</section>

@endsection