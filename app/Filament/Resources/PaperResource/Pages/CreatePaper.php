<?php

namespace App\Filament\Resources\PaperResource\Pages;

use App\Filament\Resources\PaperResource;
use App\Models\Paper;
use Filament\Resources\Pages\CreateRecord;

class CreatePaper extends CreateRecord
{
    protected static string $resource = PaperResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        if (! (auth()->user()?->canReviewAndPublish() ?? false)) {
            $data['publication_status'] = Paper::PUB_DRAFT;
            $data['verification_status'] = Paper::VERIFICATION_UNVERIFIED;
        }

        if (($data['publication_status'] ?? null) === Paper::PUB_PUBLISHED) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
