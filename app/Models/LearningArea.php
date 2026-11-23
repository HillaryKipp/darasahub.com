<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'short_name',
        'description',
        'is_active',
        'is_demo',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_demo' => 'boolean',
        ];
    }

    public function grades(): BelongsToMany
    {
        return $this->belongsToMany(Grade::class, 'grade_learning_area')
            ->using(GradeLearningArea::class)
            ->withPivot(['id', 'sort_order', 'pathway', 'verification_status', 'is_active'])
            ->withTimestamps()
            ->orderBy('grades.sort_order');
    }

    public function gradeLearningAreas(): HasMany
    {
        return $this->hasMany(GradeLearningArea::class);
    }

    public function papers(): HasMany
    {
        return $this->hasMany(Paper::class);
    }

    public function scopeNonDemo(Builder $query): Builder
    {
        return $query->where('learning_areas.is_demo', false);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('learning_areas.is_active', true)->where('learning_areas.is_demo', false);
    }
}
