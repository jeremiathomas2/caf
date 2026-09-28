@extends('admin.layouts.app')

@section('title', 'Edit '.$season->name)

@section('content')
    <x-admin.page-head
        :title="'Edit Season '.$season->number"
        :subtitle="$season->name"
    >
        <a class="btn btn-ghost" href="{{ route('admin.seasons.show', $season) }}">Cancel</a>
    </x-admin.page-head>

    <form method="POST" action="{{ route('admin.seasons.update', $season) }}" class="form-grid">
        @csrf
        @method('PUT')

        @include('admin.seasons.partials.fields', ['season' => $season])

        <div class="card" style="grid-column:1/-1">
            <div class="card-head"><h3>@icon('check') Save changes</h3></div>
            <div class="card-body" style="display:flex;gap:10px;justify-content:flex-end">
                <a class="btn btn-ghost" href="{{ route('admin.seasons.show', $season) }}">Cancel</a>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </form>
@endsection
