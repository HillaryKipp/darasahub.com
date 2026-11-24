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
            'about' => ['About Us', 'Darasahub Holdings Limited is a leading African EdTech company designing, building, and managing digital learning platforms at scale.'],
            'contact' => ['Contact Us', 'Contact Darasahub Holdings Limited for EdTech partnerships, platform delivery, curriculum corrections, or general inquiries.'],
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

        $contactEmail = (string) SiteSetting::getValue('contact_email', 'masomo@darasahub.com');
        $contactPhone = (string) SiteSetting::getValue('contact_phone', '+254 734 232 994');
        $contactAddress = (string) SiteSetting::getValue('contact_address', '1st Floor, Karen Professionals, P.O. Box 20904');

        $schemas = [$this->seo->breadcrumbSchema($breadcrumbs)];
        if (in_array($page, ['about', 'contact'], true)) {
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => 'Darasahub Holdings Limited',
                'alternateName' => $this->seo->siteName(),
                'url' => $this->seo->canonicalUrl($request, '/'),
                'logo' => $this->seo->canonicalUrl($request, '/images/logo.png'),
                'email' => $contactEmail,
                'telephone' => $contactPhone,
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => '1st Floor, Karen Professionals',
                    'postOfficeBoxNumber' => '20904',
                    'addressCountry' => 'KE',
                ],
                'sameAs' => [
                    'https://play.google.com/store/apps/details?id=com.darasadrive.darasadrive_mobile',
                    'https://cbcschemes.co.ke/',
                    'https://easytvethub.com',
                ],
            ];
        }

        $paper = null;
        if ($page === 'copyright-takedown' && $request->filled('paper')) {
            $paper = Paper::query()->publishedPublic()->find($request->integer('paper'));
        }

        return view('legal.'.$page, [
            'pageTitle' => $title,
            'contactEmail' => $contactEmail,
            'contactPhone' => $contactPhone,
            'contactPhoneHref' => 'tel:'.preg_replace('/[^+\d]/', '', $contactPhone),
            'contactAddress' => $contactAddress,
            'siteName' => $this->seo->siteName(),
            'paper' => $paper,
            'breadcrumbs' => $breadcrumbs,
            'seo' => [
                'title' => "{$title} | {$this->seo->siteName()}",
                'description' => $description,
                'canonical' => $canonical,
                'robots' => 'index, follow',
                'schemas' => $schemas,
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
