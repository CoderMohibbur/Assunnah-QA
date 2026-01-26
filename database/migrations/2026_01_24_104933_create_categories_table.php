<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            $table->string('name_bn', 191)->index();     // ✅ length fixed
            $table->string('slug', 191)->unique();       // ✅ length fixed

            $table->text('description')->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0)->index(); // ✅ better
            $table->boolean('is_active')->default(true)->index();

            $table->softDeletes(); // ✅ before timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
