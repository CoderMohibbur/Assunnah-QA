<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            // ✅ Publish-order serial (NULL until published)
            $table->unsignedBigInteger('published_serial')->nullable()->unique();

            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete()->index();

            $table->string('slug')->nullable()->unique();

            // =========================
            // ✅ Multi-language ready fields
            // =========================
            // Original language indicator (future)
            $table->string('original_lang', 5)->default('bn')->index();

            // Keep existing columns (backward compatible)
            $table->string('title')->index();
            $table->longText('body_html'); // Phase-2: sanitize করে store হবে

            // New i18n columns (nullable for now)
            $table->string('title_bn')->nullable()->index();
            $table->string('title_en')->nullable()->index();
            $table->string('title_ar')->nullable()->index();

            $table->longText('body_html_bn')->nullable();
            $table->longText('body_html_en')->nullable();
            $table->longText('body_html_ar')->nullable();

            // ✅ asker info (updated requirement)
            $table->string('asker_name')->index();
            $table->string('asker_phone', 30)->index();
            $table->string('asker_email')->nullable()->index();

            $table->enum('status', ['pending', 'published', 'rejected'])
                ->default('pending')
                ->index();

            $table->boolean('is_featured')->default(false)->index();
            $table->timestamp('published_at')->nullable()->index();

            $table->unsignedBigInteger('view_count')->default(0)->index();

            // helpful for duplicate detection / matching (optional but useful)
            $table->string('title_hash', 64)->nullable()->index();

            // ✅ Notification tracking (Answer published হলে SMS/Email পাঠানোর জন্য)
            $table->timestamp('answered_notified_at')->nullable()->index();
            $table->unsignedSmallInteger('notify_attempts')->default(0);
            $table->text('notify_last_error')->nullable();

            $table->softDeletes();   // ✅ before timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
