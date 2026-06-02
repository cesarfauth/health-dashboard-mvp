<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_recommendations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('health_record_id')
                ->constrained('health_records')
                ->cascadeOnDelete();

            // 'snapshot' = analysis of a single record;
            // 'trend'    = temporal trend analysis (the differentiator).
            $table->string('type', 20)->default('snapshot')->index();

            // Plain-language summary produced by the LLM.
            $table->text('summary');

            // Structured list of habit recommendations:
            // [{ "title": "...", "detail": "...", "category": "sleep" }, ...]
            $table->json('recommendations');

            // Mandatory medical disclaimer attached to every AI output.
            $table->text('disclaimer');

            // Observability: did this come from the live Claude API or the
            // deterministic fallback, and which model was used.
            $table->string('source', 20)->default('claude'); // claude | fallback
            $table->string('model', 60)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_recommendations');
    }
};
