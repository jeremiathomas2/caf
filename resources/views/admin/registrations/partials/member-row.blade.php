@php
    $suffix = str_contains((string) $index, '__') ? '__INDEX__' : $index;
    $name = $member?->name ?? old("members.{$suffix}.name");
    $email = $member?->email ?? old("members.{$suffix}.email");
    $phone = $member?->phone ?? old("members.{$suffix}.phone");
    $part = $member?->part ?? old("members.{$suffix}.part");
    $isLead = (bool) ($member?->is_lead ?? old("members.{$suffix}.is_lead", $suffix === 0 || $suffix === '0'));
@endphp

<div class="member-row card" style="box-shadow:none">
    <div class="card-body form-body" style="padding:14px">
        <div class="field">
            <label for="member-{{ $suffix }}-name">Name <span class="req">*</span></label>
            <input id="member-{{ $suffix }}-name" type="text" name="members[{{ $suffix }}][name]" value="{{ $name }}" required>
        </div>

        <div class="field">
            <label for="member-{{ $suffix }}-part">Part</label>
            <input id="member-{{ $suffix }}-part" type="text" name="members[{{ $suffix }}][part]" value="{{ $part }}"
                   placeholder="Soprano, Bass, Drums…">
        </div>

        <div class="field">
            <label for="member-{{ $suffix }}-email">Email</label>
            <input id="member-{{ $suffix }}-email" type="email" name="members[{{ $suffix }}][email]" value="{{ $email }}">
        </div>

        <div class="field">
            <label for="member-{{ $suffix }}-phone">Phone</label>
            <input id="member-{{ $suffix }}-phone" type="tel" name="members[{{ $suffix }}][phone]" value="{{ $phone }}">
        </div>

        <label class="checkbox">
            <input type="checkbox" name="members[{{ $suffix }}][is_lead]" value="1" @checked($isLead)>
            <span>Group lead</span>
        </label>
    </div>
</div>
