<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->id();

            // 1 প্রশ্নে 1 উত্তর
            $table->foreignId('question_id')
                ->unique()
                ->constrained('questions', 'id', 'answers_question_id_fk')
                ->cascadeOnDelete();

            $table->foreignId('answered_by')
                ->nullable()
                ->constrained('users', 'id', 'answers_answered_by_fk')
                ->nullOnDelete();

            $table->longText('answer_html');
            $table->enum('status', ['draft', 'published'])->default('draft')->index();
            $table->timestamp('answered_at')->nullable()->index();

            $table->softDeletes();   // ✅ before timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
