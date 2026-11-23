<?php

namespace App\Filament\Resources\PaperResource\Pages;

use App\Filament\Resources\PaperResource;
use App\Models\Paper;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaper extends EditRecord
{
    protected static string $resource = PaperResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        if (! (auth()->user()?->canReviewAndPublish() ?? false) && ($data['publication_status'] ?? null) === Paper::PUB_PUBLISHED) {
            $data['publication_status'] = Paper::PUB_REVIEW;
        }

        if (($data['verification_status'] ?? null) === Paper::VERIFICATION_VERIFIED && ! $this->record->verified_at) {
            $data['verified_by'] = auth()->id();
            $data['verified_at'] = now();
        }

        if (($data['publication_status'] ?? null) === Paper::PUB_PUBLISHED && ! $this->record->published_at) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
