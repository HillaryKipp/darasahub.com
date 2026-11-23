<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('papers', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('slug', 255);
            $table->string('category', 20); // term, national
            $table->string('curriculum', 20)->default('cbc'); // cbc, legacy_844

            $table->foreignId('grade_id')->nullable()->constrained('grades')->nullOnDelete();
            $table->foreignId('learning_area_id')->constrained('learning_areas')->restrictOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->foreignId('exam_type_id')->nullable()->constrained('exam_types')->nullOnDelete();
            $table->foreignId('national_exam_id')->nullable()->constrained('national_exams')->nullOnDelete();

            $table->unsignedSmallInteger('year');
            $table->string('paper_number', 30)->nullable();
            $table->string('set_label', 60)->nullable();
            $table->string('file_type', 30)->default('question_paper'); // question_paper, marking_scheme
            $table->foreignId('related_paper_id')->nullable()->constrained('papers')->nullOnDelete();

            $table->text('description')->nullable();

            // Source & rights metadata
            $table->string('source_name', 190)->nullable();
            $table->string('source_url', 1000)->nullable();
            $table->string('permission_status', 20)->default('pending'); // permitted, pending, takedown

            // File hosting metadata
            $table->string('provider', 40)->default('external_link'); // external_link, google_drive, local_storage
            $table->string('external_file_id', 190)->nullable();
            $table->string('file_url', 1000)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            // Verification & availability
            $table->string('availability', 20)->default('available'); // available, unavailable, restricted
            $table->string('verification_status', 20)->default('unverified'); // unverified, verified, broken, unavailable
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();

            // Publishing, metrics & demo isolation
            $table->unsignedBigInteger('download_count')->default(0);
            $table->string('publication_status', 20)->default('draft'); // draft, review, published, archived
            $table->timestamp('published_at')->nullable();
            $table->string('og_image_path', 255)->nullable();
            $table->boolean('is_demo')->default(false);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Composite indexes matching every public URL & readiness query
            $table->index(
                ['category', 'grade_id', 'learning_area_id', 'term_id', 'exam_type_id', 'publication_status', 'is_demo'],
                'papers_term_lookup_idx'
            );
            $table->index(
                ['category', 'national_exam_id', 'learning_area_id', 'year', 'publication_status', 'is_demo'],
                'papers_national_lookup_idx'
            );
            $table->index(
                ['publication_status', 'verification_status', 'permission_status', 'availability', 'is_demo'],
                'papers_readiness_idx'
            );
            $table->index(['slug', 'category', 'is_demo'], 'papers_slug_scope_idx');
            $table->index(['is_demo', 'publication_status', 'download_count'], 'papers_popular_idx');
            $table->index(['is_demo', 'publication_status', 'published_at'], 'papers_latest_idx');
            $table->index(['year', 'curriculum'], 'papers_year_curriculum_idx');
        });

        // Add MySQL/MariaDB FULLTEXT index for fast search without external search engines
        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE papers ADD FULLTEXT INDEX papers_search_fulltext (title, description, source_name)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('papers');
    }
};
