<?php

namespace App\Http\Requests\Admin;

use App\Enums\SeasonState;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SeasonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canDo('seasons.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $seasonId = $this->route('season')?->getKey();

        return [
            'number' => ['required', 'integer', 'min:1', 'max:999', Rule::unique('seasons', 'number')->ignore($seasonId)],
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140', 'alpha_dash', Rule::unique('seasons', 'slug')->ignore($seasonId)],
            'theme' => ['nullable', 'string', 'max:160'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'state' => ['required', Rule::enum(SeasonState::class)],
            'venue' => ['nullable', 'string', 'max:160'],
            'city' => ['nullable', 'string', 'max:80'],
            'country' => ['nullable', 'string', 'max:80'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'registration_opens_at' => ['nullable', 'date'],
            'registration_closes_at' => ['nullable', 'date', 'after:registration_opens_at'],
            'early_bird_closes_at' => ['nullable', 'date', 'before:registration_closes_at'],
            'currency' => ['required', 'string', 'size:3'],
            'secondary_currency' => ['nullable', 'string', 'size:3'],
            'per_head_fee' => ['required', 'numeric', 'min:0'],
            'early_bird_fee' => ['nullable', 'numeric', 'min:0', 'lte:per_head_fee'],
            'min_partial_payment_pct' => ['required', 'integer', 'between:0,100'],
            'rounding_increment' => ['required', 'integer', 'min:1'],
            'usd_fx_rate' => ['required', 'numeric', 'min:0'],
            'summary' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ends_on.after_or_equal' => 'The end date must fall on or after the start date.',
            'registration_closes_at.after' => 'Registration must close after it opens.',
            'early_bird_fee.lte' => 'The early bird fee cannot exceed the standard per head fee.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'number' => 'season number',
            'per_head_fee' => 'per head fee',
            'min_partial_payment_pct' => 'minimum partial payment percentage',
            'usd_fx_rate' => 'USD exchange rate',
        ];
    }
}
