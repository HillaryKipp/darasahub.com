<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Term extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'number',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function papers(): HasMany
    {
        return $this->hasMany(Paper::class);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }
}
