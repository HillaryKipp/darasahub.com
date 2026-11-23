<?php

namespace Database\Factories;

use App\Models\LandingPageContent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LandingPageContent>
 */
class LandingPageContentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'url_path' => '/demo-page-'.fake()->unique()->slug(2),
            'page_type' => LandingPageContent::TYPE_TERM_LANDING,
            'meta_title' => 'Demo Landing Page | DarasaHub',
            'meta_description' => 'Demo landing page content record for internal testing only.',
            'h1' => 'Demo Landing Page',
            'intro' => implode(' ', fake()->paragraphs(3)),
            'faq_entries' => null,
            'ai_assisted' => false,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'status' => LandingPageContent::STATUS_DRAFT,
            'is_demo' => true,
        ];
    }

    public function approvedReady(?User $reviewer = null): static
    {
        return $this->state(fn () => [
            'is_demo' => false,
            'status' => LandingPageContent::STATUS_APPROVED,
            'reviewed_by' => $reviewer?->id,
            'reviewed_at' => now(),
        ]);
    }
}
