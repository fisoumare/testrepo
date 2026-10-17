<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = ['title', 'slug', 'category', 'excerpt', 'body', 'cover', 'author', 'published_at', 'is_published'];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $post) {
            $post->slug = $post->slug ?: Str::slug($post->title);
            if ($post->is_published && ! $post->published_at) {
                $post->published_at = now();
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at');
    }

    public function readingTime(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags((string) $this->body)) / 200));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
