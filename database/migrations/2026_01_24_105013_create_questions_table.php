<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete()->index();

            $table->string('slug')->nullable()->unique();

            $table->string('title')->index();
            $table->longText('body_html'); // Phase-2: sanitize করে store হবে

            // ✅ asker info (updated requirement)
            $table->string('asker_name')->index();
            $table->string('asker_phone', 30)->index();
            $table->string('asker_email')->nullable()->index();

            $table->enum('status', ['pending', 'published', 'rejected'])->default('pending')->index();

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
