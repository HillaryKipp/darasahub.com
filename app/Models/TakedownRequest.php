<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TakedownRequest extends Model
{
    use HasFactory;

    public const REASON_COPYRIGHT = 'copyright';
    public const REASON_BROKEN_LINK = 'broken_link';
    public const REASON_INACCURATE = 'inaccurate';
    public const REASON_OTHER = 'other';

    public const STATUS_OPEN = 'open';
    public const STATUS_REVIEWING = 'reviewing';
    public const STATUS_RESOLVED_REMOVED = 'resolved_removed';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'paper_id',
        'paper_url',
        'requester_name',
        'requester_email',
        'organization',
        'reason_type',
        'details',
        'status',
        'handled_by',
        'handled_at',
        'resolution_notes',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'handled_at' => 'datetime',
        ];
    }

    public function paper(): BelongsTo
    {
        return $this->belongsTo(Paper::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
