<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('search_indexes', function (Blueprint $table) {
            $table->id();

            // Polymorphic relation: Question, Answer, Category, Page ইত্যাদি
            $table->string('searchable_type');
            $table->unsignedBigInteger('searchable_id');

            // ভাষা: bn, en, ar, bn-latin (Banglish) ইত্যাদি
            $table->string('lang', 10)->index();

            // কোন ফিল্ডের ইনডেক্স: title/body/category ইত্যাদি
            $table->string('field', 50)->nullable()->index();

            // আসল টেক্সট
            $table->text('text');

            // normalized (lowercase, punctuation remove etc.)
            $table->text('text_normalized')->nullable();

            // phonetic / transliterated form (বিশেষ করে Banglish support এর জন্য)
            $table->text('text_phonetic')->nullable();

            $table->timestamps();

            // দ্রুত morph lookup এর জন্য
            $table->index(
                ['searchable_type', 'searchable_id'],
                'search_indexes_morph_idx'
            );

            // একই জিনিসের duplicate ইনডেক্স যেন না হয়
            $table->unique(
                ['searchable_type', 'searchable_id', 'lang', 'field'],
                'search_indexes_unique'
            );

            // Fulltext index (MySQL 8+ এ কাজ করবে)
            $table->fullText(
                ['text', 'text_normalized', 'text_phonetic'],
                'search_indexes_fulltext'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_indexes');
    }
};
