<x-admin.auth-frame
    title="Set a new password"
    heading="Choose a new password"
    intro="Pick a password of at least ten characters that mixes letters and numbers."
    art-heading="Secure your"
    art-emphasis="account"
    art-body="You will be signed out of other devices once the new password is saved."
>
    @if ($errors->any())
        <div class="alert danger" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="field">
            <label for="email">Work email</label>
            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required>
            </div>
        </div>

        <div class="field">
            <label for="password">New password</label>
            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                <input id="password" type="password" name="password" autocomplete="new-password" required autofocus>
            </div>
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm new password</label>
            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
            </div>
        </div>

        <button class="btn btn-primary btn-block" type="submit">
            Save password
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
        </button>
    </form>

    <p class="login-alt">
        <a href="{{ route('admin.login') }}">&larr; Back to sign in</a>
    </p>
</x-admin.auth-frame>
