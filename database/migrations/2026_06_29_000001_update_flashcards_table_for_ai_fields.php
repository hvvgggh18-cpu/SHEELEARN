<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flashcards', function (Blueprint $table) {
            $table->foreignId('deck_id')->nullable()->after('user_id')->constrained('flashcard_decks')->nullOnDelete();
            $table->text('question')->nullable()->after('subject');
            $table->text('answer')->nullable()->after('question');
            $table->text('explanation')->nullable()->after('answer');
            $table->text('example')->nullable()->after('explanation');
            $table->text('mnemonic')->nullable()->after('example');
            $table->text('tags')->nullable()->after('mnemonic');
            $table->string('card_type', 32)->nullable()->after('tags');
            $table->string('source', 40)->nullable()->after('card_type');
            $table->string('source_reference', 200)->nullable()->after('source');
            $table->string('difficulty', 20)->nullable()->after('source_reference');
        });
    }

    public function down(): void
    {
        Schema::table('flashcards', function (Blueprint $table) {
            $table->dropForeign(['deck_id']);
            $table->dropColumn(['deck_id', 'question', 'answer', 'explanation', 'example', 'mnemonic', 'tags', 'card_type', 'source', 'source_reference', 'difficulty']);
        });
    }
};
