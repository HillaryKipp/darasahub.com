<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImportJobResource\Pages;
use App\Models\ImportJob;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ImportJobResource extends Resource
{
    protected static ?string $model = ImportJob::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Content & Papers';

    protected static ?string $navigationLabel = 'Import History';

    protected static ?int $navigationSort = 4;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('original_filename')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Uploaded By')->placeholder('CLI'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    ImportJob::STATUS_COMMITTED => 'success',
                    ImportJob::STATUS_PREVIEWED => 'info',
                    ImportJob::STATUS_FAILED => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('total_rows')->label('Total'),
                Tables\Columns\TextColumn::make('valid_rows')->label('Valid'),
                Tables\Columns\TextColumn::make('error_rows')->label('Errors'),
                Tables\Columns\TextColumn::make('committed_count')->label('Committed'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageImportJobs::route('/')];
    }
}
