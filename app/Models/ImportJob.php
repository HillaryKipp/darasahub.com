<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportJob extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PREVIEWED = 'previewed';
    public const STATUS_COMMITTED = 'committed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'original_filename',
        'stored_path',
        'status',
        'total_rows',
        'valid_rows',
        'error_rows',
        'preview_data',
        'errors',
        'committed_count',
        'committed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_rows' => 'integer',
            'valid_rows' => 'integer',
            'error_rows' => 'integer',
            'preview_data' => 'array',
            'errors' => 'array',
            'committed_count' => 'integer',
            'committed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
