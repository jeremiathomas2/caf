{{--
    Shared frame for the standalone authentication screens. The two-factor
    and password screens sit in the right-hand panel of the split shell.
--}}
@props([
    'title' => 'Sign in',
    'heading' => 'Welcome back',
    'intro' => '',
    'artHeading' => 'Singing to',
    'artEmphasis' => 'Save Lives',
    'artBody' => 'The management system that runs CAF season after season — registration, judging, payments, communications and analytics, all in one place.',
    'artStats' => null,
])

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · {{ config('caf.brand.name') }}</title>
    @vite('resources/css/admin-login.css')
</head>
<body>
<a class="skip" href="#main">Skip to main content</a>

<section class="login-shell" aria-label="{{ $heading }}">
    <div class="login-art" aria-hidden="true">
        <div>
            <span class="login-brand">
                <span class="login-brand-mark">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 17 L8 12 L12 17 L16 12 L20 17"/>
                        <path d="M8 13 V6 M12 13 V4 M16 13 V7"/>
                    </svg>
                </span>
                <span>
                    {{ config('caf.brand.tagline') }}
                    <small>Admin Console</small>
                </span>
            </span>
        </div>
        <div class="login-art-body">
            <h2>{{ $artHeading }} <em>{{ $artEmphasis }}</em></h2>
            <p>{{ $artBody }}</p>
            @if ($artStats)
                <div class="login-art-stats">
                    @foreach ($artStats as $value => $label)
                        <div class="login-art-stat"><strong>{{ $value }}</strong><span>{{ $label }}</span></div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="login-panel">
        <div class="login-panel-inner" id="main">
            <div class="login-step active">
                <div class="login-head">
                    <h1>{{ $heading }}</h1>
                    <p>{{ $intro }}</p>
                </div>

                {{ $slot }}
            </div>
        </div>
    </div>
</section>
</body>
</html>
