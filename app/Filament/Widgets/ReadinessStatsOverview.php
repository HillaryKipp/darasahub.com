<?php

namespace App\Filament\Widgets;

use App\Models\LandingPageContent;
use App\Models\Paper;
use App\Models\TakedownRequest;
use App\Services\ReadinessService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ReadinessStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $readiness = app(ReadinessService::class);

        $totalRealPapers = Paper::query()->nonDemo()->count();
        $demoPapers = Paper::query()->where('is_demo', true)->count();
        $awaitingVerification = Paper::query()->nonDemo()->where('verification_status', Paper::VERIFICATION_UNVERIFIED)->count();
        $brokenLinks = Paper::query()->nonDemo()->whereIn('verification_status', [Paper::VERIFICATION_BROKEN, Paper::VERIFICATION_UNAVAILABLE])->count();

        $draftLanding = LandingPageContent::query()->nonDemo()->whereIn('status', [LandingPageContent::STATUS_DRAFT, LandingPageContent::STATUS_REVIEW])->count();
        $approvedLanding = LandingPageContent::query()->approved()->count();

        $failingReadiness = LandingPageContent::query()->nonDemo()->get()
            ->filter(fn (LandingPageContent $c) => ! $readiness->evaluate($c)->ready)
            ->count();

        $singlePaperTermLandings = Paper::query()
            ->verifiedReady()
            ->where('category', Paper::CATEGORY_TERM)
            ->select('grade_id', 'learning_area_id', 'term_id', 'exam_type_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('grade_id', 'learning_area_id', 'term_id', 'exam_type_id')
            ->having('cnt', '=', 1)
            ->get()
            ->count();

        $openTakedowns = TakedownRequest::query()->whereIn('status', [TakedownRequest::STATUS_OPEN, TakedownRequest::STATUS_REVIEWING])->count();

        return [
            Stat::make('Total Papers (Real)', (string) $totalRealPapers)
                ->description("{$demoPapers} demo records isolated"),
            Stat::make('Awaiting Verification', (string) $awaitingVerification)
                ->color($awaitingVerification > 0 ? 'warning' : 'success'),
            Stat::make('Broken / Unavailable Links', (string) $brokenLinks)
                ->color($brokenLinks > 0 ? 'danger' : 'success'),
            Stat::make('Landing Pages (Approved / Draft)', "{$approvedLanding} / {$draftLanding}")
                ->description('Non-demo editorial content'),
            Stat::make('Landing Pages Failing Readiness', (string) $failingReadiness)
                ->color($failingReadiness > 0 ? 'warning' : 'success'),
            Stat::make('Pages With Only 1 Paper', (string) $singlePaperTermLandings)
                ->description('Thin-inventory watch'),
            Stat::make('Open Takedown Requests', (string) $openTakedowns)
                ->color($openTakedowns > 0 ? 'danger' : 'success'),
        ];
    }
}
