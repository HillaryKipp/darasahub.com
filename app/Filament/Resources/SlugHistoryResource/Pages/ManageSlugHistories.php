<?php

namespace App\Filament\Resources\SlugHistoryResource\Pages;

use App\Filament\Resources\SlugHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSlugHistories extends ManageRecords
{
    protected static string $resource = SlugHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
