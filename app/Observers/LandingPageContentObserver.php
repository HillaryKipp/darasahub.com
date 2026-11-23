<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\LandingPageContent;
use App\Services\OgImageService;
use Spatie\ResponseCache\Facades\ResponseCache;

class LandingPageContentObserver
{
    public function __construct(
        private readonly OgImageService $ogImages,
    ) {}

    public function created(LandingPageContent $content): void
    {
        AuditLog::record('landing_content.created', $content, null, $content->only(['url_path', 'page_type', 'status', 'ai_assisted']));
    }

    public function updated(LandingPageContent $content): void
    {
        $changes = $content->getChanges();
        unset($changes['updated_at']);
        if ($changes !== []) {
            AuditLog::record('landing_content.updated', $content, array_intersect_key($content->getOriginal(), $changes), $changes);
        }
    }

    public function saved(LandingPageContent $content): void
    {
        if ($content->isApprovedForPublic() && empty($content->og_image_path)) {
            $title = $content->h1 ?: $content->meta_title ?: 'Kenya CBC Past Papers';
            $ogPath = $this->ogImages->generateForTitle($title, 'Verified CBC & National Past Papers', 'landing-'.$content->id);
            if ($ogPath) {
                $content->forceFill(['og_image_path' => $ogPath])->saveQuietly();
            }
        }

        ResponseCache::clear();
    }

    public function deleted(LandingPageContent $content): void
    {
        AuditLog::record('landing_content.deleted', $content, $content->only(['url_path', 'status']));
        ResponseCache::clear();
    }
}
