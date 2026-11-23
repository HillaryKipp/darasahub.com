<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NationalExam extends Model
{
    use HasFactory;

    public const CURRICULUM_CBC = 'cbc';
    public const CURRICULUM_LEGACY_844 = 'legacy_844';
    public const CURRICULUM_BOTH = 'both';

    public const STATUS_VERIFIED = 'verified';
    public const STATUS_UNVERIFIED = 'unverified';

    protected $fillable = [
        'name',
        'slug',
        'full_name',
        'grade_id',
        'curriculum',
        'description',
        'verification_status',
        'sort_order',
        'is_active',
        'is_demo',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'is_demo' => 'boolean',
        ];
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function papers(): HasMany
    {
        return $this->hasMany(Paper::class);
    }

    public function scopeNonDemo(Builder $query): Builder
    {
        return $query->where('national_exams.is_demo', false);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('national_exams.is_active', true)
            ->where('national_exams.is_demo', false);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('national_exams.sort_order');
    }

    public function getUrlPathAttribute(): string
    {
        return '/'.$this->slug;
    }
}
