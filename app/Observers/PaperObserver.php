<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Paper;
use App\Models\SlugHistory;
use App\Services\OgImageService;
use Spatie\ResponseCache\Facades\ResponseCache;

class PaperObserver
{
    public function __construct(
        private readonly OgImageService $ogImages,
    ) {}

    public function created(Paper $paper): void
    {
        AuditLog::record('paper.created', $paper, null, $paper->only(['title', 'slug', 'publication_status', 'verification_status', 'permission_status', 'is_demo']));
    }

    public function updating(Paper $paper): void
    {
        // If a published paper's slug changes, record a 301 redirect in slug_history (Section 6)
        if ($paper->isDirty('slug') && $paper->getOriginal('publication_status') === Paper::PUB_PUBLISHED && ! $paper->is_demo) {
            $original = $paper->replicate();
            $original->slug = (string) $paper->getOriginal('slug');
            $original->loadMissing(['grade', 'learningArea', 'term', 'examType', 'nationalExam']);
            $paper->loadMissing(['grade', 'learningArea', 'term', 'examType', 'nationalExam']);

            $oldPath = $original->url_path;
            $newPath = $paper->url_path;

            if ($oldPath && $newPath && $oldPath !== $newPath) {
                SlugHistory::query()->updateOrCreate(
                    ['old_path' => $oldPath],
                    [
                        'new_path' => $newPath,
                        'status_code' => 301,
                        'model_type' => Paper::class,
                        'model_id' => $paper->id,
                        'is_active' => true,
                        'created_by' => auth()->id(),
                    ]
                );
            }
        }
    }

    public function saved(Paper $paper): void
    {
        // Generate static OG image ONCE when published (Section 12)
        if ($paper->publication_status === Paper::PUB_PUBLISHED && ! $paper->is_demo && empty($paper->og_image_path)) {
            $paper->loadMissing(['grade', 'learningArea', 'nationalExam']);
            $subtitle = $paper->category === Paper::CATEGORY_NATIONAL
                ? trim(($paper->nationalExam?->name ?? '').' '.($paper->learningArea?->name ?? '').' '.$paper->year)
                : trim(($paper->grade?->name ?? '').' '.($paper->learningArea?->name ?? '').' '.$paper->year);

            $ogPath = $this->ogImages->generateForTitle($paper->title, $subtitle, 'paper-'.$paper->id.'-'.$paper->slug);
            if ($ogPath) {
                $paper->forceFill(['og_image_path' => $ogPath])->saveQuietly();
            }
        }

        ResponseCache::clear();
    }

    public function updated(Paper $paper): void
    {
        $changes = $paper->getChanges();
        unset($changes['updated_at'], $changes['download_count'], $changes['last_checked_at']);
        if ($changes !== []) {
            AuditLog::record('paper.updated', $paper, array_intersect_key($paper->getOriginal(), $changes), $changes);
        }
    }

    public function deleted(Paper $paper): void
    {
        AuditLog::record('paper.deleted', $paper, $paper->only(['title', 'slug', 'publication_status']));
        ResponseCache::clear();
    }
}
