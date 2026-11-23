<?php

namespace App\Filament\Resources\LandingPageContentResource\Pages;

use App\Filament\Resources\LandingPageContentResource;
use App\Models\LandingPageContent;
use Filament\Resources\Pages\CreateRecord;

class CreateLandingPageContent extends CreateRecord
{
    protected static string $resource = LandingPageContentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['status'] ?? null) === LandingPageContent::STATUS_APPROVED && (auth()->user()?->canReviewAndPublish() ?? false)) {
            $data['reviewed_by'] = auth()->id();
            $data['reviewed_at'] = now();
        } elseif (! (auth()->user()?->canReviewAndPublish() ?? false)) {
            $data['status'] = LandingPageContent::STATUS_DRAFT;
        }

        return $data;
    }
}
