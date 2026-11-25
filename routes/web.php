<?php

use App\Http\Controllers\DownloadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\NationalExamController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\TermDirectoryController;
use Illuminate\Support\Facades\Route;

// Secured no-SSH maintenance route (disabled by default via MAINTENANCE_ROUTE_ENABLED=false)
Route::post('/_maintenance/{action}', [MaintenanceController::class, 'run'])
    ->middleware('throttle:10,1')
    ->name('maintenance.run');

// Core, Search, Download & SEO routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/latest', [HomeController::class, 'latest'])->name('latest');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/download/{paperId}', [DownloadController::class, 'download'])
    ->whereNumber('paperId')
    ->middleware('throttle:30,1')
    ->name('papers.download');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/sitemaps/{file}', [SeoController::class, 'sitemapChunk'])
    ->where('file', 'sitemap-[a-z0-9-]+\.xml')
    ->name('sitemap.chunk');

// Legal, trust & takedown pages (Section 19)
Route::get('/{page}', [LegalPageController::class, 'show'])
    ->where('page', 'about|contact|privacy-policy|terms|editorial-policy|copyright-takedown')
    ->name('legal.show');

Route::post('/copyright-takedown', [LegalPageController::class, 'submitTakedown'])
    ->middleware('throttle:5,1')
    ->name('takedown.submit');

// National exams routes (KPSEA, KILEA, KJSEA, KCSE, etc. — constrained to known national exam slugs or configurable pattern)
$nationalConstraint = 'kpsea|kilea|kjsea|kcse';

Route::get('/{examSlug}', [NationalExamController::class, 'examHub'])
    ->where('examSlug', $nationalConstraint)
    ->name('national.hub');

Route::get('/{examSlug}/{areaSlug}-past-papers', [NationalExamController::class, 'subjectLanding'])
    ->where('examSlug', $nationalConstraint)
    ->where('areaSlug', '[a-z0-9-]+')
    ->name('national.subject');

Route::get('/{examSlug}/{areaSlug}-past-papers/{year}', [NationalExamController::class, 'yearLanding'])
    ->where('examSlug', $nationalConstraint)
    ->where('areaSlug', '[a-z0-9-]+')
    ->where('year', '[0-9]{4}')
    ->name('national.year');

Route::get('/{examSlug}/{areaSlug}-past-papers/{year}/{paperSlug}', [NationalExamController::class, 'paperDetail'])
    ->where('examSlug', $nationalConstraint)
    ->where('areaSlug', '[a-z0-9-]+')
    ->where('year', '[0-9]{4}')
    ->where('paperSlug', '[a-z0-9-]+')
    ->name('national.paper');

// Term-based school exams routes (PP1 to Grade 12)
$gradeConstraint = 'pp1|pp2|grade-(?:[1-9]|1[0-2])';

Route::get('/{gradeSlug}', [TermDirectoryController::class, 'gradeHub'])
    ->where('gradeSlug', $gradeConstraint)
    ->name('term.grade');

Route::get('/{gradeSlug}/{areaSlug}', [TermDirectoryController::class, 'learningAreaHub'])
    ->where('gradeSlug', $gradeConstraint)
    ->where('areaSlug', '[a-z0-9-]+')
    ->name('term.area');

Route::get('/{gradeSlug}/{areaSlug}/{termSlug}', [TermDirectoryController::class, 'termHub'])
    ->where('gradeSlug', $gradeConstraint)
    ->where('areaSlug', '[a-z0-9-]+')
    ->where('termSlug', 'term-[1-3]')
    ->name('term.term');

Route::get('/{gradeSlug}/{areaSlug}/{termSlug}/{examTypeSlug}', [TermDirectoryController::class, 'termLanding'])
    ->where('gradeSlug', $gradeConstraint)
    ->where('areaSlug', '[a-z0-9-]+')
    ->where('termSlug', 'term-[1-3]')
    ->where('examTypeSlug', '[a-z0-9-]+')
    ->name('term.landing');

Route::get('/{gradeSlug}/{areaSlug}/{termSlug}/{examTypeSlug}/{paperSlug}', [TermDirectoryController::class, 'paperDetail'])
    ->where('gradeSlug', $gradeConstraint)
    ->where('areaSlug', '[a-z0-9-]+')
    ->where('termSlug', 'term-[1-3]')
    ->where('examTypeSlug', '[a-z0-9-]+')
    ->where('paperSlug', '[a-z0-9-]+')
    ->name('term.paper');
