<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeLearningAreaResource\Pages;
use App\Models\GradeLearningArea;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GradeLearningAreaResource extends Resource
{
    protected static ?string $model = GradeLearningArea::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = 'CBC Curriculum';

    protected static ?string $navigationLabel = 'Grade–Subject Mappings';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('grade_id')->relationship('grade', 'name')->required()->searchable()->preload(),
            Forms\Components\Select::make('learning_area_id')->relationship('learningArea', 'name')->required()->searchable()->preload(),
            Forms\Components\Select::make('pathway')->options(GradeLearningArea::PATHWAYS)->nullable(),
            Forms\Components\Select::make('verification_status')->options([
                GradeLearningArea::STATUS_VERIFIED => 'Verified (Official KICD)',
                GradeLearningArea::STATUS_UNVERIFIED => 'Unverified',
            ])->default(GradeLearningArea::STATUS_UNVERIFIED)->required(),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('grade.name')->sortable(),
                Tables\Columns\TextColumn::make('learningArea.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('pathway')->badge()->placeholder('-'),
                Tables\Columns\TextColumn::make('verification_status')->badge()->color(fn (string $state) => $state === 'verified' ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('sort_order')->label('#')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('grade_id')->relationship('grade', 'name')->label('Grade'),
                Tables\Filters\SelectFilter::make('pathway')->options(GradeLearningArea::PATHWAYS),
                Tables\Filters\SelectFilter::make('verification_status')->options([
                    'verified' => 'Verified',
                    'unverified' => 'Unverified',
                ]),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageGradeLearningAreas::route('/')];
    }
}
