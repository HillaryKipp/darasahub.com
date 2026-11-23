<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 80)->index();
            $table->string('auditable_type', 120)->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['auditable_type', 'auditable_id'], 'audit_logs_auditable_idx');
        });

        Schema::create('import_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('original_filename', 255);
            $table->string('stored_path', 255);
            $table->string('status', 20)->default('pending')->index(); // pending, previewed, committed, failed, cancelled
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('error_rows')->default(0);
            $table->json('preview_data')->nullable();
            $table->json('errors')->nullable();
            $table->unsignedInteger('committed_count')->default(0);
            $table->timestamp('committed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('link_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_id')->constrained('papers')->cascadeOnDelete();
            $table->string('url', 1000);
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('content_type', 120)->nullable();
            $table->string('final_url', 1000)->nullable();
            $table->string('result', 30)->index(); // ok, broken, login_wall, timeout, error, redirect
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at')->useCurrent()->index();
            $table->timestamps();

            $table->index(['paper_id', 'checked_at'], 'link_checks_paper_checked_idx');
        });

        Schema::create('takedown_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_id')->nullable()->constrained('papers')->nullOnDelete();
            $table->string('paper_url', 1000)->nullable();
            $table->string('requester_name', 150);
            $table->string('requester_email', 190);
            $table->string('organization', 190)->nullable();
            $table->string('reason_type', 30)->default('copyright'); // copyright, broken_link, inaccurate, other
            $table->text('details');
            $table->string('status', 30)->default('open')->index(); // open, reviewing, resolved_removed, rejected
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('takedown_requests');
        Schema::dropIfExists('link_checks');
        Schema::dropIfExists('import_jobs');
        Schema::dropIfExists('audit_logs');
    }
};
