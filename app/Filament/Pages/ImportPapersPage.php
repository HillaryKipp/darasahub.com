<?php

namespace App\Filament\Pages;

use App\Models\ImportJob;
use App\Services\PaperImportService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class ImportPapersPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationGroup = 'Content & Papers';

    protected static ?string $navigationLabel = 'Import CSV / Excel';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.import-papers-page';

    public ?array $data = [];

    public ?int $previewJobId = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->is_active ?? false;
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('import_file')
                    ->label('Upload CSV or Excel (.xlsx) File')
                    ->disk('local')
                    ->directory('imports')
                    ->acceptedFileTypes([
                        'text/csv',
                        'text/plain',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ])
                    ->required()
                    ->helperText('Required columns: category, learning_area, year, source, file_url (plus grade, term, exam_type for term papers, or national_exam for national papers). Imports NEVER auto-publish.'),
            ])
            ->statePath('data');
    }

    public function previewImport(PaperImportService $importer): void
    {
        $state = $this->form->getState();
        $storedPath = $state['import_file'] ?? null;

        if (! $storedPath || ! Storage::disk('local')->exists($storedPath)) {
            Notification::make()->title('Please upload a valid CSV or Excel file.')->danger()->send();

            return;
        }

        $fullPath = Storage::disk('local')->path($storedPath);
        $job = $importer->preview($fullPath, basename($storedPath), auth()->id());
        $this->previewJobId = $job->id;

        Notification::make()
            ->title("Preview ready: {$job->valid_rows} valid row(s), {$job->error_rows} error(s).")
            ->color($job->error_rows > 0 ? 'warning' : 'success')
            ->send();
    }

    public function commitImport(PaperImportService $importer): void
    {
        if (! $this->previewJobId) {
            return;
        }

        $job = ImportJob::query()->findOrFail($this->previewJobId);
        $committed = $importer->commit($job, auth()->id());

        Notification::make()
            ->title("Committed {$committed} paper(s) as draft & unverified.")
            ->success()
            ->send();
    }

    public function getPreviewJobProperty(): ?ImportJob
    {
        return $this->previewJobId ? ImportJob::query()->find($this->previewJobId) : null;
    }
}
