<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TakedownRequestResource\Pages;
use App\Models\TakedownRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TakedownRequestResource extends Resource
{
    protected static ?string $model = TakedownRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationGroup = 'SEO & Governance';

    protected static ?string $navigationLabel = 'Takedown & Problem Reports';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('paper_id')->relationship('paper', 'title')->searchable()->disabled(),
            Forms\Components\TextInput::make('paper_url')->disabled(),
            Forms\Components\TextInput::make('requester_name')->disabled(),
            Forms\Components\TextInput::make('requester_email')->disabled(),
            Forms\Components\TextInput::make('organization')->disabled(),
            Forms\Components\TextInput::make('reason_type')->disabled(),
            Forms\Components\Textarea::make('details')->rows(4)->disabled()->columnSpanFull(),
            Forms\Components\Select::make('status')->options([
                TakedownRequest::STATUS_OPEN => 'Open',
                TakedownRequest::STATUS_REVIEWING => 'Reviewing',
                TakedownRequest::STATUS_RESOLVED_REMOVED => 'Resolved (Paper Removed)',
                TakedownRequest::STATUS_REJECTED => 'Rejected',
            ])->required(),
            Forms\Components\Textarea::make('resolution_notes')->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('reason_type')->badge(),
                Tables\Columns\TextColumn::make('requester_name')->searchable(),
                Tables\Columns\TextColumn::make('paper.title')->limit(35)->placeholder('-'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    TakedownRequest::STATUS_OPEN => 'danger',
                    TakedownRequest::STATUS_REVIEWING => 'warning',
                    TakedownRequest::STATUS_RESOLVED_REMOVED => 'success',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    TakedownRequest::STATUS_OPEN => 'Open',
                    TakedownRequest::STATUS_REVIEWING => 'Reviewing',
                    TakedownRequest::STATUS_RESOLVED_REMOVED => 'Resolved',
                    TakedownRequest::STATUS_REJECTED => 'Rejected',
                ]),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageTakedownRequests::route('/')];
    }
}
