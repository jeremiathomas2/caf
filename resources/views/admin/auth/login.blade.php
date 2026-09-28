<x-admin.auth-frame
    title="Sign in"
    heading="Welcome back"
    intro="Sign in to the CAF admin console. Two-factor authentication is required for admin and finance roles."
    :art-stats="[
        $stats['seasons'] => 'Seasons',
        $stats['registrations'] => 'Groups',
        $stats['countries'] => 'Countries',
    ]"
>
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

    <form method="POST" action="{{ route('admin.login.store') }}">
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

        <div class="field">
            <label for="password">Password</label>
            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                <input
                    id="password"
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    placeholder="••••••••"
                    required
                >
            </div>
        </div>

        <div class="login-row">
            <label class="checkbox">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                Keep me signed in
            </label>
            <a href="{{ route('admin.password.request') }}">Forgot password?</a>
        </div>

        <button class="btn btn-primary btn-block" type="submit">
            Continue
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
        </button>
    </form>

    <p class="login-alt">
        <a href="/">&larr; Back to the public site</a>
    </p>
</x-admin.auth-frame>
