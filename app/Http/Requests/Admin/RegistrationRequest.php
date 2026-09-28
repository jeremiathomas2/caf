<?php

namespace App\Http\Requests\Admin;

use App\Enums\RegistrationRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canDo('registrations.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // A registration belongs to one season for life — the invoice and any
            // recorded payments follow the group — so the season is only
            // accepted when the registration is first created.
            'season_id' => [
                Rule::requiredIf($this->isMethod('POST')),
                'nullable',
                'integer',
                'exists:seasons,id',
            ],
            'group_name' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:80'],
            'role_type' => ['required', Rule::enum(RegistrationRole::class)],
            'country' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:80'],
            'members_count' => ['required', 'integer', 'min:1', 'max:60'],
            'contact_name' => ['required', 'string', 'max:160'],
            'contact_email' => ['required', 'email', 'max:190'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'performance_link' => ['nullable', 'url', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['string', 'max:40'],
            'is_public' => ['nullable', 'boolean'],

            'members' => ['required', 'array', 'min:1'],
            'members.*.name' => ['required', 'string', 'max:160'],
            'members.*.email' => ['nullable', 'email', 'max:190'],
            'members.*.phone' => ['nullable', 'string', 'max:40'],
            'members.*.part' => ['nullable', 'string', 'max:80'],
            'members.*.is_lead' => ['nullable', 'boolean'],
        ];
    }

    /**
     * The member list drives the invoice, so the count has to agree with it.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $members = $this->input('members', []);
            $declared = (int) $this->input('members_count', 0);

            if ($members !== [] && $declared !== count($members)) {
                $validator->errors()->add(
                    'members_count',
                    'The performer count must match the number of members listed.',
                );
            }

            if ($members !== [] && collect($members)->where('is_lead', true)->isEmpty()) {
                $validator->errors()->add('members.0.is_lead', 'Mark one member as the group lead.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'members_count.max' => 'A group may register at most 60 performers.',
            'members.*.name.required' => 'Every listed member needs a name.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'group_name' => 'group name',
            'role_type' => 'category',
            'members_count' => 'performer count',
            'contact_name' => 'contact name',
            'contact_email' => 'contact email',
            'contact_phone' => 'contact phone',
            'performance_link' => 'performance link',
        ];
    }

    /**
     * Collapse the member rows into the shape the model stores.
     *
     * @return list<array<string, mixed>>
     */
    public function memberRows(): array
    {
        return collect($this->validated('members'))
            ->values()
            ->reject(fn (array $member): bool => blank($member['name'] ?? null))
            ->map(fn (array $member, int $index): array => [
                'name' => $member['name'],
                'email' => $member['email'] ?? null,
                'phone' => $member['phone'] ?? null,
                'part' => $member['part'] ?? null,
                'is_lead' => (bool) ($member['is_lead'] ?? false),
                'sort_order' => $index,
            ])
            ->all();
    }

    /**
     * Attributes to write to the registration itself.
     *
     * @return array<string, mixed>
     */
    public function registrationAttributes(): array
    {
        $data = $this->safe()->except(['members', 'tags', 'is_public']);

        $data['tags'] = array_values(array_filter($this->input('tags', [])));
        $data['is_public'] = $this->boolean('is_public');

        return $data;
    }

    public function seasonId(): ?int
    {
        return $this->integer('season_id') ?: null;
    }
}
