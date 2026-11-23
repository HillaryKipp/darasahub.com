<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingPageContent extends Model
{
    use HasFactory;

    protected $table = 'landing_page_content';

    public const TYPE_GRADE_HUB = 'grade_hub';
    public const TYPE_LEARNING_AREA_HUB = 'learning_area_hub';
    public const TYPE_TERM_HUB = 'term_hub';
    public const TYPE_TERM_LANDING = 'term_landing';
    public const TYPE_NATIONAL_HUB = 'national_hub';
    public const TYPE_NATIONAL_SUBJECT = 'national_subject';
    public const TYPE_NATIONAL_YEAR = 'national_year';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_REVIEW = 'review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'url_path',
        'page_type',
        'meta_title',
        'meta_description',
        'h1',
        'intro',
        'intro_word_count',
        'faq_entries',
        'ai_assisted',
        'reviewed_by',
        'reviewed_at',
        'status',
        'og_image_path',
        'is_demo',
    ];

    protected function casts(): array
    {
        return [
            'intro_word_count' => 'integer',
            'faq_entries' => 'array',
            'ai_assisted' => 'boolean',
            'reviewed_at' => 'datetime',
            'is_demo' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (LandingPageContent $content): void {
            if ($content->url_path) {
                $normalized = '/'.ltrim(strtolower(trim($content->url_path)), '/');
                $content->url_path = $normalized === '/' ? '/' : rtrim($normalized, '/');
            }

            $plainIntro = trim(strip_tags((string) $content->intro));
            $content->intro_word_count = $plainIntro === '' ? 0 : str_word_count($plainIntro);

            if ($content->is_demo && $content->status === self::STATUS_APPROVED) {
                throw new DomainException('Demo landing page content (is_demo = true) cannot be approved for publishing.');
            }

            if ($content->status === self::STATUS_APPROVED && $content->ai_assisted && ! $content->reviewed_by) {
                throw new DomainException('AI-assisted landing page content requires a human reviewer before approval.');
            }
        });
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeNonDemo(Builder $query): Builder
    {
        return $query->where('landing_page_content.is_demo', false);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->nonDemo()->where('landing_page_content.status', self::STATUS_APPROVED);
    }

    public function isApprovedForPublic(): bool
    {
        if ($this->is_demo || $this->status !== self::STATUS_APPROVED) {
            return false;
        }

        if ($this->ai_assisted && (! $this->reviewed_by || ! $this->reviewed_at)) {
            return false;
        }

        return true;
    }

    /**
     * Return only valid, non-empty FAQ entries suitable for visible rendering & JSON-LD.
     *
     * @return array<int, array{question: string, answer: string}>
     */
    public function getVisibleFaqsAttribute(): array
    {
        if (! is_array($this->faq_entries)) {
            return [];
        }

        return array_values(array_filter($this->faq_entries, function ($item): bool {
            return is_array($item)
                && ! empty(trim((string) ($item['question'] ?? '')))
                && ! empty(trim((string) ($item['answer'] ?? '')));
        }));
    }
}
