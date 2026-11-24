<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paper extends Model
{
    use HasFactory;

    public const CATEGORY_TERM = 'term';
    public const CATEGORY_NATIONAL = 'national';

    public const CURRICULUM_CBC = 'cbc';
    public const CURRICULUM_LEGACY_844 = 'legacy_844';

    public const FILE_TYPE_QUESTION_PAPER = 'question_paper';
    public const FILE_TYPE_MARKING_SCHEME = 'marking_scheme';

    public const PERMISSION_PERMITTED = 'permitted';
    public const PERMISSION_PENDING = 'pending';
    public const PERMISSION_TAKEDOWN = 'takedown';

    public const PROVIDER_EXTERNAL_LINK = 'external_link';
    public const PROVIDER_GOOGLE_DRIVE = 'google_drive';
    public const PROVIDER_LOCAL_STORAGE = 'local_storage';

    public const AVAILABILITY_AVAILABLE = 'available';
    public const AVAILABILITY_UNAVAILABLE = 'unavailable';
    public const AVAILABILITY_RESTRICTED = 'restricted';

    public const VERIFICATION_UNVERIFIED = 'unverified';
    public const VERIFICATION_VERIFIED = 'verified';
    public const VERIFICATION_BROKEN = 'broken';
    public const VERIFICATION_UNAVAILABLE = 'unavailable';

    public const PUB_DRAFT = 'draft';
    public const PUB_REVIEW = 'review';
    public const PUB_PUBLISHED = 'published';
    public const PUB_ARCHIVED = 'archived';

    protected $fillable = [
        'title',
        'slug',
        'category',
        'curriculum',
        'grade_id',
        'learning_area_id',
        'term_id',
        'exam_type_id',
        'national_exam_id',
        'year',
        'paper_number',
        'set_label',
        'file_type',
        'related_paper_id',
        'description',
        'source_name',
        'source_url',
        'permission_status',
        'provider',
        'external_file_id',
        'file_url',
        'file_size',
        'availability',
        'verification_status',
        'verified_by',
        'verified_at',
        'last_checked_at',
        'download_count',
        'publication_status',
        'published_at',
        'og_image_path',
        'is_demo',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'file_size' => 'integer',
            'download_count' => 'integer',
            'verified_at' => 'datetime',
            'last_checked_at' => 'datetime',
            'published_at' => 'datetime',
            'is_demo' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Paper $paper): void {
            if ($paper->is_demo && $paper->publication_status === self::PUB_PUBLISHED) {
                throw new DomainException('Demo papers (is_demo = true) can never be published.');
            }

            if ($paper->publication_status === self::PUB_PUBLISHED && $paper->permission_status !== self::PERMISSION_PERMITTED) {
                throw new DomainException('Papers without permitted permission_status cannot be published.');
            }
        });
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function learningArea(): BelongsTo
    {
        return $this->belongsTo(LearningArea::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    public function nationalExam(): BelongsTo
    {
        return $this->belongsTo(NationalExam::class);
    }

    public function relatedPaper(): BelongsTo
    {
        return $this->belongsTo(self::class, 'related_paper_id');
    }

    public function linkedPapers(): HasMany
    {
        return $this->hasMany(self::class, 'related_paper_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function linkChecks(): HasMany
    {
        return $this->hasMany(LinkCheck::class);
    }

    public function takedownRequests(): HasMany
    {
        return $this->hasMany(TakedownRequest::class);
    }

    public function scopeNonDemo(Builder $query): Builder
    {
        return $query->where('papers.is_demo', false);
    }

    public function scopePublishedPublic(Builder $query): Builder
    {
        return $query->where('papers.is_demo', false)
            ->where('papers.publication_status', self::PUB_PUBLISHED)
            ->where('papers.permission_status', self::PERMISSION_PERMITTED);
    }

    public function scopeVerifiedReady(Builder $query): Builder
    {
        return $query->publishedPublic()
            ->where('papers.verification_status', self::VERIFICATION_VERIFIED)
            ->where('papers.availability', self::AVAILABILITY_AVAILABLE)
            ->whereNotNull('papers.file_url')
            ->where('papers.file_url', '!=', '');
    }

    public function hasWorkingResource(): bool
    {
        return ! empty($this->file_url)
            && $this->availability === self::AVAILABILITY_AVAILABLE
            && $this->verification_status === self::VERIFICATION_VERIFIED;
    }

    public function isVerifiedReady(): bool
    {
        return ! $this->is_demo
            && $this->publication_status === self::PUB_PUBLISHED
            && $this->permission_status === self::PERMISSION_PERMITTED
            && $this->hasWorkingResource();
    }

    public function getUrlPathAttribute(): ?string
    {
        if ($this->category === self::CATEGORY_TERM) {
            if (! $this->grade || ! $this->learningArea || ! $this->term || ! $this->examType) {
                return null;
            }

            return sprintf(
                '/%s/%s/%s/%s/%s',
                $this->grade->slug,
                $this->learningArea->slug,
                $this->term->slug,
                $this->examType->slug,
                $this->slug
            );
        }

        if ($this->category === self::CATEGORY_NATIONAL) {
            if (! $this->nationalExam || ! $this->learningArea || ! $this->year) {
                return null;
            }

            return sprintf(
                '/%s/%s-past-papers/%d/%s',
                $this->nationalExam->slug,
                $this->learningArea->slug,
                $this->year,
                $this->slug
            );
        }

        return null;
    }

    public function getBundleUrlAttribute(): ?string
    {
        if ($this->category === self::CATEGORY_TERM) {
            if ($this->grade && $this->learningArea && $this->term) {
                return sprintf(
                    '/%s/%s/%s',
                    $this->grade->slug,
                    $this->learningArea->slug,
                    $this->term->slug
                );
            }
        }

        if ($this->category === self::CATEGORY_NATIONAL) {
            if ($this->nationalExam && $this->learningArea && $this->year) {
                return sprintf(
                    '/%s/%s-past-papers/%d',
                    $this->nationalExam->slug,
                    $this->learningArea->slug,
                    $this->year
                );
            }
        }

        return $this->url_path;
    }

    public function getViewUrlAttribute(): ?string
    {
        $driveId = $this->resolveGoogleDriveFileId();
        if ($driveId !== null) {
            return "https://drive.google.com/file/d/{$driveId}/view";
        }

        if (! empty($this->source_url) && str_contains($this->source_url, 'drive.google.com')) {
            return $this->source_url;
        }

        if (! empty($this->file_url)) {
            return $this->file_url;
        }

        return $this->url_path;
    }

    public function getPreviewUrlAttribute(): ?string
    {
        $driveId = $this->resolveGoogleDriveFileId();
        if ($driveId !== null) {
            return "https://drive.google.com/file/d/{$driveId}/preview";
        }

        if (! empty($this->file_url) && str_ends_with(strtolower((string) $this->file_url), '.pdf')) {
            return $this->file_url;
        }

        return null;
    }

    private function resolveGoogleDriveFileId(): ?string
    {
        if ($this->provider === self::PROVIDER_GOOGLE_DRIVE && ! empty($this->external_file_id)) {
            return trim((string) $this->external_file_id);
        }

        foreach ([(string) $this->file_url, (string) $this->source_url] as $candidate) {
            if ($candidate === '' || ! str_contains($candidate, 'drive.google.com')) {
                continue;
            }

            if (preg_match('#/file/d/([a-zA-Z0-9_-]+)#', $candidate, $matches) === 1) {
                return $matches[1];
            }

            if (preg_match('#[?&]id=([a-zA-Z0-9_-]+)#', $candidate, $matches) === 1) {
                return $matches[1];
            }
        }

        return null;
    }

    public function getFormattedFileSizeAttribute(): ?string
    {
        if (! $this->file_size) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float) $this->file_size;
        $i = 0;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 1).' '.$units[$i];
    }
}
