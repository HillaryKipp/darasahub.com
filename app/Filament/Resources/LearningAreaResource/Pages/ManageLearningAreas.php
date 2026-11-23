<?php

namespace App\Filament\Resources\LearningAreaResource\Pages;

use App\Filament\Resources\LearningAreaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLearningAreas extends ManageRecords
{
    protected static string $resource = LearningAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
