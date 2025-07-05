<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fedex_labels', function (Blueprint $table) {
            $table->id();

            $table->string('tracking_number')->unique();
            $table->string('status')->nullable();
            $table->boolean('residential')->nullable();

            $table->json('raw_metadata')->nullable();

            $table->timestamps();
        });
    }
};
