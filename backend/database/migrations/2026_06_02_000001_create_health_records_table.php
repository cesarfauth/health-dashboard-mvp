<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_records', function (Blueprint $table) {
            $table->id();

            // Placeholder for the authenticated user. Authentication is out of
            // scope for this MVP, but the column is indexed and ready for it.
            $table->unsignedBigInteger('user_id')->default(1)->index();

            // Biomarkers
            $table->decimal('sleep_hours', 4, 2);      // e.g. 7.50 hours
            $table->unsignedSmallInteger('glucose_level'); // mg/dL
            $table->unsignedSmallInteger('hrv');           // ms (heart rate variability)

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};
