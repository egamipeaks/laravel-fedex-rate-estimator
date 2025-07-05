<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fedex_labels', function (Blueprint $table) {
            $table->json('tracking_metadata')->nullable();
            $table->string('purchase_order')->nullable();
            $table->string('reference_number')->nullable();
            $table->index(['purchase_order', 'reference_number'], 'fedex_labels_search_idx');
        });
    }
};
