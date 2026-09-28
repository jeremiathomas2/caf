@use('App\Enums\RegistrationStatus')
@use('Illuminate\Support\Str')

@extends('layouts.app')

@section('title', 'Check your registration status — Culture Acapella Festival')
@section('description', 'Enter the reference code from your CAF registration to see where your group is in the review process and what happens next.')

@section('content')
<section class="section" aria-labelledby="statusTitle">
  <div class="container">
    <div class="section-head center reveal">
      <h2 id="statusTitle">Check your status</h2>
      <p>Enter the reference code we sent you after submitting your registration to see where your group is in the process.</p>
    </div>

    {{--
      The lookup form and its result are deliberately not given the `reveal`
      class. That animation starts elements at opacity:0 and only reveals them
      once they scroll into view, which left a valid result sitting invisible
      below the fold. Feedback here must never depend on JavaScript.
    --}}
    <form class="form-card" method="GET" action="{{ route('status') }}" role="search" style="max-width:640px;margin:0 auto">
      <div class="field">
        <label for="code">Registration code</label>
        <input id="code" name="code" type="text" value="{{ $code }}" placeholder="CAF2-0001"
               autocomplete="off" autocapitalize="characters" spellcheck="false" required>
        <small>Your code looks like CAF2-0001 and appears in your confirmation message. Capitalisation and spacing do not matter.</small>
      </div>
      <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-top:18px">
        <button class="btn btn-primary" type="submit">Check status</button>
        <a class="btn btn-ghost" href="{{ route('register') }}">Register a group</a>
      </div>
    </form>

    @if ($notFound)
      <div id="status-result" tabindex="-1" style="max-width:640px;margin:32px auto 0">
        <p class="status error" role="alert">
          <strong>No registration found for &ldquo;{{ $code }}&rdquo;.</strong>
        </p>
        <p style="color:var(--muted)">Check the code for typos, or contact us on WhatsApp at
          <a href="https://wa.me/255752312128">+255 752 312 128</a> and we will look it up for you.</p>
      </div>
    @endif

    @if ($registration)
      @php
        $invoice = $registration->latestInvoice();
        $open = ! in_array($registration->status, RegistrationStatus::closed(), true);
        $balance = $registration->outstandingBalance();
        $settled = $balance <= 0;
        $currency = $invoice?->currency ?? $registration->season?->currency ?? 'TZS';
      @endphp

      <div id="status-result" tabindex="-1" style="max-width:760px;margin:32px auto 0">
        {{-- Announced to screen readers so the outcome is never silent. --}}
        <p class="status success" role="status" style="margin:0 0 14px">
          <strong>Found your registration.</strong> Here is where {{ $registration->group_name }} stands today.
        </p>

        <div class="group-card">
          <div style="padding:28px">
            <div style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-start;justify-content:space-between">
              <div>
                <span class="eyebrow"><i></i>{{ $registration->code }}</span>
                <h3 style="font-family:var(--font-head);font-size:28px;font-weight:800;margin:12px 0 4px">{{ $registration->group_name }}</h3>
                <p style="color:var(--muted);margin:0">
                  {{ $registration->season?->name }}
                  @if ($registration->city) &middot; {{ $registration->city }}, {{ $registration->country }} @endif
                </p>
              </div>
              <div style="text-align:right">
                <span class="chip" aria-pressed="true">{{ $registration->status->label() }}</span>
                <p style="color:var(--muted);margin:10px 0 0;font-size:14px">
                  {{ $registration->members_count }} {{ Str::plural('performer', $registration->members_count) }}
                </p>
              </div>
            </div>

            <hr style="border:0;border-top:1px solid var(--border);margin:24px 0">

            {{--
              The step-by-step review history is deliberately not shown here.
              The current status is what a group needs, and the internal history
              of transitions is not meaningful to applicants.
            --}}
            {{--
              The balance is always shown. When no invoice has been issued yet
              the season fee still tells the group what to expect, so the
              payment section never silently disappears.
            --}}
            <h4 style="font-family:var(--font-head);font-size:19px;margin:0 0 12px">
              {{ $invoice ? 'Invoice '.$invoice->number : 'Payment' }}
            </h4>
            <div style="display:flex;flex-wrap:wrap;gap:24px;color:var(--muted)">
              <span>Total <strong style="color:var(--text)">{{ number_format((float) ($invoice?->amount ?? $balance), 0) }} {{ $currency }}</strong></span>
              <span>Paid <strong style="color:var(--text)">{{ number_format((float) ($invoice?->amount_paid ?? 0), 0) }} {{ $currency }}</strong></span>
              <span>Outstanding <strong style="color:var(--text)">{{ number_format($balance, 0) }} {{ $currency }}</strong></span>
            </div>
            @if ($invoice?->due_at && ! $settled)
              <p style="color:var(--muted);margin:14px 0 0;font-size:14.5px">
                Payment is due by {{ $invoice->due_at->format('j M Y') }}.
              </p>
            @endif
            @if (! $settled)
              <p style="color:var(--muted);margin:14px 0 0;font-size:14.5px">
                Payment can be made by M-Pesa, Mixx by Yas, Airtel Money, Halopesa, card or bank transfer.
                @if ($invoice) Send your reference <strong>{{ $invoice->number }}</strong> and we will confirm it here. @endif
              </p>
            @else
              <p style="color:var(--muted);margin:14px 0 0;font-size:14.5px">
                Nothing further is owed on this registration.
              </p>
            @endif

            <hr style="border:0;border-top:1px solid var(--border);margin:24px 0">

            <p style="margin:0">
              @if ($open)
                <strong>What happens next:</strong> our review team is still working on your registration. You do not need to
                do anything right now — check back here, or message us on WhatsApp at
                <a href="https://wa.me/255752312128">+255 752 312 128</a> if anything changes.
              @else
                This registration is closed. If you think that is wrong, reply to the message we sent you or message
                <a href="https://wa.me/255752312128">+255 752 312 128</a> and we will take another look.
              @endif
            </p>
          </div>
        </div>
      </div>
    @endif
  </div>
</section>

{{-- Bring the outcome into view and move focus to it, so a lookup that
     happens below the fold still lands in front of the visitor. The result
     above is already visible without this; this only adds the scroll. --}}
@if ($searched)
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var result = document.getElementById('status-result');
      if (!result) return;
      result.scrollIntoView({behavior: 'smooth', block: 'center'});
      result.focus({preventScroll: true});
    });
  </script>
@endif
@endsection
