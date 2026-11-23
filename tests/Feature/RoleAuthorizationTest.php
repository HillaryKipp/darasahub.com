<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\LandingPageContent;
use App\Models\Paper;
use App\Models\SiteSetting;
use App\Models\TakedownRequest;
use App\Models\User;
use Database\Seeders\CurriculumTaxonomySeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SiteSettingSeeder::class, CurriculumTaxonomySeeder::class]);
    }

    public function test_role_policies_enforce_admin_reviewer_and_editor_boundaries(): void
    {
        $admin = User::factory()->admin()->create();
        $reviewer = User::factory()->reviewer()->create();
        $editor = User::factory()->editor()->create();

        $draftPaper = Paper::factory()->create(['is_demo' => false, 'publication_status' => Paper::PUB_DRAFT]);
        $publishedPaper = Paper::factory()->publishedReady()->create();
        $content = LandingPageContent::factory()->create(['is_demo' => false]);

        // Publishing papers
        $this->assertTrue($admin->can('publish', $draftPaper));
        $this->assertTrue($reviewer->can('publish', $draftPaper));
        $this->assertFalse($editor->can('publish', $draftPaper));

        // Editors can edit drafts but NOT published papers
        $this->assertTrue($editor->can('update', $draftPaper));
        $this->assertFalse($editor->can('update', $publishedPaper));

        // Approving landing page content
        $this->assertTrue($reviewer->can('approve', $content));
        $this->assertFalse($editor->can('approve', $content));

        // Admin-only resources (Users, SiteSettings)
        $this->assertTrue($admin->can('viewAny', User::class));
        $this->assertFalse($reviewer->can('viewAny', User::class));
        $this->assertFalse($editor->can('viewAny', SiteSetting::class));

        // Takedown & AuditLog access
        $this->assertTrue($reviewer->can('viewAny', TakedownRequest::class));
        $this->assertFalse($editor->can('viewAny', TakedownRequest::class));
        $this->assertTrue($reviewer->can('viewAny', AuditLog::class));
        $this->assertFalse($editor->can('viewAny', AuditLog::class));
        $this->assertFalse($admin->can('create', AuditLog::class));
    }
}
