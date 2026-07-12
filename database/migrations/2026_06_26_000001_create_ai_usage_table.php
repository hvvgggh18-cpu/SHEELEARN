<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan')->index();
            $table->unsignedInteger('used')->default(0);
            $table->unsignedInteger('allowed')->nullable();
            $table->unsignedSmallInteger('reset_interval_hours')->nullable();
            $table->timestamp('last_reset_at')->nullable();
            $table->timestamp('next_reset_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage');
    }
};
