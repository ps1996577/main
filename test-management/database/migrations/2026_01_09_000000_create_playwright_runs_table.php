<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playwright_runs', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source', 20)->default('upload'); // upload|api

            $table->string('run_name')->nullable();
            $table->string('branch')->nullable();
            $table->string('commit_sha', 64)->nullable();
            $table->text('ci_url')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->string('status', 20)->default('unknown'); // passed|failed|unknown
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('passed')->default(0);
            $table->unsignedInteger('failed')->default(0);
            $table->unsignedInteger('skipped')->default(0);
            $table->unsignedInteger('flaky')->default(0);

            $table->json('report');

            $table->timestamps();

            $table->index(['created_at']);
            $table->index(['status']);
            $table->index(['source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playwright_runs');
    }
};

