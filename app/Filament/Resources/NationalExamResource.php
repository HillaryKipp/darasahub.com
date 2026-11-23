<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NationalExamResource\Pages;
use App\Models\NationalExam;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NationalExamResource extends Resource
{
    protected static ?string $model = NationalExam::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'CBC Curriculum';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(40),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(40),
            Forms\Components\TextInput::make('full_name')->required()->maxLength(190)->columnSpanFull(),
            Forms\Components\Select::make('grade_id')->relationship('grade', 'name')->nullable(),
            Forms\Components\Select::make('curriculum')->options([
                NationalExam::CURRICULUM_CBC => 'CBC',
                NationalExam::CURRICULUM_LEGACY_844 => 'Legacy 8-4-4',
                NationalExam::CURRICULUM_BOTH => 'Both (CBC & Legacy 8-4-4)',
            ])->required(),
            Forms\Components\Select::make('verification_status')->options([
                NationalExam::STATUS_VERIFIED => 'Verified',
                NationalExam::STATUS_UNVERIFIED => 'Unverified',
            ])->required(),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable(),
                Tables\Columns\TextColumn::make('full_name')->limit(45),
                Tables\Columns\TextColumn::make('grade.name')->placeholder('Stage-Based'),
                Tables\Columns\TextColumn::make('curriculum')->badge(),
                Tables\Columns\TextColumn::make('verification_status')->badge()->color(fn (string $state) => $state === 'verified' ? 'success' : 'warning'),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageNationalExams::route('/')];
    }
}
