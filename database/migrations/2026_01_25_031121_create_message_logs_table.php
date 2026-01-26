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
        Schema::create('message_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('question_id')->nullable()->index();
            $table->foreign('question_id', 'message_logs_question_id_fk')
                ->references('id')->on('questions')
                ->nullOnDelete();

            $table->string('channel', 20)->index();
            $table->string('to', 191)->index();
            $table->string('template_key', 100)->nullable()->index();
            $table->json('payload')->nullable();
            $table->string('status', 20)->default('queued')->index();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable()->index();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_logs');
    }
};
