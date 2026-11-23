<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_page_content', function (Blueprint $table) {
            $table->id();
            $table->string('url_path', 255)->unique(); // normalized e.g. /grade-5/kiswahili/term-2/end-term-exams
            $table->string('page_type', 40)->index(); // grade_hub, learning_area_hub, term_hub, term_landing, national_hub, national_subject, national_year
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->string('h1', 255)->nullable();
            $table->longText('intro')->nullable();
            $table->unsignedInteger('intro_word_count')->default(0);
            $table->json('faq_entries')->nullable(); // [{"question": "...", "answer": "..."}]
            $table->boolean('ai_assisted')->default(false)->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('status', 20)->default('draft')->index(); // draft, review, approved, archived
            $table->string('og_image_path', 255)->nullable();
            $table->boolean('is_demo')->default(false)->index();
            $table->timestamps();

            $table->index(['page_type', 'status', 'is_demo'], 'lpc_type_status_demo_idx');
        });

        Schema::create('slug_history', function (Blueprint $table) {
            $table->id();
            $table->string('old_path', 255)->unique();
            $table->string('new_path', 255);
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->string('model_type', 120)->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->unsignedBigInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['old_path', 'is_active'], 'slug_history_lookup_idx');
            $table->index(['model_type', 'model_id'], 'slug_history_model_idx');
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string'); // string, integer, boolean, json
            $table->string('group', 50)->default('general')->index();
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('slug_history');
        Schema::dropIfExists('landing_page_content');
    }
};
