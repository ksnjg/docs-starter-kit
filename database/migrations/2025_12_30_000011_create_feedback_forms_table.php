<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('trigger_type', ['positive', 'negative', 'always'])->default('always');
            $table->json('fields')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('trigger_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_forms');
    }
};
