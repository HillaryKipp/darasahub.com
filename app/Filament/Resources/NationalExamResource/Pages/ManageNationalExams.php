<?php

namespace App\Filament\Resources\NationalExamResource\Pages;

use App\Filament\Resources\NationalExamResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageNationalExams extends ManageRecords
{
    protected static string $resource = NationalExamResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
