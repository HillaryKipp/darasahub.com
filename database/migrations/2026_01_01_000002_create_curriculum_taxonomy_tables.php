<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('slug', 80)->unique();
            $table->string('level', 40)->index(); // pre_primary, lower_primary, upper_primary, junior_school, senior_school
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_demo')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('learning_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 140);
            $table->string('slug', 140)->unique();
            $table->string('short_name', 80)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_demo')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('grade_learning_area', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained('grades')->cascadeOnDelete();
            $table->foreignId('learning_area_id')->constrained('learning_areas')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('pathway', 40)->nullable()->index(); // core, stem, social_sciences, arts_and_sports
            $table->string('verification_status', 20)->default('unverified'); // verified, unverified
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['grade_id', 'learning_area_id'], 'gla_grade_area_unique');
            $table->index(['grade_id', 'verification_status', 'sort_order'], 'gla_grade_verified_order_idx');
            $table->index(['learning_area_id', 'grade_id'], 'gla_area_grade_idx');
        });

        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40);
            $table->string('slug', 40)->unique();
            $table->unsignedTinyInteger('number')->unique();
            $table->unsignedTinyInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('exam_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('slug', 60)->unique();
            $table->string('short_label', 40);
            $table->unsignedTinyInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('national_exams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40); // KPSEA, KJSEA, KILEA, KCSE
            $table->string('slug', 40)->unique();
            $table->string('full_name', 190);
            $table->foreignId('grade_id')->nullable()->constrained('grades')->nullOnDelete();
            $table->string('curriculum', 20)->default('cbc')->index(); // cbc, legacy_844, both
            $table->text('description')->nullable();
            $table->string('verification_status', 20)->default('unverified')->index(); // verified, unverified
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_demo')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('national_exams');
        Schema::dropIfExists('exam_types');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('grade_learning_area');
        Schema::dropIfExists('learning_areas');
        Schema::dropIfExists('grades');
    }
};
