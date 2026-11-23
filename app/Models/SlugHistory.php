<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlugHistory extends Model
{
    use HasFactory;

    protected $table = 'slug_history';

    protected $fillable = [
        'old_path',
        'new_path',
        'status_code',
        'model_type',
        'model_id',
        'hits',
        'last_hit_at',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'hits' => 'integer',
            'last_hit_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (SlugHistory $history): void {
            $history->old_path = self::normalizePath($history->old_path);
            $history->new_path = self::normalizePath($history->new_path);
        });
    }

    public static function normalizePath(string $path): string
    {
        $normalized = '/'.ltrim(strtolower(trim($path)), '/');

        return $normalized === '/' ? '/' : rtrim($normalized, '/');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
