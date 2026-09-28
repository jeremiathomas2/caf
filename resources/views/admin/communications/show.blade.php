@extends('admin.layouts.app')

@section('title', $thread->subject)

@section('content')
    <x-admin.page-head
        :title="$thread->subject"
        :subtitle="trim($thread->contact_name . ' ' . ($thread->contact_email ?: 'no email'))"
    >
        <a class="btn btn-ghost" href="{{ route('admin.communications.index') }}">Back</a>
    </x-admin.page-head>

    @if ($status = session('status'))
        <div class="alert success" role="status">{{ $status }}</div>
    @endif

    <div class="grid-2">
        <div class="card chat">
            <div class="chat-head">
                <x-admin.avatar :name="$thread->contact_name" size="32px" />
                <div class="thread-body">
                    <div class="cell-strong">{{ $thread->contact_name }}</div>
                    <div class="cell-sub">{{ $thread->contact_email ?: $thread->contact_phone ?: 'No contact details' }}</div>
                </div>
                <x-admin.badge :value="$thread->status" />
            </div>

            <div class="chat-body">
                @forelse ($thread->messages->sortBy('sent_at') as $message)
                    <div class="bubble {{ $message->isInbound() ? 'in' : 'out' }}">
                        <b>{{ $message->sender_label }}</b>
                        <p style="margin:5px 0 0">{{ $message->body }}</p>
                        <span class="meta">{{ $message->sent_at?->format('j M Y H:i') }}</span>
                    </div>
                @empty
                    <p class="muted">No messages in this conversation yet.</p>
                @endforelse
            </div>

            @can('communications.manage')
                <div class="card-body form-body" style="border-top:1px solid var(--border)">
                    <form method="POST" action="{{ route('admin.communications.reply', $thread) }}">
                        @csrf
                        <div class="field">
                            <label for="body">Reply</label>
                            <textarea id="body" name="body" rows="4" required
                                      placeholder="Write your reply to {{ $thread->contact_name }}…">{{ old('body') }}</textarea>
                            @error('body')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                            <button type="submit" class="btn btn-primary">@icon('send') Send reply</button>
                            @if ($recipients)
                                <a class="btn btn-ghost" href="mailto:{{ $recipients }}">Open in mail client</a>
                            @endif
                        </div>
                    </form>
                </div>
            @endcan
        </div>

        <div class="stack">
            <div class="card">
                <div class="card-head"><h3>@icon('users') Ownership</h3></div>
                <div class="card-body form-body">
                    <form method="POST" action="{{ route('admin.communications.update', $thread) }}">
                        @csrf
                        <div class="field">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                @foreach (\App\Enums\ThreadStatus::cases() as $option)
                                    <option value="{{ $option->value }}" @selected($thread->status === $option)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label for="assigned_to_id">Owner</label>
                            <select id="assigned_to_id" name="assigned_to_id">
                                <option value="">Unassigned</option>
                                @foreach ($staff as $member)
                                    <option value="{{ $member->id }}" @selected($thread->assigned_to_id === $member->id)>{{ $member->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        @can('communications.manage')
                            <button type="submit" class="btn">Save</button>
                        @endcan
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-head"><h3>@icon('info') Details</h3></div>
                <div class="card-body">
                    <dl class="kv">
                        <dt>Channel</dt><dd>{{ $thread->channel?->label() ?? $thread->channel }}</dd>
                        <dt>Contact</dt><dd>{{ $thread->contact_name }}</dd>
                        <dt>Email</dt><dd>{{ $thread->contact_email ?: '—' }}</dd>
                        <dt>Phone</dt><dd>{{ $thread->contact_phone ?: '—' }}</dd>
                        <dt>Opened</dt><dd>{{ $thread->created_at->format('j M Y') }}</dd>
                        <dt>First reply</dt>
                        <dd>
                            @if ($thread->responseTimeHours() !== null)
                                {{ number_format($thread->responseTimeHours(), 1) }}h
                            @else
                                <span class="muted">Awaiting first reply</span>
                            @endif
                        </dd>
                        @if ($thread->registration)
                            <dt>Group</dt>
                            <dd>
                                <a href="{{ route('admin.registrations.show', $thread->registration) }}">
                                    {{ $thread->registration->group_name }}
                                </a>
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
