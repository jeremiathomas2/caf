@php
    use App\Enums\SeasonState;

    $errors = $errors->all();
@endphp

<div class="card">
    <div class="card-head">
        <div>
            <h3>@icon('file') Identity</h3>
            <p>How this edition appears across the console and the public site.</p>
        </div>
    </div>
    <div class="card-body form-body">
        <div class="field">
            <label for="number">Season number</label>
            <input id="number" type="number" name="number" value="{{ old('number', $season->number) }}" required>
        </div>

        <div class="field">
            <label for="name">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name', $season->name) }}" required>
        </div>

        <div class="field">
            <label for="slug">Slug</label>
            <input id="slug" type="text" name="slug" value="{{ old('slug', $season->slug) }}" placeholder="season-3">
        </div>

        <div class="field">
            <label for="theme">Theme</label>
            <input id="theme" type="text" name="theme" value="{{ old('theme', $season->theme) }}">
        </div>

        <div class="field">
            <label for="tagline">Tagline</label>
            <input id="tagline" type="text" name="tagline" value="{{ old('tagline', $season->tagline) }}">
        </div>

        <div class="field">
            <label for="state">State</label>
            <select id="state" name="state" data-submit-on-change>
                @foreach (SeasonState::cases() as $option)
                    <option value="{{ $option->value }}" @selected(old('state', $season->state?->value) === $option->value)>
                        {{ $option->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field" style="grid-column:1/-1">
            <label for="summary">Summary</label>
            <textarea id="summary" name="summary" rows="3">{{ old('summary', $season->summary) }}</textarea>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <div>
            <h3>@icon('calendar') Schedule &amp; window</h3>
            <p>When the festival runs and when groups may register.</p>
        </div>
    </div>
    <div class="card-body form-body">
        <div class="field">
            <label for="starts_on">Starts on</label>
            <input id="starts_on" type="date" name="starts_on" value="{{ old('starts_on', $season->starts_on?->format('Y-m-d')) }}">
        </div>

        <div class="field">
            <label for="ends_on">Ends on</label>
            <input id="ends_on" type="date" name="ends_on" value="{{ old('ends_on', $season->ends_on?->format('Y-m-d')) }}">
        </div>

        <div class="field">
            <label for="venue">Venue</label>
            <input id="venue" type="text" name="venue" value="{{ old('venue', $season->venue) }}">
        </div>

        <div class="field">
            <label for="city">City</label>
            <input id="city" type="text" name="city" value="{{ old('city', $season->city) }}">
        </div>

        <div class="field">
            <label for="country">Country</label>
            <input id="country" type="text" name="country" value="{{ old('country', $season->country) }}">
        </div>

        <div class="field">
            <label for="registration_opens_at">Registration opens</label>
            <input id="registration_opens_at" type="datetime-local" name="registration_opens_at"
                   value="{{ old('registration_opens_at', $season->registration_opens_at?->format('Y-m-d\TH:i')) }}">
        </div>

        <div class="field">
            <label for="registration_closes_at">Registration closes</label>
            <input id="registration_closes_at" type="datetime-local" name="registration_closes_at"
                   value="{{ old('registration_closes_at', $season->registration_closes_at?->format('Y-m-d\TH:i')) }}">
        </div>

        <div class="field">
            <label for="early_bird_closes_at">Early bird closes</label>
            <input id="early_bird_closes_at" type="datetime-local" name="early_bird_closes_at"
                   value="{{ old('early_bird_closes_at', $season->early_bird_closes_at?->format('Y-m-d\TH:i')) }}">
        </div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <div>
            <h3>@icon('card') Fees &amp; currency</h3>
            <p>Drives invoice amounts and partial payment rules.</p>
        </div>
    </div>
    <div class="card-body form-body">
        <div class="field">
            <label for="currency">Currency</label>
            <input id="currency" type="text" name="currency" maxlength="3" value="{{ old('currency', $season->currency ?? 'TZS') }}" required>
        </div>

        <div class="field">
            <label for="secondary_currency">Secondary currency</label>
            <input id="secondary_currency" type="text" name="secondary_currency" maxlength="3"
                   value="{{ old('secondary_currency', $season->secondary_currency ?? 'USD') }}">
        </div>

        <div class="field">
            <label for="usd_fx_rate">USD exchange rate</label>
            <input id="usd_fx_rate" type="number" step="0.0001" name="usd_fx_rate"
                   value="{{ old('usd_fx_rate', $season->usd_fx_rate ?? 2585.40) }}" required>
        </div>

        <div class="field">
            <label for="per_head_fee">Per head fee</label>
            <input id="per_head_fee" type="number" step="0.01" name="per_head_fee"
                   value="{{ old('per_head_fee', $season->per_head_fee ?? 150000) }}" required>
        </div>

        <div class="field">
            <label for="early_bird_fee">Early bird fee</label>
            <input id="early_bird_fee" type="number" step="0.01" name="early_bird_fee"
                   value="{{ old('early_bird_fee', $season->early_bird_fee) }}">
        </div>

        <div class="field">
            <label for="min_partial_payment_pct">Minimum partial payment %</label>
            <input id="min_partial_payment_pct" type="number" min="0" max="100" name="min_partial_payment_pct"
                   value="{{ old('min_partial_payment_pct', $season->min_partial_payment_pct ?? 40) }}" required>
        </div>

        <div class="field">
            <label for="rounding_increment">Rounding increment</label>
            <input id="rounding_increment" type="number" min="1" name="rounding_increment"
                   value="{{ old('rounding_increment', $season->rounding_increment ?? 1000) }}" required>
        </div>
    </div>
</div>
