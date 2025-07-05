<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fedex_label_estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fedex_label_id')->constrained()->onDelete('cascade');
            $table->string('service_type'); // e.g. FEDEX_GROUND
            $table->unsignedInteger('estimate'); // in cents: 1234 = $12.34
            $table->json('origin')->nullable();
            $table->json('destination')->nullable();
            $table->json('package')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fedex_label_estimates');
    }
};
