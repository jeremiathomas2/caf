@extends('admin.layouts.app')

@section('title', 'Edit '.$registration->group_name)

@section('content')
    <x-admin.page-head
        :title="'Edit '.$registration->group_name"
        :subtitle="$registration->code.' · '.$registration->status->label()"
    >
        <a class="btn btn-ghost" href="{{ route('admin.registrations.show', $registration) }}">Cancel</a>
    </x-admin.page-head>

    @if ($errors->any())
        <div class="alert danger" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.registrations.update', $registration) }}" class="form-grid">
        @csrf
        @method('PUT')

        @include('admin.registrations.partials.fields', [
            'season' => $season,
            'registration' => $registration,
            'members' => $members,
        ])

        <div class="card" style="grid-column:1/-1">
            <div class="card-head"><h3>@icon('check') Save changes</h3></div>
            <div class="card-body" style="display:flex;gap:10px;justify-content:flex-end">
                <a class="btn btn-ghost" href="{{ route('admin.registrations.show', $registration) }}">Cancel</a>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.registrations.destroy', $registration) }}"
          data-confirm="Remove {{ $registration->code }} ({{ $registration->group_name }})? This cannot be undone."
          style="margin-top:16px">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-ghost" style="color:var(--danger)">@icon('x') Remove registration</button>
    </form>
@endsection
