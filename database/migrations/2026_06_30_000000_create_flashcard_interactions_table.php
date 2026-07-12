<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('flashcard_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('deck_id')->nullable()->constrained('flashcard_decks')->onDelete('cascade');
            $table->foreignId('flashcard_id')->nullable()->constrained('flashcards')->onDelete('cascade');
            $table->string('action');
            $table->boolean('correct')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('flashcard_interactions');
    }
};
