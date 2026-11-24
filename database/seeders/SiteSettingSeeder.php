<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['site_name', 'DarasaHub', 'string', 'branding', 'Public site name used in titles, headers, and JSON-LD Organization.'],
            ['site_tagline', 'Kenya CBC Past Papers, Assessments & National Exams Directory', 'string', 'branding', 'Short site description for metadata and footer.'],
            ['canonical_host', 'darasahub.com', 'string', 'seo', 'Enforced canonical host (non-www).'],
            ['contact_email', 'masomo@darasahub.com', 'string', 'legal', 'Public contact and copyright takedown email.'],
            ['contact_phone', '+254 734 232 994', 'string', 'legal', 'Public contact telephone number.'],
            ['contact_address', '1st Floor, Karen Professionals, P.O. Box 20904', 'string', 'legal', 'Physical and postal office address.'],
            ['readiness_min_landing_papers', '1', 'integer', 'readiness', 'Minimum verified, published, permitted papers required on a term/national landing page.'],
            ['readiness_min_landing_intro_words', '0', 'integer', 'readiness', 'Minimum word count for approved editorial intro on term and national landing pages (0 = optional).'],
            ['readiness_min_hub_intro_words', '0', 'integer', 'readiness', 'Minimum word count for approved short intro on hub pages (0 = optional).'],
            ['readiness_min_paper_description_words', '15', 'integer', 'readiness', 'Minimum contextual description words required on a paper detail page to be indexable.'],
            ['readiness_similarity_warning_threshold', '75', 'integer', 'readiness', 'Percentage threshold to flag near-duplicate landing page intros in admin.'],
            ['sitemap_chunk_size', '5000', 'integer', 'seo', 'Maximum URLs per XML sitemap chunk.'],
        ];

        foreach ($defaults as [$key, $value, $type, $group, $description]) {
            SiteSetting::query()->firstOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => $type,
                    'group' => $group,
                    'description' => $description,
                ]
            );
        }
    }
}
