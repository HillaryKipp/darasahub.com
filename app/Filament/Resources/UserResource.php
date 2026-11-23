<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            Forms\Components\Select::make('role')->options(User::ROLES)->required()->default(User::ROLE_EDITOR),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\TextInput::make('password')
                ->password()
                ->dehydrated(fn (?string $state) => filled($state))
                ->required(fn (string $operation) => $operation === 'create')
                ->minLength(8),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('email')->searchable(),
            Tables\Columns\TextColumn::make('role')->badge()->color(fn (string $state) => match ($state) {
                User::ROLE_ADMIN => 'danger',
                User::ROLE_REVIEWER => 'info',
                default => 'gray',
            }),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageUsers::route('/')];
    }
}
