@extends('admin.layouts.app')

@section('title', 'New registration')

@section('content')
    <x-admin.page-head
        title="New registration"
        :subtitle="'Manual entry for Season '.$season->number.' · next code '.$nextCode"
    >
        <a class="btn btn-ghost" href="{{ route('admin.registrations.index') }}">Cancel</a>
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

    <form method="POST" action="{{ route('admin.registrations.store') }}" class="form-grid">
        @csrf

        @include('admin.registrations.partials.fields', [
            'season' => $season,
            'registration' => $registration,
            'members' => [
                (object) ['name' => '', 'email' => null, 'phone' => null, 'part' => null, 'is_lead' => true],
                (object) ['name' => '', 'email' => null, 'phone' => null, 'part' => null, 'is_lead' => false],
                (object) ['name' => '', 'email' => null, 'phone' => null, 'part' => null, 'is_lead' => false],
            ],
        ])

        <div class="card" style="grid-column:1/-1">
            <div class="card-head">
                <div>
                    <h3>@icon('check') Save</h3>
                    <p>An invoice is issued automatically for {{ number_format((float) $season->feeFor($registration->members_count ?? 3), 0) }} {{ $season->currency }}.</p>
                </div>
            </div>
            <div class="card-body" style="display:flex;gap:10px;justify-content:flex-end">
                <a class="btn btn-ghost" href="{{ route('admin.registrations.index') }}">Cancel</a>
                <button type="submit" class="btn btn-primary">Create registration</button>
            </div>
        </div>
    </form>
@endsection
