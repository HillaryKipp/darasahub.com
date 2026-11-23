<?php

namespace App\Filament\Resources\LandingPageContentResource\Pages;

use App\Filament\Resources\LandingPageContentResource;
use App\Models\LandingPageContent;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLandingPageContent extends EditRecord
{
    protected static string $resource = LandingPageContentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === LandingPageContent::STATUS_APPROVED) {
            if (auth()->user()?->canReviewAndPublish() ?? false) {
                $data['reviewed_by'] = auth()->id();
                $data['reviewed_at'] = now();
            } else {
                $data['status'] = LandingPageContent::STATUS_REVIEW;
            }
        }

        return $data;
    }
}
