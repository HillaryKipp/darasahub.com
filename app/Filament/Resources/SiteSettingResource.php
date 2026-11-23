<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteSettingResource\Pages;
use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')->required()->disabledOn('edit'),
            Forms\Components\Select::make('type')->options([
                'string' => 'String',
                'integer' => 'Integer',
                'boolean' => 'Boolean',
                'json' => 'JSON',
            ])->required(),
            Forms\Components\TextInput::make('group')->required(),
            Forms\Components\Textarea::make('value')->rows(3)->columnSpanFull(),
            Forms\Components\TextInput::make('description')->maxLength(255)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('group')->columns([
            Tables\Columns\TextColumn::make('group')->badge()->sortable(),
            Tables\Columns\TextColumn::make('key')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('value')->limit(45),
            Tables\Columns\TextColumn::make('description')->limit(50),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageSiteSettings::route('/')];
    }
}
