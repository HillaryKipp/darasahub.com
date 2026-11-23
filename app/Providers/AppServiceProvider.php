<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\ExamType;
use App\Models\Grade;
use App\Models\GradeLearningArea;
use App\Models\ImportJob;
use App\Models\LandingPageContent;
use App\Models\LearningArea;
use App\Models\NationalExam;
use App\Models\Paper;
use App\Models\SiteSetting;
use App\Models\SlugHistory;
use App\Models\TakedownRequest;
use App\Models\Term;
use App\Models\User;
use App\Observers\LandingPageContentObserver;
use App\Observers\PaperObserver;
use App\Policies\AdminOnlyPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\ImportJobPolicy;
use App\Policies\LandingPageContentPolicy;
use App\Policies\PaperPolicy;
use App\Policies\TakedownRequestPolicy;
use App\Policies\TaxonomyPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paper::observe(PaperObserver::class);
        LandingPageContent::observe(LandingPageContentObserver::class);

        Gate::policy(Paper::class, PaperPolicy::class);
        Gate::policy(LandingPageContent::class, LandingPageContentPolicy::class);
        Gate::policy(TakedownRequest::class, TakedownRequestPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(ImportJob::class, ImportJobPolicy::class);

        foreach ([Grade::class, LearningArea::class, GradeLearningArea::class, Term::class, ExamType::class, NationalExam::class, SlugHistory::class] as $model) {
            Gate::policy($model, TaxonomyPolicy::class);
        }

        foreach ([User::class, SiteSetting::class] as $adminOnlyModel) {
            Gate::policy($adminOnlyModel, AdminOnlyPolicy::class);
        }
    }
}
