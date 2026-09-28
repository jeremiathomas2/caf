<?php

namespace App\Models;

use App\Enums\ContentType;
use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

#[Fillable([
    'slug', 'title', 'type', 'status', 'body', 'excerpt', 'hero_image_url',
    'published_at', 'scheduled_for', 'is_in_footer', 'meta',
])]
class ContentPage extends Model
{
    use HasFactory;

    /**
     * Resolve this page to a public route path, when one is mapped.
     */
    public function url(): ?string
    {
        $path = Arr::get($this->meta ?? [], 'path');

        return is_string($path) ? $path : null;
    }

    public function isLive(): bool
    {
        return $this->status === PublishStatus::Published
            && ($this->published_at === null || $this->published_at->lessThanOrEqualTo(now()));
    }

    public static function forPath(string $path): ?self
    {
        $page = static::query()
            ->where('type', ContentType::Page->value)
            ->where('status', PublishStatus::Published->value)
            ->get()
            ->first(fn (self $page): bool => $page->url() === $path);

        return $page?->isLive() ? $page : null;
    }

    public static function makeSlug(string $title): string
    {
        return Str::slug($title);
    }

    /**
     * @param  Builder<ContentPage>  $query
     * @return Builder<ContentPage>
     */
    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('status', PublishStatus::Published->value)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    /**
     * @param  Builder<ContentPage>  $query
     * @return Builder<ContentPage>
     */
    #[Scope]
    protected function ofType(Builder $query, ContentType|array $type): Builder
    {
        return $query->whereIn('type', array_map(
            fn (ContentType|string $value): string => $value instanceof ContentType ? $value->value : $value,
            Arr::wrap($type),
        ));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ContentType::class,
            'status' => PublishStatus::class,
            'meta' => 'array',
            'is_in_footer' => 'boolean',
            'published_at' => 'datetime',
            'scheduled_for' => 'datetime',
        ];
    }
}
