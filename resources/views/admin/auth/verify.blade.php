<x-admin.auth-frame
    title="Two-factor verification"
    heading="Two-factor authentication"
    :intro="'Enter the six digit code sent to '.e($user->email).' to continue.'"
    art-heading="One more"
    art-emphasis="step"
    art-body="Confirm the code sent to your registered device to finish signing in. The code expires in five minutes."
>
    @if (session('dev_code'))
        <div class="alert info" role="status">
            No mailer is configured in this environment, so the code is shown here:
            <b class="mono">{{ session('dev_code') }}</b>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert danger" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.verify.store') }}">
        @csrf

        <div class="field">
            <label for="code">Verification code</label>
            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                <input
                    id="code"
                    type="text"
                    name="code"
                    inputmode="numeric"
                    pattern="\d{6}"
                    maxlength="6"
                    autocomplete="one-time-code"
                    placeholder="000000"
                    class="mono code-input"
                    required
                    autofocus
                >
            </div>
            <p class="field-hint">The code expires in five minutes and may only be used once.</p>
        </div>

        <button class="btn btn-primary btn-block" type="submit">
            Verify &amp; sign in
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
        </button>
    </form>

    <p class="login-alt">
        <a href="{{ route('admin.login') }}">&larr; Back to sign in</a>
    </p>
</x-admin.auth-frame>
