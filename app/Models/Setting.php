<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['group', 'key', 'value', 'type', 'label', 'hint', 'sort_order'])]
class Setting extends Model
{
    use HasFactory;

    /**
     * Read a value coerced to the declared type, falling back to the given default.
     */
    public function typed(mixed $default = null): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => $this->value === null ? $default : (int) $this->value,
            'float' => $this->value === null ? $default : (float) $this->value,
            'array', 'json' => json_decode((string) $this->value, true) ?? $default,
            default => $this->value ?? $default,
        };
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }
}
