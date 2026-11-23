<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaperResource\Pages;
use App\Models\Paper;
use App\Services\ReadinessService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class PaperResource extends Resource
{
    protected static ?string $model = Paper::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Content & Papers';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        $canPublish = auth()->user()?->canReviewAndPublish() ?? false;

        return $form->schema([
            Forms\Components\Section::make('Paper Identity & Curriculum')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Forms\Set $set, ?string $state, ?Paper $record) => $record === null ? $set('slug', Str::slug((string) $state)) : null),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Changing a published slug automatically records a 301 redirect in slug_history.'),
                    Forms\Components\Select::make('category')
                        ->options([
                            Paper::CATEGORY_TERM => 'Term-Based School Exam',
                            Paper::CATEGORY_NATIONAL => 'National Exam (KPSEA / KILEA / KJSEA / KCSE)',
                        ])
                        ->required()
                        ->live(),
                    Forms\Components\Select::make('curriculum')
                        ->options([
                            Paper::CURRICULUM_CBC => 'CBC',
                            Paper::CURRICULUM_LEGACY_844 => 'Legacy 8-4-4',
                        ])
                        ->required()
                        ->default(Paper::CURRICULUM_CBC),
                    Forms\Components\Select::make('grade_id')
                        ->relationship('grade', 'name')
                        ->searchable()
                        ->preload()
                        ->visible(fn (Forms\Get $get) => $get('category') === Paper::CATEGORY_TERM),
                    Forms\Components\Select::make('national_exam_id')
                        ->relationship('nationalExam', 'name')
                        ->searchable()
                        ->preload()
                        ->visible(fn (Forms\Get $get) => $get('category') === Paper::CATEGORY_NATIONAL),
                    Forms\Components\Select::make('learning_area_id')
                        ->relationship('learningArea', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('term_id')
                        ->relationship('term', 'name')
                        ->visible(fn (Forms\Get $get) => $get('category') === Paper::CATEGORY_TERM),
                    Forms\Components\Select::make('exam_type_id')
                        ->relationship('examType', 'name')
                        ->visible(fn (Forms\Get $get) => $get('category') === Paper::CATEGORY_TERM),
                    Forms\Components\TextInput::make('year')
                        ->numeric()
                        ->required()
                        ->minValue(1990)
                        ->maxValue(2035)
                        ->default((int) date('Y')),
                    Forms\Components\TextInput::make('paper_number')->maxLength(30)->placeholder('e.g. Paper 1'),
                    Forms\Components\TextInput::make('set_label')->maxLength(60)->placeholder('e.g. Set 1'),
                    Forms\Components\Select::make('file_type')
                        ->options([
                            Paper::FILE_TYPE_QUESTION_PAPER => 'Question Paper',
                            Paper::FILE_TYPE_MARKING_SCHEME => 'Marking Scheme',
                        ])
                        ->required()
                        ->default(Paper::FILE_TYPE_QUESTION_PAPER),
                    Forms\Components\Select::make('related_paper_id')
                        ->label('Linked Marking Scheme / Question Paper')
                        ->relationship('relatedPaper', 'title')
                        ->searchable(),
                    Forms\Components\Textarea::make('description')
                        ->rows(4)
                        ->columnSpanFull()
                        ->helperText('Provide useful revision context (at least 15 words) so the paper page passes the readiness gate.'),
                ]),

            Forms\Components\Section::make('Source, Rights & File Resource')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('source_name')->maxLength(190)->required(),
                    Forms\Components\TextInput::make('source_url')->url()->maxLength(1000),
                    Forms\Components\Select::make('permission_status')
                        ->options([
                            Paper::PERMISSION_PENDING => 'Pending',
                            Paper::PERMISSION_PERMITTED => 'Permitted',
                            Paper::PERMISSION_TAKEDOWN => 'Takedown',
                        ])
                        ->required()
                        ->default(Paper::PERMISSION_PENDING),
                    Forms\Components\Select::make('provider')
                        ->options([
                            Paper::PROVIDER_EXTERNAL_LINK => 'External Link',
                            Paper::PROVIDER_GOOGLE_DRIVE => 'Google Drive',
                            Paper::PROVIDER_LOCAL_STORAGE => 'Local Storage',
                        ])
                        ->required()
                        ->default(Paper::PROVIDER_EXTERNAL_LINK),
                    Forms\Components\TextInput::make('file_url')->url()->maxLength(1000)->columnSpanFull(),
                    Forms\Components\TextInput::make('external_file_id')->maxLength(190),
                    Forms\Components\TextInput::make('file_size')->numeric()->suffix('bytes'),
                ]),

            Forms\Components\Section::make('Verification & Publishing')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('availability')
                        ->options([
                            Paper::AVAILABILITY_AVAILABLE => 'Available',
                            Paper::AVAILABILITY_UNAVAILABLE => 'Unavailable',
                            Paper::AVAILABILITY_RESTRICTED => 'Restricted',
                        ])
                        ->default(Paper::AVAILABILITY_AVAILABLE)
                        ->required(),
                    Forms\Components\Select::make('verification_status')
                        ->options([
                            Paper::VERIFICATION_UNVERIFIED => 'Unverified',
                            Paper::VERIFICATION_VERIFIED => 'Verified',
                            Paper::VERIFICATION_BROKEN => 'Broken',
                            Paper::VERIFICATION_UNAVAILABLE => 'Unavailable',
                        ])
                        ->default(Paper::VERIFICATION_UNVERIFIED)
                        ->disabled(! $canPublish)
                        ->dehydrated(true)
                        ->required(),
                    Forms\Components\Select::make('publication_status')
                        ->options(
                            $canPublish
                                ? [
                                    Paper::PUB_DRAFT => 'Draft',
                                    Paper::PUB_REVIEW => 'In Review',
                                    Paper::PUB_PUBLISHED => 'Published',
                                    Paper::PUB_ARCHIVED => 'Archived',
                                ]
                                : [
                                    Paper::PUB_DRAFT => 'Draft',
                                    Paper::PUB_REVIEW => 'In Review',
                                ]
                        )
                        ->default(Paper::PUB_DRAFT)
                        ->required(),
                    Forms\Components\Toggle::make('is_demo')
                        ->label('Demo Record (can never be published)')
                        ->default(false)
                        ->disabled(! (auth()->user()?->isAdmin() ?? false)),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()->limit(45),
                Tables\Columns\TextColumn::make('category')->badge(),
                Tables\Columns\TextColumn::make('grade.name')->label('Grade')->sortable()->placeholder('-'),
                Tables\Columns\TextColumn::make('nationalExam.name')->label('Exam')->sortable()->placeholder('-'),
                Tables\Columns\TextColumn::make('learningArea.name')->label('Learning Area')->searchable()->limit(25),
                Tables\Columns\TextColumn::make('year')->sortable(),
                Tables\Columns\TextColumn::make('permission_status')->badge()->color(fn (string $state) => match ($state) {
                    Paper::PERMISSION_PERMITTED => 'success',
                    Paper::PERMISSION_TAKEDOWN => 'danger',
                    default => 'warning',
                }),
                Tables\Columns\TextColumn::make('verification_status')->badge()->color(fn (string $state) => match ($state) {
                    Paper::VERIFICATION_VERIFIED => 'success',
                    Paper::VERIFICATION_BROKEN, Paper::VERIFICATION_UNAVAILABLE => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('publication_status')->badge()->color(fn (string $state) => match ($state) {
                    Paper::PUB_PUBLISHED => 'success',
                    Paper::PUB_REVIEW => 'info',
                    Paper::PUB_ARCHIVED => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\IconColumn::make('is_demo')->boolean()->label('Demo'),
                Tables\Columns\TextColumn::make('download_count')->label('DLs')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->options([
                    Paper::CATEGORY_TERM => 'Term',
                    Paper::CATEGORY_NATIONAL => 'National',
                ]),
                Tables\Filters\SelectFilter::make('publication_status')->options([
                    Paper::PUB_DRAFT => 'Draft',
                    Paper::PUB_REVIEW => 'Review',
                    Paper::PUB_PUBLISHED => 'Published',
                    Paper::PUB_ARCHIVED => 'Archived',
                ]),
                Tables\Filters\SelectFilter::make('verification_status')->options([
                    Paper::VERIFICATION_UNVERIFIED => 'Unverified',
                    Paper::VERIFICATION_VERIFIED => 'Verified',
                    Paper::VERIFICATION_BROKEN => 'Broken',
                    Paper::VERIFICATION_UNAVAILABLE => 'Unavailable',
                ]),
                Tables\Filters\SelectFilter::make('permission_status')->options([
                    Paper::PERMISSION_PERMITTED => 'Permitted',
                    Paper::PERMISSION_PENDING => 'Pending',
                    Paper::PERMISSION_TAKEDOWN => 'Takedown',
                ]),
                Tables\Filters\TernaryFilter::make('is_demo')->label('Demo Records'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulk_verify')
                    ->label('Update Verification Status')
                    ->icon('heroicon-o-check-badge')
                    ->visible(fn () => auth()->user()?->canReviewAndPublish() ?? false)
                    ->form([
                        Forms\Components\Select::make('verification_status')
                            ->options([
                                Paper::VERIFICATION_VERIFIED => 'Verified',
                                Paper::VERIFICATION_UNVERIFIED => 'Unverified',
                                Paper::VERIFICATION_BROKEN => 'Broken',
                                Paper::VERIFICATION_UNAVAILABLE => 'Unavailable',
                            ])
                            ->required(),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        foreach ($records as $paper) {
                            $paper->update([
                                'verification_status' => $data['verification_status'],
                                'verified_by' => $data['verification_status'] === Paper::VERIFICATION_VERIFIED ? auth()->id() : $paper->verified_by,
                                'verified_at' => $data['verification_status'] === Paper::VERIFICATION_VERIFIED ? now() : $paper->verified_at,
                            ]);
                        }
                        Notification::make()->title('Updated verification status for '.$records->count().' papers.')->success()->send();
                    }),

                Tables\Actions\BulkAction::make('bulk_publish')
                    ->label('Update Publication Status')
                    ->icon('heroicon-o-globe-alt')
                    ->visible(fn () => auth()->user()?->canReviewAndPublish() ?? false)
                    ->form([
                        Forms\Components\Select::make('publication_status')
                            ->options([
                                Paper::PUB_DRAFT => 'Draft',
                                Paper::PUB_REVIEW => 'In Review',
                                Paper::PUB_PUBLISHED => 'Published',
                                Paper::PUB_ARCHIVED => 'Archived',
                            ])
                            ->required(),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        $updated = 0;
                        $skipped = 0;

                        foreach ($records as $paper) {
                            if ($data['publication_status'] === Paper::PUB_PUBLISHED && ($paper->is_demo || $paper->permission_status !== Paper::PERMISSION_PERMITTED)) {
                                $skipped++;
                                continue;
                            }

                            $paper->update([
                                'publication_status' => $data['publication_status'],
                                'published_at' => $data['publication_status'] === Paper::PUB_PUBLISHED ? ($paper->published_at ?? now()) : $paper->published_at,
                            ]);
                            $updated++;
                        }

                        $msg = "Updated {$updated} paper(s).";
                        if ($skipped > 0) {
                            $msg .= " Skipped {$skipped} demo or non-permitted paper(s).";
                        }
                        Notification::make()->title($msg)->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPapers::route('/'),
            'create' => Pages\CreatePaper::route('/create'),
            'edit' => Pages\EditPaper::route('/{record}/edit'),
        ];
    }
}
