@php
    use App\Enums\RegistrationRole;

    $value = fn (string $key, mixed $default = null): mixed => old($key, $registration->{$key} ?? $default);
@endphp

<div class="card">
    <div class="card-head">
        <div>
            <h3>@icon('file') Group</h3>
            <p>Who is registering and who to contact.</p>
        </div>
    </div>
    <div class="card-body form-body">
        <input type="hidden" name="season_id" value="{{ $value('season_id', $season->getKey()) }}">

        <div class="field">
            <label for="group_name">Group name <span class="req">*</span></label>
            <input id="group_name" type="text" name="group_name" value="{{ $value('group_name') }}" required>
        </div>

        <div class="field">
            <label for="role_type">Registering as <span class="req">*</span></label>
            <select id="role_type" name="role_type" required data-role-toggle>
                @foreach (RegistrationRole::cases() as $option)
                    <option value="{{ $option->value }}" @selected($value('role_type')?->value === $option->value)>
                        {{ $option->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        @foreach (RegistrationRole::cases() as $option)
            <div class="field" data-category-group="{{ $option->value }}"
                 @hidden($value('role_type')?->value !== $option->value)>
                <label for="category-{{ $option->value }}">
                    {{ $option === RegistrationRole::Singers ? 'Category' : 'I am a' }} <span class="req">*</span>
                </label>
                <select id="category-{{ $option->value }}" name="category" data-category-select
                        @disabled($value('role_type')?->value !== $option->value)>
                    @foreach ($option->categories() as $category)
                        <option value="{{ $category }}" @selected($value('category') === $category)>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach

        <div class="field">
            <label for="members_count">Performers <span class="req">*</span></label>
            <input id="members_count" type="number" min="1" max="60" name="members_count"
                   value="{{ $value('members_count', 3) }}" required>
            <p class="field-hint">Must match the number of members listed below.</p>
        </div>

        <div class="field">
            <label for="country">Country</label>
            <input id="country" type="text" name="country" value="{{ $value('country') }}">
        </div>

        <div class="field">
            <label for="city">City</label>
            <input id="city" type="text" name="city" value="{{ $value('city') }}">
        </div>

        <div class="field">
            <label for="contact_name">Contact name <span class="req">*</span></label>
            <input id="contact_name" type="text" name="contact_name" value="{{ $value('contact_name') }}" required>
        </div>

        <div class="field">
            <label for="contact_email">Contact email <span class="req">*</span></label>
            <input id="contact_email" type="email" name="contact_email" value="{{ $value('contact_email') }}" required>
        </div>

        <div class="field">
            <label for="contact_phone">Contact phone</label>
            <input id="contact_phone" type="tel" name="contact_phone" value="{{ $value('contact_phone') }}">
        </div>

        <div class="field">
            <label for="performance_link">Performance link</label>
            <input id="performance_link" type="url" name="performance_link"
                   value="{{ $value('performance_link') }}" placeholder="https://">
        </div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <div>
            <h3>@icon('users') Members</h3>
            <p>List every performer. The first one is marked as the group lead.</p>
        </div>
        <button type="button" class="btn btn-sm btn-ghost" id="addMember">@icon('plus') Add member</button>
    </div>
    <div class="card-body">
        <div id="memberRows" class="stack">
            @foreach ($members as $index => $member)
                @include('admin.registrations.partials.member-row', ['index' => $index, 'member' => $member])
            @endforeach
        </div>

        <template id="memberRowTemplate">
            @include('admin.registrations.partials.member-row', ['index' => '__INDEX__', 'member' => null])
        </template>
    </div>
</div>

<div class="card">
    <div class="card-head"><h3>@icon('file') Extra</h3></div>
    <div class="card-body form-body">
        <div class="field" style="grid-column:1/-1">
            <label for="bio">Public biography</label>
            <textarea id="bio" name="bio" rows="3">{{ $value('bio') }}</textarea>
        </div>

        <div class="field" style="grid-column:1/-1">
            <label for="notes">Internal notes</label>
            <textarea id="notes" name="notes" rows="3">{{ $value('notes') }}</textarea>
        </div>

        <label class="checkbox" style="grid-column:1/-1">
            <input type="checkbox" name="is_public" value="1" @checked($value('is_public', false))>
            <span>Show this group on the public site</span>
        </label>
    </div>
</div>

<script>
    (function () {
        const roleSelect = document.querySelector('[data-role-toggle]');
        const groups = document.querySelectorAll('[data-category-group]');

        if (roleSelect && groups.length) {
            const sync = () => {
                groups.forEach((group) => {
                    const active = group.dataset.categoryGroup === roleSelect.value;
                    group.hidden = !active;
                    const select = group.querySelector('[data-category-select]');
                    if (select) select.disabled = !active;
                });
            };

            roleSelect.addEventListener('change', sync);
            sync();
        }

        const host = document.getElementById('memberRows');
        const template = document.getElementById('memberRowTemplate');
        const addButton = document.getElementById('addMember');
        if (!host || !template) return;

        let nextIndex = host.querySelectorAll('.member-row').length;

        addButton?.addEventListener('click', () => {
            const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
            host.insertAdjacentHTML('beforeend', html);

            const rows = host.querySelectorAll('.member-row');
            const latest = rows[rows.length - 1];
            latest?.querySelector('input')?.focus();
        });
    })();
</script>
