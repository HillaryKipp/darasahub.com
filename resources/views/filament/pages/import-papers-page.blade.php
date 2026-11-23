<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Step 1: Upload &amp; Preview CSV / Excel File</x-slot>
            <x-slot name="description">
                Every row is validated against official CBC grades, learning areas, terms, exam types, URLs, and existing duplicates before anything is written to the database. Imported papers always land as <strong>draft</strong> and <strong>unverified</strong>.
            </x-slot>

            <form wire:submit="previewImport" class="space-y-4">
                {{ $this->form }}

                <div class="flex items-center gap-3">
                    <x-filament::button type="submit" icon="heroicon-o-magnifying-glass">
                        Validate &amp; Preview
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        @if($this->previewJob)
            @php($job = $this->previewJob)
            <x-filament::section>
                <x-slot name="heading">
                    Step 2: Preview Results (Job #{{ $job->id }} &middot; {{ strtoupper($job->status) }})
                </x-slot>

                <div class="grid gap-4 sm:grid-cols-3 mb-6">
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                        <div class="text-xs text-gray-500">Total Rows</div>
                        <div class="text-2xl font-bold">{{ $job->total_rows }}</div>
                    </div>
                    <div class="p-4 rounded-lg bg-emerald-50 dark:bg-emerald-950 border border-emerald-200 dark:border-emerald-800">
                        <div class="text-xs text-emerald-700 dark:text-emerald-300">Valid Rows Ready</div>
                        <div class="text-2xl font-bold text-emerald-800 dark:text-emerald-200">{{ $job->valid_rows }}</div>
                    </div>
                    <div class="p-4 rounded-lg bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800">
                        <div class="text-xs text-red-700 dark:text-red-300">Validation Errors</div>
                        <div class="text-2xl font-bold text-red-800 dark:text-red-200">{{ $job->error_rows }}</div>
                    </div>
                </div>

                @if(!empty($job->errors))
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-red-700 dark:text-red-400 mb-2">Rows With Validation Errors (will be skipped)</h3>
                        <div class="overflow-x-auto border rounded-lg">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-3 py-2">Row #</th>
                                        <th class="px-3 py-2">Title</th>
                                        <th class="px-3 py-2">Errors</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach($job->errors as $err)
                                        <tr>
                                            <td class="px-3 py-2 font-mono">{{ $err['row'] }}</td>
                                            <td class="px-3 py-2">{{ $err['title'] }}</td>
                                            <td class="px-3 py-2 text-red-600 dark:text-red-400">{{ implode(' | ', $err['messages']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if($job->status === \App\Models\ImportJob::STATUS_PREVIEWED && $job->valid_rows > 0)
                    <div class="flex items-center gap-3">
                        <x-filament::button wire:click="commitImport" color="success" icon="heroicon-o-check-circle">
                            Commit {{ $job->valid_rows }} Valid Row(s) as Drafts
                        </x-filament::button>
                    </div>
                @elseif($job->status === \App\Models\ImportJob::STATUS_COMMITTED)
                    <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm">
                        Committed {{ $job->committed_count }} paper(s) as draft &amp; unverified at {{ $job->committed_at }}.
                    </div>
                @endif
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
