<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkCheck extends Model
{
    use HasFactory;

    public const RESULT_OK = 'ok';
    public const RESULT_BROKEN = 'broken';
    public const RESULT_LOGIN_WALL = 'login_wall';
    public const RESULT_TIMEOUT = 'timeout';
    public const RESULT_ERROR = 'error';
    public const RESULT_REDIRECT = 'redirect';

    protected $fillable = [
        'paper_id',
        'url',
        'http_status',
        'content_type',
        'final_url',
        'result',
        'response_time_ms',
        'notes',
        'checked_by',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'http_status' => 'integer',
            'response_time_ms' => 'integer',
            'checked_at' => 'datetime',
        ];
    }

    public function paper(): BelongsTo
    {
        return $this->belongsTo(Paper::class);
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
