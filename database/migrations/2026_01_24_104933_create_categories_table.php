<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // Name (3 languages)
            $table->string('name_bn', 191)->nullable()->index();
            $table->string('name_en', 191)->nullable()->index();
            $table->string('name_ar', 191)->nullable()->index();

            // Slug (language-agnostic, one per category)
            $table->string('slug', 191)->unique();

            // Descriptions (fallback + per-language)
            $table->text('description')->nullable();        // fallback / legacy
            $table->text('description_bn')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();

            $table->unsignedSmallInteger('sort_order')
                ->default(0)
                ->index();

            $table->boolean('is_active')
                ->default(true)
                ->index();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
