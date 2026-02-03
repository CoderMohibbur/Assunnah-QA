<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();

            // মূল ভাষা (bn / en / ar) - ভবিষ্যতে কাজে লাগবে
            $table->string('original_lang', 5)
                ->default('bn')
                ->index();

            // Slug (language-agnostic, একটি পেজের জন্য একটাই)
            $table->string('slug')->unique(); // about, home_featured ইত্যাদি

            // Title (fallback + per-language)
            $table->string('title')->nullable();        // legacy/fallback
            $table->string('title_bn')->nullable();
            $table->string('title_en')->nullable();
            $table->string('title_ar')->nullable();

            // Content (fallback + per-language)
            $table->longText('content_html')->nullable();      // legacy/fallback
            $table->longText('content_html_bn')->nullable();
            $table->longText('content_html_en')->nullable();
            $table->longText('content_html_ar')->nullable();

            $table->boolean('is_active')
                ->default(true)
                ->index();

            $table->softDeletes(); // ✅ before timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
