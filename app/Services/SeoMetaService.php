<?php

namespace App\Services;

use App\Models\LandingPageContent;
use App\Models\Paper;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SeoMetaService
{
    public function siteName(): string
    {
        return (string) SiteSetting::getValue('site_name', config('app.name', 'DarasaHub'));
    }

    public function canonicalUrl(Request $request, ?string $cleanPath = null, ?int $currentPage = null): string
    {
        $host = (string) SiteSetting::getValue('canonical_host', config('app.canonical_host', 'darasahub.com'));
        $scheme = app()->environment('production') ? 'https' : $request->getScheme();
        $baseHost = app()->environment('production') ? $host : $request->getHttpHost();

        $path = $cleanPath ?? $request->getPathInfo();
        $path = '/'.ltrim(strtolower(trim($path)), '/');
        $path = $path === '/' ? '' : rtrim($path, '/');

        $url = "{$scheme}://{$baseHost}{$path}";

        // Paginated pages use clean ?page=N; page 1 canonicalizes to base URL (Section 12)
        if ($currentPage !== null && $currentPage > 1) {
            $url .= '?page='.$currentPage;
        }

        return $url;
    }

    /**
     * @return array<string, mixed>
     */
    public function organizationSchema(): array
    {
        $host = (string) SiteSetting::getValue('canonical_host', 'darasahub.com');

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $this->siteName(),
            'url' => 'https://'.$host,
            'logo' => 'https://'.$host.'/images/logo.png',
            'description' => (string) SiteSetting::getValue('site_tagline', 'Kenya CBC Past Papers, Assessments & National Exams Directory'),
        ];
    }

    /**
     * @param  array<int, array{label: string, url: ?string}>  $breadcrumbs
     * @return array<string, mixed>
     */
    public function breadcrumbSchema(array $breadcrumbs): array
    {
        $items = [];
        $position = 1;

        foreach ($breadcrumbs as $crumb) {
            $item = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $crumb['label'],
            ];

            if (! empty($crumb['url'])) {
                $item['item'] = $crumb['url'];
            }

            $items[] = $item;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /**
     * @param  array<int, array{name: string, url: string}>  $items
     * @return array<string, mixed>
     */
    public function collectionPageSchema(string $name, string $description, string $url, array $items = []): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $name,
            'description' => $description,
            'url' => $url,
        ];

        if ($items !== []) {
            $listItems = [];
            foreach (array_values($items) as $idx => $item) {
                $listItems[] = [
                    '@type' => 'ListItem',
                    'position' => $idx + 1,
                    'name' => $item['name'],
                    'url' => $item['url'],
                ];
            }

            $schema['mainEntity'] = [
                '@type' => 'ItemList',
                'numberOfItems' => count($listItems),
                'itemListElement' => $listItems,
            ];
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    public function learningResourceSchema(Paper $paper, string $canonicalUrl): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LearningResource',
            'name' => $paper->title,
            'url' => $canonicalUrl,
            'description' => trim(strip_tags((string) $paper->description)) ?: $paper->title,
            'learningResourceType' => $paper->file_type === Paper::FILE_TYPE_MARKING_SCHEME ? 'Marking Scheme' : 'Past Exam Paper',
            'inLanguage' => 'en-KE',
            'datePublished' => optional($paper->published_at ?? $paper->created_at)->toAtomString(),
            'dateModified' => optional($paper->updated_at)->toAtomString(),
        ];

        if ($paper->grade) {
            $schema['educationalLevel'] = $paper->grade->name;
        }

        if ($paper->learningArea) {
            $schema['about'] = $paper->learningArea->name;
        }

        if ($paper->source_name) {
            $schema['publisher'] = [
                '@type' => 'Organization',
                'name' => $paper->source_name,
            ];
        }

        return $schema;
    }

    /**
     * Emit FAQPage JSON-LD ONLY for real, visible FAQs (Section 10 & 12).
     *
     * @return array<string, mixed>|null
     */
    public function faqSchema(?LandingPageContent $content): ?array
    {
        if (! $content) {
            return null;
        }

        $faqs = $content->visible_faqs;
        if ($faqs === []) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $faq): array => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ], $faqs),
        ];
    }
}
