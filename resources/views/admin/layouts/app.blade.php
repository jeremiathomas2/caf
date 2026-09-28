{{--
    The management system shell.

    Structure and class names mirror caf-system2.html; the dynamic parts
    (navigation, season menu, command palette, flash messages) are supplied by
    ShareSiteContext and consumed by resources/js/admin.js.
--}}
@php
    $flashes = [];

    if (session('status')) {
        $flashes[] = ['message' => session('status'), 'tone' => 'info'];
    }

    if (session('success')) {
        $flashes[] = ['message' => session('success'), 'tone' => ''];
    }

    if (session('error')) {
        $flashes[] = ['message' => session('error'), 'tone' => 'danger'];
    }

    $errors = $errors->all();

    if ($errors !== []) {
        $flashes[] = ['message' => $errors[0], 'tone' => 'danger'];
    }

    $adminConfig = [
        'commands' => $adminCommands ?? [],
        'theme' => request()->cookie('caf-theme', 'light'),
        'flashes' => $flashes,
        'csrf' => csrf_token(),
    ];
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('caf.brand.name') }}</title>
    @vite(['resources/css/admin.css', 'resources/js/admin-app.js'])
    <style>
        /* Apply the stored theme before first paint to avoid a flash. */
        try {
            const t = localStorage.getItem('caf-theme');
            if (t) document.documentElement.setAttribute('data-theme', t);
        } catch (e) {}
    </style>
</head>
<body>
<div class="app">

    @include('admin.partials.sidebar')

    <div class="scrim" id="scrim"></div>

    <div class="main">
        @include('admin.partials.topbar')

        <main class="content" id="view" tabindex="-1">
            @yield('content')
        </main>
    </div>
</div>

@include('admin.partials.season-menu')
@include('admin.partials.notifications')
@include('admin.partials.command-palette')

<div class="toasts" id="toasts" aria-live="polite"></div>

<script type="application/json" id="caf-admin-config">@json($adminConfig)</script>
</body>
</html>
