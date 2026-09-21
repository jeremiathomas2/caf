@extends('layouts.app')

@section('title', 'Programme — Culture Acapella Festival · Season 2')
@section('description', 'A first look at the shape of the day. The full Season 2 programme with confirmed groups is published after registration closes on 30 October 2026.')

@section('content')

<!-- ============================================================
     Programme
     ============================================================ -->
<section class="section section-alt" id="programme" aria-labelledby="progTitle">
  <div class="container">
    <div class="section-head reveal">
      <h2 id="progTitle">Season 2 programme preview</h2>
      <p>A first look at the shape of the day. The full programme with confirmed groups is published after registration closes on 30 October 2026.</p>
    </div>

    <div class="program">
      <div class="day reveal">
        <div>
          <div class="day-label">Day one</div>
          <div class="day-date">17<small>July 2027 · Saturday</small></div>
        </div>
        <div class="slots">
          <div class="slot">
            <div class="slot-time">09:00</div>
            <div class="slot-copy">
              <h4>Gates open &amp; group check-in</h4>
              <p>Groups arrive, warm up and register with the festival desk.</p>
            </div>
            <span class="slot-stage">Main stage</span>
          </div>
          <div class="slot">
            <div class="slot-time">11:00</div>
            <div class="slot-copy">
              <h4>Opening ceremony &amp; welcome</h4>
              <p>Welcome from the CAF team and a word from our beneficiary partners.</p>
            </div>
            <span class="slot-stage">Main stage</span>
          </div>
          <div class="slot">
            <div class="slot-time">12:00</div>
            <div class="slot-copy">
              <h4>Youth category performances</h4>
              <p>Youth groups take the stage, each with a 12-minute set.</p>
            </div>
            <span class="slot-stage">Main stage</span>
          </div>
          <div class="slot">
            <div class="slot-time">14:30</div>
            <div class="slot-copy">
              <h4>Gospel category performances</h4>
              <p>Gospel ensembles share their arrangements with the audience.</p>
            </div>
            <span class="slot-stage">Main stage</span>
          </div>
          <div class="slot">
            <div class="slot-time">17:00</div>
            <div class="slot-copy">
              <h4>Afro &amp; contemporary showcases</h4>
              <p>Cross-genre acapella featuring our international groups.</p>
            </div>
            <span class="slot-stage">Main stage</span>
          </div>
          <div class="slot">
            <div class="slot-time">19:30</div>
            <div class="slot-copy">
              <h4>Finals &amp; awards</h4>
              <p>Finalists perform, judges announce the Season 2 winners.</p>
            </div>
            <span class="slot-stage">Main stage</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@include('partials.register-strip')

@endsection