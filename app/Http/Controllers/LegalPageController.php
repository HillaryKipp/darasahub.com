<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Paper;
use App\Models\SiteSetting;
use App\Models\TakedownRequest;
use App\Services\SeoMetaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function __construct(
        private readonly SeoMetaService $seo,
    ) {}

    public function show(Request $request, string $page): View
    {
        $pages = [
            'about' => ['About Us', 'Learn how DarasaHub organizes and verifies Kenyan CBC past papers and national exams.'],
            'contact' => ['Contact Us', 'Get in touch with the DarasaHub editorial team for corrections, submissions, or inquiries.'],
            'privacy-policy' => ['Privacy Policy', 'DarasaHub privacy policy: minimal data collection designed for Kenyan learners and teachers.'],
            'terms' => ['Terms of Use', 'Terms and conditions for using DarasaHub educational resources.'],
            'editorial-policy' => ['Editorial & Verification Policy', 'How DarasaHub verifies exam papers, attributes sources, and maintains curriculum accuracy.'],
            'copyright-takedown' => ['Copyright & Takedown Policy', 'Submit a copyright takedown request or report a broken or inaccurate exam paper.'],
        ];

        abort_unless(isset($pages[$page]), 404);

        [$title, $description] = $pages[$page];
        $canonical = $this->seo->canonicalUrl($request, '/'.$page);
        $breadcrumbs = [
            ['label' => 'Home', 'url' => $this->seo->canonicalUrl($request, '/')],
            ['label' => $title, 'url' => null],
        ];

        $paper = null;
        if ($page === 'copyright-takedown' && $request->filled('paper')) {
            $paper = Paper::query()->publishedPublic()->find($request->integer('paper'));
        }

        return view('legal.'.$page, [
            'pageTitle' => $title,
            'contactEmail' => (string) SiteSetting::getValue('contact_email', 'hello@darasahub.com'),
            'siteName' => $this->seo->siteName(),
            'paper' => $paper,
            'breadcrumbs' => $breadcrumbs,
            'seo' => [
                'title' => "{$title} | {$this->seo->siteName()}",
                'description' => $description,
                'canonical' => $canonical,
                'robots' => 'index, follow',
                'schemas' => [$this->seo->breadcrumbSchema($breadcrumbs)],
            ],
        ]);
    }

    public function submitTakedown(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'paper_id' => ['nullable', 'integer', 'exists:papers,id'],
            'paper_url' => ['nullable', 'url', 'max:1000'],
            'requester_name' => ['required', 'string', 'max:150'],
            'requester_email' => ['required', 'email', 'max:190'],
            'organization' => ['nullable', 'string', 'max:190'],
            'reason_type' => ['required', 'in:copyright,broken_link,inaccurate,other'],
            'details' => ['required', 'string', 'min:15', 'max:3000'],
        ]);

        $takedown = TakedownRequest::create([
            'paper_id' => $validated['paper_id'] ?? null,
            'paper_url' => $validated['paper_url'] ?? null,
            'requester_name' => $validated['requester_name'],
            'requester_email' => $validated['requester_email'],
            'organization' => $validated['organization'] ?? null,
            'reason_type' => $validated['reason_type'],
            'details' => $validated['details'],
            'status' => TakedownRequest::STATUS_OPEN,
            'ip_address' => $request->ip(),
        ]);

        AuditLog::record('takedown.submitted', $takedown, null, $takedown->only(['paper_id', 'reason_type', 'status']));

        return back()->with('status', 'Thank you. Your report/takedown request (#'.$takedown->id.') has been logged for editorial review.');
    }
}
