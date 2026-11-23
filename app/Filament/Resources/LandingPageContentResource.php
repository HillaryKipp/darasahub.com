<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LandingPageContentResource\Pages;
use App\Models\LandingPageContent;
use App\Services\IntroSimilarityService;
use App\Services\ReadinessService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LandingPageContentResource extends Resource
{
    protected static ?string $model = LandingPageContent::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'Content & Papers';

    protected static ?string $navigationLabel = 'Landing Page Content';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        $canApprove = auth()->user()?->canReviewAndPublish() ?? false;

        return $form->schema([
            Forms\Components\Section::make('Target URL & SEO Overrides')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('url_path')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->placeholder('/grade-5/kiswahili/term-2/end-term-exams'),
                    Forms\Components\Select::make('page_type')
                        ->options([
                            LandingPageContent::TYPE_TERM_LANDING => 'Term Landing Page',
                            LandingPageContent::TYPE_NATIONAL_SUBJECT => 'National Subject Page',
                            LandingPageContent::TYPE_NATIONAL_YEAR => 'National Year Page',
                            LandingPageContent::TYPE_GRADE_HUB => 'Grade Hub',
                            LandingPageContent::TYPE_LEARNING_AREA_HUB => 'Learning Area Hub',
                            LandingPageContent::TYPE_TERM_HUB => 'Term Hub',
                            LandingPageContent::TYPE_NATIONAL_HUB => 'National Exam Hub',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('h1')->maxLength(255),
                    Forms\Components\TextInput::make('meta_title')->maxLength(255),
                    Forms\Components\Textarea::make('meta_description')->rows(2)->maxLength(500)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Editorial Intro & Similarity Check (Section 10)')
                ->schema([
                    Forms\Components\Textarea::make('intro')
                        ->rows(7)
                        ->live(onBlur: true)
                        ->helperText('Write page-specific guidance (minimum 80 words for landing pages, 25 words for hubs). Never copy-paste near-identical templates.'),
                    Forms\Components\Placeholder::make('similarity_warning')
                        ->label('Duplicate-Similarity Check')
                        ->content(function (Forms\Get $get, ?LandingPageContent $record): string {
                            $intro = (string) $get('intro');
                            $words = str_word_count(trim(strip_tags($intro)));
                            $matches = app(IntroSimilarityService::class)->findSimilar($intro, $record?->id);

                            if ($matches === []) {
                                return "Word count: {$words}. No near-duplicate intros detected.";
                            }

                            $list = collect($matches)->map(fn ($m) => "{$m['url_path']} ({$m['percent']}% similar)")->implode(', ');

                            return "WARNING — Word count: {$words}. Near-identical intro found on: {$list}";
                        }),
                ]),

            Forms\Components\Section::make('Visible FAQs (Only Real, Answered Questions)')
                ->schema([
                    Forms\Components\Repeater::make('faq_entries')
                        ->schema([
                            Forms\Components\TextInput::make('question')->required()->maxLength(255),
                            Forms\Components\Textarea::make('answer')->required()->rows(3),
                        ])
                        ->defaultItems(0)
                        ->collapsible(),
                ]),

            Forms\Components\Section::make('Review & Approval')
                ->columns(3)
                ->schema([
                    Forms\Components\Toggle::make('ai_assisted')
                        ->label('AI-Assisted Draft (requires human reviewer before approval)')
                        ->default(false),
                    Forms\Components\Select::make('status')
                        ->options(
                            $canApprove
                                ? [
                                    LandingPageContent::STATUS_DRAFT => 'Draft',
                                    LandingPageContent::STATUS_REVIEW => 'In Review',
                                    LandingPageContent::STATUS_APPROVED => 'Approved',
                                    LandingPageContent::STATUS_ARCHIVED => 'Archived',
                                ]
                                : [
                                    LandingPageContent::STATUS_DRAFT => 'Draft',
                                    LandingPageContent::STATUS_REVIEW => 'In Review',
                                ]
                        )
                        ->default(LandingPageContent::STATUS_DRAFT)
                        ->required(),
                    Forms\Components\Toggle::make('is_demo')
                        ->label('Demo Record')
                        ->default(false)
                        ->disabled(! (auth()->user()?->isAdmin() ?? false)),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('url_path')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('page_type')->badge(),
                Tables\Columns\TextColumn::make('intro_word_count')->label('Words')->sortable(),
                Tables\Columns\IconColumn::make('ai_assisted')->boolean()->label('AI'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    LandingPageContent::STATUS_APPROVED => 'success',
                    LandingPageContent::STATUS_REVIEW => 'info',
                    LandingPageContent::STATUS_ARCHIVED => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('readiness_state')
                    ->label('Readiness Gate')
                    ->state(fn (LandingPageContent $record) => app(ReadinessService::class)->evaluate($record)->ready ? 'Ready' : 'Not Ready')
                    ->badge()
                    ->color(fn (string $state) => $state === 'Ready' ? 'success' : 'warning'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('page_type')->options([
                    LandingPageContent::TYPE_TERM_LANDING => 'Term Landing',
                    LandingPageContent::TYPE_NATIONAL_SUBJECT => 'National Subject',
                    LandingPageContent::TYPE_NATIONAL_YEAR => 'National Year',
                    LandingPageContent::TYPE_GRADE_HUB => 'Grade Hub',
                    LandingPageContent::TYPE_LEARNING_AREA_HUB => 'Learning Area Hub',
                    LandingPageContent::TYPE_TERM_HUB => 'Term Hub',
                    LandingPageContent::TYPE_NATIONAL_HUB => 'National Hub',
                ]),
                Tables\Filters\SelectFilter::make('status')->options([
                    LandingPageContent::STATUS_DRAFT => 'Draft',
                    LandingPageContent::STATUS_REVIEW => 'Review',
                    LandingPageContent::STATUS_APPROVED => 'Approved',
                    LandingPageContent::STATUS_ARCHIVED => 'Archived',
                ]),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLandingPageContents::route('/'),
            'create' => Pages\CreateLandingPageContent::route('/create'),
            'edit' => Pages\EditLandingPageContent::route('/{record}/edit'),
        ];
    }
}
