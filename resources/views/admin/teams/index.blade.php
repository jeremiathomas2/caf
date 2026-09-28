@extends('admin.layouts.app')

@section('title', 'Team')

@section('content')
    <x-admin.page-head
        title="Team"
        subtitle="The organising committee and the staff accounts behind the festival"
    />

    <div class="card" style="margin-bottom:16px">
        <div class="card-head">
            <div>
                <h3>@icon('users') Committee &amp; staff</h3>
                <p>{{ $members->count() }} people &middot; {{ $publicCount }} shown on the public site</p>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Contact</th>
                        <th>Visibility</th>
                        @can('teams.manage')
                            <th></th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        <tr>
                            <td>
                                <div class="cell-group">
                                    <x-admin.avatar :name="$member->name" class="mini-avatar" />
                                    <div>
                                        <div class="cell-strong">{{ $member->name }}</div>
                                        @if ($member->bio)
                                            <div class="cell-sub">{{ Str::limit($member->bio, 60) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge outline">{{ $member->role_title }}</span></td>
                            <td>
                                <div>{{ $member->email ?? '—' }}</div>
                                @if ($member->phone)
                                    <div class="cell-sub">{{ $member->phone }}</div>
                                @endif
                            </td>
                            <td>
                                <x-admin.badge :value="$member->is_public ? 'published' : 'draft'" />
                            </td>
                            @can('teams.manage')
                                <td>
                                    <form method="POST" action="{{ route('admin.teams.destroy', $member) }}"
                                          onsubmit="return confirm('Remove {{ $member->name }} from the team?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-ghost" aria-label="Remove {{ $member->name }}">
                                            @icon('x')
                                        </button>
                                    </form>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:0">
                                <div class="empty">
                                    @icon('users')
                                    <b>No team members yet</b>
                                    <p>Add the people who run the festival so they appear on the public site.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-bottom:16px">
        <div class="card-head">
            <div>
                <h3>@icon('lock') Access roles</h3>
                <p>{{ count($roles) }} defined roles &middot; permissions are code-backed in <code>UserRole</code></p>
            </div>
        </div>
        <div class="card-body" style="padding:0">
            @foreach ($roles as $role)
                <div class="queue-item" style="cursor:default">
                    <span class="queue-ico {{ $role->accessLevel() === 'highest' ? 'purple' : 'blue' }}">@icon('lock')</span>
                    <div class="queue-text">
                        <b>{{ $role->label() }}</b>
                        <span>{{ $role->description() }}</span>
                    </div>
                    <span class="badge gray" style="font-size:10px">{{ Str::headline($role->accessLevel()) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    @can('teams.manage')
        <div class="card">
            <div class="card-head">
                <div><h3>@icon('plus') Add a team member</h3><p>Public members appear on the About page.</p></div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.teams.store') }}" class="grid grid-2">
                    @csrf
                    <label>
                        <span class="lbl">Name</span>
                        <input type="text" name="name" required maxlength="255">
                    </label>
                    <label>
                        <span class="lbl">Role</span>
                        <input type="text" name="role_title" required maxlength="255" placeholder="Festival director">
                    </label>
                    <label>
                        <span class="lbl">Email</span>
                        <input type="email" name="email" maxlength="255">
                    </label>
                    <label>
                        <span class="lbl">Phone</span>
                        <input type="text" name="phone" maxlength="30">
                    </label>
                    <label style="grid-column:1/-1">
                        <span class="lbl">Bio</span>
                        <textarea name="bio" rows="3" maxlength="2000"></textarea>
                    </label>
                    <div style="display:flex;align-items:center;gap:14px">
                        <label class="check-label">
                            <input type="checkbox" name="is_public" value="1" checked> Show on public site
                        </label>
                        <button type="submit" class="btn btn-primary">@icon('check') Add member</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan
@endsection
