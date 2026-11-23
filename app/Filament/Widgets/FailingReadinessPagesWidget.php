<?php

namespace App\Filament\Widgets;

use App\Models\LandingPageContent;
use App\Services\ReadinessService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class FailingReadinessPagesWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Landing Pages Failing Content-Readiness Gate';

    public function table(Table $table): Table
    {
        $readiness = app(ReadinessService::class);
        $failingIds = LandingPageContent::query()
            ->nonDemo()
            ->get()
            ->filter(fn (LandingPageContent $c) => ! $readiness->evaluate($c)->ready)
            ->pluck('id')
            ->all();

        return $table
            ->query(LandingPageContent::query()->whereIn('id', $failingIds ?: [0]))
            ->columns([
                Tables\Columns\TextColumn::make('url_path')->searchable(),
                Tables\Columns\TextColumn::make('page_type')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('intro_word_count')->label('Words'),
                Tables\Columns\TextColumn::make('failure_reasons')
                    ->label('Failure Reasons')
                    ->state(fn (LandingPageContent $record) => app(ReadinessService::class)->evaluate($record)->summary())
                    ->wrap(),
            ]);
    }
}
