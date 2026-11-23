<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GradeLearningArea extends Pivot
{
    protected $table = 'grade_learning_area';

    public $incrementing = true;

    public const STATUS_VERIFIED = 'verified';
    public const STATUS_UNVERIFIED = 'unverified';

    public const PATHWAY_CORE = 'core';
    public const PATHWAY_STEM = 'stem';
    public const PATHWAY_SOCIAL_SCIENCES = 'social_sciences';
    public const PATHWAY_ARTS_AND_SPORTS = 'arts_and_sports';

    public const PATHWAYS = [
        self::PATHWAY_CORE => 'Core',
        self::PATHWAY_STEM => 'STEM',
        self::PATHWAY_SOCIAL_SCIENCES => 'Social Sciences',
        self::PATHWAY_ARTS_AND_SPORTS => 'Arts & Sports Science',
    ];

    protected $fillable = [
        'grade_id',
        'learning_area_id',
        'sort_order',
        'pathway',
        'verification_status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function learningArea(): BelongsTo
    {
        return $this->belongsTo(LearningArea::class);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('verification_status', self::STATUS_VERIFIED)
            ->where('is_active', true);
    }
}
