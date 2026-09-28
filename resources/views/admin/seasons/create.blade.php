@extends('admin.layouts.app')

@section('title', 'New season')

@section('content')
    <x-admin.page-head
        title="New season"
        subtitle="Set the identity, window and fees for a festival edition."
    >
        <a class="btn btn-ghost" href="{{ route('admin.seasons.index') }}">Cancel</a>
    </x-admin.page-head>

    <form method="POST" action="{{ route('admin.seasons.store') }}" class="form-grid">
        @csrf

        @include('admin.seasons.partials.fields', ['season' => $season])

        <div class="card" style="grid-column:1/-1">
            <div class="card-head"><h3>@icon('check') Save season</h3></div>
            <div class="card-body" style="display:flex;gap:10px;justify-content:flex-end">
                <a class="btn btn-ghost" href="{{ route('admin.seasons.index') }}">Cancel</a>
                <button type="submit" class="btn btn-primary">Create season</button>
            </div>
        </div>
    </form>
@endsection
