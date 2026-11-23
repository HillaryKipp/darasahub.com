<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SlugHistoryResource\Pages;
use App\Models\SlugHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SlugHistoryResource extends Resource
{
    protected static ?string $model = SlugHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-right';

    protected static ?string $navigationGroup = 'SEO & Governance';

    protected static ?string $navigationLabel = 'Redirects (Slug History)';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('old_path')->required()->unique(ignoreRecord: true)->placeholder('/old-path'),
            Forms\Components\TextInput::make('new_path')->required()->placeholder('/new-path'),
            Forms\Components\Select::make('status_code')->options([301 => '301 Permanent', 302 => '302 Temporary'])->default(301)->required(),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('old_path')->searchable(),
            Tables\Columns\TextColumn::make('new_path')->searchable(),
            Tables\Columns\TextColumn::make('status_code')->badge(),
            Tables\Columns\TextColumn::make('hits')->sortable(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageSlugHistories::route('/')];
    }
}
