<x-admin.auth-frame
    title="Forgot password"
    heading="Forgot your password?"
    intro="Enter the work email on your account and we will send a link to set a new password."
    art-heading="Reset your"
    art-emphasis="password"
    art-body="A reset link lets you choose a new password. The link is valid for a short period and can only be used once."
>
    @if (session('dev_link'))
        <div class="alert warn" role="status">
            No mailer is configured in this environment. Use this link to continue:
            <a class="mono" href="{{ session('dev_link') }}">{{ session('dev_link') }}</a>
        </div>
    @endif

    @if (session('status'))
        <div class="alert info" role="status">{{ session('status') }}</div>
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

    <form method="POST" action="{{ route('admin.password.email') }}">
        @csrf

        <div class="field">
            <label for="email">Work email</label>
            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    placeholder="you@cultureacapellafestival.com"
                    required
                    autofocus
                >
            </div>
        </div>

        <button class="btn btn-primary btn-block" type="submit">
            Email the reset link
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
        </button>
    </form>

    <p class="login-alt">
        <a href="{{ route('admin.login') }}">&larr; Back to sign in</a>
    </p>
</x-admin.auth-frame>
