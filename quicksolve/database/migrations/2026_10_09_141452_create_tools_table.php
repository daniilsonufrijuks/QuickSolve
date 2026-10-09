<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('long_description')->nullable();
            $table->string('icon')->nullable();
            $table->string('access_type')->default('free');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('popularity')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'access_type']);
            $table->index(['is_published', 'is_featured']);
            $table->index(['is_published', 'popularity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
