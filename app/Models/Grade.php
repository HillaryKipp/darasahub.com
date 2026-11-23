<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grade extends Model
{
    use HasFactory;

    public const LEVEL_PRE_PRIMARY = 'pre_primary';
    public const LEVEL_LOWER_PRIMARY = 'lower_primary';
    public const LEVEL_UPPER_PRIMARY = 'upper_primary';
    public const LEVEL_JUNIOR_SCHOOL = 'junior_school';
    public const LEVEL_SENIOR_SCHOOL = 'senior_school';

    public const LEVELS = [
        self::LEVEL_PRE_PRIMARY => 'Pre-Primary',
        self::LEVEL_LOWER_PRIMARY => 'Lower Primary',
        self::LEVEL_UPPER_PRIMARY => 'Upper Primary',
        self::LEVEL_JUNIOR_SCHOOL => 'Junior School',
        self::LEVEL_SENIOR_SCHOOL => 'Senior School',
    ];

    protected $fillable = [
        'name',
        'slug',
        'level',
        'sort_order',
        'description',
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

    public function learningAreas(): BelongsToMany
    {
        return $this->belongsToMany(LearningArea::class, 'grade_learning_area')
            ->using(GradeLearningArea::class)
            ->withPivot(['id', 'sort_order', 'pathway', 'verification_status', 'is_active'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function verifiedLearningAreas(): BelongsToMany
    {
        return $this->learningAreas()
            ->wherePivot('verification_status', GradeLearningArea::STATUS_VERIFIED)
            ->wherePivot('is_active', true)
            ->where('learning_areas.is_active', true)
            ->where('learning_areas.is_demo', false);
    }

    public function gradeLearningAreas(): HasMany
    {
        return $this->hasMany(GradeLearningArea::class);
    }

    public function papers(): HasMany
    {
        return $this->hasMany(Paper::class);
    }

    public function nationalExams(): HasMany
    {
        return $this->hasMany(NationalExam::class);
    }

    public function scopeNonDemo(Builder $query): Builder
    {
        return $query->where('grades.is_demo', false);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('grades.is_active', true)->where('grades.is_demo', false);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('grades.sort_order');
    }

    public function getUrlPathAttribute(): string
    {
        return '/'.$this->slug;
    }
}
