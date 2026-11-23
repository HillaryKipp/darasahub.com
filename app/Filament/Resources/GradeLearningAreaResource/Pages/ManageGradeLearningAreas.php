<?php

namespace App\Filament\Resources\GradeLearningAreaResource\Pages;

use App\Filament\Resources\GradeLearningAreaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageGradeLearningAreas extends ManageRecords
{
    protected static string $resource = GradeLearningAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
